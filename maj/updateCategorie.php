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

$postdata = file_get_contents('php://input');
if (isset($postdata)) {
	$request = json_decode($postdata);



	if (isset($request->siret)) {

		$con = conDbclient();
		$clientID = $request->siret;
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


		if(isset($request->ajoutCategorie)){
			$categories = $request->ajoutCategorie;
			include('../DBConfig.php');
			foreach($categories as $categorie){
				$nomcategorie = $categorie->nomcategorie;
				$sql = "SELECT nomcategorie FROM table_client_categorie WHERE nomcategorie='$nomcategorie'";
				$check = $conn->query($sql);
				if($check->num_rows==0){
					$branche = $categorie->branche;
					$id_categorie = $categorie->id_categorie;
					$id_parent = $categorie->id_parent;
					$date = time();
					$sql = "INSERT INTO `table_client_categorie`( `nomcategorie`, `branche`, `id_categorie`, `id_parent`) VALUES ('$nomcategorie','$branche','$id_categorie','$id_parent')";

					$insert = $conn->query($sql);
					if($insert){
						$update = $conn->query("UPDATE table_client_variable SET modif_serveur_famille = '$date'  WHERE num = 1");
						if($update){
							echo json_encode(array("response"=>"Categorie mis a jour"));
						}

					}
				}
			}


		}
	}



	

}

?>