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
		$clientID = $request->idclient;
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


		if(isset($request->ajoutCatalogue)){
			$articles = $request->ajoutCatalogue;
			foreach ($articles as $item) {
				$gencode = $item->ref;
				$sql = "SELECT ref FROM table_client_catalogue WHERE ref=$gencode";
				$check = $conn->query($sql);
				if($check->num_rows==0){
					$famille = $item->cath;
					$id_produit = $item->id;
					$designation = $item->titre;
					$prix = $item->prixttc_euro;
					$promottc = $item->prixttc_promo_euro;
					$codetva = $item->code_tva;
					$promo_debut = $item->promo_debut;
					$promo_fin = $item->promo_fin;
					$mode = $item->choix_mode_prix;
					$mode_prix_1_achat = $item->mode_prix_1_achat_ht;
					$marge = $item->mode_prix_1_marge;
					$mode_prix_2 = $item->mode_prix_2_fixe_ht;
					$mode_prix_3 = $item->mode_prix_3_fixe_ttc;
					$dateajout = $item->dateajout;
					$datemodif = $item->datemodif;
					$stock_actuel = $item->stock;
					$stock_alerte = $item->stock_alerte;
					$unite = $item->unite;
					$package = $item->package == '' ? 'null' : $item->package;
					$img = $item->img == '' ? 'null' : $item->img;
					$quantite = $item->qte_unite == '' ? 'null' : $item->unite;
					$prix_variable = $item->prix_variable;

					$sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`)
					VALUES($famille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',0,$stock_actuel,$stock_alerte,$unite,$quantite,'',$prix_variable,'',0)";
					$ajoutArticle = $conn->query($sql);

					if($ajoutArticle){
						$update = $conn->query("UPDATE table_client_variable SET modif_serveur_ajout_catalogue = '$dateajout'  WHERE num = 1");
						if($update){
							echo json_encode(array("response"=>1,"articleID"=>$item->num));
						}

					}
				}

			}
		}
		elseif (isset($request->modifCatalogue)) {
			$articles = $request->modifCatalogue;
			foreach ($articles as $item) {
				$gencode = $item->ref;
				$famille = $item->cath;
				$id_produit = $item->id;
				$designation = $item->titre;
				$prix = $item->prixttc_euro;
				$promottc = $item->prixttc_promo_euro;
				$codetva = $item->code_tva;
				$promo_debut = $item->promo_debut;
				$promo_fin = $item->promo_fin;
				$mode = $item->choix_mode_prix;
				$mode_prix_1_achat = $item->mode_prix_1_achat_ht;
				$marge = $item->mode_prix_1_marge;
				$mode_prix_2 = $item->mode_prix_2_fixe_ht;
				$mode_prix_3 = $item->mode_prix_3_fixe_ttc;
				$dateajout = $item->dateajout;
				$datemodif = $item->datemodif;
				$stock_actuel = $item->stock;
				$stock_alerte = $item->stock_alerte;
				$unite = $item->unite;
				$package = $item->package == '' ? 'null' : $item->package;
				$img = $item->img == '' ? 'null' : $item->img;
				$quantite = $item->qte_unite == '' ? 'null' : $item->unite;
				$prix_variable = $item->prix_variable;

				$sql = "UPDATE table_client_catalogue SET 
				`cath`= $famille, 
				`id` = '$id_produit',
				`ref` = '$gencode',
				`titre` = '$designation',
				`prixttc_euro` = $prix,
				`prixttc_promo_euro` = $promottc,
				`code_tva` = $codetva,
				`promo_debut` = '$promo_debut',
				`promo_fin` = '$promo_fin',
				`choix_mode_prix` = $mode,
				`mode_prix_1_achat_ht` = $mode_prix_1_achat,
				`mode_prix_1_marge` = $marge,
				`mode_prix_2_fixe_ht`=$mode_prix_2,
				`mode_prix_3_fixe_ttc`=$mode_prix_3,
				`dateajout`='$dateajout',
				`datemodif`='$datemodif',
				`accueil` = 0,
				`stock`=$stock_actuel,
				`stock_alerte`=$stock_alerte,
				`unite`=$unite,
				`qte_unite`=$quantite,
				`package`='$package',
				`prix_variable`=$prix_variable,
				`img` = NULL,
				`send_web` = 1 WHERE ref = '$gencode' ";
				$updateArticle = $conn->query($sql);

				if($updateArticle){
					$update = $conn->query("UPDATE table_client_variable SET modif_serveur_modif_catalogue = '$datemodif'  WHERE num = 1");
					if($update){
						echo "ok";
					}

				}

			}
		}
	}

	
	
}
?>