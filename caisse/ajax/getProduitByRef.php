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
    if(isset($request->ref,$request->idtable)){
        $idtable = $request->idtable;
        $ref = $request->ref;
        $id_caisse = $request->id_caisse;
        $sql = "SELECT qte,pu_euro,promo,remise FROM table_client_panier WHERE id_produit = '$ref' AND idtable = $idtable AND id_caisse = $id_caisse";
        $qte = $conn->query($sql)->fetch_assoc()['qte'];
        $pu_euro = $conn->query($sql)->fetch_assoc()['pu_euro'];
        $promo = $conn->query($sql)->fetch_assoc()['promo'];
        $remise = $conn->query($sql)->fetch_assoc()['remise'];
        $prix = $promo > 0 ? $promo : $pu_euro; 
        $prix = $remise > 0 ? $prix - ($prix * ($remise/100)) : $prix;
        if($qte>0){
            echo successResponse("ok" , 1,[$qte,$prix]);
        }else{
            echo successResponse("Erreur de suppresion du client",0);
        }
    }
}

?>