<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include('dbconfig.php');
include('../functions.php');

$postdata = file_get_contents('php://input');

if (isset($postdata)) {
    $request = json_decode($postdata);
    if (isset($request->id_caisse, $request->action)) {
        $id_caisse = $request->id_caisse;
        if ($id_caisse >= 1) {
            $sql = "SELECT id_caisse FROM id_caisse_used WHERE id_caisse = $id_caisse";
            $res = $con->query($sql);
            if ($res->num_rows > 1) {
                echo successResponse("Session active", 1);
            } else {
                session_destroy();
                echo errorResponse("Session inactive, veuillez vous reconnecter svp!", 0);

            }
        }else{
            $sql = "SELECT id_caisse FROM id_caisse_used WHERE id_caisse = $id_caisse";
            $res = $con->query($sql);
            if($res->num_rows == 0){
                header("Location: ../login/");
            }
        }
    }

}
