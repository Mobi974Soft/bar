<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
require '../vendor/autoload.php';

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

if (PHP_OS_FAMILY == "Windows") {
	$filepath = 'ticket.txt';
	require('C://xampp/htdocs/caisse-backend/parametre.php');
} else {
	$filepath = '/var/www/localhost/caisse-backend/bar/caisse/ticket.txt';
	require('/var/www/localhost/caisse-backend/parametre.php');
}
$dernier_ticket = file_get_contents($filepath);


// REIMPRESSION DERNIER COMMANDES
if (PHP_OS_FAMILY == "Windows") {
	$fichiercommande = "C:/xampp/htdocs/caisse-backend/bar/caisse/commande.txt";
} else {
	$fichiercommande = "/var/www/localhost/caisse-backend/bar/caisse/commande.txt";
}
if (PHP_OS_FAMILY == "Windows") {
	include("C:/xampp/htdocs/caisse-backend/parametre.php");
	try {
		exec('c:\WINDOWS\system32\cmd.exe /c START C:\xampp\htdocs\caisse-backend\bar\tickets\imprimer.bat ' . escapeshellarg($imprimante_codebarre));
		// unlink($fichier);
	} catch (Exception $e) {
		echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;
	}
} else {
	include("/var/www/localhost/caisse-backend/parametre.php");
	try {
		$type = "";
		if ($port == "" && $ip_imprimante == "") {
			$type = "usb";
			exec("sh /var/www/localhost/caisse-backend/bar/tickets/imprimer.sh $imprimante_codebarre $type");
		} else {
			$type = "network";
			exec("sh /var/www/localhost/caisse-backend/bar/tickets/imprimer.sh $ip_imprimante $port $type");
		}

		// unlink($fichier);
	} catch (Exception $e) {
		echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;
	}
}



// FIN

try {
	// Créer une connexion vers l'imprimante
	if ($ip_imprimante_ticket !== "" && $port_ticket !== "") {
		$connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
	} elseif (PHP_OS_FAMILY == "Windows") {
		$connector = new WindowsPrintConnector($imprimante_nom);
	} else {
		$connector = new CupsPrintConnector($imprimante_nom);
	}


	$printer = new Printer($connector);

	// Envoyer les commandes d'impression
	$logo = "../tickets/logo.png";
	if (is_file($logo) && file_exists($logo)) {
		$printer->setJustification(Printer::JUSTIFY_CENTER);
		$img = EscposImage::load($logo);
		$printer->bitImage($img);
		$printer->feed(1);
	}
	$printer->setJustification(Printer::JUSTIFY_CENTER);
	$printer->text($dernier_ticket);
	$printer->feed(2);
	if ($imprimante_nom != "Printer800") {
		$printer->cut();
	} else {
		$printer->cut(Printer::CUT_PARTIAL);
	}
	// Utilisez le message reçu pour générer le contenu de l'impression



	// Envoyer une réponse au navigateur indiquant que l'impression a réussi
} finally {
	echo json_encode(array('response' => 1, "plugin" => 0, 'message' => 'IMPRIME TICKET + OUVERTURE TIROIR CAISSE'));
	$printer->close();
}
