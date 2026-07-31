<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
 require 'vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

$ticket = "
        *********************
****************************************
******C'IDEAL Magasin Art Discount******
****************************************
        *********************
Chemin LEFAGUYES La Cocoteraie BAT 3
        97440 SAINT ANDRE

       Telephone: 0262 50 18 18
       SIRET: 49205116400019

 Qte*PU   Designation     Mttc   TVA
--------------------------------------
1*14.90  SAC LICORNE    14.90€    8.5%
1*19.90  BOITE PROTECT  19.90€    8.5%

34.8€
      MERCI DE VOTRE VISITE
        ET A TRES BIENTOT
------------------------------------------

ECHANGE OU AVOIR SOUS 72H
MARCHANDISE AVEC EMBALLAGE D'ORIGINE 
INTACT ET TICKET DE CAISSE
";


$connector = new NetworkPrintConnector("localhost", 7000);
$printer = new Printer($connector);
try {
   $printer -> text($ticket);
   $printer -> cut();
} finally {
    $printer -> close();
}
