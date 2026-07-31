<?php
//
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
//header('Access-Control-Allow-Origin: *');
//header("Access-Control-Allow-Credentials: true");
//header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//header('Access-Control-Max-Age: 1000');
//header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
//header("Content-Type:application/json");
include 'DBConfig.php';
//include 'functions.php';
//

$sql = "SELECT * FROM table_client_variable WHERE num = 1";
$query = $conn->query($sql);
$variables = $query->fetch_assoc();
if(isset($_GET['catalogue_ajout'])){
    $local_catalogue_ajout = date('Y-m-d H:i:s',$_GET['catalogue_ajout']);
    $server_catalogue_ajout = $variables['modif_serveur_ajout_catalogue'];
    if($server_catalogue_ajout > $local_catalogue_ajout){
        $sql = "SELECT * FROM table_client_catalogue WHERE dateajout > '$local_catalogue_ajout'";
        $query = $conn->query($sql);
        $data = array();
        while($row = $query->fetch_assoc()){
            $data[] = $row;

        }
        echo json_encode($data);
    }
}

if(isset($_GET['catalogue_modif'])){
    $local_catalogue_modif = date('Y-m-d H:i:s',$_GET['catalogue_modif']);
    $server_catalogue_modif = $variables['modif_serveur_modif_catalogue'];
    if($server_catalogue_modif > $local_catalogue_modif){
        $sql = "SELECT * FROM table_client_catalogue WHERE datemodif > '$local_catalogue_modif'";
        $query = $conn->query($sql);
        $data = array();
        while($row = $query->fetch_assoc()){
            $data[] = $row;

        }
        echo json_encode($data);
    }
}



//if (isset($_GET['catalogue_ajout']){
//  include 'DBConfig.php';
//  $sql = "SELECT * FROM table_client_variable WHERE num = 1";
//  $query = $conn->query($sql);
//  $variables = $query->fetch_assoc();
//  $local_catalogue_ajout = date('Y-m-d H:i:s,'$_GET['catalogue_ajout']);
//  $server_catalogue_ajout = $variables['modif_serveur_ajout_catalogue'];
//  echo json_encode($server_catalogue_ajout,$local_catalogue_ajout);
//  if($server_catalogue_ajout > $local_catalogue_ajout){
//    $sql = "SELECT * FROM table_client_catalogue WHERE dateajout > '$local_date_ajout'";
//    $query = $conn->query($sql);
//    $data = array();
//    while($row = $query->fetch_assoc()){
//      $data[] = $row;
//    }
//    echo json_encode($data);
//  }
//}




?>
