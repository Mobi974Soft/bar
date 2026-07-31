<?php 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include('../../DBConfig.php');

require '../../vendor/autoload.php'; // Chargez la bibliothèque PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Spécifiez le nom du fichier Excel à lire
$nomFichierExcel = 'cideal.xls';

// Chargez le fichier Excel
$spreadsheet = IOFactory::load($nomFichierExcel);

// Sélectionnez la feuille de calcul
$feuille = $spreadsheet->getActiveSheet();


// Récupérez les données de la feuille de calcul
$donnees = $feuille->toArray();


function random_strings($length_of_string)
{

    // String of all alphanumeric character
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // Shuffle the $str_result and returns substring
    // of specified length
    return substr(str_shuffle($str_result),
       0, $length_of_string);
}

// Parcourez les données et insérez-les dans la base de données
$premiereLigne = true;
$nbarticle = 0;
foreach ($donnees as $ligne) {
    if ($premiereLigne) {
                // Ignorez la première ligne
        $premiereLigne = false;
        continue;
    }

    $stock_actuel = $conn->real_escape_string($ligne[7]);
    $stock_alerte = 99;
    
    $prix = $conn->real_escape_string($ligne[9]);
    $prix = $prix * 3;
    $partieEntiere = floor($prix);
    $prix = $partieEntiere . ".90";
    $prix = (float) $prix;

    $designation = $conn->real_escape_string($ligne[6]);
    $gencode = $conn->real_escape_string($ligne[13]); 
    $famille = 14;
    $codetva = 8.5;
    $package = ""  ;
    $quantite = 0.0000;
    $unite = 0;
    $prix_variable = 0;
    $marge = 0;
    $mode_prix_3 = $conn->real_escape_string($ligne[9]);
    $mode_prix_2 = 0.00;
    $mode_prix_1_achat = 0.00;
    $mode = 3;
    $promottc = 0.00;
    $promo_debut = "0000-00-00";
    $promo_fin = "0000-00-00";
    $dateajout =  date('Y-m-d H:i:s');
    $check = $conn->query("SELECT * FROM table_client_catalogue WHERE ref = '$gencode' ");
    if ($check->num_rows>0) {
        while($row = $check->fetch_assoc()){
            $dateajout = $row['dateajout'];
            $cath = $row['cath'];
            $codetva = $row['code_tva'];
            $stock_actuel = $row['stock'] + $stock_actuel;
        }
        
        $colisage = "" ;
        $datemodif =  date('Y-m-d H:i:s');
        $raccourci = 0;
        $sql = "UPDATE table_client_catalogue SET 
        `cath`= $famille, 
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
        `accueil` = $raccourci,
        `stock`=$stock_actuel,
        `stock_alerte`=$stock_alerte,
        `unite`=$unite,
        `qte_unite`=$quantite,
        `package`='$colisage',
        `prix_variable`=$prix_variable,
        `img` = NULL,
        `send_web` = 1 WHERE ref = '$gencode' " ;
        if ($conn->query($sql) === TRUE) {
            echo "Mise a jour réussi." . "\n";
            $nbarticle++;
        } else {
            echo "Erreur lors de l'insertion de l'enregistrement : " . $conn->error;
        }
    }else{
        $id_produit = random_strings(12);
        $sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`) 
        VALUES($famille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',0,$stock_actuel,$stock_alerte,$unite,$quantite,'$package',$prix_variable,'',1)";
        

        if ($conn->query($sql) === TRUE) {
            echo "Enregistrement inséré avec succès." . "\n";
            $nbarticle++;
        } else {
            echo "Erreur lors de l'insertion de l'enregistrement : " . $conn->error;
        }
    }


    
    
    
    
    
}

 ?>