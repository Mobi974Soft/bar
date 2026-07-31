<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require 'vendor/autoload.php';
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
include 'DBConfig.php';
include 'functions.php';
include 'parametre.php';
date_default_timezone_set('Indian/Reunion');
$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    $id_caisse = $request->id_caisse;
    $today_date = date('Y-m-d');

    session_start();
    $id_client = $_SESSION['client_id'];
    $today_date = "2026-04-22";
    $id_caisse = 1;
    $sql = "SELECT * FROM `table_client_ticket` WHERE date like '%$today_date%' AND id_caisse = $id_caisse";
    var_dump($sql);
    $tickets = $conn->query($sql);
    $nbligne = $tickets->num_rows;

    $total_espece = 0;
    $total_cb = 0;
    $total_cheques = 0;
    $total_ttc = 0;
    $total_virement = 0;

    $nb_espece = 0;
    $nb_cb = 0;
    $nb_cheque = 0;
    $nb_virement = 0;
        $sql = "SELECT siret FROM client LIMIT 1";
        $optionQuery = $conn->query($sql);
        $optionCategorie = 0;
        $siret = $optionQuery->fetch_assoc()['siret'];
        while($ticket = $tickets->fetch_assoc()){
            $total_espece+= $ticket['p_espece_euro'];
            $total_cheques += $ticket['p_cheque_euro'];
            $total_cb += $ticket['p_cb'];
            $total_virement += $ticket['p_restaurant'];
            $total_ttc += $ticket['p_espece_euro'] + $ticket['p_cheque_euro'] + $ticket['p_cb'] + $ticket['p_restaurant'];

            if($ticket['p_espece_euro'] > 0){
                $nb_espece++;
            }
            if($ticket['p_cb'] > 0){
                $nb_cb++;
            }
            if($ticket['p_cheque_euro'] > 0){
                $nb_cheque++;
            }
            if($ticket['p_restaurant']>0){ // si c'est fiesta dragees
                $nb_virement++; 
            }

            
        }
        var_dump("total espece : ".$total_espece);
        var_dump("total cb : ".$total_cb);
        var_dump("total cheque : ".$total_cheques);
        var_dump("total virement : ".$total_virement);
        var_dump("total ttc : ".$total_ttc);
        if ( $siret == "89919056500049") {
            $optionCategorie = 1; // ACTIVE OPTION DE TOTAL PAR CATEGORIE SUR TOTAL CAISSE
            $cat_row = "-----------------------------\n\n"."        STATISTIQUES PAR CATEGORIE : \n\n"; 
            if ($optionCategorie == 1) {
                $sql = "SELECT sum( (c.pu_euro*c.qte)-c.promo-c.remise-( (c.remise_pourcent*c.pu_euro)/100) - c.remise_euro) as totalByCat,nomcategorie FROM `table_client_commandes` c 
                INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
                INNER JOIN table_client_ticket t ON c.id_ticket = t.id_ticket
                WHERE c.date = '$today_date'  AND c.id_caisse = $id_caisse
                GROUP BY famille";
                $commandes = $conn->query($sql);
                if ($commandes->num_rows>0) {
                    while ($commande = $commandes->fetch_assoc()) {

                        $pourcentage = $commande['totalByCat'] * 100 / $total_ttc;
                        $pourcentage = formatNumber($pourcentage);
                        $nomcategorie = trim(ucfirst($commande['nomcategorie'])) . "(".$pourcentage."%)";
                        $total_categorie = formatNumber($commande['totalByCat'])."€";
                        $cat_row .= "    ". setStringLen($nomcategorie,25) .  setStringLen($total_categorie,12) . "\n";
                    }
                    
                }
            }
        }

        $total_ttc = number_format((float)$total_ttc, 2, '.', '');
        $total_cheques = number_format((float)$total_cheques, 2, '.', '');
        $total_espece = number_format((float)$total_espece, 2, '.', '');
        $total_cb = number_format((float)$total_cb, 2, '.', '');
        $total_virement = number_format((float)$total_virement, 2, '.', '');
        $date_sortie_ticket = $today_date." ".date('H:i:s');


        $cat_row = isset($cat_row) ? $cat_row : "";
        $ligne_virement = $id_client == 22 ? "TOTAL VIREMENT: " . $total_virement . " EUR (".$nb_virement.")" : "";
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

        $cat_row
        ";
        var_dump($total_caisse);
        file_put_contents('ticket_total_caisse.txt', $total_caisse);
         echo json_encode(array('response'=>1,"plugin"=>0));die();
        // file_put_contents('ticket_total_caisse.txt', $total_caisse);
        if($imprimante_nom=="Printer800"){
            if($ip_imprimante_ticket !== "" && $port_ticket !== ""){
                $connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
            }
            else if (PHP_OS_FAMILY=="Windows") {
                $connector = new WindowsPrintConnector($imprimante_nom);
            }else{
                $connector = new CupsPrintConnector($imprimante_nom);

            }
            $printer = new Printer($connector);
            $printer->pulse();
            $printer->feed(1);
            $printer -> text($total_caisse);
            $printer->feed(4);
            $printer->cut();
            $printer->feed(1);
            $printer -> cut(Printer::CUT_PARTIAL);
            $printer->close();
            echo json_encode(array('response'=>1,"plugin"=>0));
            // echo json_encode(array('response'=>1,"plugin"=>1,"ticket"=>$total_caisse,'total_espece' => round($total_espece,2),'total_cb'=>round($total_cb,2),'total_cheques'=>round($total_cheques,2),'total_ttc'=>round($total_ttc,2)));
        }else{
            if($ip_imprimante_ticket !== "" && $port_ticket !== ""){
                $connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
            }
            else if (PHP_OS_FAMILY=="Windows") {
                $connector = new WindowsPrintConnector($imprimante_nom);
            }else{
                $connector = new CupsPrintConnector($imprimante_nom);

            }
            $printer = new Printer($connector);
            $printer->pulse();
            $printer->feed(4);
            $printer -> text($total_caisse);
            $printer->feed(4);
            $printer->cut();
            $printer->feed(1);
            $printer->close();
            echo json_encode(array('response'=>1,"plugin"=>0));
        }
        
    
}
