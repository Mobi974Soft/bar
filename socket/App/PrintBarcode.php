<?php

namespace App;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;






class PrintBarcode implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {

        if (PHP_OS_FAMILY=="Windows") {
            include ("C:/xampp/htdocs/caisse-backend/parametre.php");
        }else{
            include ("/var/www/localhost/caisse-backend/parametre.php");
        } 
        $this->clients = new \SplObjectStorage;
        $this->ip_imprimante = $ip_imprimante;
        $this->imprimante_codebarre = $imprimante_codebarre;
        $this->port = $port;
    }



    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $connexion, $message) {
    // Valider et filtrer les données reçues du navigateur si nécessaire
        if (PHP_OS_FAMILY=="Windows") {
            try {
                $fichier = "C:/xampp/htdocs/caisse-backend/admin/etiquette.txt";
                file_put_contents($fichier,$message);
                exec('c:\WINDOWS\system32\cmd.exe /c START C:\xampp\htdocs\caisse-backend\admin\imprimer.bat ' . escapeshellarg($this->imprimante_codebarre));
                $connexion->send($message);
                // unlink($fichier);
            } catch (Exception $e) {
                echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;

            }
        }else{
            try {
                $fichier = "/var/www/localhost/caisse-backend/admin/etiquette.txt";
                file_put_contents($fichier,$message);
                $ip_imprimante = $this->ip_imprimante;
                $port = $this->port;
                $imprimante = $this->imprimante_codebarre;
                $type = "";
                if ($port == "" && $ip_imprimante == "") {
                    $type = "usb";
                     exec("sh /var/www/localhost/caisse-backend/admin/imprimer.sh $imprimante $type");
                }else{
                    $type = "network";
                    exec("sh /var/www/localhost/caisse-backend/admin/imprimer.sh $ip_imprimante $port $type");
                }
                
                $connexion->send($message);
                // unlink($fichier);
            } catch (Exception $e) {
                echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;
            }
        } 

    }


    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}