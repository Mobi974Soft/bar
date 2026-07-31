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
include '../tickets/print-ticket.php';

$postdata = file_get_contents('php://input');

$startDate = date("Y-m-d", strtotime("-2 month"));
$endDate =  date("Y-m-d", strtotime("last day of -2 month"));
$end = new DateTime($endDate);
$end->setTime(0,0,1);
$period = new DatePeriod(
	new DateTime($startDate),
	new DateInterval('P1D'),
	$end
);

foreach ($period as $key => $value) {

     // ON RECUPERE TOUS LES TICKETS PAR JOUR 
	$startDate = $value->format('Y-m-d')." 00:00:00";
	$endDate = $value->format('Y-m-d')." 23:59:59";
	$sql = "SELECT date,total_euro,total_euro_du,p_espece_euro,p_cb,p_cheque_euro,id_caisse,id_ticket	 FROM table_client_ticket WHERE date between '$startDate' AND '$endDate'";
	$ticket_info = $conn->query($sql);
	while($ticket_details = $ticket_info->fetch_assoc()){
		$prix_total_ticket = 0;
		$totalTTC = 0;
		$totalHT = 0;
		$totalTVA8 = 0;
		$totalTVA1 = 0;
		$totalTVA2 =0;
		$totalTVA1 = 0;
		$ticket_ligne = "";
		$ticket = "";
		$retour_article = 0;
		$date = $ticket_details['date'];
		$total_euro = $ticket_details['total_euro'];
		$total_euro_du = $ticket_details['total_euro_du'];
		$arendre = $total_euro > $total_euro_du ? $total_euro - $total_euro_du : 0;
		$id_caisse = $ticket_details['id_caisse'];
		$numero_ticket = $ticket_details['id_ticket'];
		// ON RECUPERE LES COMMANDES PAR TICKET
		$sql = "SELECT ct.titre as titre,c.pu_euro as pu_euro, c.qte as qte,c.remise as remise, c.promo as promo,c.taux_tva as taux_tva FROM table_client_commandes c INNER JOIN table_client_catalogue ct ON c.id_produit = ct.id WHERE id_ticket = $numero_ticket ;";
		$commandes = $conn->query($sql);

		$sql = "SELECT * FROM `table_paiement_temp` WHERE id_ticket = $numero_ticket";
		$paiementsQuery = $conn->query($sql);

		$detailsPayment = "";
		$paiementMultiple="false";

		// ON RECUPERE LES DONNEES POUR L'AFFICHAGE DES MULTIPAIEMENT POUR LE DETAILS DES PAIEMENTS
		if($paiementsQuery->num_rows==1){
			$p_espece = 0;
			$p_cb = 0;
			$p_cheque = 0;
			$paiementMultiple = "true";
			$paiementsQuery = $paiementsQuery->fetch_assoc();
			$paiement_column = $paiementsQuery["paiement"];
			$premierPaiement = $paiementsQuery['premierPaiement'];

			$paiements = explode('|', $paiement_column);
			$premierPaiementSplitted = explode(",", $premierPaiement);

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

		}

		// AFFICHAGE DEU DETAILS DE PAIEMENT SI PAS DE MULTIPAIEMENT
		if($paiementMultiple=="false"){
			$p_espece = $ticket_details['p_espece_euro'];
			$p_cb = $ticket_details['p_cb'];
			$p_cheque = $ticket_details['p_cheque_euro'];
			$detailsPayment = "  Details du paiement:";
			if ($p_espece > 0) {
				$detailsPayment .= "\n     > " . formatNumber($p_espece+$arendre) . " € EN ESPECES";
			}
			if ($arendre > 0) {
				$detailsPayment .= "\n      > MONNAIE RENDU " . formatNumber($arendre) . " EUR";
			}
			if ($p_cb > 0) {
				$detailsPayment .= "\n     > " . formatNumber($p_cb) . " € EN CB";
			}
			if ($p_cheque > 0) {
				$detailsPayment .= "\n     > " . formatNumber($p_cheque) . " € EN CHEQUE";
			}
		}

		// AFFICHAGES DES COMMANDES SUR LE TICKET 
		if($commandes->num_rows>0){
			while($row = $commandes->fetch_assoc()){

				$titre = $row['titre'];
				$qte = $row['qte'];
				$pu_euro = $row['pu_euro'];
				$prix = $pu_euro * $row['qte'];
				$prix_total_ticket+=$prix;
				$taux_tva = $row['taux_tva'];
				$taux_tva_ticket = $taux_tva . "%";
				$remise = $row['remise'];
				$promo = $row['promo'] ;
				if(substr($qte, -2) == "00"){
					$qte = substr($qte, 0, -3);
				}else{
					$qte = substr($qte, 0, -1);
					$qte_prix_limit+= 2;
					$designiation_limit-=2;
				}

				$ligne_ticket = setStringLen($titre, $designiation_limit);
				$ligne_prix_commande = setStringLen(formatNumber($prix), $mttc_limit, true);
				$ligne_tva = setStringLen($taux_tva_ticket, $tva_limit);
				$ligne_qte = setStringLen($qte . "*" . $pu_euro, $qte_prix_limit);
				$ticket_ligne .= $ligne_qte . $ligne_ticket . "  " .$ligne_prix_commande . " " . $ligne_tva. "\n";
				
				if($remise>0){
					$ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",$designiation_limit).str_repeat(" ",12);
					$ticket_ligne.= setStringLen("-".formatNumber($prix  - $remise  ), $mttc_limit, true);
					$ticket_ligne .= "\n";
				}
				elseif($promo>0){
					if($remise>0){
						$ticket_ligne .= str_repeat(" ",3).setStringLen("Remise".$remise."",$designiation_limit-1)." ";
						$ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",$designiation_limit).str_repeat(" ",12);
						$ticket_ligne.= setStringLen("-".formatNumber(($prix  - ($promo*$qte))-$remise), $mttc_limit, true);
					}
					else{
						$ticket_ligne .= str_repeat(" ",12).setStringLen("Remise",9)." ";
						$ticket_ligne .= setStringLen("-".formatNumber(   ($prix  - ($promo*$qte)) ), $mttc_limit, true);
					}
					$ticket_ligne .= "\n";
					
				}

				$totalTTC += $pu_euro * $qte - $remise -$promo;
				$totalHT += (($pu_euro - $remise -$promo) / (1+($taux_tva/100)))*$qte;

				if($taux_tva == 8.5){

					if($remise>0){

						$prix_apres_remise = $pu_euro - $remise -$promo;
						$montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
						$totalTVA8 += $montant_tva * $qte;
					}
					else{
						$totalTVA8 += ($pu_euro -($pu_euro / (1+$taux_tva/100))) * $qte;
					}
				}
				if($taux_tva == 2.1){

					if($remise>0){
						$prix_apres_remise = $pu_euro - $remise -$promo;
						$montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
						$totalTVA2 += $montant_tva * $qte;
					}
					else{
						$totalTVA2 += ($pu_euro -($pu_euro / (1+$taux_tva/100))) * $qte;
					}
				}
				if($taux_tva == 1.05){

					if($remise>0){
						$prix_apres_remise = $pu_euro - $remise -$promo;
						$montant_tva = $prix_apres_remise - ($prix_apres_remise/(1+$taux_tva/100));
						$totalTVA1 += $montant_tva * $qte;
					}
					else{
						$totalTVA1 += ($pu_euro -($pu_euro / (1+$taux_tva/100))) * $qte;
					}
				}
			}

			$numero_ticket = $numero_ticket;
			$ticket .= $ticket_entete
			.$ticket_corps
			.$ticket_ligne
			."\n"
			.$separator;


			$totalTTC = formatNumber($totalTTC);
			$totalHT = formatNumber($totalHT);
			$totalTVA8 = formatNumber($totalTVA8);
			$totalTVA2 = formatNumber($totalTVA2);
			$totalTVA1 = formatNumber($totalTVA1);
			$total_a_payer = str_repeat(" ", 10) ."TOTAL A PAYER TTC". str_repeat(" ",4).setStringLen($total_euro_du,6)." EUR"; ;
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



			$footer_ticket = "T".$id_caisse."W".$numero_ticket." - ".date('d/m/Y H:i:s',strtotime($date));
			$journal = $conn->query('SELECT journal FROM table_client_info where id = 1');
			if($journal->num_rows == 1){
				$journal = $journal->fetch_assoc();
        		$nom_magasin = $journal['journal']; // nom magasin
        		$fullticket = $ticket. $ticket_corps. "\n". $separator. $ticket_part2. "--------------------------------------". "\n\n".$detailsPayment
        		. "\n\n".  "\n\n". "  ======================================". "\n". "       " . $footer_ticket. "\n";
        		$dateTicket = date('dmY',strtotime($startDate));
        		$filename = "journal_du_".$dateTicket.".txt";
        		// $filepath = '../journaux/'.$nom_magasin.'/'.$filename;
        		$filepath = '../journaux/test/'.$filename;
        		if(!file_exists($filepath)){
        			file_put_contents($filepath, $fullticket);
        		}else{
        			$data = $fullticket.PHP_EOL;
        			$fp = fopen($filepath, 'a');
        			fwrite($fp, $data);
        		}

        	}

        }

    }
}



?>