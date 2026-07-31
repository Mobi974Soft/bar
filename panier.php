<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include 'DBConfig.php';
include 'functions.php';

$postdata = file_get_contents('php://input');
$response = array();
if (isset($postdata)) {
    $request = json_decode($postdata);

    if (isset($request->article)) {
        $id_produit = $request->article->id;;
        $ref = $request->article->ref;
        $credit = 0;
        $pu_euro = $request->article->prixttc_euro;
        $tva = $request->article->code_tva;
        $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));
        $remise = 0;
        $qte = 1;
        $titre = $request->article->titre;
        $date = time();
        $id_caisse = $request->id_caisse;
        $session = "1";
        $retour = 'false';


        // $query = mysqli_query($conn, "SELECT ref FROM table_client_panier WHERE ref='".$ref."'");

        $sql = "SELECT ref FROM table_client_panier WHERE ref=" . $ref;
        $query = mysqli_query($conn, $sql);
        $panier = $conn->query($sql)->fetch_assoc();

        if ($panier == null) {
            $sql = "INSERT INTO table_client_panier (`session`,`id_produit`, `ref`, `qte`, `credit`, `pu_euro`, `promo`, `retour`, `famille`, `titre`, `taux_tva`,`date`, `remise`, `id_caisse`, `idtable`) VALUES ('" . $session . "','" . $id_produit . "' ,'" . $ref . "', $qte, 0 , $pu_euro, 0, $retour ,0,'" . $titre . "',$taux_tva,$date, $remise, $id_caisse, $session)";
            $conn->query($sql);
        } else {
            $sql = "UPDATE table_client_panier SET qte= qte + 1 WHERE ref=" . $panier['ref'];
            $conn->query($sql);
        }

    }

    else if (isset($request->articleDivers)) {

        $ref_inconnu = '164750';
        $session = $request->session;
        $id_produit = "#DIVERS";
        $id_caisse = $request->idcaisse;
        $counter = mysqli_query($conn, "SELECT count FROM table_counter WHERE type = 'jiji'");
        $row = $counter->fetch_row();
        $count = $row[0];
        $ref = 'articleinconnu' . $ref_inconnu . $count;

        $credit = 0;
        $pu_euro = $request->articleDivers;
        $tva = $request->tvaDivers;
        $qte = $request->qteDivers;
        $remise = 0;
        $remise_euro = 0;
        $titre = "Divers";
        $famille = 14;
        $date = time();
        $retour = 'false';
        $prixttc_promo_euro=0;

        $sql = "INSERT INTO table_client_panier 
    ( `id_caisse`, `id_produit`, `session`, `ref`, `qte`, `pu_euro`, `remise_euro`, `retour`,  `titre`, `taux_tva`, `date`, `remise`, `famille`, `idtable`,  `en_cuisine`,  `options`) 
    VALUES ( $id_caisse,'$id_produit',$session ,'$ref', $qte, $pu_euro, $remise_euro, 'false' ,'$titre',8.5,$date,0,$famille,$session,0, 1)";
        $insertDivers = $conn->query($sql);
        if ($insertDivers) {
            $conn->query("UPDATE table_counter SET count = count + 1 WHERE type = 'produit_divers'");
            $json = regenerePanier($conn, "SELECT * FROM table_client_panier", 'jsons/panier.json');
            $calculTotal = calculTotal($conn,$session,$id_caisse);
            $total = $calculTotal[0];
            $qteTotal = $calculTotal[1];
            $data = array('titre' => $titre, 'pu_euro' => $pu_euro, 'remise' => $remise, 'remise_euro' => $remise_euro, 'session' => $session,  'ref' => $ref, "qte" => $qte, "promo" => $prixttc_promo_euro);
            echo json_encode(array('response' => 1, 'data' => $data,'total' => $total,'qteTotal'=>$qteTotal));
            die();
        }
    }
    else if(isset($request->remisePanier)){
        $remisePanier = $request->remisePanier;
        $session = $request->session;
        $id_caisse = $request->idcaisse;
        $totalPanier = $request->totalPanier;
        $montant = $totalPanier * ($remisePanier / 100);
        $titre = "Remise Exceptionnelle de " . $remisePanier . "%";
        $date = time();
        $sql = "INSERT INTO table_client_panier 
    (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `famille`, `titre`, `taux_tva`,`date`, `remise`) 
    VALUES ( $session,'remise','remise',1,  $id_caisse ,$montant, 0, 'true' ,0,'$titre',8.5,$date, 0)";
        $insertRemisePanier = $conn->query($sql);
        if ($insertRemisePanier) {
            $json = regenerePanier($conn, "SELECT * FROM table_client_panier", 'jsons/panier.json');
            echo json_encode(array('response' => 1));
            die();
        }
    }
    else if (isset($request->retourArticle)) {

        $id_caisse = $request->idcaisse;
        $session = $request->session;

        $date = time();
        $retour = $request->retourArticle;


        if (isset($request->retourArticleCatalogue)) {
            $ref = $request->retourArticleCatalogue;
            $sql = "SELECT * FROM table_client_catalogue WHERE ref = '$ref'";
            $checkProduit = $conn->query($sql);
            if ($checkProduit) {
                $sql = "SELECT * FROM table_client_panier WHERE ref= '$ref' AND retour = $retour AND session = $session AND id_caisse = $id_caisse ";
                $query = $conn->query($sql);
                if ($query->num_rows > 0) {
                    $sql = "UPDATE table_client_panier SET qte = qte + 1 WHERE ref= '$ref' AND retour = $retour AND session = $session AND id_caisse = $id_caisse";
                    $conn->query($sql);
                } else {
                    $newRetour = $checkProduit->fetch_assoc();
                    $id_produit = $newRetour['id'];
                    $qte = 1;
                    $pu_euro = $newRetour['prixttc_euro'];
                    $promo = $newRetour['prixttc_promo_euro'];
                    $remise_euro = 0;
                    $famille = $newRetour['cath'];
                    $tva = $newRetour['code_tva'];
                    $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));
                    $date = time();
                    $remise = 0;
                    $titre = 'RET ART: ' . $newRetour['titre'];
                    $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`,`promo`, `famille`, `titre`, `taux_tva`,`date`, `remise`) 
                VALUES ($session,'$id_produit' ,'$ref', $qte, $id_caisse , $pu_euro, $remise_euro, $retour ,$promo,$famille,'$titre',$taux_tva,$date, $remise)";
                    $newRetourArticle = $conn->query($sql);
                    if ($newRetourArticle) {
                        $json = regenerePanier($conn, "SELECT * FROM table_client_panier", 'jsons/panier.json');
                        echo json_encode(array('response' => 1));
                        die();
                    } else {
                        echo 0;
                    }
                }
            }
        } else if (isset($request->retourArticleDivers)) {
            $pu_euro = $request->retourArticleDivers;
            $qte = $request->retourQteDivers;
            $taux_tva = $request->retourTvaDivers;
            $ref = 0;
            $id_produit = "#DIVERS";
            $titre = 'RET ART: DIVERS';
            $remise = $remise_euro = 0;
            $famille = 14;
            $date = time();
            $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`,`famille`, `titre`, `taux_tva`,`date`, `remise`) 
                VALUES ($session,'$id_produit' ,$ref, $qte, $id_caisse , $pu_euro, $remise_euro, $retour ,0,$famille,'$titre',$taux_tva,$date, $remise)";
            $newRetourDivers = $conn->query($sql);
            if ($newRetourDivers) {
                $json = regenerePanier($conn, "SELECT * FROM table_client_panier", 'jsons/panier.json');
                echo json_encode(array('response' => 1));
                die();
            } else {
                echo 0;
            }
        }


        if ($query->num_rows == 0) {

        } else {
            $sql = "UPDATE table_client_panier SET qte= qte + 1 WHERE ref=" . $ref;
            $conn->query($sql);
        }


    } else if (isset($request->deleteArticle)) {
        $ref = $request->deleteArticle;
        $session = $request->session;
        $id_caisse = $request->id_caisse;
        $sql = "DELETE FROM table_client_panier WHERE ref = '$ref' AND session = $session AND id_caisse = $id_caisse";
        $delete = $conn->query($sql);
        if ($delete == TRUE) {
            $sql = "SELECT * FROM table_client_panier";

            $response['response'] = 1;
            regenerePanier($conn, $sql, 'jsons/panier.json');
            echo json_encode($response);
            die();
        } else {
            echo json_encode(array('response' => 0, 'message' => 'Échec de la suppresion du fichier'));
        }
    } else if (isset($request->ajoutRemise)) {
        $remise = $request->ajoutRemise;
        $ref = $request->refRemise;
        $session = $request->session;
        $id_caisse = $request->id_caisse;
        $sql = "UPDATE table_client_panier SET remise = $remise WHERE ref = '$ref' AND session = $session AND id_caisse = $id_caisse";
        $ajoutRemise = $conn->query($sql);
        if ($ajoutRemise) {
            echo $remise;
            $sql = "SELECT * FROM table_client_panier";
            regenerePanier($conn, $sql, "jsons/panier.json");
            die();
        }
    }
    else if (isset($request->ajoutRemiseEuro)) {
        $remise_euro = $request->ajoutRemiseEuro;
        $ref = $request->refRemiseEuro;
        $session = $request->session;
        $id_caisse = $request->id_caisse;
        $sql = "UPDATE table_client_panier SET remise_euro = $remise_euro WHERE ref = '$ref' AND session = $session AND id_caisse = $id_caisse";
        $ajoutRemiseEuro = $conn->query($sql);
        if ($ajoutRemiseEuro) {
            echo $remise_euro;
            $sql = "SELECT * FROM table_client_panier";
            regenerePanier($conn, $sql, "jsons/panier.json");
            die();
        }
    }
    else if (isset($request->updateQTE)) {
        $qte = $request->updateQTE;
        $ref = $request->refQte;
        $session = $request->session;
        $timestamp = time();
        $sql = "UPDATE table_client_panier SET qte = $qte, date = '$timestamp' WHERE ref = '$ref' AND session = $session";
        $updateQte = $conn->query($sql);
        if ($updateQte) {
            echo $qte;
            $sql = "SELECT * FROM table_client_panier";
            regenerePanier($conn, $sql, "jsons/panier.json");
            die();
        }
    } else if (isset($request->addPromo)) {
        $promo = $request->addPromo;
        $totalPanier = $request->totalPanier;
        $session = $request->session;
        if ($promo < 100) {
            $montant_promo = $totalPanier * ($promo / 100);
            $titre = "Remise Exceptionnelle";
            $date = time();
            $sql = "INSERT INTO table_client_panier
    (`session`,`id_produit`,`ref`, `qte`, `credit`, `pu_euro`, `promo`, `retour`, `famille`, `titre`, `taux_tva`,`date`, `remise`)
    VALUES ($session,
            '#promo',
            '0007',
            1, 0 ,
            $montant_promo, 0, 'false',0,'" . $titre . "',8.5,'" . $date . "', 0)";
            $ajoutPromo = $conn->query($sql);
            if ($ajoutPromo) {
                $json = regenerePanier($conn, "SELECT * FROM table_client_panier", 'jsons/panier.json');
                echo json_encode(array('response' => 1, 'result' => $json));
                die();

            } else {
                echo json_encode(array('response' => 0, 'result' => null));
                die();
            }

        } else {
            echo json_encode(array('response' => 0, 'result' => null));
            die();
        }

    } else if (isset($request->getTotalPanier)) {
        $getotal = $request->getTotalPanier;
        if ($getotal) {
            $sql = "SELECT * FROM table_client_panier";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $total = 0;
                while ($row[] = $result->fetch_assoc()) {
                    $total += $row[0]['remise'] > 0 ? $row[0]['pu_euro'] * $row[0]['qte'] * ($row[0]['remise'] / 100) : $row[0]['pu_euro'] * $row[0]['qte'];
                }
                echo round($total, 2);
            }
        }
    }


    $sql = "SELECT * FROM table_client_panier";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        while ($row[] = $result->fetch_assoc()) {

            $tem = $row;

            $json = $tem;
        }
//        echo json_encode($json);

        $fp = fopen('jsons/panier.json', 'w');
        fwrite($fp, json_encode($json));
        fclose($fp);
    } else if (isset($request->deleteArticle) && $response['response'] == 0) {
        echo json_encode(array('response' => 0));
        $fp = fopen('jsons/panier.json', 'w');
        fwrite($fp, json_encode([]));
        fclose($fp);
    }
}
