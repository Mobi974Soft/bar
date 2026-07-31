<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
header("Content-Type: application/json");

function conDbclient(){
	session_start();
	$DATABASE_HOST = 'localhost';
	$DATABASE_USER = 'DjlQMvzTdYZwdmXP';
	$DATABASE_PASS = 'lAOy0feUOCvl0jFo';
	$DATABASE_NAME = 'mobipos_clients';
	$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

	return $con;
}



if (!isset($_GET['password'])) {
	echo json_encode('Veuillez entrez le mot de passe !');
	die();
} else {
	$con = conDbclient();
	if ($stmt = $con->prepare('SELECT id, password,client_id,role FROM user WHERE password = ?')) {
		$password_entry = md5($_GET['password']);
		$stmt->bind_param('s', $password_entry);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows > 0) {
			$stmt->bind_result($id, $password,$client_id,$role);
			$stmt->fetch();
			if ($password_entry == $password) {
				session_regenerate_id();
				$_SESSION['loggedin'] = TRUE;
				$_SESSION['id'] = $id;
				$_SESSION['client_id'] = $client_id;
				$_SESSION['user_id'] = $id;
				$_SESSION['role'] = $role;
				


				$password = htmlspecialchars($_GET['password']);
				$array = array(
					'response' => 1 ,'loggedin' => TRUE, 'client_id' => $client_id
				);
				echo $_GET['callback']."(".json_encode($array).")";
			} else {
				$array = array(
					'response'=>0,"message"=>'Code d\'accès incorrect'
				);
				echo $_GET['callback']."(".json_encode($array).")";
				
			}
		} else {
			$array = array(
				'response'=>0,"message"=>'Code d\'accès incorrect'
			);
			echo $_GET['callback']."(".json_encode($array).")";
		}
		$stmt->close();
	}
}




?>


