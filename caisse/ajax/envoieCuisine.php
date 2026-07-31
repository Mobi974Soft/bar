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

    if (isset($request->table,$request->id_caisse)) {
    	$table = $request->table;
    	$id_caisse = $request->id_caisse;
    	$sql = "SELECT * FROM table_client_panier WHERE idtable = $table AND id_caisse = $id_caisse AND en_cuisine = 0";
		$articles = $conn->query($sql);

		$numerotable = "";
		$produits = "";
		while ($ligne = $articles->fetch_assoc()) {
			$numerotable = $ligne['idtable'];
			$produits .= $ligne['titre'] . "    x " . $ligne['qte'] . "\n";
			$id_produit = $ligne['num'];
			$update_panier = $conn->query("UPDATE table_client_panier SET en_cuisine = 1 WHERE num = $id_produit");
			
		}

		$sql = "SELECT * FROM table_order WHERE numero_table = $table AND id_caisse = $id_caisse";
		$order = $conn->query($sql);
		$order_info = $order->fetch_assoc()['info_order'];

		$update = $conn->query("UPDATE table_order SET en_cuisine = 1 WHERE numero_table = $table AND id_caisse = $id_caisse ");

		$ticket_cuisine = "


TABLE $numerotable


$produits


Info : $order_info



		";

		echo json_encode(array('response' => 1 , 'ticket' => $ticket_cuisine));


    }
}


 ?>