<?php

include('../DBConfig.php');
include('../functions.php');


// MISE A JOUR CATALOGUE
$sql = "SELECT * FROM table_client_variable WHERE num = 1";
$query = $conn->query($sql);
$variables = $query->fetch_assoc();
$modif_serveur_ajout_catalogue = $variables['modif_serveur_ajout_catalogue'];
$timestamp_ajout_catalogue = strtotime($modif_serveur_ajout_catalogue);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://caisse.mobisoft.fr/restaurant/test.php?catalogue_ajout=$timestamp_ajout_catalogue");
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
if ($result) {
    $newArticle = json_decode($result);
    if (count($newArticle) > 0) {
        foreach ($newArticle as $item) {
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

            $sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`)
            VALUES($famille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',0,$stock_actuel,$stock_alerte,$unite,$quantite,'',$prix_variable,'',1)";
            $ajoutArticle = $conn->query($sql);
            if ($ajoutArticle) {
                $sql = "UPDATE table_client_variable SET modif_serveur_ajout_catalogue = '$dateajout' WHERE num = 1";
                $updateSync = $conn->query($sql);
//                if($updateSync){
//                    echo "ok";
//                }
            }
        }

    }
}


$modif_serveur_modif_catalogue = $variables['modif_serveur_modif_catalogue'];
$timestamp_modif_catalogue = strtotime($modif_serveur_modif_catalogue);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://caisse.mobisoft.fr/restaurant/test.php?catalogue_modif=$timestamp_modif_catalogue");
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
if ($result) {
    $updateArticle = json_decode($result);
    if (count($newArticle) > 0) {
        foreach ($newArticle as $item) {
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
            if ($updateArticle) {
                $sql = "UPDATE table_client_variable SET modif_serveur_ajout_catalogue = '$dateajout' WHERE num = 1";
                $updateSync = $conn->query($sql);
//                if($updateSync){
//                    echo "ok";
//                }
            }
        }

    }
}

