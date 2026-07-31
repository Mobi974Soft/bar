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

    if (isset($request->session)) {
    	$numero = $request->session;
    	$id_caisse = $request->id_caisse;
    	$sql = "SELECT info_order FROM table_order WHERE  numero_commande = $numero";
		$articles = $conn->query($sql)->fetch_assoc();
		$json = $articles['info_order'];
		$data = json_decode($json,true);
		$produits = $data['data'];
		$calcultotal = $data['total'];
		$cumul_tva = 0; 
		$total = $data['total']; 
		$total_ht = 0;


		echo json_encode(array('response' => 1 , 'data' => $produits, 'total' => $total, 'cumul_tva' => $cumul_tva, 'total_ht' => $total_ht,'sessionpanier' => 1));


    }
}

 ?>