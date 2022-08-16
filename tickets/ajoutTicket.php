<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require '../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$postdata = file_get_contents('php://input');
if (isset($postdata)) {
    $request = json_decode($postdata);

    $totalPanier = $request->total;

    $qte_total = 0;
    $ticket = "";
    $ticket_ligne = "";
    $commandes = [];
    $total_remise_euro = 0;
    $total_remise_pourcent = 0;
    $total_euro_du = 0;
    $totalTTC = 0;
    include('print-ticket.php');
    include '../DBConfig.php';
    session_start();
    $id_caisse = $_SESSION['id_caisse'];
    $session = $_SESSION['session'];
    $sql = "SELECT * FROM table_client_panier WHERE id_caisse = $id_caisse AND session = $session";
    $query = $conn->query($sql);
    while ($ligne = $query->fetch_assoc()) {

        $sessionPanier = $ligne['session'];
        $ref = $ligne['ref'];
        if ($sessionPanier == $session && $ref != "0007") {
            $deconsigne = 0;
            $qte = $ligne['qte'];
            $qte_total += $qte;
            $promo =  $ligne['pu_euro'] - $ligne['promo'];
            $pu_euro = $ligne['pu_euro'];
            $taux_tva = (float)$ligne['taux_tva'];
            $idproduit = $ligne['id_produit'];
            $remise_euro = $ligne['remise_euro'];
            $remise_pourcent = $ligne['remise'];
            $titre = $ligne['titre'];
            $famille = $ligne['famille'];

            $totalTTC += $qte * $pu_euro;
            $total_remise_euro += $remise_euro;
            $remise_pourcents = $remise_pourcent > 0 ? $pu_euro * $qte * ($remise_pourcent / 100) : 0;
            $total_remise_pourcent += $remise_pourcents;
            $taux_tva_ticket = $taux_tva . "%";


            // GENERATION DES COMMANDES

            $commandes[] = (object) array('id_caisse' => $id_caisse, 'pu_euro' => $pu_euro , 'idproduit' => $idproduit, 'qte' => $qte, 'remise' => $remise_euro + $remise_pourcents,  'taux_tva' => $taux_tva, 'famille' => $famille, 'promo' => $promo );
            $prix_total = ($remise_pourcent > 0 ? $pu_euro * $qte * ($remise_pourcent / 100) : ($remise_euro > 0 ? $pu_euro*$qte - $remise_euro : $qte * $pu_euro));
            $prix_total_ticket = number_format((float)$prix_total, 2, '.', '');
            

            $ticket_ligne .= setStringLen($qte . "*" . $pu_euro, $qte_prix_limit) .
            setStringLen($titre, $designiation_limit) .
            setStringLen($prix_total_ticket, $mttc_limit, true) . " " .
            setStringLen($taux_tva_ticket, $tva_limit) . "\n";


//		$ticket_ligne .= sprintf($ticket_layout, strval($qte) . "*" . strval($pu_euro), $titreTicket, strval($pu_euro) . "€", strval($taux_tva_rounded) . "%") . "\n";
            if (strlen($ref) > 9 && strlen($ref) <= 13  ) {
                $sql = "SELECT choix_mode_prix,stock,stock_alerte,num FROM table_client_catalogue WHERE ref = '$ref'";

                $query_res = $conn->query($sql);
                $res = $query_res->fetch_row();
                $mode = $res[0];
                $stock = $res[1];
                $stock_alerte = $res[2];
                $num = $res[3];
                $total_euro_du += ($mode == 3 ? $pu_euro * $qte : ($mode == 2 ? ($pu_euro + ($pu_euro * $taux_tva / 100)) : $pu_euro * $qte));
                // MISE A JOUR DU STOCK
                if ($stock_alerte < 0 && $stock > 0) {
                    $sql = "UPDATE table_client_catalogue 
                    SET stock = stock - $qte , stock_alerte = stock_alerte + $qte 
                    WHERE num = $num";
                    $updateCatalogue = $conn->query($sql);
                }
            }
        }
    }
    $ttc = $totalPanier . "€";
    $ticket .= $ticket_entete . $ticket_corps . $ticket_ligne . "\n" . $ttc . $ticket_pied;
    file_put_contents('ticket.txt', $ticket);

    $retour_article = 0;
    $echange_article = 0;
    $date = date('Y-m-d H:i:s');
    $printTicket = true;

    // RECUPERE LE MONTANT POUR CHAQUE TYPE DE PAIEMENT

    $p_espece = (float)$request->espece;
    $p_cb = (float)$request->cb;
    $p_cheque = (float)$request->cheques;
    $p_restaurant = (float)$request->ticket_restaurant;
    $total_euro = $p_espece + $p_cb + $p_cheque + $p_restaurant;
    $total_remise = $total_remise_euro + $total_remise_pourcent;
    if ($p_espece == 0) {
            $printTicket = false; // ON OUVRE PAS LE TIROIR CAISSE SI PAS DE PAIEMENT EN ESPECE
        }
        $sql = "INSERT INTO table_client_ticket (`id_caisse`, `p_cheque_euro`, `p_cb`, `p_espece_euro`, `p_restaurant`,`total_remise`,`deconsigne`, `retourarticle`, `echangearticle`, `total_euro`, `total_euro_du`, `qte_total`,`date`, `sendserveur`,`id_ticket`) 
        VALUES ($id_caisse,$p_cheque,$p_cb,$p_espece,$p_restaurant,$total_remise,$deconsigne,$retour_article,$echange_article,$total_euro,$total_euro_du,$qte_total, '" . $date . "',1,0)";
            $insertTicket=   $conn->query($sql);
            $last_id = $conn->insert_id;
            if ( $insertTicket == TRUE) {
                foreach ($commandes as $commande) {
                    $idproduit = $commande->idproduit;
                    $qte = $commande->qte;
                    $pu_euro = $commande->pu_euro;
                    $taux_tva = $commande->taux_tva;
                    $famille = $commande->famille;
                    $remise = $commande->remise;
                    $promo = $commande->promo;
                    $d = date('Y-m-d');
                    $newCommandes = $conn->query("INSERT INTO `table_client_commandes`(`id_ticket`, `id_caisse`, `id_produit`, `qte`,`pu_euro`, `promo`, `remise`, `taux_tva`, `famille`, `date`, `sendserveur`) 
                       VALUES ($last_id,$id_caisse,'$idproduit',$qte,$pu_euro,$promo,$remise,$taux_tva,$famille,'$d',1)");
                }

                try {
                 $connector = null;
                 $connector = new WindowsPrintConnector("Receipt Printer");

                 $printer = new Printer($connector);
                    if ($printTicket == true) {
                     $printer -> text($ticket);
                     $printer -> cut();
                     $printer->pulse();
                        echo json_encode(array('response' => 1, 'message' => 'IMPRIME TICKET + OUVERTURE TIROIR CAISSE', 'session' => $session, 'id_caisse' => $id_caisse));
                        exit;
                    } else {
                     $printer -> text($ticket);
                     $printer -> cut();
                        echo json_encode(array('response' => 1, 'message' => 'IMPRIME TICKET SEULEMENT', 'session' => $session, 'id_caisse' => $id_caisse));
                        exit;
                    }
                 $printer->close();
                } catch (Exception $e) {
                    echo json_encode("Impossible d'imprimer sur cette imprimante: " . $e->getMessage() . "\n");
                    exit;
                }

            } else {
                echo json_encode(array('response' => 0, 'message' => 'ERREUR INSERTION TICKET'));
                exit;
            }
        }
