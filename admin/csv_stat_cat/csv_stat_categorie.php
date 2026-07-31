<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../../DBConfig.php');

$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info');
$nom_magasin = $nom_magasin->fetch_assoc()['nom_magasin'];

date_default_timezone_set('Indian/Reunion');
$today_date = date('Y-m-d');

$sql = "SELECT sum(CASE WHEN c.id_produit != 'remise' THEN (c.pu_euro*c.qte - c.remise - c.promo) ELSE (pu_euro*-qte - remise - promo) END) as totalByCat,nomcategorie FROM `table_client_commandes` c 
INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
WHERE c.date >= '$today_date' 
GROUP BY famille ORDER BY totalByCat DESC";

$commandes = $conn->query($sql);

$sql = "SELECT sum(CASE WHEN c.id_produit != 'remise' THEN (c.pu_euro*c.qte - c.remise - c.promo) ELSE (pu_euro*-qte - remise - promo) END) as totalHC FROM `table_client_commandes` c where date LIKE '$today_date' and famille NOT IN (SELECT id_categorie FROM table_client_categorie)";
$horscategorie = $conn->query($sql);
$total_hc = $horscategorie->fetch_assoc()["totalHC"];

$filename = "$nom_magasin.csv";
foreach ($commandes as $commande) {
	$total_par_cat = $commande['totalByCat'];
	$nomcategorie = $commande['nomcategorie'];
	if ($total_par_cat > 0 ) {

		if (!file_exists($filename)) {
			$output = fopen($filename, 'w');
			fputcsv($output, array('Date', 'nomcategorie', 'total_par_cat'));
			$data = array(date('Y-m-d'),$nomcategorie,$total_par_cat);
			fputcsv($output, $data);
		}else{
			$data = array(date('Y-m-d'),$nomcategorie,$total_par_cat);
			fputcsv($output, $data);
		}

	}
}

if ($total_hc>0) {
	$data = array(date('Y-m-d'),"horscategorie",$total_hc);
	fputcsv($output, $data);
}



?>