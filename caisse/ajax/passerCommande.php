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

    if (isset($request->id_caisse,$request->numerotable)) {
    	$id_caisse = $request->id_caisse;
    	$table = $request->numerotable;
    	$order_info = htmlspecialchars($request->order_info);
    	$statut = 0;
        $en_cuisine = 1; // 0 = pas en cuisine - 1 = en cours de préparation
    	$date = date('Y-m-d H:i:s');
		$client_id = 1;

		$sql = "SELECT * FROM table_client_panier WHERE id_caisse = $id_caisse AND idtable = $table";
		$query = $conn->query($sql);

		// if($query->num_rows>0){
			$rows = [];
			while ($row = $query->fetch_assoc()) {
				$rows[] = $row;
			}
			$data = json_encode(["data"=>$rows,"total"=>calculTotal($conn, $table, $id_caisse)],JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
			$order_info = $data;
		// }

    	// on recupere le panier qui correspond a la session
    	$sql = "INSERT INTO `table_order`( `statut`, `numero_table`, `id_caisse`, `client_id`, `info_order`, `date`,`en_cuisine`) VALUES ('$statut','$table','$id_caisse',$client_id,'$order_info','$date',$en_cuisine)";
    	$query = $conn->query($sql);

    	if ($query) {

    		$last_id = $conn->insert_id;
    		$sql = "UPDATE table_order SET numero_commande = $last_id WHERE id = $last_id";
    		$update = $conn->query($sql);
    		if ($update) {
    			echo json_encode(array("response" => 1 , "message" => "La commande a bien été envoyé à la cuisine. "  ));
    		}

    		
    	}else{
    		echo json_encode(array("response" => 1 , "message" => "Une erreur c'est produit la commande n'a pas été enregistré. " ));
    	}



    }
}


 ?>