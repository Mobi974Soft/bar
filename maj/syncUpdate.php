<?php 

ini_set('max_execution_time', '3000'); //300 seconds = 5 minutes
ini_set('max_execution_time', '0'); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include('../DBConfig.php');
include('../functions.php');
$postdata = file_get_contents('php://input');
date_default_timezone_set('Indian/Reunion');
$request = json_decode($postdata);


$client = $conn->query("SELECT siret FROM client");
$client = $client->fetch_assoc();
$siret = "9874563211478";
        // MISE A JOUR CATALOGUE
$sql = "SELECT * FROM table_client_variable WHERE num = 1";
$query = $conn->query($sql);
$variables = $query->fetch_assoc();
$modif_serveur_modif_catalogue = $variables['modif_serveur_modif_catalogue'];
    $timestamp_modif_catalogue = strtotime($modif_serveur_modif_catalogue);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://evo.mobisoft.fr/bar/maj/update.php?catalogue_modif=$timestamp_modif_catalogue&modif=1&siret=$siret");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


    $headers = array();
    $headers[] = "Accept: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $result = json_decode($result);
    
    if (isset($result->serverModif)) {
        $server = $result->serverModif;
        $local = $result->localModif;

        if ($server>$local) {
            $sql = "SELECT * FROM table_client_catalogue WHERE datemodif BETWEEN '$server' AND '$local'";
        }else{
            $sql = "SELECT * FROM table_client_catalogue WHERE datemodif BETWEEN '$local' AND '$server'";
        }

        $query = $conn->query($sql);

        if($query->num_rows>0){
            $data = array();
            while($row = $query->fetch_assoc()){
                $data[] = $row;
            }
            $url = "https://evo.mobisoft.fr/bar/maj/updateCatalogue.php?siret=$siret";
            $ch = curl_init( $url );

            $json = json_encode( array("modifCatalogue"=>$data) );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $json );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec($ch);
            var_dump($result);
            
        }


    }elseif(isset($result->dataModif)){
        $updateArticle = $result->dataModif;
           
        if (count($updateArticle) > 0) {


            foreach ($updateArticle as $item) {
                $famille = $item->cath;
                $id_produit = $item->id;
                $gencode = $item->ref;
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
                $accueil = $item->accueil;
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
                `accueil` = $accueil,
                `stock`=$stock_actuel,
                `stock_alerte`=$stock_alerte,
                `unite`=$unite,
                `qte_unite`=$quantite,
                `package`='$package',
                `prix_variable`=$prix_variable,
                `img` = NULL,
                `send_web` = 1 WHERE id = '$id_produit' ";
                $updateArticle = $conn->query($sql);
                if ($updateArticle) {
                    $sql = "UPDATE table_client_variable SET modif_serveur_modif_catalogue = '$datemodif' WHERE num = 1";
                    $updateSync = $conn->query($sql);
                    if($updateSync){
                     echo "modif";
                 }
             }
         }

     } 
 }
 ?>
