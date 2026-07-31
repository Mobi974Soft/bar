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
require '../vendor/autoload.php';
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
 
 
// test
date_default_timezone_set('Indian/Reunion');
 
$postdata = file_get_contents('php://input');
if (isset($postdata)) {
    $request = json_decode($postdata);
 
    $totalPanier = $request->total;
    $totalPanier= (float)$totalPanier;
 
    $qte_total = 0;
    $ticket = "";
    $ticket_ligne = "";
    $commandes = [];
    $total_remise_euro = 0;
    $total_remise_euro = (float)$total_remise_euro;
    $total_remise_pourcent = 0;
    $total_euro_du = 0;
    $total_euro_du = (float)$total_euro_du;
    $totalTTC = 0;
    $totalTTC = (float)$totalTTC;
    $totalHT = 0;
    $totalHT = (float)$totalHT;
    $totalTVA8 = 0;
    $totalTVA8 = (float)$totalTVA8;
    $totalTVA2 = 0;
    $totalTVA2 = (float)$totalTVA2;
    $totalTVA1 = 0;
    $retour_article = 0;
	$fullcommande = "";
   
    include('print-ticket.php');
    $alphabet = range('A', 'Z');

				// Lettre basée sur le créneau de 30 minutes (il y en a 48 dans une journée)
				$heure = date('H');
				$minute = date('i');
				$indexCreneau = floor($heure * 2 + $minute / 30); // 0 à 47
				$lettre = $alphabet[$indexCreneau % 26]; // lettre de A à Z
			
				// Millisecondes depuis minuit
				$now = microtime(true);
				$secondsSinceMidnight = (date('H') * 3600) + (date('i') * 60) + date('s');
				$millis = ($secondsSinceMidnight * 1000) + ((int)(($now - floor($now)) * 1000));
			
				$code = ($millis + ($id_caisse % 100)) % 1000;
			
				
                // $timestamp = time(); 
				// $lettre = chr(rand(65, 90));
                
    $id_caisse = $_SESSION['id_caisse'];
    
    $session = $_SESSION['session'];
    $qte_sur_etiquette = $conn->query("SELECT sum(qte) as qtetotal FROM table_client_panier WHERE id_caisse = $id_caisse AND session = $session")->fetch_assoc()['qtetotal'];
    $sql = "SELECT * FROM table_client_panier WHERE id_caisse = $id_caisse AND session = $session";
    $panier = $conn->query($sql);
    while ($ligne = $panier->fetch_assoc()) {
 
        $sessionPanier = $ligne['session'];
        $ref = $ligne['ref'];
        if ($sessionPanier == $session && $ref != "84949489749849498498") {
            $deconsigne = 0;
            $qte = $ligne['qte'];
            $qte_total += $qte;
            $promo = (float)$ligne['promo'] ;
            $pu_euro = (float)$ligne['pu_euro'];
            $taux_tva = $ligne['taux_tva'];
            $idproduit = $ligne['id_produit'];
            $remise_euro = $ligne['remise_euro'];
            $remise_pourcent = $ligne['remise'];
            $titre = remove_accents($ligne['titre']);
            $famille = $ligne['famille'];
            $retour = $ligne['retour'];
            $options = $ligne['options'];
 
            if($retour==1){
                if($promo>0){
                    $retour_article+=$promo*$qte;
                }else{
                    $retour_article+=$pu_euro*$qte;
                }
                
                $qte = -$qte;
            }
 
 
            // TOTAL HT ICI
 
            if($taux_tva == 8.5){
                if($promo>0){
                    if($remise_pourcent>0){
                        $prix_apres_remise = $promo - ($promo*($remise_pourcent/100));
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                        $totalTVA8 += $montant_tva * $qte;
                    }
                    elseif ($remise_euro>0) {
                        $prix_apres_remise = $promo - $remise_euro;
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                        $totalTVA8 += $montant_tva * $qte;
                    }else{
                        $totalTVA8 += ($promo -($promo / (1+$taux_tva/100))) * $qte;
                    }
                }
                elseif($remise_pourcent>0){
                    $prix_apres_remise = $pu_euro - ($pu_euro*($remise_pourcent/100));
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA8 += $montant_tva * $qte;
                }
                elseif($remise_euro>0){
                    $prix_apres_remise = $pu_euro - $remise_euro;
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA8 += $montant_tva * $qte;
                }
                else{
                    $totalTVA8 += ($pu_euro -($pu_euro / (1+$taux_tva/100))) * $qte;
                }
            }
            if($taux_tva == 2.1){
                if($promo>0){
                    if($remise_pourcent>0){
                        $prix_apres_remise = $promo - ($promo*($remise_pourcent/100));
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                        $totalTVA2 += $montant_tva * $qte;
                    }
                    elseif ($remise_euro>0) {
                        $prix_apres_remise = $promo - $remise_euro;
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                        $totalTVA2 += $montant_tva * $qte;
                    }else{
                        $totalTVA2 += ($promo -($promo / (1+$taux_tva/100))) * $qte;
                    }
                }
                elseif($remise_pourcent>0){
                    $prix_apres_remise = $pu_euro - ($pu_euro*($remise_pourcent/100));
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA2 += $montant_tva * $qte;
                }
                elseif($remise_euro>0){
                    $prix_apres_remise = $pu_euro - $remise_euro;
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA2 += $montant_tva * $qte;
                }
                else{
                    $totalTVA2 += ($pu_euro -($pu_euro / (1+$taux_tva/100))) * $qte;
                }
            }
            if($taux_tva == 1.05){
                if($promo>0){
                    if($remise_pourcent>0){
                        $prix_apres_remise = $promo - ($promo*($remise_pourcent/100));
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+($taux_tva/100) ));
                        $totalTVA1 += $montant_tva * $qte;
                    }
                    elseif ($remise_euro>0) {
                        $prix_apres_remise = $promo - $remise_euro;
                        $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+($taux_tva/100) ));
                        $totalTVA1 += $montant_tva * $qte;
                    }else{
                        $montantTVA1 = $promo /  (1 + (1.05/100));
                        $montantTVA1 = round($montantTVA1,2);
                        $montantTVA1 = $promo - $montantTVA1;
                        $montantTVA1 = round($montantTVA1,2);
                        $totalTVA1 += $montantTVA1 * $qte;
 
                    }
                }
                elseif($remise_pourcent>0){
                    $prix_apres_remise = $pu_euro - ($pu_euro*($remise_pourcent/100));
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA1 += $montant_tva * $qte;
                }
                elseif($remise_euro>0){
                    $prix_apres_remise = $pu_euro - $remise_euro;
                    $montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
                    $totalTVA1 += $montant_tva * $qte;
                }
                else{
                    $montantTVA1 = $pu_euro /  (1 + (1.05/100));
                    $montantTVA1 = round($montantTVA1,2);
                    $montantTVA1 = $pu_euro - $montantTVA1;
                    $totalTVA1 += $montantTVA1 * $qte;
 
                }
            }
 
            $total_remise_euro += $remise_euro;
            if($promo>0){
                $remise_pourcents = $remise_pourcent > 0 ? $promo * $qte * ($remise_pourcent / 100) : 0;
            }else{
                $remise_pourcents = $remise_pourcent > 0 ? $pu_euro * $qte * ($remise_pourcent / 100) : 0;
            }
            
            $total_remise_pourcent += $remise_pourcents;
            $taux_tva_ticket = $taux_tva . "%";
            $pu_euro = $ligne['pu_euro'];
            // GENERATION DES COMMANDES
 
            $commandes[] = (object) array('id_caisse' => $id_caisse, 'pu_euro' => $pu_euro , 'idproduit' => $idproduit, 'qte' => $qte, 'remise' => $remise_euro + $remise_pourcents,  'taux_tva' => $taux_tva, 'famille' => $famille, 'promo' => $promo );
            $prix_total = $pu_euro * $qte;
            $prix_total_ticket = formatNumber($prix_total);
            if(strlen($prix_total_ticket)>5){
                $designiation_limit-=2;
                $mttc_limit+=2;
                $qte_prix_limit+=1;
            }
//            $titre = $remise_pourcent > 0 ?  : $titre;
//            $pu_euro = ($remise_pourcent > 0 ? $pu_euro - $pu_euro *  ($remise_pourcent / 100) : ($remise_euro > 0 ? $pu_euro - $remise_euro :  $pu_euro));
            // ENVOI ETIQUETTE EN CUISINE 
            $numero_commande = $lettre . str_pad($code, 3, '0', STR_PAD_LEFT);
                $options = json_decode($options);
                $dim_vertical_defaut = 80;  // Position verticale initiale
                $commandeListe = "";
                $max_caracteres = 28;       // Nombre max de caractères par ligne
                $options_700mL = "";        // Variable pour stocker l'option 700mL
                $liste_options_concat = ""; // Chaîne concaténée des options
                $options_sur_ticket = [];
                if($titre != "Offert"){
                    // foreach($options as $option) {
                    //     $idoptions = $option[0];
                    //     $nomoption = remove_accents($option[1]);
                    //     $options_sur_ticket[] = array('nom' => $nomoption, "prix" => $option[2]);
                    //     if (trim($nomoption) === "700mL") {
                    //         $options_700mL = $nomoption;
                    //         continue;
                    //     }
                        
                    //     $liste_options_concat .= $nomoption . ",";
                        
                    // }

                    if (!empty($options) && is_array($options)) {
    foreach ($options as $option) {
        $idoptions = $option[0];
        $nomoption = remove_accents($option[1]);
        $options_sur_ticket[] = array('nom' => $nomoption, "prix" => $option[2]);

        if (trim($nomoption) === "700mL") {
            $options_700mL = $nomoption;
            continue;
        }

        $liste_options_concat .= $nomoption . ",";
    }
}
     
                    // Suppression de la dernière virgule
                    $liste_options_concat = rtrim($liste_options_concat, ",");
     
                    // Découpage et génération des commandes TSPL
                    while (strlen($liste_options_concat) > 0) {
                        $ligne = substr($liste_options_concat, 0, $max_caracteres);
                        
                        // Trouver le dernier espace ou virgule pour éviter de couper un mot
                        $last_break = max(strrpos($ligne, " "), strrpos($ligne, ","));
                        
                        if ($last_break !== false && strlen($ligne) == $max_caracteres) {
                            $ligne = substr($liste_options_concat, 0, $last_break + 1);
                        }
                        
                        $commandeListe .= "TEXT 10,".$dim_vertical_defaut.",\"2\",0,1,1,\"".trim($ligne)."\"\n";
                        $dim_vertical_defaut += 20;
                        
                        $liste_options_concat = substr($liste_options_concat, strlen($ligne));
                        $liste_options_concat = ltrim($liste_options_concat); // Supprime les espaces en début
                    }
     
                    if($options_700mL == ""){
                        $options_700mL = "500mL";
                    }
                    $numero_commande_sur_ticket = $numero_commande;
                    $numero_commande = mb_convert_encoding($numero_commande. " - " . $options_700mL . " [".$qte_sur_etiquette."]" , "ISO-8859-1", "UTF-8");
                    $titre_commande = mb_convert_encoding(remove_accents($titre), "ISO-8859-1", "UTF-8");
                    
                    
                    $commande = "SIZE 50 mm, 25 mm\n";
                    $commande .= "GAP 2mm, 0mm\n";
                    $commande .= "DIRECTION 1\n";
                    $commande .= "REFERENCE 0,0\n";
                    $commande .= "CLS\n";
                    $commande .= "TEXT 10,15,\"3\" ,0,1,1,\"$numero_commande\"\n";
                    $commande .= "TEXT 10,45,\"3\",0,1,1,\"$titre\"\n";
                    $commande .= "BAR 0,70,800,2 \n";
                    $commande .= $commandeListe;
                    $commande .= "PRINT $qte\n";
                    $fullcommande .= $commande . "\n\n";
                }else{
                    $numero_commande_sur_ticket = $numero_commande;
                }
                
 
            // FIN ENVOI ETIQUETTE
            $ligne_ticket = setStringLen($titre, $designiation_limit);
            if($ref == "remise"){
                $ligne_qte = setStringLen($qte . "*-" . $pu_euro, $qte_prix_limit);
                $ligne_tva = setStringLen($taux_tva_ticket, $tva_limit);
                $ligne_prix_commande = setStringLen("-".$prix_total_ticket, $mttc_limit, true);
            }else{
				$ligne_qte = setStringLen($qte . "*" . $pu_euro, $qte_prix_limit);
                $ligne_tva = setStringLen($taux_tva_ticket, $tva_limit);
                $ligne_prix_commande = setStringLen($prix_total_ticket, $mttc_limit, true);
				if(count($options_sur_ticket) > 0){
					$prixoption = 0;
					foreach ($options_sur_ticket as $option) {
						$prixoption+=$option["prix"];
					}
					$prixsansoption = $pu_euro-$prixoption;
					$ligne_qte = setStringLen($qte . "*" . formatNumber($prixsansoption,2), $qte_prix_limit);
					$ligne_prix_commande = setStringLen(formatNumber($prixsansoption,2), $mttc_limit+1, true);
				}
                
				
            }
            $ticket_ligne .= $ligne_qte . $ligne_ticket . "  " .$ligne_prix_commande . " " . $ligne_tva. "\n";
            // $promo = $ligne['promo'];
			if(count($options_sur_ticket) > 0 ){
				
				foreach ($options_sur_ticket as $option) {
					$prix = $option['prix'] == 0 ;
					$ticket_ligne .= setStringLen($option['nom'],25) . setStringLen($prix, $mttc_limit, true)."\n";
				}
				$ticket_ligne .= "\n";
			}
            if($promo>0){
                if($remise_pourcent>0){
                    $ticket_ligne .= str_repeat(" ",3).setStringLen("Remise",$designiation_limit-1)." ";
                    $ticket_ligne.= setStringLen("-".formatNumber((  $pu_euro * $qte - $promo * $qte - ($promo * ($remise_pourcent / 100) * $qte) )), $mttc_limit-1, true);
                }elseif($remise_euro>0){
                    $ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",9)." ";
                    $ticket_ligne.= setStringLen("-".formatNumber($remise_euro), $mttc_limit, true);
                }else{
                    $ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",9)." ";
                    if($retour != "false"){
                        $ticket_ligne .= setStringLen(formatNumber($pu_euro * $qte - $promo*$qte), $mttc_limit, true);
                    }else{
                        $ticket_ligne .= setStringLen("-".formatNumber($pu_euro * $qte - $promo*$qte), $mttc_limit, true);
                    }
                }
                $ticket_ligne .= "\n";
 
            }
            elseif($remise_pourcent > 0 ){
                $ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",9)." ";
                $ticket_ligne.= setStringLen("-".formatNumber(($pu_euro * ($remise_pourcent / 100) * $qte)), $mttc_limit, true);
                $ticket_ligne .= "\n";
            }
            elseif($remise_euro>0){
                $ticket_ligne .= str_repeat(" ",12).setStringLen("Remise ",9)." ";
                $ticket_ligne.= setStringLen("-".formatNumber($remise_euro), $mttc_limit+1, true);
                $ticket_ligne .= "\n";
            }
 
 
 
//        $ticket_ligne .= sprintf($ticket_layout, strval($qte) . "*" . strval($pu_euro), $titreTicket, strval($pu_euro) . "€", strval($taux_tva_rounded) . "%") . "\n";        
 
            if (strlen($ref) > 9 && strlen($ref) <= 13  ) {
                $sql = "SELECT choix_mode_prix,stock,stock_alerte,num FROM table_client_catalogue WHERE ref = '$ref'";
 
                $query_res = $conn->query($sql);
                $res = $query_res->fetch_row();
                $mode = $res[0];
                $stock = $res[1];
                $num = $res[3];
                $total_euro_du += ($mode == 3 ? $pu_euro * $qte : ($mode == 2 ? ($pu_euro + ($pu_euro * $taux_tva / 100)) : $pu_euro * $qte));
                // MISE A JOUR DU STOCK
                if ($stock > 0) {
                    $sql = "UPDATE table_client_catalogue 
                    SET stock = stock - $qte 
                    WHERE num = $num";
                    $updateCatalogue = $conn->query($sql);
                }
            }
            elseif($idproduit == "#DIVERS"){
                $total_euro_du += $pu_euro *$qte;
            }
            elseif($idproduit == "remise"){
                $total_euro_du -= $pu_euro * $qte;
                $total_remise_euro += $pu_euro * $qte;
            }
            $journal = $conn->query('SELECT journal FROM table_client_info where id = 1');
            if($journal->num_rows == 1){
                $journal = $journal->fetch_assoc();
                $nom_magasin = $journal['journal']; // nom magasin
 
                if ($nom_magasin == "ideco") {
                    $infomode = isset($mode) ? $mode : "no";
                    $datafile = "TOTAL EURO DU => ". $total_euro_du ." / pu_euro => ".$pu_euro." / qte => " . $qte . " / mode => ".$infomode. " / taux_tva => " . $taux_tva . " // REF => " . $ref ;
                    $fichier = '../journaux/'.$nom_magasin.'/total_euro_du-log.txt';
                    if(!file_exists($fichier)){
                        file_put_contents($fichier, $datafile);
                    }else{
                        $data = $datafile.PHP_EOL;
                        $fp = fopen($fichier, 'a');
                        fwrite($fp, $data);
                    }
                }
            }
        }
    }
 
    $echange_article = 0;
    $date = date('Y-m-d H:i:s');
    $printTicket = true;
 
 
    $detailsPayment = "";
    $paymentMutliple = "false";
 
    // RECUPERE LE MONTANT POUR CHAQUE TYPE DE PAIEMENT
    if(isset($request->premierPaiement,$request->paiements)){
        $paiements = $request->paiements;
        $p_espece = 0;
        $p_cb = 0;
        $p_cheque = 0;
        $p_restaurant = 0;
 
        $paiements = explode('|', $paiements);
        $premierPaiementSplitted = explode(",", $request->premierPaiement);
 
        foreach ($paiements as $paiement) {
            if($paiement != ""){
                $paiement = explode(',', $paiement);
 
                $typePaiement = $paiement[0];
                $montantPaiement = $paiement[1];
 
                if($typePaiement == 1){
                    $detailsPayment .= "  >  " .formatNumber($montantPaiement) . " EUR EN ESPECES" . "\n";
                    $p_espece += $montantPaiement;
                }
                elseif($typePaiement == 2){
                    $detailsPayment .= "  >  " .formatNumber($montantPaiement) . " EUR EN CB" . "\n";
                    $p_cb += $montantPaiement;
                }
                elseif($typePaiement === 3){
                    $detailsPayment .= "  >  " .formatNumber($montantPaiement) . " EUR EN CHEQUES" . "\n";
                    $p_cheque += $p_cheque;
                }
            }
 
        }
        $typePremierPaiement = ($premierPaiementSplitted[0] == 1 ? "ESPECES" : ($premierPaiementSplitted[0] == 2 ? "CB" : ($premierPaiementSplitted[0] == 3 ? "CHEQUES" : "")));
        if($premierPaiementSplitted[0] == 1){
            $p_espece += $premierPaiementSplitted[1];
        }
        elseif($premierPaiementSplitted[0] == 2){
            $p_cb += $premierPaiementSplitted[1];
        }
        elseif($premierPaiementSplitted[0] == 3){
            $p_cheque += $premierPaiementSplitted[1];
        }
        $detailsPayment =  "\n  >  " . formatNumber($premierPaiementSplitted[1]) . " EUR EN ".$typePremierPaiement ."\n". $detailsPayment;
 
        $paymentMutliple = "true";
    }
    elseif(isset($request->paiement2xCB) && $request->paiement2xCB == 1){
        $p_espece = (float)$request->espece;
        $p_cheque = (float)$request->cheques;
        $p_restaurant = (float)$request->ticket_restaurant;
        $p_cb = (float)$request->cb + (float)$request->cb2;
 
    }
    else{
        $p_espece = (float)$request->espece;
        $p_cb = (float)$request->cb;
        $p_cheque = (float)$request->cheques;
        $p_restaurant = (float)$request->ticket_restaurant;
    }
 
    $total_euro = $p_espece + $p_cb + $p_cheque + $p_restaurant;
 
    $total_remise = $total_remise_euro + $total_remise_pourcent;
    if ($p_espece == 0) {
            $printTicket = false; // ON OUVRE PAS LE TIROIR CAISSE SI PAS DE PAIEMENT EN ESPECE
        }
 
        $arendre = 0;
        if(isset($request->rendu)){
            if($request->rendu == "true"){
                $arendre = $request->arendre;
                if(!isset($request->paiements)){
                    $p_espece = $request->espece - $request->arendre;
                }
                if(isset($request->paiements)){
                    $detailsPayment .= "  >  MONNAIE RENDU " .formatNumber($arendre) . " EUR" ;
                }
                
            }
        }
		if (PHP_OS_FAMILY=="Windows") {
			$fichiercommande = "C:/xampp/htdocs/caisse-backend/bar/caisse/commande.txt";
		}else{
			$fichiercommande = "/var/www/localhost/caisse-backend/bar/caisse/commande.txt";
		}  
		file_put_contents($fichiercommande,$fullcommande);
                if (PHP_OS_FAMILY=="Windows") {
                    include ("C:/xampp/htdocs/caisse-backend/parametre.php");
                    try {
                        exec('c:\WINDOWS\system32\cmd.exe /c START C:\xampp\htdocs\caisse-backend\bar\tickets\imprimer.bat ' . escapeshellarg($imprimante_codebarre));
                        // unlink($fichier);
                    } catch (Exception $e) {
                        echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;
        
                    }
                }else{
                    include ("/var/www/localhost/caisse-backend/parametre.php");
                    try {
                        $type = "";
                        if ($port == "" && $ip_imprimante == "") {
                            $type = "usb";
                             exec("sh /var/www/localhost/caisse-backend/bar/tickets/imprimer.sh $imprimante_codebarre $type");
                        }else{
                            $type = "network";
                            exec("sh /var/www/localhost/caisse-backend/bar/tickets/imprimer.sh $ip_imprimante $port $type");
                        }
                        
                        // unlink($fichier);
                    } catch (Exception $e) {
                        echo "Une erreur s'est produite : " . $e->getMessage() . PHP_EOL;
                    }
                } 
        $sql = "INSERT INTO table_client_ticket (`id_caisse`, `p_cheque_euro`, `p_cb`, `p_espece_euro`, `p_restaurant`,`total_remise`,`deconsigne`, `retourarticle`, `echangearticle`, `total_euro`, `total_euro_du`, `qte_total`,`date`, `sendserveur`,`id_ticket`) 
        VALUES ($id_caisse,$p_cheque,$p_cb,$p_espece,$p_restaurant,$total_remise,$deconsigne,$retour_article,$echange_article,$total_euro,$total_euro_du,$qte_total, '$date',1,0)";
        
        $insertTicket=$conn->query($sql);
        
        if($insertTicket){
            $last_id = $conn->insert_id;
            $sql = "UPDATE table_client_ticket SET id_ticket = $last_id WHERE id = $last_id";
            $updateTicket = $conn->query($sql);
 
            // MAJ TABLE PAIMENT MULTIPLE
            if(isset($request->paiements,$request->premierPaiement)){
                $paiements = $request->paiements;
                $premierPayment = $request->premierPaiement;
                $sql = "INSERT INTO `table_paiement_temp`(`paiement`, `premierPaiement`, `id_ticket`, `id_caisse`, `session`) VALUES ('$paiements','$premierPayment','$last_id','$id_caisse','$session')";
                $paiement_multiple = $conn->query($sql);
            }elseif(isset($request->paiement2xCB) && $request->paiement2xCB == 1){
                $paiement2foisCB = $request->cb."-".$request->cb2;
                $sql = "INSERT INTO `table_paiement_temp`(`paiement`, `premierPaiement`, `id_ticket`, `id_caisse`, `session`) VALUES ('$paiement2foisCB','2fois','$last_id','$id_caisse','$session')";
                $query = $conn->query($sql);
                if($query){
                    $p_cb = $paiement2foisCB;
                }
            }
            $numero_ticket = "Numero de ticket: ".$last_id."\n";
        }
 
        
        $ticket .= $ticket_entete
        .$ticket_corps
        .$ticket_ligne
        ."\n"
        .$separator;
 
        $logo = file_get_contents('logo.txt');
        $logo = mb_convert_encoding($logo, "UTF-8");
        // $ticket .= $logo . "\n" .$ticket;
 
        $totalHT = $totalPanier - ($totalTVA8+$totalTVA2+$totalTVA1);
        $totalHT = (float)$totalHT;
        $totalPanier = formatNumber($totalPanier);
        $totalHT = formatNumber($totalHT);
        $totalTVA8 = formatNumber($totalTVA8);
        $totalTVA2 = formatNumber($totalTVA2);
       // $totalTVA1 = formatNumber($totalTVA1);
        if(strlen($totalPanier) > 5){
            $total_a_payer = str_repeat(" ", 10) ."TOTAL A PAYER TTC".str_repeat(" ",3).setStringLen($totalPanier,8)."EUR"; 
        }
        else{
            $total_a_payer = str_repeat(" ", 10) ."TOTAL A PAYER TTC".str_repeat(" ",4).setStringLen($totalPanier,6)." EUR"; 
        }
        $total_ht = str_repeat(" ", 19) ."TOTAL HT". str_repeat(" ",4).setStringLen($totalHT,6)." EUR";
        $total_tva8 = str_repeat(" ", 19) ."TVA 8.5%". str_repeat(" ",4).setStringLen($totalTVA8,6)." EUR";
        $total_tva2 = str_repeat(" ", 19) ."TVA 2.1%". str_repeat(" ",4).setStringLen($totalTVA2,6)." EUR";
        $total_tva1 = str_repeat(" ", 19) ."TVA 1.05%". str_repeat(" ",4).setStringLen($totalTVA1,6)." EUR";
 
        $ticket_part2 = $total_a_payer."\n";
        $ticket_part2 .= $total_ht ."\n";
 
        if($totalTVA8 != 0){
            $ticket_part2 .= $total_tva8 ."\n";
        }
        if($totalTVA2 != 0){
            $ticket_part2 .= $total_tva2 ."\n";
        }
        if($totalTVA1 != 0){
            $ticket_part2 .= $total_tva1."\n";
        }
 
		$footer_ticket = "T".$id_caisse."W".$last_id." - ".date('d/m/Y H:i:s',strtotime($date));
 
        // file_put_contents('ticket.txt', $ticket);
 
        // CREATION DES JOURNAUX
        $journal = $conn->query('SELECT journal FROM table_client_info where id = 1');
        if($journal->num_rows == 1){
            $journal = $journal->fetch_assoc();
            $nom_magasin = $journal['journal']; // nom magasin
            $details_paiement_journal = "  Details du paiement:";
            if ($p_espece > 0) {
                $details_paiement_journal .= "\n     > " . formatNumber($p_espece+$arendre) . " EUR EN ESPECES";
            }
            if ($arendre > 0) {
                $details_paiement_journal .= "\n      > MONNAIE RENDU " . formatNumber($arendre) . " EUR";
            }
            if ($p_cb > 0) {
                $details_paiement_journal .= "\n     > " . formatNumber($p_cb) . " EUR EN CB";
            }
            if ($p_cheque > 0) {
                $details_paiement_journal .= "\n     > " . formatNumber($p_cheque) . " EUR EN CHEQUE";
            }
            $fullticket = $ticket. $ticket_corps. "\n". $separator. $ticket_part2. "--------------------------------------". "\n\n".$details_paiement_journal
            . "\n\n". $ticket_pied. "\n\n". "  ======================================". "\n". "       " . $footer_ticket. "\n";
            $today = date('dmY');
            $filename = "journal_du_".$today.".txt";
            $filepath = '../journaux/'.$nom_magasin.'/'.$filename;
            if(!file_exists($filepath)){
                file_put_contents($filepath, $fullticket);
            }else{
                $data = $fullticket.PHP_EOL;
                $fp = fopen($filepath, 'a');
                fwrite($fp, $data);
            }
            
        }
        $solde_en_ligne = "";
        $ticket1 = 
            $ticket.  
            "\n". 
            // $separator. 
            $ticket_part2. 
            "--------------------------------------". 
            "\n\n";
            
            $ticket2=
            $details_paiement_journal. 
            "\n\n ";
            
            $ticket3 = $ticket_pied.
            "\n"
            ."-----------"
            ."\n"
            ."Caisse "
            .$id_caisse . "\n" . "Numero de commande " . $numero_commande_sur_ticket
            ."\n\n". 
            "  ======================================". 
            "\n". 
            $footer_ticket. 
            "\n";
         file_put_contents("../caisse/ticket.txt",$ticket1.$ticket2.$ticket3);
        // file_put_contents("ticket.txt",$ticket1);
 
        if ( $insertTicket == TRUE) {
            foreach ($commandes as $commande) {
                $idproduit = $commande->idproduit;
                $qte = $commande->qte;
                $pu_euro = $commande->pu_euro;
                $taux_tva = $commande->taux_tva;
                $famille = $commande->famille;
                $remise = $commande->remise;
                $promo = $commande->promo > 0 ? $pu_euro * $qte - $commande->promo * $qte : 0  ;
                $d = date('Y-m-d');
                $newCommandes = $conn->query("INSERT INTO `table_client_commandes`(`id_ticket`, `id_caisse`, `id_produit`, `qte`,`pu_euro`, `promo`, `remise`, `taux_tva`, `famille`, `date`, `sendserveur`) 
                    VALUES ($last_id,$id_caisse,'$idproduit',$qte,$pu_euro,$promo,$remise,$taux_tva,$famille,'$d',1)");
            }
 
                try {
                    $connector = null;
                    if($ip_imprimante_ticket !== "" && $port_ticket !== ""){
                        $connector = new NetworkPrintConnector($ip_imprimante_ticket, $port_ticket);
                    }
                    else if (PHP_OS_FAMILY=="Windows") {
                        $connector = new WindowsPrintConnector($imprimante_nom);
                    }else{
                        $connector = new CupsPrintConnector($imprimante_nom);
                        
                    }
 
                    $printer = new Printer($connector);
                    if(is_file('logo.png') && file_exists('logo.png')){
                        $img = EscposImage::load("../tickets/logo.png");
                    }
                    $printer->setTextSize(1,1);
                    if ($printTicket == true) {
 
                        
                        try {
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            if(is_file('logo.png') && file_exists('logo.png')){
                                $printer->bitImage($img);
                                $printer->feed(1);
                            }
                            $printer->pulse();
                            $printer -> text($ticket1);
                            $printer -> text($ticket2);
                            $printer -> text($ticket3);
                            $printer -> cut(Printer::CUT_PARTIAL);
                        } finally {
                            echo json_encode(array('response' => 1, "plugin"=>0,'message' => 'IMPRIME TICKET + OUVERTURE TIROIR CAISSE','session'=>$session,'id_caisse'=>$id_caisse));
                            $printer -> close();
                        }
                        
                        
                    // exit;
                    } else {
                        
                        try {
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            if(is_file('logo.png') && file_exists('logo.png')){
                                $printer->bitImage($img);
                                $printer->feed(1);
                            }
                            $printer -> text($ticket1);
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer -> text($ticket2);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            $printer -> text($ticket3);
                            $printer -> cut(Printer::CUT_PARTIAL);
                        } finally {
                            echo json_encode(array('response' => 1, "plugin"=>0,'message' => 'IMPRIME TICKET SEULEMENT','session' => $session, 'id_caisse' => $id_caisse,));
                            $printer -> close();
                        }
                        
                    // exit;
                    }
 
                    
                } catch (Exception $e) {
                    echo json_encode("Impossible d'imprimer sur cette imprimante: " . $e->getMessage() . "\n");
                    exit;
                }
            
            
        } else {
            echo json_encode(array('response' => 0, 'message' => 'ERREUR INSERTION TICKET'));
        }
    }
 