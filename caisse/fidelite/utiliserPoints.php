<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');


date_default_timezone_set('Indian/Reunion');
include '../../DBConfig.php';
$postdata = file_get_contents('php://input');
if (isset($postdata)) {
	$request = json_decode($postdata);
	$uuid = $request->qrCode;
	$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	$total = $request->total;

	$session = $request->session;
	$id_caisse = $request->id_caisse;
	$id_client = $request->id_client;

	if($id_client==19){
		$uuid = $uuid . "-" . "3hO8QvWNmk1sI7HwE";
		$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	}
	elseif($id_client==25){
		$uuid = $uuid . "-" . "5nP4XtJLoq3yR9KuM";
		$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	}
	elseif($id_client==26){
		$uuid = $uuid . "-" . "8vQ7YwZKtp6XJ3Ns";
		$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	}elseif ($id_client == 15) {
		$uuid = $uuid . "-" . "7RpK2wXmNc4VqJ8Z";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);
	
	} 
	elseif ($id_client == 28) {
		$uuid = $uuid . "-" . "a9GmX4TbQ2wL7pVr";
		$segments = explode("-", $uuid);
		$codemagasin = end($segments);
	
	} 
	elseif($id_client==11){
		$uuid = $uuid . "-" . "2XOpYXGqMQispBCKm";
		$segments = explode("-", $uuid);
	$codemagasin = end($segments);
	}
	$date = date('Y-m-d H:i:s',time());
	$sql = "INSERT INTO `fidelite`(`uuid`, `codemagasin`, `points`,`created_at`) VALUES ('$uuid','$codemagasin','$total','$date')";
	$insert = $conn->query($sql);

	if ($insert) {
		$last_id = $conn->insert_id;
		$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info')->fetch_assoc()['nom_magasin'];
		$timeStamp = time();
		

		// $tickets = $conn->query("SELECT * FROM table_client_panier WHERE `session` = $session AND id_caisse = $id_caisse AND ref != 'totalpanier'");
		// $data = array();
		// if($tickets->num_rows>0){

		// 	while($row = $tickets->fetch_assoc()) {
		// 		// Ajouter les données de chaque ligne au tableau
		// 		$data[] = $row;
		// 	}
		// }
		
		$updatePanier = $conn->query("UPDATE table_client_panier SET fidelite_id = $last_id WHERE `session` = $session AND id_caisse = $id_caisse AND ref != 'totalpanier' ");
		// var_dump("UPDATE table_client_panier SET fidelite_id = $last_id WHERE `session` = $session AND id_caisse = $id_caisse AND ref != 'totalpanier' ");
		// if($updatePanier === TRUE){
			// $filename = dirname(__FILE__)."/tmp/".$nom_magasin."_".$uuid."_".$last_id.".json"; 
				// if(!file_exists($filename)){
					
					

					// if(file_put_contents($filename, json_encode($fidelitearray))){
						$data = array(
							'uuid' => $uuid,
							'codemagasin' => $codemagasin,
							'points' => 0
						);
						$url = 'https://fidelias.fr/fidelite/caisse/getRemise.php';
		
						$ch = curl_init();
		
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		
						$response = curl_exec($ch);
						
						$result = json_decode($response);
						if ($result->response === 0) {
							echo 'Erreur cURL : ' . curl_error($ch);
						} else {
		
							if(isset($result->response) && $result->response == 1) {
								echo $response;
							}
		
						}
						curl_close($ch);
					// }
			// }
		// }




		
	}
}

?>