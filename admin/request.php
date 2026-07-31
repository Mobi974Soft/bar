<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../DBConfig.php';
include '../functions.php';
include '../infos.php';

function validateAndFixJson($jsonData) {
    $decodedData = json_decode($jsonData);

    // Check for JSON syntax errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Attempt to fix the JSON data (for example, by adding double quotes around property names)
        $jsonData = preg_replace('/([{,])([a-zA-Z_][a-zA-Z0-9_]*):/', '$1"$2":', $jsonData);

        // Try to decode the fixed JSON data
        $decodedData = json_decode($jsonData);

        
    }

    return $decodedData; // Valid JSON data
}

$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    if(isset($request->deleteArticleAdmin)){
        $ref = $request->deleteArticleAdmin;
        $sql = "DELETE FROM table_client_catalogue WHERE ref = '$ref'";
        $delete = $conn->query($sql);
        if($delete){
            $nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info')->fetch_assoc()['nom_magasin'];
            $timeStamp = time();
            $filename = "/home/TISbBLV45VICoXvj/CaisseDev/public_html/restaurant/synchro/".$nom_magasin."/catalogue/delete/delete_".$timeStamp.".json";
            file_put_contents($filename, json_encode([$sql]));
            echo successResponse('Article supprimé avec succès',1);
            
        }
        else{
            echo errorResponse('Echec de la suppression de l\'articlee.',0);
        }
    }
    elseif(isset($request->deleteGroup)){
        $ids = $request->deleteGroup;
        $i = 0;
        foreach ($ids as $id){
            $sql = "DELETE FROM table_client_catalogue WHERE num = $id";
            $delete = $conn->query($sql);
            $i++;
        }
        if($i==count($ids)){
            echo successResponse('Article(s) supprimés',1);
        }
        else{
            echo errorResponse('Une erreur c\'est produite ',0);
        }
    }elseif(isset($request->changeFamille)){
        $newFamille =  $request->changeFamille;
        $produits = $request->produits;
        $i = 0;
        foreach ($produits as $produit){
            $sql = "UPDATE table_client_catalogue SET cath = $newFamille WHERE num = $produit";
            $updateCat = $conn->query($sql);
            $i++;
        }
        if($i==count($produits)){
            echo successResponse('Catégorie mis à jour sur le(s) article(s) sélectionée(s) ! ',1);
        }
        else{
            echo errorResponse('Une erreur c\'est produite.',0);
        }
    }elseif(isset($request->changeFamilleImport)){
        $newFamille =  $request->changeFamilleImport;
        $json = preg_replace('/[[:cntrl:]]/', '', $request->produits);
        $produits = json_decode($json, true);
        // $produits = json_decode($json, true);
        $codebarre = $request->gencode;
        $ref = $request->ref;
        $prix = $request->prix;
        $quantite = $request->quantite;
        $multiplicateur = $request->multiplicateur;
        $arrondi = $request->arrondi;
        $titre = $request->titre;
        $i = 1;
        $premiereLigne = true;
        foreach ($produits as $ligne){
            if ($premiereLigne) {
                $premiereLigne = false;
                continue;
            }
            $codebarre = $request->gencode;
        $ref = $request->ref;
        $prix = $request->prix;
        $quantite = $request->quantite;
        $multiplicateur = $request->multiplicateur;
        $arrondi = $request->arrondi;
        $titre = $request->titre;
            $prix = $ligne[$prix];
            $prix = $prix * 3;
            $partieEntiere = floor($prix);
            $prix = $partieEntiere . ".90";
            
            $prix = (float) $prix;
            // echo $prix;die();
            $designation = $conn->real_escape_string($ligne[$titre]);
            $package = isset($ligne[$ref]) ;
            $gencode = $ligne[$codebarre]; 
            $stock_actuel = $ligne[$quantite];
            $stock_alerte = 99;
            $codetva = 8;
            $quantite = 0.0000;
            $unite = 0;
            $prix_variable = 0;
            $marge = 0;
            $mode_prix_3 = $prix;
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
                 $famille = $row['cath'];
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
                   $addStockMouvement = $conn->query("INSERT INTO `stock_mouvement`(`ref`, `mouvement_ajout`, `mouvement_qte`, `stock_initial`, `created_at`) VALUES ('$gencode','$stock_actuel','0','0','$dateajout')");
                   $success = true;
                   $nbarticle++;
               } 
         }else{
             $id_produit = random_strings(12);
             $sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`) 
             VALUES($newFamille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',0,$stock_actuel,$stock_alerte,$unite,$quantite,'$package',$prix_variable,'',1)";
             if ($conn->query($sql) === TRUE) {
                 $addStockMouvement = $conn->query("INSERT INTO `stock_mouvement`(`ref`, `mouvement_ajout`, `mouvement_qte`, `stock_initial`, `created_at`) VALUES ('$gencode','$stock_actuel','0','0','$dateajout')");
                 $success = true;
                 $nbarticle++;
                 $i++;
             } 
         }
         $sql = "UPDATE table_client_catalogue SET cath = $newFamille WHERE ref = $ligne[$gencode]";
         $updateCat = $conn->query($sql);

     }
     
     if($i==count($produits)){
        echo successResponse('Articles mis à jour ! ',1);
    }
    else{
        echo errorResponse('Une erreur c\'est produite.',0);
    }
}
elseif (isset($request->createFamille)){
    $nomcat = htmlspecialchars($request->createFamille);
    if($nomcat!= ""){
        $sql = "INSERT INTO `table_client_categorie`( `nomcategorie`, `branche`, `id_categorie`, `id_parent`,`sendserveur`) VALUES ('$nomcat','0','0','0','0')";
        $insertCat = $conn->query($sql);
        if ($insertCat){
            $last_id = $conn->insert_id;
            $sql = "UPDATE table_client_categorie SET id_categorie = $last_id WHERE id = $last_id";
            $updateCategorie = $conn->query($sql);
            if($updateCategorie){
                echo successResponse('Création de la catégorie réussi !',1);
            }
            else{
                echo errorResponse('Echec de la création de la catégorie.Veuillez réeesayez!',0);
            }
        }
    }
}elseif(isset($request->printLabelGroup)){
    $qte = $request->printLabelGroup;
    $titre = $request->titre;
    $barcode = $request->barcode;
    $prix = $request->prix;
    $package = $request->package;
    $promo = $request->promo;
    $promoFin = $request->promoFin;

    $zpl = formatLabel($titre,$prix,$barcode,$arrayPos,$package,$promo,$promoFin,$promodebut);

    if($zpl != null){
        echo successResponse($zpl,1);
    }
    else{
        echo errorResponse('Erreur',0);
    }

}elseif(isset($request->imprimeArticle)){
    $qte = $request->imprimeArticle;
    $titre = $request->titre;
    $prix = $request->prix;
    $promo = $request->promo;
    $ref = $request->barcode;
    $promofin = $request->promofin;
    $promodebut = $request->promodebut;
    $package = $request->package;
    
    $zpl = formatLabel($titre,$prix,$ref,$arrayPos,$package,$promo,$promofin,$promodebut);
    if($zpl != null){
        echo successResponse($zpl,1);
    }
    else{
        echo errorResponse('Erreur',0);
    }

}elseif(isset($request->cloture)){
    $espece = $request->espece;
    $cb = $request->cb;
    $cheque = $request->cheque;
    $total_euro = $total_euro_du = $espece + $cb + $cheque;
    $date = $request->date;
    $date = str_replace('/', '-', $date);
    $date = date('Y-m-d',strtotime($date));
    $datevaleur = $date . " 20:00:00";
    $sql = "INSERT INTO table_client_ticket (`id_caisse`, `p_cheque_euro`, `p_cb`, `p_espece_euro`, `p_restaurant`,`total_remise`,`deconsigne`, `retourarticle`, `echangearticle`, `total_euro`, `total_euro_du`, `qte_total`,`date`, `sendserveur`,`id_ticket`) 
    VALUES (99,$cheque,$cb,$espece,0,0,0,0,0,0,0,0, '$date',1,0)";
    $query = $conn->query($sql);
    if ($query) {
        $sql = "INSERT INTO `table_client_valeurcaisse`(`id_caisse`, `qte_articles`, `p_especes_euro`, `p_cheque_euro`, `p_cb`, `retourarticle`, `total_euro`, `total_euro_du`, `date`, `sendserveur`) VALUES (99,0,0,0,0,'0','0','1','$datevaleur','0')";
        $query = $conn->query($sql);
        if($query){
            echo json_encode(array('dateNew' => $date,'response' => 1,'todayDate'=>date('Y-m-d')));
        }else{
            echo json_encode(array('response' => 0 , "sql" => $sql));
        }
    }
    else{
        echo $sql;
    }
}elseif (isset($request->deleteCategorie) && isset($request->idcat)) {
    $id = $request->idcat;
    $sql = "DELETE FROM table_client_categorie WHERE id_categorie = $id";
    $query = $conn->query($sql);

    if ($query) {
        echo 1;
    }

}elseif(isset($request->updateQuantityStock)){
    $qte_up = $request->qte_value;
    $article_id = $request->articleID;

    $sql = "UPDATE table_client_catalogue SET stock = stock + $qte_up WHERE num = $articleID";
    $update = $conn->query($sql);

    if ($update) {
        echo json_encode(array('response' => 1 , 'message' => 'Stock mis a jour'));
    }else{
        echo json_encode(array('response' => 0 , 'message' => $sql));
    }
}elseif(isset($request->mouvementStock)){
    $mouvementStock = $request->mouvementStock;
    $type = $request->type;
    $ref = $request->ref;
    $ajout = 0;
    $modif = 0;
    $stock_initial = $conn->query("SELECT stock FROM table_client_catalogue WHERE ref = '$ref' ")->fetch_assoc()['stock'];
    if ($type == "ajout") {
        $query = "UPDATE table_client_catalogue SET stock = stock + $mouvementStock WHERE ref = '$ref' ";
        $ajout = $mouvementStock;
    }elseif ($type == "modif") {
        $query = "UPDATE table_client_catalogue SET stock = $mouvementStock WHERE ref = '$ref' ";
        $modif = $mouvementStock;
    }
    $update = $conn->query($query);
    if ($update) {
        $date = date('d/m/Y H:i:s');
        $query = "INSERT INTO `stock_mouvement`(`ref`, `mouvement_ajout`, `mouvement_qte`,`stock_initial`,`created_at`) VALUES ('$ref','$ajout','$modif','$stock_initial','$date')";
        if ($conn->query($query)) {
           echo 1;
       }else{
        echo 0;
    }
}

}
elseif(isset($request->imprimeSingleTag)){
    $content = $request->imprimeSingleTag;

    $textes = explode( PHP_EOL, wordwrap( $content, 25, PHP_EOL ) );
    if(count($textes)>6){
        echo json_encode(array('response'=> 0 ));
        die();
    }
    $zpl = "";
    $height = 20;
    foreach($textes as $texte){
        $zpl .= "^FO50,".$height."^A0N,30,30^FD".$texte."^FS";
        $height+=30;
    }
    $zpl .= "^XZ";

    echo json_encode(array('response' => 1 , 'zpl' => $zpl));
}elseif(isset($request->accueil)){
    $accueil = $request->accueil;
    $ref = $request->ref;

    $update=  $conn->query("UPDATE table_client_catalogue SET accueil = 0 WHERE ref = '$ref' ");
    if ($update) {
        echo json_encode(array('response'=>1 , 'message' => "Mise a jour réussie"));
    }
}
elseif(isset($request->numoption)){
    $numoption = $request->numoption;

    $delete=  $conn->query("DELETE FROM options_produit WHERE idoption = $numoption ");
    if ($delete) {
        echo json_encode(array('response'=>1 , 'message' => "Option supprimée !"));
    }
}
}

?>
