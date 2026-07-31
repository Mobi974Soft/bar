<?php
set_time_limit(0);

//$filepath = "caisse.txt";
//$file = fopen($filepath, "a");

/*init 0 */
$today = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
$today_start_ok = mktime(16,30,0,date("m"),date("d"),date("Y"));
$today_end_ok = mktime(20,30,0,date("m"),date("d"),date("Y"));
//if($today <= $today_start_ok || $today > $today_end_ok){
//	echo 'Mauvaise heure';
//	die();
//}

/*init 1*/
$nowY = date("Y");
$nowM = date("n");
$fichier = "C:/xampp/htdocs/restaurant/pass-mareux.php";
$handle = fopen("$fichier","r");
$content = fread($handle,filesize($fichier));
fclose($handle);
$pat = '/SIR\#.*\#/';
preg_match($pat,$content,$match);
$tmp = explode('#',$match[0]);
$num = $tmp[1] + 0;
$nowY = intval($nowY);
$nowM = intval($nowM);
/*code entr� par user*/
//$code = intval($argv[1]);
/*on genere le code combo*/
$combo = $num*$nowY*$nowM;
$combo = substr($combo,0,7);
$combo = str_replace('.','',$combo);
$combo = intval($combo);

/*on recupere l'id caisse*/
$pat2 = '/.id_caisse=.*;/';
preg_match($pat2,$content,$match);
$the_id_caisse = intval(substr($match[0],11,-1));

$today = date("Y-m-d H:i:s");
$today = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
$today_start = mktime(3,0,0,date("m"),date("d"),date("Y"));
$today_end = mktime(23,0,0,date("m"),date("d"),date("Y"));

//if($code != $combo){
//	echo "Code faux";
//	die();
//}


//--partie synchro caisse et server
//include "C:/xampp/htdocs/restaurant/pass-mareux.php";
include "C:/xampp/htdocs/restaurant/parametre.php";
include "C:/xampp/htdocs/restaurant/functions.php";
include "C:/xampp/htdocs/restaurant/DBConfig.php";

//if($ip_serveur == "localhost") {
//	echo "ne pas lancer sur le serveur\n";
//	die();
//}
//$ip_serveur = "192.168.1.250";
//$connexion_locale = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
$connexion_locale = $conn;
$connexion_serveur = remoteConnexion($ip_serveur, $user, $password,$usebdd);
if($conn === false || $connexion_serveur === false) {
	echo "Impossible de se connecter\n";
	die();
}

$date_limite = date("Y-m", strtotime('last month')) . '-01';

$query_recup_nb_lignes = "SELECT id_ticket, COUNT(*) as nb_ligne FROM table_client_commandes where date > '$date_limite' GROUP BY id_ticket";
$res_locale = mysqli_query($conn,$query_recup_nb_lignes);
$res_serveur = mysqli_query($connexion_serveur,$query_recup_nb_lignes);


$nb_lignes_ticket_locale = array();
$nb_lignes_ticket_serveur = array();

while($ligne = mysqli_fetch_assoc($res_locale)) {
	$nb_lignes_ticket_locale[$ligne['id_ticket']] = $ligne;
}
while($ligne = mysqli_fetch_assoc($res_serveur)) {
	$nb_lignes_ticket_serveur[$ligne['id_ticket']] = $ligne;
}
foreach($nb_lignes_ticket_locale as $ticket) {


//	var_dump($nb_lignes_ticket_serveur[$ticket['id_ticket']]);
	if(!isset($nb_lignes_ticket_serveur[$ticket['id_ticket']]))
	continue;
	if($ticket['nb_ligne'] != $nb_lignes_ticket_serveur[$ticket['id_ticket']]['nb_ligne']) {
		echo $ticket['id_ticket'] . ': ' . $ticket['nb_ligne'] . '|' . $nb_lignes_ticket_serveur[$ticket['id_ticket']]['nb_ligne'] . "\r\n";

		//on supprime le ticket du serveur
		$query = "DELETE FROM table_client_commandes WHERE id_ticket = " . $ticket['id_ticket'];
		$response = mysqli_query($connexion_serveur,$query);

		$query = "select * from table_client_commandes where id_ticket = " . $ticket['id_ticket'];
		$resp = mysqli_query($connexion_locale , $query );

		while ($ligne = mysqli_fetch_assoc($resp)) {

			$query= "INSERT INTO table_client_commandes (`id_ticket`, `id_caisse`, `id_produit`, `qte`,`pu_euro`, `promo`, `remise`, `taux_tva`, `famille`, `date`, `sendserveur`) VALUES (". $ligne['id_ticket'].",". $ligne['id_caisse'].",'". $ligne['id_produit']."',". $ligne['qte'].",". $ligne['pu_euro'].",". $ligne['promo'].",". $ligne['remise'].",". $ligne['taux_tva'].",". $ligne['famille'].",'". $ligne['date']."',". $ligne['sendserveur'] .")";
			$response = mysqli_query($connexion_serveur,$query);
		}
		echo "insertion \r\n";
		//echo "insertion <br>";
	}

}
//--fin partie synchro caisse et server


$start = date("Y-m-d H:i:s",$today_start);
$end = date("Y-m-d H:i:s",$today_end);

$dbhost = "localhost";
$user = "root";
$password = "";
$usebdd = "mobipos";

@$connexion = mysqli_connect("$dbhost","$user","$password");
$db = mysqli_select_db($connexion,"$usebdd");

/*add data*/
$q = "SELECT * FROM table_client_variable";
$r = mysqli_query($connexion,$q);

/*pour la table commande*/
$id_ticket_tmp = null;
$id_produit_tmp = null;
$qte_tmp = 1;
$promo_tmp = 0;
$pu_deref_tmp = 0.00;
$taux_tva_tmp = null;
$famille_tmp = null;
$date_tmp = date("Y-m-d");
$sendserver_tmp = 0;

/*pour la table ticket*/
$id_ticket_tmp = null;
$p_cheque_euro_t_tmp = 0.00;
$p_cheque_franc_euro_t_tmp = 0.00;
$p_cb_t_tmp = 0.00;
$p_cc_t_tmp = 0.00;
$p_or_t_tmp = 0.00;
$p_ck_t_tmp = 0.00;
$p_vi_t_tmp = 0.00;
$p_fg_t_tmp = 0.00;
$p_espece_franc_t_tmp = 0.00;
$p_espece_euro_t_tmp = 0.00;
$p_credit_t_tmp = 0.00;
$deconsigne_t_tmp = 0.00;
$retourarticle_t_tmp = 0.00;
$echangearticle_t_tmp = 0.00;
$total_euro_t_tmp = 0.00;
$total_euro_du_t_tmp = 0.00;
$qte_t_tmp = 1;
$date_t_tmp = date("Y-m-d H:i:s");
$sendserver_t_tmp = 0;


$resultat = mysqli_fetch_assoc($r);
//$id_ticket_tmp = mysqli_result($r,0,"compteurcommande");
$id_ticket_tmp = $resultat["compteurcommande"];
$id_ticket_tmp ++;

$q = "UPDATE table_client_variable SET compteurcommande = ".$id_ticket_tmp;
$r = mysqli_query($connexion,$q);

$q = "SELECT * FROM table_client_catalogue WHERE prixttc_euro < 1.50 ORDER BY rand() LIMIT 1";
$r = mysqli_query($connexion,$q);
$resultat = mysqli_fetch_assoc($r);
//$rnd = rand(0,mysqli_num_rows($r));

//$id_produit_tmp = mysqli_result($r,$rnd,"id");
$id_produit_tmp = $resultat['id'];

switch(mysqli_result($r,$rnd,"code_tva")){
	case 0:
		$taux_tva_tmp = 0.00;
		break;
	case 1:
		$taux_tva_tmp = 1.05;
		break;
	case 2:
		$taux_tva_tmp = 2.10;
		break;
	case 8:
		$taux_tva_tmp = 8.50;
		break;
	default:
		$taux_tva_tmp = 0.00;
}
die();
$famille_tmp = mysql_result($r,$rnd,"cath");
$pu_euro_tmp = mysql_result($r,$rnd,"prixttc_euro");

$p_espece_euro_t_tmp = $total_euro_t_tmp = $total_euro_du_t_tmp = $pu_euro_tmp;
$id_caisse = $the_id_caisse;

/*on ajoute dans la table commande*/
$q = "INSERT INTO table_client_commandes VALUES (null,".$id_ticket_tmp.",".$id_caisse.",'".$id_produit_tmp."',".$qte_tmp.",".$pu_euro_tmp.",".$promo_tmp.",".$pu_deref_tmp.",".$taux_tva_tmp.",".$famille_tmp.",'".$date_tmp."',".$sendserver_tmp.")";
$r = mysql_query($q,$connexion);
/*on ajoute dans la table ticket*/
$q2 = "INSERT INTO table_client_ticket VALUES (null,".$id_ticket_tmp.",".$id_caisse.",".$p_cheque_euro_t_tmp.",".$p_cheque_franc_euro_t_tmp.",".$p_cb_t_tmp.",".$p_cc_t_tmp.",".$p_or_t_tmp.",".$p_ck_t_tmp.",".$p_vi_t_tmp.",".$p_fg_t_tmp.",".$p_espece_franc_t_tmp.",".$p_espece_euro_t_tmp.",".$p_credit_t_tmp.",".$deconsigne_t_tmp.",".$retourarticle_t_tmp.",".$echangearticle_t_tmp.",".$total_euro_t_tmp.",".$total_euro_du_t_tmp.",".$qte_t_tmp.",'".$date_t_tmp."',".$sendserver_t_tmp.")";
$r2 = mysql_query($q2,$connexion);

/* Mise � jour de valeurcaisse */
$q = "SELECT * FROM table_client_valeurcaisse WHERE date = '".$date_tmp."'";
$r = mysql_query($q,$connexion);

if (mysql_num_rows($r)>0){
	while($ligne = mysql_fetch_assoc($r)){
		/* Ajout de la valeur espece de la commande ajoutee au total et montant espece de la caisse */
		$total_euro_du = $ligne["total_euro_du"] + $p_espece_euro_t_tmp;
		$p_especes_euro = $ligne["p_especes_euro"] + $p_espece_euro_t_tmp;
		$total_euro =  $ligne["total_euro"] + $p_espece_euro_t_tmp;
		$qte_articles = $ligne["qte_articles"]+1;
		$q = "UPDATE table_client_valeurcaisse SET total_euro_du = ".$total_euro_du.", p_especes_euro = ".$p_especes_euro.", total_euro = ".$total_euro.", qte_articles = ".$qte_articles." WHERE date = '".$ligne["date"]. "' AND id_caisse = ".$id_caisse;
		$r = mysql_query($q,$connexion);
	}
}

/*
* RECUPERATION DES TICKETS PAYES UNIQUEMENT EN ESPECES
*/
$tickets = array();
$tickets_id = array();

$q = "SELECT * FROM table_client_ticket WHERE p_espece_euro != 0";
$q .= " AND p_cheque_euro = 0";
$q .= " AND p_cheque_franc_euro = 0";
$q .= " AND p_cb = 0";
$q .= " AND p_cc = 0";
/*$q .= " AND p_or = 0";*/
$q .= " AND p_ck = 0";
$q .= " AND p_vi = 0";
$q .= " AND p_fg = 0";
$q .= " AND p_espece_franc = 0";
$q .= " AND p_credit = 0";
$q .= " AND date > '".$start."'";
$q .= " AND date < '".$end."'";

$r = mysql_query($q,$connexion);

while($ligne = mysql_fetch_assoc($r)){
	array_push($tickets,$ligne);
	array_push($tickets_id,$ligne["id_ticket"]);
}

/*
* RECUPERATION DES COMMANDES DES TICKETS PRECEDENTS
*/
$commandes = array();
$commandes_to_keep = array();
$commandes_to_delete = array();
$commandes_id_to_delete = array();
$tmp_ticket_id = null;
$nbLigneParTicket_tmp = array(); /*id_ticket,nbLigne*/
$nbLigneParTicket = array();
$ensemble_prix_espece_a_retirer = array();
$caisse_prix_espece_a_retirer = 0;

/*on compte le nombre de ligne par ticket*/
$q = "SELECT id_ticket, COUNT(*) as nbLigne FROM table_client_commandes WHERE id_ticket IN (".join(",",$tickets_id).") GROUP BY id_ticket";
$r = mysql_query($q,$connexion);

while($ligne = mysql_fetch_assoc($r)){
	array_push($nbLigneParTicket_tmp,$ligne);

}




$s_nbLigneParTicket_tmp = array();
$s_dbhost = "192.168.1.250";
$s_connexion = mysql_connect("$s_dbhost","$user","$password");
$db = mysql_select_db("mareux", $s_connexion);

$s_q = "SELECT id_ticket, COUNT(*) as nbLigne FROM table_client_commandes group by id_ticket";
$s_r = mysql_query($s_q,$s_connexion); //---azerty
while($ligne = mysql_fetch_assoc($s_r)){
	//array_push($s_nbLigneParTicket_tmp,$ligne);
	$s_nbLigneParTicket_tmp[$ligne['id_ticket']] = $ligne;
}

foreach($nbLigneParTicket_tmp as $ticket) {
	if($ticket['id_ticket'] == $tmp_id_ticket)
		continue;
	if($ticket['nbLigne'] != $s_nbLigneParTicket_tmp[$ticket['id_ticket']]['nbLigne']) {
		echo $ticket['id_ticket'] . "|";

		//on supprime le ticket du serveur
		$query = "DELETE FROM table_client_commandes WHERE id_ticket = " . $ticket['id_ticket'];
		$response = mysql_query($query, $s_connexion);

		$q = "select * from table_client_commandes where id_ticket = " . $ticket['id_ticket'];
		$r = mysql_query($q, $connexion);

		while ($ligne = mysql_fetch_assoc($r)) {
			//--array_push($lignes_caisse, $ligne);
			$num = $ligne['num'] + 1791;
			$query= "INSERT INTO table_client_commandes (id_ticket, id_caisse, id_produit, qte, pu_euro, promo, pu_deref, taux_tva, famille, date, sendserveur) VALUES (". $ligne['id_ticket'].",". $ligne['id_caisse'].",'". $ligne['id_produit']."',". $ligne['qte'].",". $ligne['pu_euro'].",". $ligne['promo'].",". $ligne['pu_deref'].",". $ligne['taux_tva'].",". $ligne['famille'].",'". $ligne['date']."',". $ligne['sendserveur'] .")";

			$response = mysql_query($query, $s_connexion);
			if($response == false)
				echo"erreur importation";
		}

	}
}







/*on divise par 2 le nb de ligne par ticket*/
foreach($nbLigneParTicket_tmp as $t){
	$tmp_id_ticket = $t['id_ticket'];
	$tmp_nb = ceil($t['nbLigne']*0.30);
	$nbLigneParTicket[$tmp_id_ticket]=$tmp_nb;
}

$q = "SELECT * FROM table_client_commandes WHERE id_ticket IN (".join(",",$tickets_id).") ORDER BY id_ticket, (qte * pu_euro) ASC";
$r = mysql_query($q,$connexion);
$d_montant_garde = 0;
$d_montant_retire = 0;
/* Pour chaque commande renvoy�e */
while($ligne = mysql_fetch_assoc($r)){
	/* On ajoute la ligne en cours � la liste des commandes */
	array_push($commandes,$ligne);
	/* Si la commande est sur un nouveau ticket */
	if($tmp_ticket_id != $ligne["id_ticket"]){
		/* On recupere le nb de ligne du ticket en cours */
		$tmp_nbLigne = $nbLigneParTicket[$ligne["id_ticket"]];
		/* On initialise le compteur de ligne � 0 */
		$compteur_ligne = 0;
	}
	/* Si c'est la premi�re ligne du ticket */
	if($compteur_ligne == 0){
		/* On ajoute la commande dans l'ensemble � garder */
		array_push($commandes_to_keep,$ligne);
		//fwrite($file, "ajout commande a garder: " . $ligne['id_ticket'] . ", num: " . $ligne['num'] . ", montant: " . $ligne["qte"]*$ligne["pu_euro"] . "\r\n");
		$d_montant_garde += ($ligne["pu_euro"] * $ligne["qte"]);
		/* On passe � la ligne suivante */
		$compteur_ligne++;
	/* Pour les autres lignes, si la commande est dans le ticket en cours */
	}else if($tmp_ticket_id == $ligne["id_ticket"]){
		/* Si le compteur est inf�rieur au nombre de commandes � conserver */
		if($compteur_ligne < $tmp_nbLigne){
			/* On stock les lignes que l'on va garder */
			array_push($commandes_to_keep,$ligne);
			//fwrite($file, "ajout commande a garder: " . $ligne['id_ticket'] . ", num: " . $ligne['num'] . ", montant: " . $ligne["qte"]*$ligne["pu_euro"] . "\r\n");
			$d_montant_garde += ($ligne["pu_euro"] * $ligne["qte"]);
		/* Si le compteur a d�pass� la limite */
		}else{
			/* On stock les id des lignes a supprimer */
			array_push($commandes_id_to_delete,$ligne["num"]);
			array_push($commandes_to_delete,$ligne);
			//fwrite($file, "ajout commande a delete: " . $ligne['id_ticket'] . ", num: " . $ligne['num'] . ", montant: " . $ligne["qte"]*$ligne["pu_euro"] . "\r\n");
			$d_montant_retire += ($ligne["pu_euro"] * $ligne["qte"]);
			/* On ajoute leur prix au total � retirer du montant esp�ce du ticket */
			$prix_espece_a_retirer = $ligne["qte"]*$ligne["pu_euro"];
			$ensemble_prix_espece_a_retirer[$tmp_ticket_id] += $prix_espece_a_retirer;
			$caisse_prix_espece_a_retirer += $prix_espece_a_retirer;
		}
		/* On passe � la ligne suivante */
		$compteur_ligne++;
	}
	/* On stock l'id du ticket en cours */
	$tmp_ticket_id = $ligne["id_ticket"];
}
//fwrite($file, "total garde: " . $d_montant_garde ."\r\n");
//fwrite($file, "total retir: " . $d_montant_retire."\r\n");
/*
print_r($commandes_to_keep);
print_r($commandes_to_delete);
*/

/*
* MISE A JOUR TICKETS
*/
$tmp_ticket_id = null;
$tmp_qte = 0;
$tmp_pu_euro = 0;
$tmp_total_euro_du = 0;
$i = 0;
foreach($commandes_to_keep as $cmd){
	$i++;
	/*on recalcule chaque ticket*/
	if($tmp_ticket_id != $cmd["id_ticket"]){
		if($tmp_total_euro_du != 0){
			$q = "UPDATE table_client_ticket SET total_euro_du = ".$tmp_total_euro_du.", qte_total = ".$tmp_qte." WHERE id_ticket = ".$tmp_ticket_id;
			$r = mysql_query($q,$connexion);
		}

		$tmp_qte = $cmd["qte"];
		$tmp_pu_euro = $cmd["pu_euro"];
		$tmp_total_euro_du = $cmd["qte"]*$cmd["pu_euro"];
	}else{
		$tmp_qte += $cmd["qte"];
		$tmp_pu_euro += $cmd["pu_euro"];
		$tmp_total_euro_du += $cmd["qte"]*$cmd["pu_euro"];
	}

	/*on stock l'id du ticket en cours*/
	$tmp_ticket_id = $cmd["id_ticket"];

	/*pour le dernier*/
	if($i == count($commandes_to_keep)){
		$q = "UPDATE table_client_ticket SET total_euro_du = ".$tmp_total_euro_du.", qte_total = ".$tmp_qte." where id_ticket = ".$tmp_ticket_id;
		$r = mysql_query($q,$connexion);
	}
}

/* Pour chaque ticket */
foreach ($tickets as $ticket) {
	/* Retrait du montant especes des commandes supprimees */
    $ancien_montant_especes = $ticket["p_espece_euro"]+0;
    $prix_espece_a_retirer = 0;
    if($ensemble_prix_espece_a_retirer[$ticket["id_ticket"]] > 0) {
    	$prix_espece_a_retirer = $ensemble_prix_espece_a_retirer[$ticket["id_ticket"]];
    }
    $nouveau_montant_especes = $ancien_montant_especes - $prix_espece_a_retirer;
    $nouveau_montant_total = $nouveau_montant_especes;
	/* Update de la table ticket */
	$q = "UPDATE table_client_ticket SET total_euro = ".$nouveau_montant_total.", p_espece_euro = ".$nouveau_montant_especes." where id_ticket = ".$ticket["id_ticket"];
	$r = mysql_query($q,$connexion);
}

/*
* MISE A JOUR VALEUR CAISSE
*/
$tmp_date = null;
$tmp_qte = 0;
$tmp_pu_euro = 0;
$data = array();
$data_date = array();

/* Comme la date des commandes est la meme
 * soit celle du jour on peut modifier le
 * code ci-dessous pour les commandes_to_delete
 */



foreach($commandes_to_delete as $cmd){
	/*
	if($tmp_date != $cmd["date"]){
		$tmp_qte = $cmd["qte"];
		$tmp_pu_euro = $cmd["pu_euro"];
	}else{
		$tmp_qte += $cmd["qte"];
		$tmp_pu_euro += $cmd["pu_euro"];
	}

	$data[$cmd["date"]] = array($tmp_qte,$tmp_pu_euro,$cmd["id_caisse"]);
	array_push($data_date,"'".$cmd["date"]."'");
	$tmp_date = $cmd["date"];
	*/


	$tmp_qte += $cmd["qte"];
	$tmp_pu_euro += $cmd["pu_euro"];


	$data[$cmd["date"]] = array($tmp_qte,$tmp_pu_euro,$cmd["id_caisse"]);
	array_push($data_date,"'".$cmd["date"]."'");
	$tmp_date = $cmd["date"];
}

/*
$data_date = array_unique($data_date);
print_r($data_date);
print_r($data);
*/


/*$q = "SELECT * FROM table_client_valeurcaisse WHERE date IN (".join(",",$data_date).")";*/
$q = "SELECT * FROM table_client_valeurcaisse WHERE date = '".$tmp_date."'";
$r = mysql_query($q,$connexion);

if (mysql_num_rows($r)>0){
	while($ligne = mysql_fetch_assoc($r)){
		foreach($data as $key => $value){
			if($key == $ligne["date"]){
				/*$total_euro_du = $ligne["total_euro_du"]-($value[1]*$value[0]);*/
				/* Retrait de la valeur espece des commandes enlevees au total et montant espece de la caisse */
				$total_euro_du = $ligne["total_euro_du"] - $caisse_prix_espece_a_retirer;
				$p_especes_euro = $ligne["p_especes_euro"] - $caisse_prix_espece_a_retirer;
				$total_euro =  $ligne["total_euro"] - $caisse_prix_espece_a_retirer;
				$qte_articles = $ligne["qte_articles"]-$value[0];
				$q = "UPDATE table_client_valeurcaisse SET total_euro_du = ".$total_euro_du.", p_especes_euro = ".$p_especes_euro.", total_euro = ".$total_euro.", qte_articles = ".$qte_articles." WHERE date = '".$ligne["date"]. "' AND id_caisse = ".$value[2];
				$r = mysql_query($q,$connexion);
			}
		}
	}
}

/*
* SUPPRESSION DES LIGNES DES COMMANDES RECUPEREES PRECEDEMMENT
*/
$q = "DELETE FROM table_client_commandes WHERE num IN (".join(",",$commandes_id_to_delete).")";
$r = mysql_query($q,$connexion);

/*
* REGENERER JOURNAL
*/

/*
*	FONCTION
*/
function aligne($prix_euro,$nbcar,$align,$sep){
	$a1=strlen("$prix_euro");
	if ($a1>$nbcar) $prix_euro=substr("$prix_euro",0,$nbcar);

	if ($a1<$nbcar){
	$a=0;
		while($a<$nbcar-$a1){

			if ($align=="droite"){
			$prix_euro=$sep.$prix_euro;
			} else {
			$prix_euro=$prix_euro.$sep;
			}

		$a++;
		}
	}

	return $prix_euro;
}


/*
*	VARIABLES
*/
$tickets_id = array();

/*ne pas oublier de changer le chemin*/
$rep_journal = "C:/journal/";

/*pour vider le journal il faut la date du jour et l'id caisse*/
$date = date("d-m-Y");

$q = "SELECT id_caisse FROM table_client_ticket GROUP BY id_caisse";
$r = mysql_query($q,$connexion);

while($ligne = mysql_fetch_assoc($r)){
	$caisse = $ligne["id_caisse"];
	$filename = "journal_du_".$date."_caisse".$caisse.".txt";
	$fichier=$rep_journal.$filename;
	$fp = fopen( "$fichier", "w");
	fclose($fp);
}


$var_entete_ligne_ticket=" Qte*PU   Designation       Mttc  TVA";
$var_sep_ligne_ticket="                          --------";

$q = "SELECT * FROM table_client_ticket";
$q .= " WHERE date > '".$start."'";
$q .= " AND date < '".$end."'";


$r = mysql_query($q,$connexion);

while($ligne = mysql_fetch_assoc($r)){
	array_push($tickets_id,$ligne["id_ticket"]);
}

/*
*	CORPS
*/

$id_ticket = null;
for($i=0;$i<count($tickets_id);$i++){

	$id_ticket = $tickets_id[$i];

	$q = "SELECT * FROM table_client_ticket WHERE id_ticket = ".$id_ticket;
	$r = mysql_query($q,$connexion);

	/*echo $q." NEXT ";*/

	$especes = mysql_result($r,0,'p_espece_euro');
	$cb = mysql_result($r,0,'p_cb');
	$cheque = mysql_result($r,0,'p_cheque_euro');
	$dateheure = mysql_result($r,0,'date');
	/* TODO : formattage de la date DD/MM/YYYY */
	$id_caisse = mysql_result($r,0,'id_caisse');
	$r_ticket_corps = "";
	$total_ttc = 0;
	$rendu = 0;
	$info_titre = array();

	/*on recupere les commandes que l'on va traiter separemment car "ai#DIVERS #DIVERS" n'existe pas*/
	$q = "SELECT qte as qte, pu_euro as pu, taux_tva as tva, id_produit FROM table_client_commandes WHERE id_ticket = ".$id_ticket;
	$r = mysql_query($q,$connexion);

	while ($ligne = mysql_fetch_assoc($r)){
		$total = number_format(round($ligne["qte"]*$ligne["pu"],2),2);
		$colone1=aligne($ligne["qte"],3,"droite"," ");
		$colone3=aligne($total." �",9,"droite"," ");
		$colone4=aligne($ligne["tva"],1,"droite"," ");
		if($ligne["pu"]!="") $ligne["pu"] = "*".$ligne["pu"];
		$colone5=aligne($ligne["pu"],6,"gauche"," ");

		/*if ($param_ticket_tva!=1){
		$colone2=aligne($ligne["titre"],15,"gauche"," ");
		$r_ticket_corps.="$colone1$colone5 $colone2   $colone3\n";
		} else {*/

		if($ligne["id_produit"] == "ai#DIVERS #DIVERS"){
			$titre = "DIVERS";
		}else{
			$q2 = "SELECT * FROM table_client_catalogue WHERE id = '".$ligne["id_produit"]."'";
			$r2 = mysql_query($q2,$connexion);
			$titre = mysql_result($r2,0,'titre');
		}
		
		$colone2=aligne($titre,13,"gauche"," ");
				
		if ($colone4!=" ") $colone4.="%";
		$r_ticket_corps.="$colone1$colone5 $colone2  $colone3 $colone4".chr(13).chr(10);
		//}
		
		$tva = 8.5;
		$total_ttc = number_format(round($total_ttc + $total,2),2);
		$total_ht = number_format(round($total_ttc/(1+(8.5/100)),2),2);
		$total_tva = number_format(round($total_ttc - $total_ht,2),2);
		
		$col_ttc_1 = aligne("",6,"droite"," ");
		$col_ttc_2 = aligne("TOTAL A PAYER TTC",17,"droite"," ");
		$col_ttc_3 = aligne("",1,"droite"," ");
		$col_ttc_4 = aligne($total_ttc." �",9,"droite"," ");
		$r_ticket_total_ttc = "$col_ttc_1 $col_ttc_2 $col_ttc_3 $col_ttc_4".chr(13).chr(10);
		
		$col_ht_1 = aligne("",6,"droite"," ");
		$col_ht_2 = aligne("TOTAL HT",17,"droite"," ");
		$col_ht_3 = aligne("",1,"droite"," ");
		$col_ht_4 = aligne($total_ht." �",9,"droite"," ");
		$r_ticket_total_ht = "$col_ht_1 $col_ht_2 $col_ht_3 $col_ht_4".chr(13).chr(10);
		
		$col_tva_1 = aligne("",6,"droite"," ");
		$col_tva_2 = aligne("TVA $tva %",17,"droite"," ");
		$col_tva_3 = aligne("",1,"droite"," ");
		$col_tva_4 = aligne($total_tva." �",9,"droite"," ");
		$r_ticket_total_tva = "$col_tva_1 $col_tva_2 $col_tva_3 $col_tva_4".chr(13).chr(10);
		
		$col_tvac_1 = aligne("",6,"droite"," ");
		$col_tvac_2 = aligne("TAXES (CUMUL TVA)",17,"droite"," ");
		$col_tvac_3 = aligne("",1,"droite"," ");
		$col_tvac_4 = aligne($total_tva." �",9,"droite"," ");
		$r_ticket_total_tvac = "$col_tvac_1 $col_tvac_2 $col_tvac_3 $col_tvac_4".chr(13).chr(10);
		
	}

	/* Initialisation des affichages � vide */
	$r_ticket_paiement_especes = "";
	$r_ticket_paiement_cb = "";
	$r_ticket_paiement_cheque = "";
	$r_ticket_paiement = "";

	/* Affichage du paiement par esp�ces */
	if($especes != 0){
		$r_ticket_paiement_especes = "> $especes � EN ESPECES";
		/* Rendu des esp�ces */
		$rendu = number_format(round($especes - $total_ttc,2),2);
	}
	
	/* Affichage du paiement par CB */
	if($cb != 0){
		$r_ticket_paiement_cb = "> $cb � EN CARTE CB-CA";
		$rendu = 0;
	}
	
	/* Affichage du paiement par ch�que */
	if($cheque != 0){
		$r_ticket_paiement_cheque = "> $cheque � EN CHEQUE";
		$rendu = 0;
	}
	
	/* Concat�nation des lignes d'affichage de paiement */
	$r_ticket_paiement = $r_ticket_paiement_cb.chr(13).chr(10)."   "
						.$r_ticket_paiement_especes.chr(13).chr(10)."   "
						.$r_ticket_paiement_cheque.chr(13).chr(10);

	/* Affichage de la monnaie rendue */
	if($rendu != 0){
		$ticket_monnaie_a_rendre = "MONNAIE RENDUE EN EUROS: $rendu �";
	}else{
		$ticket_monnaie_a_rendre = "";
	}

	
	/*
	* NE PAS INDENTER $journal
	*/

$journal="#####################################
### $dateheure # T$id_caisse"."W$id_ticket ###
#####################################

$var_entete_ligne_ticket
--------------------------------------

$r_ticket_corps
$var_sep_ligne_ticket
$r_ticket_total_ttc
$r_ticket_total_ht$r_ticket_total_tva$r_ticket_total_tvac
--------------------------------------

   Details du paiement:
   $r_ticket_paiement
   
   $ticket_monnaie_a_rendre
   
";
	
	$tmp_date = explode(" ",$dateheure);
	$date = date("d-m-Y", strtotime($tmp_date[0]));
	//$fichier=$rep_journal."journal_du_".gmdate("d-m-Y",time()+14400)."_caisse$id_caisse.txt";
	$filename = "journal_du_".$date."_caisse$id_caisse.txt";
	$fichier=$rep_journal.$filename;
	$fp = fopen( "$fichier", "a");
	fputs($fp,  "$journal");
	fclose($fp);

}


?>
