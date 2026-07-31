<?php
header('Access-Control-Allow-Origin: *'); 
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include 'DBConfig.php';
include 'functions.php';


if(isset($_POST['ajoutArticle'])){

    $stock = $_POST['newStock'];
    $famille =$_POST['famille'];
    $gencode =$_POST['newRef'];
    $designation = $_POST['ajoutArticle'];
    $taux_tva = $_POST['newTauxTva'];
    $codetva = ($taux_tva == "8.5" ? 8 : ($taux_tva == "2.1" ? 2  : ($taux_tva == "1.05" ? 1 : 0)));
    $quantite = (float)$_POST['newQte'];
    $prix = (float) $_POST['newPu'];
    $promottc =  $_POST['newPromo'];

    if($designation == ""){
        errorResponse('Vous devez entrer un titre.',0);
        exit;
    }
    if($codetva == ""){
        errorResponse('Vous devez entrer un code de TVA(8.5, 2.1, 1.5).',0);exit;
    }
    if($prix == "0.00" or $prix == "" or !is_numeric($prix)){
        errorResponse('Vous devez entrer un prix.',0);
        exit;
    }
    if($famille == ""){
        $famille = 0;
    }

    $id_produit = random_strings(12);
    $mode = $prix;
//		$mode = $mode == 'prixTTC' ? 3 : 2;
//		$mode_prix_2 = $mode == 2 ? $prix :  '0.00';
//		$mode_prix_3 = $mode == 2 ? $prix  : '0.00';
    $dateajout =  date('Y-m-d H:i:s');



    $sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`) 
	VALUES($famille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'1000-01-01 00:00:00','1000-01-01 00:00:00',$mode,'0.00','30.00',0,0,'$dateajout','1000-01-01 00:00:00',0,$stock,'-99',0,$quantite,'',0,'',1)";
    $insert_article = $conn->query($sql);

    if($insert_article){
        $session = $_POST['newSession'];
        $id_caisse = $_POST['newIdcaisse'];
        $date = time();
        $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`,`date`, `remise`, `famille`) 
            VALUES ($session,'$id_produit','$gencode', $quantite, $id_caisse , $prix, 0, 'false',$promottc,'$designation',$taux_tva,$date, 0,$famille)";
        $insertpanier = $conn->query($sql);
        if($insertpanier){
            regenerePanier($conn,"SELECT * FROM table_client_panier","jsons/panier.json");
            successResponse('Article ajouté au catalogue avec succès !',1);
            exit;
        }

    }
    else{
        errorResponse($sql,0);
        exit;
    }

}
// Create connection
//if (isset($_GET['action']) && $_GET['action'] == 'articleList' ) {
//	$sql = "SELECT * FROM `table_client_catalogue` ORDER BY `table_client_catalogue`.`dateajout` DESC";
//	// var_dump($sql);die();
//	$result = $conn->query($sql);
//
//	if ($result->num_rows >0) {
//
//		while($row[] = $result->fetch_assoc()) {
//			$tem = $row;
//
//			$json['articles'] = $tem;
//
//		}
//
//	} else {
//		echo json_encode("Aucun articles trouvés.") ;
//	}
//	echo json_encode($json);
//	$conn->close();
//}
//
//
//$postdata = file_get_contents('php://input');
//
//if (isset($postdata)) {
//	$request = json_decode($postdata);
//    var_dump($request);
//	if (isset($request->retourArticle)) {
//		$ref = $request->retourArticle;
//		$sql = "SELECT * FROM table_client_catalogue WHERE ref = '$ref'";
//		$result = $conn->query($sql);
//		if($result->num_rows >0){
//			$article = $result->fetch_assoc();
//			echo json_encode($article);
//		}
//		else{
//			echo 0;
//		}
//
//
//		$conn->close();
//	}
////	if(isset($request->search)){
////		$search = $request->search;
////		echo json_encode($search);
////	}
//
//}
