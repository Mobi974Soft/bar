<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header("Content-Type:application/json");


function conDbclient(){
  session_start();
  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'DjlQMvzTdYZwdmXP';
  $DATABASE_PASS = 'lAOy0feUOCvl0jFo';
  $DATABASE_NAME = 'mobipos_clients';
  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

  return $con;
}




if (isset($_GET['siret'])) {

    $con = conDbclient();
    $siret = $_GET['siret'];
    $client_info = $con->query("SELECT * FROM clients WHERE siret = '$siret'");
    $client = $client_info->fetch_assoc();
    $databasename = $client['nom_base'];
    $database_user = $client['userdatabase'];
    $database_pasword = $client['password_database'];

    $HostName = "161.97.64.133";
    $DatabaseName = $databasename;
    $HostUser = $database_user;
    $HostPass =  $database_pasword;
    $conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
    if ($conn->connect_error) {
        echo json_encode(array('message'=>"Connection failed: " . $conn->connect_error));
    }


    $sql = "SELECT * FROM table_client_variable WHERE num = 1";
    $query = $conn->query($sql);
    $variables = $query->fetch_assoc();
    if(isset($_GET['catalogue_ajout'],$_GET['ajout'])){




        $server_catalogue_ajout = $variables['modif_serveur_ajout_catalogue'];
        if($_GET['catalogue_ajout'] == "false"){
            $sql = "SELECT * FROM table_client_catalogue WHERE dateajout < '$server_catalogue_ajout'";
            $query = $conn->query($sql);
            $data = array();
            while($row = $query->fetch_assoc()){
                $data[] = $row;

            }
            echo json_encode(array("dataAjout"=>$data));
        }else{
            $local_catalogue_ajout = date('Y-m-d H:i:s',$_GET['catalogue_ajout']);
        }
        if($server_catalogue_ajout > $local_catalogue_ajout){

            $sql = "SELECT * FROM table_client_catalogue WHERE dateajout > '$local_catalogue_ajout'";
            $query = $conn->query($sql);
            $data = array();
            while($row = $query->fetch_assoc()){
                $data[] = $row;

            }
            echo json_encode(array("dataAjout"=>$data));
        }elseif($server_catalogue_ajout < $local_catalogue_ajout){
          echo json_encode(array('serverAjout'=>$server_catalogue_ajout,"localAjout"=>$local_catalogue_ajout));

      }

  }

  if(isset($_GET['catalogue_modif'],$_GET['modif'])){
    $local_catalogue_modif = date('Y-m-d H:i:s',$_GET['catalogue_modif']);
    $server_catalogue_modif = $variables['modif_serveur_modif_catalogue'];
    if($server_catalogue_modif > $local_catalogue_modif){
        $sql = "SELECT * FROM table_client_catalogue WHERE datemodif > '$local_catalogue_modif' ORDER BY datemodif ASC";
        $query = $conn->query($sql);
        $data = array();
        while($row = $query->fetch_assoc()){
            $data[] = $row;
        }
        echo json_encode(array("dataModif"=>$data));
    }elseif($server_catalogue_modif < $local_catalogue_modif){
        echo json_encode(array('serverModif'=>$server_catalogue_modif,"localModif"=>$local_catalogue_modif));
    }
}
}




// if(isset($_GET['categorieAjout'],$_GET['categorie_ajout'])){
//     $server_categorie_famille = $variables['modif_serveur_famille'];
//      if($_GET['categorieAjout'] == "false"){
//         $sql = "SELECT * FROM table_client_catalogue WHERE dateajout < '$server_categorie_famille'";
//         $query = $conn->query($sql);
//         $data = array();
//         while($row = $query->fetch_assoc()){
//             $data[] = $row;

//         }
//         echo json_encode(array("dataCategorieAjout"=>$data));
//     }else{
//         $local_catalogue_modif = date('Y-m-d H:i:s',$_GET['categorie_ajout']);
//     }
//     if($server_categorie_famille > $local_categorie_famille){

//         $sql = "SELECT * FROM table_client_categorie WHERE dateajout > '$local_catalogue_ajout'";
//         $query = $conn->query($sql);
//         $data = array();
//         while($row = $query->fetch_assoc()){
//             $data[] = $row;

//         }
//         echo json_encode(array("dataAjout"=>$data));
//     }elseif($server_catalogue_ajout < $local_catalogue_ajout){
//       echo json_encode(array('serverAjout'=>$server_catalogue_ajout,"localAjout"=>$local_catalogue_ajout));

// }

// }



?>
