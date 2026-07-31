<?php
namespace App;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\EscposImage;




class Printing implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }



    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $connexion, $message) {
    // Valider et filtrer les données reçues du navigateur si nécessaire
    // Exécuter le script PHP pour lancer l'impression
        
        if (PHP_OS_FAMILY=="Windows") {
            include('C://xampp/htdocs/caisse-backend/parametre.php');
        }else{
            include('/var/www/localhost/caisse-backend/parametre.php');
        } 
        try {
            // Créer une connexion vers l'imprimante
         if($ip_imprimante_ticket !== "" && $port_ticket !== ""){
            $connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
        }
        else if (PHP_OS_FAMILY=="Windows") {
            $connector = new WindowsPrintConnector($imprimante_nom);
        }else{
            $connector = new CupsPrintConnector($imprimante_nom);
        } 


        $printer = new Printer($connector);

            // Envoyer les commandes d'impression
        if (PHP_OS_FAMILY=="Windows") {
            $logo = "C:/xampp/htdocs/caisse-backend/tickets/logo.png";
        }else{
            $logo = "/var/www/localhost/caisse-backend/tickets/logo.png";
        } 
        if(is_file($logo) && file_exists($logo)){
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $img = EscposImage::load($logo);
            $printer->bitImage($img);
            $printer->feed(1);
        }
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($message); 
        $printer->feed(3);
        $printer->cut();
            // Utilisez le message reçu pour générer le contenu de l'impression

            // Fermer la connexion avec l'imprimante
        $printer->close();

            // Envoyer une réponse au navigateur indiquant que l'impression a réussi
        $connexion->send("Impression réussie !");
    } catch (\Exception $e) {
            // Gérer les erreurs d'impression
        $connexion->send("Erreur d'impression : " . $e->getMessage());
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