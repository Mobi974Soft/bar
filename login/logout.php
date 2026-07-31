<?php
include 'dbconfig.php';

if(isset($_GET['id_caisse']) && isset($_GET['userid'])){
    session_start();
    session_destroy();

    $base_url = $_SERVER['SERVER_NAME'];
    $id_caisse = $_GET['id_caisse'];
    $userid = $_GET['userid'];

    $sql = "DELETE FROM id_caisse_used WHERE id_caisse = $id_caisse AND user_id = $userid";
    $free_caisse = $con->query($sql);
    if($free_caisse){
        $url = "http://".$base_url."/caisse-backend/bar/login/";
        header("Location: $url");
    }


}elseif(isset($_GET['userid'],$_GET['action'])){
    session_start();
    session_destroy();

    $base_url = $_SERVER['SERVER_NAME'];
    $userid = $_GET['userid'];
    if($_GET['action'] == "admin"){
        $url = "http://".$base_url."/caisse-backend/bar/login/";
        header("Location: $url");
    }

}
elseif(isset($_GET['user_id_deco'],$_GET['id_caisse'])){

    session_start();
    unset($_SESSION["id_caisse"]);
    $base_url = $_SERVER['SERVER_NAME'];
    $id_caisse = $_GET['id_caisse'];
    $userid = $_GET['user_id_deco'];

    $sql = "DELETE FROM id_caisse_used WHERE id_caisse = $id_caisse AND user_id = $userid";
    $free_caisse = $con->query($sql);
    if($free_caisse){
        $url = "http://".$base_url."/caisse-backend/bar/login/step2.php";
        header("Location: $url");
    }

}





