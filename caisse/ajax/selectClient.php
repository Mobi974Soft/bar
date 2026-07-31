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

    if (isset($request->selectClient)) {
    	$id = $request->id;
        $statut = $request->statut;
    	$sql = "SELECT * FROM clients WHERE id = $id";
		$client = $conn->query($sql);
		
        if($client->num_rows>0){
            $current_table = $conn->query('SELECT numero FROM restaurant_tables WHERE status = 1');
            $current_table = $current_table->num_rows>0 ? $current_table->fetch_assoc()['numero'] : false;
            if($current_table!=false){
                $update = $conn->query("UPDATE clients SET idtable = $current_table, statut = 1 WHERE id = $id");
                if($update){
                    echo json_encode(array('response' => 1 ));
                }else{
                    echo json_encode(array('response' => 0 ));
                }
            }

        }



    }
}

 ?>