<?php

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

require 'vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

include 'DBConfig.php';
include 'functions.php';
include 'parametre.php';
require_once __DIR__ . '/caisse/promo/PromoCode.php';

date_default_timezone_set('Indian/Reunion');

function totalCaisseResponse($success, $message = '', array $extra = array())
{
    echo json_encode(array_merge(array(
        'response' => $success ? 1 : 0,
        'plugin' => 0,
        'message' => $message,
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$request = json_decode(file_get_contents('php://input'));
if (!$request || !isset($request->id_caisse)) {
    totalCaisseResponse(false, 'Numéro de caisse manquant.');
}

$id_caisse = (int) $request->id_caisse;
if ($id_caisse <= 0) {
    totalCaisseResponse(false, 'Numéro de caisse invalide.');
}

$today_date = date('Y-m-d');
$id_client = isset($_SESSION['client_id']) ? (int) $_SESSION['client_id'] : 0;
$tickets = $conn->query(
    "SELECT * FROM table_client_ticket WHERE date LIKE '" . $conn->real_escape_string($today_date) . "%' AND id_caisse = $id_caisse"
);
if (!$tickets) {
    totalCaisseResponse(false, 'Impossible de calculer le total caisse.');
}

$total_espece = 0;
$total_cb = 0;
$total_cheques = 0;
$total_ttc = 0;
$total_virement = 0;
$nb_espece = 0;
$nb_cb = 0;
$nb_cheque = 0;
$nb_virement = 0;

while ($ticket = $tickets->fetch_assoc()) {
    $total_espece += (float) $ticket['p_espece_euro'];
    $total_cheques += (float) $ticket['p_cheque_euro'];
    $total_cb += (float) $ticket['p_cb'];
    $total_virement += (float) $ticket['p_restaurant'];
    $total_ttc += (float) $ticket['p_espece_euro'] + (float) $ticket['p_cheque_euro']
        + (float) $ticket['p_cb'] + (float) $ticket['p_restaurant'];

    $nb_espece += (float) $ticket['p_espece_euro'] > 0 ? 1 : 0;
    $nb_cb += (float) $ticket['p_cb'] > 0 ? 1 : 0;
    $nb_cheque += (float) $ticket['p_cheque_euro'] > 0 ? 1 : 0;
    $nb_virement += (float) $ticket['p_restaurant'] > 0 ? 1 : 0;
}

$cat_row = '';
$optionQuery = $conn->query('SELECT siret FROM client LIMIT 1');
$siret = $optionQuery && $optionQuery->num_rows > 0 ? $optionQuery->fetch_assoc()['siret'] : '';
if ($siret === '89919056500049' && $total_ttc > 0) {
    $cat_row = "-----------------------------\n\n        STATISTIQUES PAR CATEGORIE : \n\n";
    $sql = "SELECT SUM((c.pu_euro*c.qte)-c.promo-c.remise-((c.remise_pourcent*c.pu_euro)/100)-c.remise_euro) AS totalByCat,
                   nomcategorie
            FROM table_client_commandes c
            INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
            INNER JOIN table_client_ticket t ON c.id_ticket = t.id_ticket
            WHERE c.date = '$today_date' AND c.id_caisse = $id_caisse
            GROUP BY famille";
    $commandes = $conn->query($sql);
    if ($commandes) {
        while ($commande = $commandes->fetch_assoc()) {
            $totalCategorie = (float) $commande['totalByCat'];
            $pourcentage = formatNumber($totalCategorie * 100 / $total_ttc);
            $nomcategorie = trim(ucfirst($commande['nomcategorie'])) . '(' . $pourcentage . '%)';
            $cat_row .= '    ' . setStringLen($nomcategorie, 25)
                . setStringLen(formatNumber($totalCategorie) . 'EUR', 12) . "\n";
        }
    }
}

$promo = PromoCode::dailySummary($today_date, $id_caisse);
$promo_row = "-----------------------------\n\n        CODE PROMO -1 EUR :\n\n";
$promo_row .= '        TICKETS : ' . $promo['totals']['ticket_count'] . "\n";
$promo_row .= '        PRODUITS : ' . $promo['totals']['product_count'] . "\n";
$promo_row .= '        REMISE : ' . formatNumber($promo['totals']['discount_total']) . " EUR\n";
foreach ($promo['tickets'] as $promoTicket) {
    $ticketLabel = 'T' . $id_caisse . 'W' . $promoTicket['ticket_id'];
    $ticketValue = $promoTicket['product_count'] . ' prod. / -' . formatNumber($promoTicket['discount_total']) . ' EUR';
    $promo_row .= '    ' . setStringLen($ticketLabel, 12) . setStringLen($ticketValue, 25) . "\n";
}

$total_espece = formatNumber($total_espece);
$total_cb = formatNumber($total_cb);
$total_cheques = formatNumber($total_cheques);
$total_virement = formatNumber($total_virement);
$total_ttc = formatNumber($total_ttc);
$date_sortie_ticket = date('d/m/Y H:i:s');
$ligne_virement = $id_client === 22
    ? "TOTAL VIREMENT: $total_virement EUR ($nb_virement)"
    : '';

$total_caisse = "
        CAISSE $id_caisse
        DATE : $date_sortie_ticket

        -----------------------------

        TOTAL ESPECE : $total_espece EUR ($nb_espece)
        TOTAL CB : $total_cb EUR ($nb_cb)
        TOTAL CHEQUE: $total_cheques EUR ($nb_cheque)
        $ligne_virement

        -----------------------------

        TOTAL TTC : $total_ttc EUR

$promo_row
$cat_row
";

file_put_contents(__DIR__ . '/ticket_total_caisse.txt', $total_caisse, LOCK_EX);

try {
    if ($ip_imprimante_ticket !== '' && $port_ticket !== '') {
        $connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
    } elseif (PHP_OS_FAMILY === 'Windows') {
        $connector = new WindowsPrintConnector($imprimante_nom);
    } else {
        $connector = new CupsPrintConnector($imprimante_nom);
    }

    $printer = new Printer($connector);
    $printer->pulse();
    $printer->feed(1);
    $printer->text($total_caisse);
    $printer->feed(4);
    $printer->cut(Printer::CUT_PARTIAL);
    $printer->close();
} catch (Exception $e) {
    totalCaisseResponse(false, 'Total calculé, mais impression impossible : ' . $e->getMessage(), array(
        'ticket' => $total_caisse,
    ));
}

totalCaisseResponse(true, 'Ticket du total caisse imprimé.', array(
    'ticket' => $total_caisse,
    'promo' => $promo['totals'],
));
