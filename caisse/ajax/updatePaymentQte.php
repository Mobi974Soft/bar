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
    if(isset($request->ref,$request->table,$request->qteToUpdate)){
        $ref = $request->ref;
        $idtable = $request->table;
        $newQte = $request->qteToUpdate;
        
        $check = $conn->query("SELECT refupdated FROM paiement_quantite WHERE `table` = $idtable AND refupdated = '$ref' ");
        if($check->num_rows>0 && $check){
            $sql = "UPDATE `paiement_quantite` set qte = $newQte WHERE `table` = $idtable AND ref = '$ref' ";
        $query = $conn->query($sql);
        }else{
             $sql = "INSERT INTO `paiement_quantite`(`table`, `qteUpdated`, `refupdated`) VALUES ('$idtable','$newQte','$ref')";
             $query = $conn->query($sql);
        }

        if($query){

            $checkQte = $conn->query("SELECT statut FROM table_client_panier  WHERE ref = '$ref' AND idtable = $idtable")->fetch_assoc()['statut'];
            if($checkQte == 0){
                $conn->query("UPDATE table_client_panier SET statut = 2 WHERE ref = '$ref' AND idtable = $idtable");
            }
            echo json_encode(array('response' => 1 , 'newQte' => $newQte));
        }else{
            echo 0;
        }
    }
}


?>