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


if(isset($_POST['nom'],$_POST['prenom'],$_POST['tel'])){
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $tel = $_POST['tel'];
    $adresse = $_POST['adresse'];
    $email = $_POST['email'];
    $date = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO `clients`( `nom`, `prenom`, `email`, `telephone`, `adresse`, `statut`, `idtable`,`created_at`) 
    VALUES ('$nom','$prenom','$email','$tel','$adresse',0,0,'$date')";
    if($conn->query($sql)){
        echo successResponse("Client crée ! " , 1);
    }else{
        echo successResponse("Erreur de création du client",0);
    }
    
    
}

?>