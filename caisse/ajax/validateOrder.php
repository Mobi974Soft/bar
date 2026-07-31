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
    if(isset($request->commande)){
        $numero_commande = $request->commande;
        $sql = "DELETE FROM `table_order` WHERE numero_commande = $numero_commande";
        if($conn->query($sql)){
            echo successResponse("Commande validé ! " , 1);
        }else{
            echo successResponse("Erreur",0);
        }
    }
}

?>