<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../DBConfig.php';
require_once __DIR__ . '/promo/PromoCode.php';
if (PHP_OS_FAMILY=="Windows") {
   require("C:/xampp/htdocs/caisse-backend/bar/tickets/print-ticket.php");
}else{
    require("/var/www/localhost/caisse-backend/bar/tickets/print-ticket.php");
} 

include '../infos.php';




$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    
    if (isset($request->id_categorie)) {
        $id_categorie = $request->id_categorie;
        if($id_categorie!=0){
            $query = $conn->query("SELECT * FROM table_client_catalogue WHERE cath = $id_categorie");
        }else{
            $query = $conn->query("SELECT * FROM table_client_catalogue ORDER BY num DESC LIMIT 12");
        }
        $produits = [];
        while ($row = $query->fetch_assoc()) {
            if ($row['cath'] == $id_categorie && $id_categorie!= 0) {
                $produits[] = $row;
            }elseif($id_categorie ==0){
                $produits[] = $row;
            }
        }
        $produits = $id_categorie!=0 ? array_slice($produits,0,12) : array_slice($produits,0,12);
        echo count($produits) > 0 ? json_encode(array('response' => 1 , 'data' => $produits)) : json_encode(array('response' => 0 , 'data' => "Aucun produit trouvé pour cette catégorie"));
    }else if (isset($request->deleteArticle)) {
        $id = trim($request->deleteArticle);
        $idtable = 1;
        $id_caisse = $request->id_caisse;
        $sql = "DELETE FROM table_client_panier WHERE num = $id AND id_caisse = $id_caisse AND idtable = $idtable";
        $delete = $conn->query($sql);
        if ($delete == TRUE) {
            $response['response'] = 1;
            echo json_encode($response);
            die();
        } else {
            echo json_encode(array('response' => 0, 'message' => 'Échec de la suppresion du fichier'));
        }
    } 
    else if (isset($request->offrirArticle)) {
        $id = trim($request->offrirArticle);
        $idtable = 1;
        $id_caisse = $request->id_caisse;
        $titre = $request->titre . " OFFERT";
        $sql = "UPDATE table_client_panier SET titre = '$titre', pu_euro = 0 WHERE num = $id AND id_caisse = $id_caisse";
        $delete = $conn->query($sql);
        if ($delete == TRUE) {
            $response['response'] = 1;
            echo json_encode($response);
            die();
        } else {
            echo json_encode(array('response' => 0, 'message' => 'Échec de la suppresion du fichier'));
        }
    } 
    elseif (isset($request->addToCart)) {
        $titre = remove_accents($request->titre);
        $titre = $conn->real_escape_string($titre);
        $pu_euro = $request->prixttc_euro;
        $prixttc_promo_euro = $request->prixPromo;
        $id_produit = $request->id_produit;
        $ref = $request->ref;
        $code_tva = $request->code_tva;
        $taux_tva = ($code_tva == 8 ? 8.5 : ($code_tva == 2 ? 2.1 : ($code_tva == 1 ? 1.05 : 0)));
        $date = time();
        $famille = $request->famille;
        $id_caisse = $request->id_caisse;
        $options = $request->options;
        $optionsArray = $request->options;
        if(is_array($options)){
            if(count($options)>0){
                $totalPrixOption = 0;
                foreach($options as $option){
                    $id = $option[0]; // id de l'option
                    $sql = "SELECT prix FROM options_produit WHERE idoption = $id";
                    $queryOption = $conn->query($sql);
                    if($queryOption){
                        $prixOption = $queryOption->fetch_assoc()['prix'];
                        $totalPrixOption+=$prixOption;
                    }
                }
                $pu_euro += $totalPrixOption;
            }
        }
        
        $options = json_encode($request->options);
        $tables = $conn->query('SELECT status,numero from restaurant_tables WHERE status = 1')->fetch_assoc();
        if ($tables['status'] == 1) {
            $idtable = $tables['numero'];
            $sql = "SELECT ref FROM table_client_panier WHERE ref = '$ref' AND idtable = $idtable AND id_caisse = $id_caisse";
        }else{
            $sql = "SELECT ref FROM table_client_panier WHERE ref = '$ref'";
        }
        
        
        $query = $conn->query($sql);
        
        if ($query->num_rows>0) {
            if(!is_array($optionsArray)){
                // $date = time();
                $sql = "UPDATE table_client_panier SET qte = qte + 1 WHERE ref = '$ref' AND idtable = $idtable AND id_caisse = $id_caisse";
                $query = $conn->query($sql);
                if ($query) {
                    $newQte = $conn->query("SELECT qte FROM table_client_panier WHERE ref = '$ref' AND idtable = $idtable AND id_caisse = $id_caisse")->fetch_assoc()['qte'];
                    $num = $conn->query("SELECT num FROM table_client_panier WHERE ref = '$ref' AND idtable = $idtable AND id_caisse = $id_caisse")->fetch_assoc()['num'];
                    successResponse($num,2,$newQte);
                    
                }
                die();
            }
        }
        if (isset($_SESSION['numero_table']) && $_SESSION['numero_table'] != 0) {
            $idtable = $_SESSION['numero_table'];
            $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`,`date`, `remise`, `famille`,`idtable`,`statut`,`en_cuisine`, `r_globale`, `r_globale_eur`, `options`) 
            VALUES (1,'" . $id_produit . "','" . $ref . "', 1, $id_caisse , $pu_euro, 0, 'false',$prixttc_promo_euro,'" . $titre . "',$taux_tva,$date, 0,$famille,$idtable,0,0,0,0,'$options')";
        }else{
            $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`,`date`, `remise`, `famille`,`idtable`,`statut`,`en_cuisine`, `r_globale`, `r_globale_eur`, `options`) 
            VALUES (1,'" . $id_produit . "','" . $ref . "', 1, $id_caisse , $pu_euro, 0, 'false',$prixttc_promo_euro,'" . $titre . "',$taux_tva,$date, 0,$famille,$idtable,0,0,0,0,'$options')"; 
        }

        $query = $conn->query($sql);
        if ($query) {
            $last_id = $conn->insert_id;
            PromoCode::applyToCartRow($conn, $id_caisse, $idtable, $last_id);
            $produit = $conn->query("SELECT * FROM table_client_panier WHERE num = $last_id ");
            
            $produit = $produit->fetch_assoc();
            successResponse($produit,1);
        }else{
            // errorResponse("Erreur, impossible d'ajouter le produit dans le panier.",0);
            errorResponse($sql,0);
        }
    }
    elseif(isset($request->selectTable)){
        $id_table = $request->id_table;
        $sql = "SELECT id FROM restaurant_tables WHERE status = 1";
        $query = $conn->query($sql);
        if ($query->num_rows>0) {
            $id = $query->fetch_assoc()["id"];
            $changeCurrent = $conn->query("UPDATE restaurant_tables SET status = 0 WHERE id = $id");
            if ($changeCurrent) {
                $updateCurrent = $conn->query("UPDATE restaurant_tables SET status = 1 WHERE id = $id_table");
                if ($updateCurrent) {
                    $_SESSION['numero_table'] = $id_table;
                    echo successResponse('Ok',1);
                }
                else{
                    echo errorResponse("Non",2);
                }
            }
        }else{
            $activetable = $conn->query("UPDATE restaurant_tables SET status = 1 WHERE id = $id_table");
            if ($activetable) {
                echo successResponse('Ok',1);
            }
            else{
                echo errorResponse("Non",2);
            }
        }
	}elseif(isset($request->paiementDetails)){
		$numerotable = isset($request->id_table) ? max(0, (int) $request->id_table) : 0;
		$activeTableNumber = 0;
		$sql = "SELECT numero FROM restaurant_tables WHERE status = 1";
		$table = $conn->query($sql);
		if ($table && $table->num_rows > 0) {
			$activeTableNumber = (int) $table->fetch_assoc()["numero"];
		}
		// L'écran de paiement peut ne plus contenir le bouton promo et envoyer 0.
		// Dans ce cas, le panier réel reste celui de la table active.
		if ($numerotable <= 0 && $activeTableNumber > 0) {
			$numerotable = $activeTableNumber;
		}
        $resteApayer = $request->resteAPayer;
        $paiementDetails = $request->paiementDetails;
        $produitChoix = isset($request->produitChoix) ? $request->produitChoix : "";
        // JSON.stringify omet les propriétés JavaScript à undefined. Un paiement
        // standard arrive donc parfois sans infoTicket : on le normalise ici afin
        // que son panier passe bien dans le calcul des statistiques promo.
        $infoTicket = isset($request->infoTicket) ? (string) $request->infoTicket : '';
        $id_caisse = $request->id_caisse;
		$promoState = PromoCode::getState($id_caisse, $numerotable);
		// Repli serveur si l'identifiant envoyé par l'interface est périmé.
		if (!$promoState && $activeTableNumber > 0 && $activeTableNumber !== $numerotable) {
			$activePromoState = PromoCode::getState($id_caisse, $activeTableNumber);
			if ($activePromoState) {
				$numerotable = $activeTableNumber;
				$promoState = $activePromoState;
			}
		}
		$promoProductCount = 0;
		$promoDiscountTotal = 0;
		$promoTrackingWarning = '';
		$shouldClearCart = false;
        $espece = 0;
        $cb = 0;
        $cheque = 0;
        $chequeRestaurant = 0;
        $total_euro_du = 0;
        $total_euro = 0;
        $qte_total = 0;
        $totalHT = 0;
        $totalTVA8 = 0;
        $totalTVA2 = 0;
        $totalTVA1 = 0;
        $commandes = [];
        $detailsPayment = "Details du paiement: " . "\n";


        if ($infoTicket == "") {
            foreach($paiementDetails as $payment){
                $paiement = explode('|',$payment); 
                if (isset($paiement[1])) {
                    $espece += $paiement[1] == 'Espèce' ? $paiement[0] : 0;
                    $cb += $paiement[1] == 'Carte Bancaire' ? $paiement[0] : 0;
                    $cheque += $paiement[1] == 'Chèque' ? $paiement[0] : 0;
                    $chequeRestaurant += $paiement[1] == 'Chèque Restaurant' ? $paiement[0] : 0;
                }
            }
        }
        else{
            $espece = 0;
            $cb = 0;
            $chequeRestaurant = 0;
            $cheque = 0;
            foreach($paiementDetails as $payment){
                $paiement = explode('|',$payment); 

                if (isset($paiement[1]) && $paiement[1] != "undefined") {
                    $espece = $paiement[1] == 'Espèce' ? $paiement[0] : 0;
                    $cb = $paiement[1] == 'Carte Bancaire' ? $paiement[0] : 0;
                    $cheque = $paiement[1] == 'Chèque' ? $paiement[0] : 0;
                    $chequeRestaurant = $paiement[1] == 'Chèque Restaurant' ? $paiement[0] : 0;
                }
            }
        }
        
        if ($infoTicket=="choixProduit") {
            $produits = "";
            foreach($produitChoix as $produit){
                

                $sql = "SELECT remise,id_produit FROM table_client_panier WHERE ref = '$produit->ref' and id_produit = '$produit->id_produit' and idtable = $numerotable AND id_caisse = $id_caisse AND remise > 0";
                $checkremise = $conn->query($sql);
                if($checkremise->num_rows>0){
                    while($row=$checkremise->fetch_assoc()){
                        if($row['remise'] > 0 && $produit->id_produit == $row['id_produit']){
                            $produits .= "'".$produit->id_produit."'". ',';

                            $qteActuelle = $conn->query("SELECT qte FROM table_client_panier WHERE id_produit = '$produit->id_produit' and idtable = $numerotable AND id_caisse = $id_caisse")->fetch_assoc()['qte'];
                            // var_dump("SQL 1 => SELECT qte FROM table_client_panier WHERE id_produit = '$produit->id_produit' and idtable = $numerotable AND id_caisse = $id_caisse");
                            if(isset($produit->qte)){
                                $nouvelleQuantite = $qteActuelle - $produit->qte;
                                $updateQtePanier = $conn->query("UPDATE table_client_panier SET qte = $nouvelleQuantite WHERE id_produit = '$produit->id_produit' and idtable = $numerotable AND id_caisse = $id_caisse");
                                // var_dump("QUERY 2 => UPDATE table_client_panier SET qte = $nouvelleQuantite WHERE id_produit = '$produit->id_produit' and idtable = $numerotable AND id_caisse = $id_caisse" );
                            }
                        }
                    }
                }else{
                    $produits .= "'".$produit->ref."'". ',';
                    $qteActuelle = $conn->query("SELECT qte FROM table_client_panier WHERE ref = '$produit->ref' and idtable = $numerotable AND id_caisse = $id_caisse AND remise = 0 ")->fetch_assoc()['qte'];
                            if(isset($produit->qte)){
                                $nouvelleQuantite = $qteActuelle - $produit->qte;
                                $updateQtePanier = $conn->query("UPDATE table_client_panier SET qte = $nouvelleQuantite WHERE ref = '$produit->ref' and idtable = $numerotable AND id_caisse = $id_caisse AND remise = 0 ");
                            }
                }
                // var_dump($sql);
                
                
            }
            
            $produits = rtrim($produits,',');
            $sql = "SELECT * FROM table_client_panier WHERE idtable = $numerotable AND id_caisse = $id_caisse AND (CASE WHEN remise > 0 THEN id_produit ELSE ref END) IN ($produits);";
        }
        elseif($infoTicket=="Repas"){
            $ticket_ligne = "";
            $titre = $infoTicket;
            $paiement = explode('|',$paiementDetails[0]);
            if (isset($paiement[1]) && $paiement[1] != "undefined") {
                $espece = $paiement[1] == 'Espèce' ? $paiement[0] : 0;
                $cb = $paiement[1] == 'Carte Bancaire' ? $paiement[0] : 0;
                $cheque = $paiement[1] == 'Chèque' ? $paiement[0] : 0;
                $chequeRestaurant = $paiement[1] == 'Chèque Restaurant' ? $paiement[0] : 0;
            }
            // $montant = $espece > 0 ? $espece : ($cb > 0 ? $cb : ($cheque>0 ? $cheque : $chequeRestaurant > 0 ? $chequeRestaurant : 0));
            $ligne_ticket = setStringLen($titre, $designiation_limit);
            $ligne_qte = setStringLen(1 . "*" . $montant, $qte_prix_limit);
            $ligne_tva = setStringLen("0.00", $tva_limit);
            $ligne_prix_commande = setStringLen(formatNumber($montant), $mttc_limit, true);
            $ticket_ligne .= $ligne_qte . $ligne_ticket . "  " .$ligne_prix_commande . " " . $ligne_tva. "\n";

            $sql = "SELECT * FROM table_client_panier WHERE idtable = $numerotable AND id_caisse = $id_caisse";
            $panier = $conn->query($sql);
            while($row = $panier->fetch_assoc()){
                $num = $row['num'];
                $pu_euro = $row['pu_euro'];
                $promo = $row['promo'];
                $session = 1;
                $ref = $row['ref'];
                $idproduit = $row['id_produit'];
                $taux_tva = $row['taux_tva'];
                $famille = $row['famille'];
                $date = date('Y-m-d',$row['date']);
                $qte = $row['qte'];
				$remise_euro = (float) $row['remise_euro'];
                $remise_pourcent = $row['remise'];
                $qte_total+=$qte;
                $id_table = $row['idtable'];
                $id_table = $row['id_caisse'];
                $titre = $row['titre'];
                $statut = $row['statut'];
                // Remplissage pour la table commandes
				$basePrice = $promo > 0 ? $promo : $pu_euro;
				$netUnitPrice = max(0, $basePrice - ($basePrice * ($remise_pourcent / 100)) - $remise_euro);
				$commandes[] = (object) array('id_caisse' => $id_caisse, 'pu_euro' => $pu_euro , 'idproduit' => $idproduit, 'qte' => $qte, 'remise' => $remise_euro * $qte,  'taux_tva' => $taux_tva, 'famille' => $famille, 'promo' => $promo, 'id_table' => $id_table, 'statut' => 0,'date' => $date );
				$total_euro_du += $netUnitPrice * $qte;
				$promoItem = PromoCode::itemFromState($promoState, $num);
				if ($promoItem) {
					$promoProductCount += $qte;
					$promoDiscountTotal += (float) $promoItem['unit_discount'] * $qte;
				}
                // $paidProduct = $conn->query("UPDATE table_client_panier SET statut = 1 WHERE num = $num");
            }

        }
        else{
            $sql = "SELECT * FROM table_client_panier WHERE idtable = $numerotable AND id_caisse = $id_caisse";
        }
        
        $panier = $conn->query($sql);
        // Le paiement standard ne passe pas par la boucle "choixProduit".
        // On calcule donc explicitement sa consommation promo avant l'encaissement.
        if ($panier && $panier->num_rows > 0 && $infoTicket === "" && $promoState) {
            while ($promoRow = $panier->fetch_assoc()) {
                $promoItem = PromoCode::itemFromState($promoState, $promoRow['num']);
                if ($promoItem) {
                    $promoQte = max(0, (int) $promoRow['qte']);
                    $promoProductCount += $promoQte;
                    $promoDiscountTotal += (float) $promoItem['unit_discount'] * $promoQte;
                }
            }
            $panier->data_seek(0);
        }
        // var_dump($sql);
        
        
        if ($panier && $panier->num_rows>0 && $infoTicket == "choixProduit") {
            $ticket_ligne = "";
            while($row = $panier->fetch_assoc()){
                $num = $row['num'];
                $pu_euro = $row['pu_euro'];
                $promo = $row['promo'];
                $session = 1;
                $ref = $row['ref'];
                $idproduit = $row['id_produit'];
                $taux_tva = $row['taux_tva'];
                $famille = $row['famille'];
                $date = date('Y-m-d',$row['date']);
                
				$remise_euro = (float) $row['remise_euro'];
                $remise_pourcent = $row['remise'];
                
                $id_table = $row['idtable'];
                $titre = $row['titre'];
                $statut = $row['statut'];
                

                // CHOIX DE LA BONNE QUANTITE
                $qte = 0;
                foreach($produitChoix as $produit){
                    if(isset($produit->qte)){
                        if($produit->ref == $ref){
                            $qte = $produit->qte;
                        }
                    }else{
                        $qte = $row['qte'];
                    }
                }
                
                $qte_total+=$qte;
				$basePrice = $promo > 0 ? $promo : $pu_euro;
				$netUnitPrice = max(0, $basePrice - ($basePrice * ($remise_pourcent / 100)) - $remise_euro);
				// CALCUL TAUX TVA
				if ($taux_tva == 8.5) {
					$totalTVA8 += ($netUnitPrice - ($netUnitPrice / (1 + $taux_tva / 100))) * $qte;
				}elseif($taux_tva == 2.1){
					$totalTVA2 += ($netUnitPrice - ($netUnitPrice / (1 + $taux_tva / 100))) * $qte;
				}
				elseif($taux_tva == 1.05){
					$totalTVA1 += ($netUnitPrice - ($netUnitPrice / (1 + $taux_tva / 100))) * $qte;
				}
                // FIN CACUL TAUX TVA
                // GENERATION DES LIGNES DU TICKET 
				$prix_total = $netUnitPrice * $qte;
				$ligne_ticket = setStringLen($titre, $designiation_limit);
				$ligne_qte = setStringLen($qte . "*" . formatNumber($netUnitPrice), $qte_prix_limit);
                $ligne_tva = setStringLen($taux_tva, $tva_limit);
                $ligne_prix_commande = setStringLen(formatNumber($prix_total), $mttc_limit, true);
                $ticket_ligne .= $ligne_qte . $ligne_ticket . "  " .$ligne_prix_commande . " " . $ligne_tva. "\n";
                
                if ($promo>0) {
                    $ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",9)." ";
                }
                // FIN GENERATION DU TICKET 
				$commandes[] = (object) array('id_caisse' => $id_caisse, 'pu_euro' => $pu_euro , 'idproduit' => $idproduit, 'qte' => $qte, 'remise' => $remise_euro * $qte,  'taux_tva' => $taux_tva, 'famille' => $famille, 'promo' => $promo, 'id_table' => $id_table, 'statut' => 0,'date' => $date );
				// var_dump($pu_euro."*".$qte);
				$total_euro_du += $netUnitPrice * $qte;
				$promoItem = PromoCode::itemFromState($promoState, $num);
				if ($promoItem) {
					$promoProductCount += $qte;
					$promoDiscountTotal += (float) $promoItem['unit_discount'] * $qte;
					$ticket_ligne .= str_repeat(' ', 4) . 'CODE PROMO -' . formatNumber((float) $promoItem['unit_discount'] * $qte) . " EUR\n";
				}
            }
        }


        $ticket ="";
        $total_euro += (float)$espece + (float)$cb + (float)$chequeRestaurant + (float)$cheque;
		// En paiement standard, resteAPayer contient le solde avant cet encaissement.
		// Le serveur peut donc confirmer la fin du panier sans dépendre des valeurs
		// recalculées dans le navigateur. Les paiements par produits / fractionnés
		// conservent volontairement l'état promo pour les tickets suivants.
		if ($infoTicket === '') {
			$shouldClearCart = $total_euro + 0.005 >= (float) $resteApayer;
		}
		// Si les identifiants de lignes du panier ont changé entre l'application
		// de la promo et l'encaissement, le rapprochement détaillé ci-dessus peut
		// ne rien trouver. Pour un encaissement complet et encore non tracé, les
		// totaux mémorisés lors de l'application constituent alors le repli fiable.
		if (
			$shouldClearCart
			&& $promoState
			&& $promoProductCount === 0
			&& $promoDiscountTotal <= 0
			&& empty($promoState['used_ticket_ids'])
		) {
			$promoProductCount = isset($promoState['product_count_at_apply'])
				? max(0, (int) $promoState['product_count_at_apply'])
				: 0;
			$promoDiscountTotal = isset($promoState['discount_at_apply'])
				? max(0, (float) $promoState['discount_at_apply'])
				: 0;
		}
        // var_dump($total_euro,$total_euro_du);
        $monnaieArendre = $total_euro > $total_euro_du ? $total_euro - $total_euro_du : 0;
        // var_dump($monnaieArendre);die();
        $totalHT = $infoTicket == "Repas" ? 0.00 : $total_euro_du - ($totalTVA8+$totalTVA2+$totalTVA1);
        // var_dump($monnaieArendre,"TOTAL EURO=>",$total_euro,"TOTAL EURO DU=>",$total_euro_du);
        // SUITE CREATION TICKET 
        // DETAILS PAIEMENT SUR LE TICKET
        if ($espece>0) {
            $detailsPayment .=  "  >  " .formatNumber($espece) . " EUR EN ESPECES" . "\n";
        }
        if ($cb > 0) {
            $detailsPayment .= "  >  " .formatNumber($cb) . " EUR EN CB" . "\n";
        }
        if ($cheque > 0) {
            $detailsPayment .= "  >  " .formatNumber($cheque) . " EUR EN CHEQUES" . "\n";
        }
        if ($chequeRestaurant > 0) {
            $detailsPayment .= "  >  " .formatNumber($cb) . " EUR EN CHEQUE RESTAURANT" . "\n";
        }
        
        if ($monnaieArendre>0) {
            $detailsPayment .= "  >  MONNAIE RENDU " .formatNumber($monnaieArendre) . " EUR" ;
            $espece = $espece - $monnaieArendre;
        }
        // FIN DETAILS DE PAIEMENT
        if(strlen($total_euro) > 5){
            $total_a_payer = str_repeat(" ", 10) ."TOTAL A PAYER TTC".str_repeat(" ",3).setStringLen(formatNumber($total_euro),8)."EUR"; 
        }
        else{
            $total_a_payer = str_repeat(" ", 10) ."TOTAL A PAYER TTC".str_repeat(" ",4).setStringLen(formatNumber($total_euro),6)." EUR"; 
        }
        $total_ht = $infoTicket == "Repas" ? "" : str_repeat(" ", 19) ."TOTAL HT". str_repeat(" ",4).setStringLen(formatNumber($totalHT),6)." EUR";
        $total_tva8 = $infoTicket == "Repas" ?  "" :  str_repeat(" ", 19) ."TVA 8.5%". str_repeat(" ",4).setStringLen(formatNumber($totalTVA8),6)." EUR";
        $total_tva2 = $infoTicket == "Repas" ?  "" :  str_repeat(" ", 19) ."TVA 2.1%". str_repeat(" ",4).setStringLen(formatNumber($totalTVA2),6)." EUR";
        $total_tva1 = $infoTicket == "Repas" ?  "" : str_repeat(" ", 19) ."TVA 1.05%". str_repeat(" ",4).setStringLen(formatNumber($totalTVA1),6)." EUR";
        $tva_ticket = "";
        if($totalTVA8 != 0){
            $tva_ticket .= $total_tva8 ."\n";
        }
        if($totalTVA2 != 0){
            $tva_ticket .= $total_tva2 ."\n";
        }
        if($totalTVA1 != 0){
            $tva_ticket .= $total_tva1."\n";
        }
        
        $date = date('Y-m-d H:i:s');
        $sql = "INSERT INTO table_client_ticket (`id_caisse`, `p_cheque_euro`, `p_cb`, `p_espece_euro`, `p_restaurant`,`total_remise`,`deconsigne`, `retourarticle`, `echangearticle`, `total_euro`, `total_euro_du`, `qte_total`,`date`, `sendserveur`,`id_ticket`) 
        VALUES ($id_caisse,$cheque,$cb,$espece,$chequeRestaurant,0,0,false,0,$total_euro,$total_euro_du,$qte_total, '$date',1,0)";
        $insert_ticket = $conn->query($sql);
        
        if ($insert_ticket) {
            $lastID = $conn->insert_id;
            $sql = "UPDATE table_client_ticket SET id_ticket = $lastID WHERE id = $lastID";
            $updateTicket = $conn->query($sql);
            // if ($updateTicket) {

                // }

                // ASSEMBLAGE DU TICKET
            $footer_ticket = "T".$id_caisse."W".$lastID." - ".date('d/m/Y H:i:s',strtotime($date));

            $ticket .= $ticket_entete
            ."\n"
            ."TABLE ". $numerotable


            .$ticket_corps
            .$ticket_ligne
            ."\n"
            .$separator."\n"
            .$total_a_payer."\n"
            .$total_ht ."\n"
            .$tva_ticket."\n"
            .$detailsPayment."\n"
            ."======================================"."\n"
            .$footer_ticket;

           
            if ($updateTicket) {
                $updateClient = $conn->query( "UPDATE clients SET statut = 0 AND idtable = 0 WHERE idtable = $numerotable");
                $deletePaiement = $conn->query("DELETE FROM paiement_quantite  WHERE `table` = $numerotable");
				foreach($commandes as $commande){
                    $idproduit = $commande->idproduit;
                    $qte = $commande->qte;
                    $pu_euro = $commande->pu_euro;
                    $taux_tva = $commande->taux_tva;
                    $famille = $commande->famille;
                    $remise = $commande->remise;
                    $promo = $commande->promo > 0 ? $pu_euro * $qte - $commande->promo * $qte : 0  ;
                    $date = $commande->date;
                    $sql = "INSERT INTO `table_client_commandes`(`id_ticket`, `id_caisse`, `id_produit`, `qte`,`pu_euro`, `promo`, `remise`, `taux_tva`, `famille`,`date`, `sendserveur`) 
                    VALUES ($lastID,$id_caisse,'$idproduit',$qte,$pu_euro,$promo,$remise,$taux_tva,$famille,'$date',1)";
                    
					$newCommandes = $conn->query($sql);
				}
				$promoRecorded = false;
				if ($promoProductCount > 0 && $promoDiscountTotal > 0) {
					try {
						$promoRecorded = PromoCode::recordUsage(
							$lastID,
							$id_caisse,
							$numerotable,
							$promoProductCount,
							$promoDiscountTotal
						);
					} catch (Exception $e) {
						$promoTrackingWarning = 'Le ticket est encaissé, mais la trace JSON de la promo a échoué : ' . $e->getMessage();
						error_log($promoTrackingWarning . ' Ticket ' . $lastID . ' : ' . $e->getMessage());
					}
				}
				if ($promoState && empty($promoState['used_ticket_ids']) && !$promoRecorded && $promoTrackingWarning === '') {
					$promoTrackingWarning = 'Le ticket est encaissé, mais aucune statistique promo n’a pu être calculée.';
					error_log($promoTrackingWarning . ' Ticket ' . $lastID . '.');
				}
				if ($shouldClearCart && $promoTrackingWarning === '' && !PromoCode::clearState($id_caisse, $numerotable)) {
					$promoTrackingWarning = 'Le ticket est encaissé, mais l’état du bouton promo n’a pas pu être réinitialisé.';
				}
				$total = calculTotal($conn,1,$id_caisse);

				if (!$shouldClearCart) {
					echo json_encode(array('response' => 1, 'ticket' => $ticket, 'arendre' => $monnaieArendre, 'clear' => false , 'table' => $numerotable, 'promo_recorded' => $promoRecorded, 'warning' => $promoTrackingWarning));
				}else{
					echo json_encode(array('response' => 1, 'ticket' => $ticket, 'arendre' => $monnaieArendre, 'clear' => true,'table' => $numerotable, 'promo_recorded' => $promoRecorded, 'warning' => $promoTrackingWarning));
                }



                $filepath = "ticket.txt";
                file_put_contents($filepath, $ticket);
            }

        }
    }

}
