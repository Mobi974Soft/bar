<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

include '../DBConfig.php';
include '../functions.php';


$postdata = file_get_contents('php://input');
if (isset($postdata)) {
	$request = json_decode($postdata);

	$id_caisse = $request->id_caisse;
	$session = $request->session;

	

	$totals = calculTotal($conn,$session,$id_caisse);
	$total = $totals[0] ;

	

        echo json_encode(array("total" => $total));

    }

?>