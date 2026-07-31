<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    include ('dbconfig.php');
    $idcaisse = $request->idcaisse;
    if (isset($_SESSION['id'],$_SESSION['client_id'])){
        $user_id = $_SESSION['id'];
        $client_id = $_SESSION['client_id'];
        $sql = "INSERT INTO id_caisse_used(id_caisse,status,user_id,client_id) VALUES($idcaisse,1,$user_id,$client_id)";
        $query = $con->query($sql);

        if($query){
            $_SESSION['id_caisse'] = $idcaisse;
            $_SESSION['session'] = 1;
            $type=2;
            echo json_encode(array('response' => 1, 'session' => $_SESSION,"type"=>$type));
        }
    }

}
