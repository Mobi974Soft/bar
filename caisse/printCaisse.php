<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

include '../DBConfig.php';
include '../functions.php';
include '../parametre.php';
// EN LOCAL
include '../infos.php';
require '../vendor/autoload.php';
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
// SUR CONTABO

$postdata = file_get_contents('php://input');
$idcaisse = $_SESSION['id_caisse'];
if(isset($postdata)){
    $request = json_decode($postdata);
    if(isset($request->printCaisse)){
//        echo successResponse($request->printCaisse,1);
        $monnaieArr = $request->printCaisse;
        $totalCaisse = $request->totalCaculCaisse;
        $ticket_body = "";
        foreach ($monnaieArr as $key => $monnaie){
            $keySplitted = explode("-",$key);
            $newKey = $keySplitted[1] . " " . $keySplitted[0];
            $ticket_body .= $newKey  . " : " . $monnaie . "\n            ";
        }
        $date_sortie_ticket = date('d/m/Y') ." ". date('H:i:s');
        $ticket = "
            CAISSE numero $idcaisse
            DATE : $date_sortie_ticket
            ------------------
            $ticket_body

            ------------------
            TOTAL : $totalCaisse EUR";

        // file_put_contents('ticket_calcul_caisse.txt', $ticket);
        // echo successResponse($ticket, 1,);

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
            $printer -> text($ticket);
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
            $printer -> text($ticket);
            $printer->feed(4);
            $printer->cut();
            $printer->feed(1);
            $printer->close();
            echo json_encode(array('response'=>1,"plugin"=>0));
        }
    }
}


