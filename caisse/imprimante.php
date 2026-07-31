<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');



$postdata = file_get_contents('php://input');
if(isset($postdata)){
	$request = json_decode($postdata);
	$encoded = $request->log;
	$details = $request->infoTicket;
	$infos = $encoded . "=>" . $details;
	$filepath = "log_imprimante.txt";
	if(!file_exists($filepath)){
		file_put_contents($filepath, $infos);
	}else{
		$data = $infos.PHP_EOL;
		$fp = fopen($filepath, 'a');
		fwrite($fp, $data);
	}

}

?>