<?php


if (PHP_OS_FAMILY=="Windows") {
	require 'C://xampp/htdocs/caisse-backend/vendor/autoload.php';
    require 'C://xampp/htdocs/caisse-backend/parametre.php';
}else{
	require '/var/www/localhost/caisse-backend/vendor/autoload.php';
    require '/var/www/localhost/caisse-backend/parametre.php';
}


use App\Printing;
use App\Pulse;
use App\PrintBarcode;
use App\AvoirImpression;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;


if ($idclient==31) {
    $ip = "192.168.1.7";
    $port = 8080;
    $printing = new Printing();
    $pulse = new Pulse();
    $printBarcode = new PrintBarcode();
    $avoirImpression = new AvoirImpression();

    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
            // new Printing(),
            // new Pulse,
                $printBarcode,
                $printing,
                $avoirImpression


            )
        ),
        $port,
        $ip
    );
}else{
    $server = new Ratchet\App('localhost', 8080);
    $server->route('/print', new Printing, array('*'));
    $server->route('/pulse', new Pulse, array('*'));
    $server->route('/barcode', new PrintBarcode, array('*'));
    $server->route('/avoir', new AvoirImpression, array('*'));
}
$server->run();

$server->broadcast("New message for all connected clients!");
