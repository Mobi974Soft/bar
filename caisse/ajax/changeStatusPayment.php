<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../../DBConfig.php';
include '../../functions.php';


$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    if (isset($request->ref,$request->idtable)) {
    	$ref = $request->ref;
    	$idtable = $request->idtable;
    	$sql = "SELECT statut FROM table_client_panier WHERE idtable = $idtable and ref = '$ref'";
    	$query = $conn->query($sql);
    	if ($query->num_rows==1) {
    		echo $query->fetch_assoc()['statut'];
    	}

    }

 }

 ?>