<?php


if (PHP_OS_FAMILY == "Windows") {
    require 'C://xampp/htdocs/caisse-backend/vendor/autoload.php';
    require 'C://xampp/htdocs/caisse-backend/parametre.php';
} else {
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



$server = new Ratchet\App('localhost', 4000);
$server->route('/print', new Printing, array('*'));
$server->route('/pulse', new Pulse, array('*'));
$server->route('/barcode', new PrintBarcode, array('*'));
$server->route('/avoir', new AvoirImpression, array('*'));
$server->run();

$server->broadcast("New message for all connected clients!");
