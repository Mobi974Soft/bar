<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require '../../vendor/autoload.php';

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

date_default_timezone_set('Indian/Reunion');
include '../../DBConfig.php';
include '../../functions.php';
include '../../parametre.php';
include '../../infos.php';
$postdata = file_get_contents('php://input');
if (isset($postdata)) {
	$request = json_decode($postdata);
	$uuid = $request->qrCode;
	$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	$total = $request->total;

	$session = $request->session;
	$id_caisse = $request->id_caisse;
	$id_client = $request->id_client;
	$codeclient = $uuid;
	if ($id_client == 19) {
		$uuid = $uuid . "-" . "3hO8QvWNmk1sI7HwE";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);
	} elseif ($id_client == 25) {
		$uuid = $uuid . "-" . "5nP4XtJLoq3yR9KuM";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);
	}
	elseif ($id_client == 26) {
		$uuid = $uuid . "-" . "8vQ7YwZKtp6XJ3Ns";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);

} 
elseif ($id_client == 28) {
		$uuid = $uuid . "-" . "a9GmX4TbQ2wL7pVr";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);

} 
elseif ($id_client == 15) {
	$uuid = $uuid . "-" . "7RpK2wXmNc4VqJ8Z";
	$segments = explode("-", $uuid);
	$codemagasin = end($segments);

} 
	elseif ($id_client == 11) {
		$uuid = $uuid . "-" . "2XOpYXGqMQispBCKm";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);
	}
	$date = date('Y-m-d H:i:s', time());
	$id_ticket = $conn->query('SELECT id_ticket FROM table_client_ticket order by date DESC LIMIT 1')->fetch_assoc()['id_ticket'];
	$qte = $conn->query('SELECT qte_total FROM table_client_ticket order by date DESC LIMIT 1')->fetch_assoc()['qte_total'];
	$sql = "INSERT INTO `fidelite`(`uuid`, `codemagasin`, `points`,`created_at`,`id_ticket`) VALUES ('$uuid','$codemagasin','$qte','$date','$id_ticket')";
	$insert = $conn->query($sql);

	if ($insert) {
		$last_id = $conn->insert_id;
		$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info')->fetch_assoc()['nom_magasin'];
		$timeStamp = time();


		// $updatePanier = $conn->query("UPDATE table_client_panier SET fidelite_id = $last_id WHERE `session` = $session AND id_caisse = $id_caisse AND ref != 'totalpanier' ");

		// if($updatePanier === TRUE){
		// $filename = dirname(__FILE__)."/tmp/".$nom_magasin."_".$uuid."_".$last_id.".json"; 
		// if(!file_exists($filename)){



		// if(file_put_contents($filename, json_encode($fidelitearray))){
		$data = array(
			'uuid' => $uuid,
			'codemagasin' => $codemagasin,
			'points' => $qte
		);
		$url = 'https://fidelias.fr/fidelite/caisse/ajoutPoint.php';

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

		$response = curl_exec($ch);
		$result = json_decode($response);
		if ($result->response === 0) {
			echo 'Erreur cURL : ' . curl_error($ch);
		} else {

			if (isset($result->response) && $result->response == 1) {
				$data = json_decode($response);
				// var_dump($response);
				$client = $data->client;
                $pointActuel = $data->solde;
                $totalEnPoint = $pointActuel ;
                $ancienSolder = $pointActuel;
                // $newpoint = $data->percentage != 0 ? $pointActuel * ($data->percentage/100) : $pointActuel; 
                // $soldeClient = $totalEnPoint + $newpoint;
				// var_dump($soldeClient,$totalEnPoint);
                // $remise = $data->remise;
                $codemagasin = $data->codemagasin;
                $uuid = $data->uuid;
                $formule = $data->formule_points;

				// $currency = $currency == "EUR" ? "EUR" : $currency;
				if (PHP_OS_FAMILY == "Windows") {
					$logo = "C:/xampp/htdocs/caisse-backend/tickets/logo.png";
				} else {
					$logo = "/var/www/localhost/caisse-backend/tickets/logo.png";
				}
				if (!file_exists($logo)) {
					$magasin = ticketFormatString($magasin, 40);
				} else {
					$magasin = "";
				}

				$ticket_entete = utf8_encode("$magasin
								$adresse
								$adresse2
								
								Telephone: $numero_telephone
								SIRET: $siret
								");

				$date = date('d/m/Y');
				$heure = date('H:i:s');
				$res_ticket = $pointActuel;
				$ticket = "----------------------------------------
$codeclient
CLIENT: $client
$date                       $heure
----------------------------------------

Nombre de points

$res_ticket 
----------------------------------------

";
					file_put_contents('fidelite.txt',$ticket);
			}
		}
		curl_close($ch);
		if ($imprimante_nom == "Printer800") {
			// echo json_encode(array('response' => 1, "plugin" => 1, "ticket" => $total_caisse, 'total_espece' => round($total_espece, 2), 'total_cb' => round($total_cb, 2), 'total_cheques' => round($total_cheques, 2), 'total_ttc' => round($total_ttc, 2)));
		} else {
			if ($ip_imprimante_ticket != "" && $port_ticket != "") {
				$connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
			} elseif (PHP_OS_FAMILY == "Windows") {
				$connector = new WindowsPrintConnector($imprimante_nom);
			} else {
				$connector = new CupsPrintConnector($imprimante_nom);
			}
			$printer = new Printer($connector);
			$printer->feed(1);
			if (PHP_OS_FAMILY=="Windows") {
                $logo = "C:/xampp/htdocs/caisse-backend/tickets/logo.png";
            }else{
                $logo = "/var/www/localhost/caisse-backend/tickets/logo.png";
            } 
			$printer->setTextSize(1, 1);
			if (is_file($logo) && file_exists($logo)) {
				$printer->setJustification(Printer::JUSTIFY_CENTER);
				$img = EscposImage::load($logo);
				$printer->bitImage($img);
				$printer->feed(1);
			} else {
				// $printer->setJustification(Printer::JUSTIFY_CENTER);
				// $printer->text($magasin);
			}
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			// $printer->text(trim($ticket_entete));
			// $printer->feed(1);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->text($ticket);
			$printer->feed();
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->text($ticket_pied);
			$printer->feed(1);
			$printer->cut();
			$printer->close();
			echo $response ;
		}





	}
}
