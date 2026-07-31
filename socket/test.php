<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
require 'vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

var_dump(printer_list(PRINTER_ENUM_LOCAL | PRINTER_ENUM_SHARED));
// try {
//             // Créer une connexion vers l'imprimante
//             $connector = null;
//            $connector = new WindowsPrintConnector("ZDesigner ZD411");// Remplacez par le chemin de votre imprimante
//            $printer = new Printer($connector);

//             // Envoyer les commandes d'impression
//            $printer->setJustification(Printer::JUSTIFY_CENTER);
//            $printer->setTextSize(1,1);
//             $printer->text("dzadadaz dza daz d azdzad azd azd azdaz dazaz
//             dazazzad
//             azdazazd
//             az
//             dazazzadazd
//             azdazazddaz
//             dazazzadazdazd
//             az"); // Utilisez le message reçu pour générer le contenu de l'impression

//             // Fermer la connexion avec l'imprimante
//             $printer->close();
            
//             // Envoyer une réponse au navigateur indiquant que l'impression a réussi
//         } catch (\Exception $e) {
//             echo $e;
//         }
    ?>