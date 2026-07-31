<?php

/**
 * Promotion temporaire de 1 EUR par article, sans migration SQL.
 *
 * Les lignes du panier utilisent la colonne remise_euro déjà existante. L'état
 * permettant d'annuler la promotion et les statistiques journalières sont
 * conservés dans des fichiers JSON protégés par flock().
 */
class PromoCode
{
    const CODE = 'PROMO_1_EURO';
    const LABEL = 'Code promo -1 EUR/article';
    const UNIT_AMOUNT = 1.00;

    // Période métier à modifier ici si les dates définitives changent.
    const START_AT = '2026-08-01 00:00:00';
    const END_AT = '2026-08-31 23:59:59';

    public static function timezone()
    {
        return new DateTimeZone('Indian/Reunion');
    }

    public static function now()
    {
        return new DateTimeImmutable('now', self::timezone());
    }

    public static function isAvailable(DateTimeImmutable $now = null)
    {
        $now = $now ?: self::now();
        $start = new DateTimeImmutable(self::START_AT, self::timezone());
        $end = new DateTimeImmutable(self::END_AT, self::timezone());

        return $now >= $start && $now <= $end;
    }

    public static function periodMessage(DateTimeImmutable $now = null)
    {
        $now = $now ?: self::now();
        $start = new DateTimeImmutable(self::START_AT, self::timezone());
        $end = new DateTimeImmutable(self::END_AT, self::timezone());

        if ($now < $start) {
            return 'Le code promo sera disponible à partir du ' . $start->format('d/m/Y') . '.';
        }
        if ($now > $end) {
            return 'Le code promo a expiré le ' . $end->format('d/m/Y') . '.';
        }

        return '';
    }

    public static function dataDirectory()
    {
        $override = getenv('PROMO_CODE_DATA_DIR');
        return $override !== false && $override !== ''
            ? rtrim($override, DIRECTORY_SEPARATOR)
            : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'promo-code';
    }

    public static function cartKey($idCaisse, $idTable)
    {
        return max(0, (int) $idCaisse) . '_' . max(0, (int) $idTable);
    }

    public static function statePath($idCaisse, $idTable)
    {
        return self::dataDirectory() . DIRECTORY_SEPARATOR . 'active' . DIRECTORY_SEPARATOR
            . self::cartKey($idCaisse, $idTable) . '.json';
    }

    public static function getState($idCaisse, $idTable)
    {
        return self::readJson(self::statePath($idCaisse, $idTable));
    }

    public static function getStatus($idCaisse, $idTable, DateTimeImmutable $now = null)
    {
        $state = self::getState($idCaisse, $idTable);
        return array(
            'available' => self::isAvailable($now),
            'active' => is_array($state) && isset($state['code']) && $state['code'] === self::CODE,
            'message' => self::periodMessage($now),
            'state' => $state,
        );
    }

    public static function itemFromState(array $state = null, $num = null)
    {
        if (!$state || !isset($state['items']) || !is_array($state['items'])) {
            return null;
        }
        $key = (string) $num;
        return isset($state['items'][$key]) ? $state['items'][$key] : null;
    }

    public static function apply(mysqli $conn, $idCaisse, $idTable)
    {
        $idCaisse = (int) $idCaisse;
        $idTable = (int) $idTable;
        if (!self::isAvailable()) {
            throw new RuntimeException(self::periodMessage());
        }
        if (self::getState($idCaisse, $idTable)) {
            throw new RuntimeException('Un code promo est déjà appliqué à ce panier.');
        }

        $sql = "SELECT num, ref, titre, qte, pu_euro, promo, remise, remise_euro, retour
                FROM table_client_panier
                WHERE id_caisse = $idCaisse AND idtable = $idTable AND ref <> 'remise'";
        $result = $conn->query($sql);
        if (!$result || $result->num_rows === 0) {
            throw new RuntimeException('Le panier est vide. Ajoutez au moins un produit avant la promo.');
        }

        $state = self::newState($idCaisse, $idTable);
        $updates = array();
        while ($row = $result->fetch_assoc()) {
            if (!self::isSaleRow($row)) {
                continue;
            }
            $item = self::buildItem($row);
            if ($item['unit_discount'] <= 0) {
                continue;
            }
            $state['items'][(string) $row['num']] = $item;
            $updates[] = array(
                'num' => (int) $row['num'],
                'remise_euro' => (float) $row['remise_euro'] + $item['unit_discount'],
            );
            $state['product_count_at_apply'] += (int) $row['qte'];
            $state['discount_at_apply'] += $item['unit_discount'] * (int) $row['qte'];
        }

        if (!$updates) {
            throw new RuntimeException('Aucun produit du panier ne peut recevoir cette promotion.');
        }

        $state['discount_at_apply'] = round($state['discount_at_apply'], 2);

        $conn->begin_transaction();
        try {
            foreach ($updates as $update) {
                $num = $update['num'];
                $discount = number_format($update['remise_euro'], 2, '.', '');
                if (!$conn->query("UPDATE table_client_panier SET remise_euro = $discount WHERE num = $num AND id_caisse = $idCaisse AND idtable = $idTable")) {
                    throw new RuntimeException('Impossible de mettre à jour le produit ' . $num . '.');
                }
            }
            self::writeJson(self::statePath($idCaisse, $idTable), $state, false);
            // Le fichier journalier doit exister avant le premier encaissement.
            // Ainsi, une promo ne peut pas être appliquée si son suivi n'est pas
            // inscriptible sur cette installation.
            self::initializeDailyReport();
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            @unlink(self::statePath($idCaisse, $idTable));
            throw $e;
        }
        return $state;
    }

    public static function undo(mysqli $conn, $idCaisse, $idTable)
    {
        $idCaisse = (int) $idCaisse;
        $idTable = (int) $idTable;
        $state = self::getState($idCaisse, $idTable);
        if (!$state) {
            throw new RuntimeException('Aucun code promo actif sur ce panier.');
        }
        if (!empty($state['used_ticket_ids'])) {
            throw new RuntimeException('La promo figure déjà sur un ticket. Elle ne peut plus être annulée.');
        }

        $conn->begin_transaction();
        try {
            foreach ($state['items'] as $num => $item) {
                $num = (int) $num;
                $original = number_format((float) $item['original_remise_euro'], 2, '.', '');
                if (!$conn->query("UPDATE table_client_panier SET remise_euro = $original WHERE num = $num AND id_caisse = $idCaisse AND idtable = $idTable")) {
                    throw new RuntimeException('Impossible de restaurer le produit ' . $num . '.');
                }
            }
            $conn->commit();
            self::clearState($idCaisse, $idTable);
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        return true;
    }

    /** Applique automatiquement la promo à une ligne ajoutée après le clic initial. */
    public static function applyToCartRow(mysqli $conn, $idCaisse, $idTable, $num)
    {
        $idCaisse = (int) $idCaisse;
        $idTable = (int) $idTable;
        $num = (int) $num;
        $state = self::getState($idCaisse, $idTable);
        if (!$state || isset($state['items'][(string) $num])) {
            return false;
        }

        $result = $conn->query("SELECT num, ref, titre, qte, pu_euro, promo, remise, remise_euro, retour
                                FROM table_client_panier
                                WHERE num = $num AND id_caisse = $idCaisse AND idtable = $idTable LIMIT 1");
        if (!$result || $result->num_rows !== 1) {
            return false;
        }
        $row = $result->fetch_assoc();
        if (!self::isSaleRow($row)) {
            return false;
        }
        $item = self::buildItem($row);
        if ($item['unit_discount'] <= 0) {
            return false;
        }
        $newDiscount = number_format((float) $row['remise_euro'] + $item['unit_discount'], 2, '.', '');
        if (!$conn->query("UPDATE table_client_panier SET remise_euro = $newDiscount WHERE num = $num")) {
            return false;
        }
        $state['items'][(string) $num] = $item;
        try {
            self::writeJson(self::statePath($idCaisse, $idTable), $state, false);
        } catch (Exception $e) {
            $original = number_format((float) $row['remise_euro'], 2, '.', '');
            $conn->query("UPDATE table_client_panier SET remise_euro = $original WHERE num = $num");
            throw $e;
        }
        return true;
    }

    public static function recordUsage($ticketId, $idCaisse, $idTable, $productCount, $discountTotal)
    {
        $ticketId = (int) $ticketId;
        $idCaisse = (int) $idCaisse;
        $idTable = (int) $idTable;
        $productCount = (int) $productCount;
        $discountTotal = round((float) $discountTotal, 2);
        if ($ticketId <= 0 || $productCount <= 0 || $discountTotal <= 0) {
            return false;
        }

        $now = self::now();
        $path = self::dailyPath($now->format('Y-m-d'));
        self::mutateJson($path, function ($data) use ($ticketId, $idCaisse, $idTable, $productCount, $discountTotal, $now) {
            if (!is_array($data)) {
                $data = self::emptyDailyReport($now->format('Y-m-d'));
            }
            foreach ($data['tickets'] as $ticket) {
                if ((int) $ticket['ticket_id'] === $ticketId && (int) $ticket['id_caisse'] === $idCaisse) {
                    return $data;
                }
            }
            $data['tickets'][] = array(
                'ticket_id' => $ticketId,
                'id_caisse' => $idCaisse,
                'table' => $idTable,
                'created_at' => $now->format(DateTime::ATOM),
                'product_count' => $productCount,
                'discount_total' => $discountTotal,
            );
            $data['totals']['ticket_count']++;
            $data['totals']['product_count'] += $productCount;
            $data['totals']['discount_total'] = round($data['totals']['discount_total'] + $discountTotal, 2);
            return $data;
        });

        $statePath = self::statePath($idCaisse, $idTable);
        self::mutateJson($statePath, function ($state) use ($ticketId) {
            if (!is_array($state)) {
                return $state;
            }
            if (!isset($state['used_ticket_ids']) || !is_array($state['used_ticket_ids'])) {
                $state['used_ticket_ids'] = array();
            }
            if (!in_array($ticketId, $state['used_ticket_ids'], true)) {
                $state['used_ticket_ids'][] = $ticketId;
            }
            return $state;
        });
        return true;
    }

    public static function initializeDailyReport(DateTimeImmutable $now = null)
    {
        $now = $now ?: self::now();
        $date = $now->format('Y-m-d');
        return self::mutateJson(self::dailyPath($date), function ($data) use ($date) {
            return is_array($data) ? $data : self::emptyDailyReport($date);
        });
    }

    public static function dailySummary($date, $idCaisse = null)
    {
        $data = self::readJson(self::dailyPath($date));
        $summary = self::emptyDailyReport($date);
        if (!$data || empty($data['tickets'])) {
            return $summary;
        }
        foreach ($data['tickets'] as $ticket) {
            if ($idCaisse !== null && (int) $ticket['id_caisse'] !== (int) $idCaisse) {
                continue;
            }
            $summary['tickets'][] = $ticket;
            $summary['totals']['ticket_count']++;
            $summary['totals']['product_count'] += (int) $ticket['product_count'];
            $summary['totals']['discount_total'] = round(
                $summary['totals']['discount_total'] + (float) $ticket['discount_total'],
                2
            );
        }
        return $summary;
    }

    public static function clearState($idCaisse, $idTable)
    {
        $path = self::statePath($idCaisse, $idTable);
        return !file_exists($path) || @unlink($path);
    }

    public static function clearCaisseStates($idCaisse)
    {
        $pattern = self::dataDirectory() . DIRECTORY_SEPARATOR . 'active' . DIRECTORY_SEPARATOR
            . max(0, (int) $idCaisse) . '_*.json';
        $success = true;
        foreach (glob($pattern) ?: array() as $path) {
            if (is_file($path) && !@unlink($path)) {
                $success = false;
            }
        }
        return $success;
    }

    private static function newState($idCaisse, $idTable)
    {
        return array(
            'version' => 1,
            'code' => self::CODE,
            'label' => self::LABEL,
            'unit_amount' => self::UNIT_AMOUNT,
            'id_caisse' => (int) $idCaisse,
            'table' => (int) $idTable,
            'applied_at' => self::now()->format(DateTime::ATOM),
            'product_count_at_apply' => 0,
            'discount_at_apply' => 0,
            'used_ticket_ids' => array(),
            'items' => array(),
        );
    }

    private static function buildItem(array $row)
    {
        $basePrice = (float) $row['promo'] > 0 ? (float) $row['promo'] : (float) $row['pu_euro'];
        $afterPercent = $basePrice * (1 - min(100, max(0, (float) $row['remise'])) / 100);
        $remaining = max(0, $afterPercent - max(0, (float) $row['remise_euro']));
        return array(
            'num' => (int) $row['num'],
            'ref' => (string) $row['ref'],
            'title' => (string) $row['titre'],
            'original_remise_euro' => (float) $row['remise_euro'],
            'unit_discount' => round(min(self::UNIT_AMOUNT, $remaining), 2),
        );
    }

    private static function isSaleRow(array $row)
    {
        $retour = strtolower((string) $row['retour']);
        return $row['ref'] !== 'remise' && $retour !== 'true' && $retour !== '1';
    }

    private static function dailyPath($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException('Date de rapport invalide.');
        }
        return self::dataDirectory() . DIRECTORY_SEPARATOR . $date . '.json';
    }

    private static function emptyDailyReport($date)
    {
        return array(
            'version' => 1,
            'date' => $date,
            'promotion' => array(
                'code' => self::CODE,
                'label' => self::LABEL,
                'unit_amount' => self::UNIT_AMOUNT,
            ),
            'totals' => array(
                'ticket_count' => 0,
                'product_count' => 0,
                'discount_total' => 0,
            ),
            'tickets' => array(),
        );
    }

    private static function ensureDirectory($path)
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de créer le dossier JSON de la promotion : ' . $directory);
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('Le dossier JSON de la promotion n’est pas accessible en écriture : ' . $directory);
        }
    }

    private static function readJson($path)
    {
        if (!is_file($path)) {
            return null;
        }
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return null;
        }
        flock($handle, LOCK_SH);
        $contents = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        $data = json_decode($contents, true);
        return is_array($data) ? $data : null;
    }

    private static function writeJson($path, array $data, $merge)
    {
        self::ensureDirectory($path);
        $handle = fopen($path, 'c+');
        if (!$handle) {
            throw new RuntimeException('Impossible d’ouvrir le fichier JSON de la promotion : ' . $path);
        }
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException('Impossible de verrouiller le fichier JSON de la promotion : ' . $path);
        }
        if ($merge) {
            rewind($handle);
            $existing = json_decode(stream_get_contents($handle), true);
            if (is_array($existing)) {
                $data = array_replace_recursive($existing, $data);
            }
        }
        ftruncate($handle, 0);
        rewind($handle);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false || fwrite($handle, $json) !== strlen($json)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw new RuntimeException('Impossible d’enregistrer le fichier JSON de la promotion : ' . $path);
        }
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private static function mutateJson($path, callable $mutator)
    {
        self::ensureDirectory($path);
        $handle = fopen($path, 'c+');
        if (!$handle) {
            throw new RuntimeException('Impossible d’ouvrir le fichier JSON de la promotion : ' . $path);
        }
        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            throw new RuntimeException('Impossible de verrouiller le fichier JSON de la promotion : ' . $path);
        }
        rewind($handle);
        $data = json_decode(stream_get_contents($handle), true);
        $data = $mutator(is_array($data) ? $data : null);
        if (is_array($data)) {
            ftruncate($handle, 0);
            rewind($handle);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false || fwrite($handle, $json) !== strlen($json)) {
                flock($handle, LOCK_UN);
                fclose($handle);
                throw new RuntimeException('Impossible d’enregistrer le fichier JSON de la promotion : ' . $path);
            }
            fflush($handle);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
        return $data;
    }
}
