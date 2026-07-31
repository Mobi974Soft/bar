<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../DBConfig.php';
include '../functions.php';
include '../infos.php';

$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);

    if(isset($request->deleteArticleAdmin)){
        $ref = $request->deleteArticleAdmin;
        $sql = "DELETE FROM table_client_catalogue WHERE ref = '$ref'";
        $delete = $conn->query($sql);
        if($delete){
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
    }elseif (isset($request->createFamille)){
        $nomcat = htmlspecialchars($request->createFamille);
        if($nomcat!= ""){
            $sql = "INSERT INTO `table_client_categorie`( `nomcategorie`, `branche`, `id_categorie`, `id_parent`) VALUES ('$nomcat','0','0','0')";
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

        $zpl = formatLabel($titre,$prix,$barcode,$arrayPos,$package,$promo,$promoFin);

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
        $package = $request->package;

        $zpl = formatLabel($titre,$prix,$ref,$arrayPos,$package,$promo,$promofin);
        if($zpl != null){
            echo successResponse($zpl,1);
        }
        else{
            echo errorResponse('Erreur',0);
        }

    }
    // elseif(isset($request->creerFamille)){
    //     $nomcategorie = $request->creerFamille;
    //     $stmt = $conn->prepare("INSERT INTO table_client_categorie (nomcategorie, branche, id_categorie,id_parent) VALUES (?, ?, ?, ?)");
    //     $stmt->bind_param("siii", $nomcategorie, $branche, $id_categorie,$id_parent);
    //     $nomcategorie = $nomcategorie;
    //     $branche = 0;
    //     $id_categorie = 0;
    //     $id_parent = 0;

    //     if($stmt->execute()){
    //          echo successResponse('Création de la catégorie réussi !',1);
    //     }


    // }
}

?>
