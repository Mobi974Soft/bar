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



	var_dump($siret);
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



		$tickets = $request->tickets;
		$commandes = $request->commandes;
		foreach ($tickets as $ticket) {

			$id_caisse = $ticket->id_caisse;
			$p_cheque_euro = $ticket->p_cheque_euro;
			$p_espece_euro = $ticket->p_espece_euro;
			$p_cb = $ticket->p_cb;
			$p_restaurant = $ticket->p_restaurant;
			$total_remise = $ticket->total_remise;
			$deconsigne = $ticket->deconsigne;
			$retourarticle = $ticket->retourarticle;
			$echangearticle = $ticket->echangearticle;
			$total_euro = $ticket->total_euro;
			$total_euro_du = $ticket->total_euro_du;
			$qte_total = $ticket->qte_total;
			$date = $ticket->date;

			$sql = "INSERT INTO `table_client_ticket`(`id_caisse`, `p_cheque_euro`, `p_cb`, `p_espece_euro`, `p_restaurant`, `total_remise`, `deconsigne`, `retourarticle`, `echangearticle`, `total_euro`, `total_euro_du`, `qte_total`, `date`, `sendserveur`, `id_ticket`) VALUES ($id_caisse,$p_cheque_euro,$p_cb,$p_espece_euro,$p_restaurant,$total_remise,$deconsigne,$retourarticle,$echangearticle,$total_euro,$total_euro_du,$qte_total,'$date',0,0)";
			$insert = $conn->query($sql);
			var_dump($sql,$insert);die();
			if($insert){
				$lastid = $conn->insert_id;
				foreach ($commandes as $commande) {
					if($commande->id_ticket == $ticket->id_ticket){
						$idproduit = $commande->id_produit;
						$qte = $commande->qte;
						$pu_euro = $commande->pu_euro;
						$taux_tva = $commande->taux_tva;
						$famille = $commande->famille;
						$remise = $commande->remise;
						$promo = $commande->promo > 0 ? $pu_euro * $qte - $commande->promo * $qte : 0  ;
						$d = date('Y-m-d');

						$newCommandes = $conn->query("INSERT INTO `table_client_commandes`(`id_ticket`, `id_caisse`, `id_produit`, `qte`,`pu_euro`, `promo`, `remise`, `taux_tva`, `famille`, `date`, `sendserveur`) 
							VALUES ($lastid,$id_caisse,'$idproduit',$qte,$pu_euro,$promo,$remise,$taux_tva,$famille,'$d',0)");
					}
				}

				$updateTicketCommandes = $conn->query("UPDATE table_client_ticket SET id_ticket = $lastid WHERE id = $lastid");
				if($updateTicketCommandes){
					echo $ticket->id_ticket;
				}
			}
		}
	}


	
}


?>