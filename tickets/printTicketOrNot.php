<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

include '../DBConfig.php';
include '../infos.php';
// SUR CONTABO
$postdata = file_get_contents('php://input');
if(isset($postdata)){
	$sql = "SELECT status FROM client_options WHERE client_id = $client_id AND option_id = 3";
	$query = $con->query($sql);
	if ($query->fetch_assoc()['status'] == 1) {
		echo 1;
	}else{
		echo 0;
	}

}

 ?>