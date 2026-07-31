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

$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    if(isset($request->num,$request->idtable,$request->newQte)){
        $num = $request->num;
        $idtable = $request->idtable;
        $newQte = $request->newQte;
        $id_caisse = $request->id_caisse;
        
        $sql = "UPDATE table_client_panier SET qte = $newQte WHERE num = $num  AND id_caisse = $id_caisse";
        $query = $conn->query($sql);
        if($query){
            $updatedQte = $conn->query("SELECT qte FROM table_client_panier  WHERE num = $num  AND id_caisse = $id_caisse")->fetch_assoc()['qte'];
            echo json_encode(array('response' => 1 , 'newQte' => $updatedQte));
        }else{
            echo 0;
        }
    }
}


?>