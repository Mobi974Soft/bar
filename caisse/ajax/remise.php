<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../../DBConfig.php';
include '../../functions.php';


$postdata = file_get_contents('php://input');
if(isset($postdata)){
    $request = json_decode($postdata);
    if(isset($request->remisePanierEuro)){
        $remisePanier = $request->remisePanierEuro;
        $idtable = $request->idtable;
        $session = $request->session;
        $id_caisse = $request->idcaisse;
        $totalPanier = $request->totalPanier;
        $montant = $remisePanier;
        $titre = "Remise Exceptionnelle" . $remisePanier . "EUR";
        $date = time();
        $sql = "INSERT INTO table_client_panier 
    (`id_caisse`,`id_produit`,`session`,`ref`, `qte`,  `pu_euro`, `remise_euro`, `retour`,`promo`, `titre`,`taux_tva`,`date`,`remise`,`famille`,`idtable`,`statut`,`en_cuisine`,`r_globale_eur`) 
    VALUES ( $id_caisse,'remise',$session,'remise', 1, $montant, 0, 'true' ,0,'$titre',8.5,$date,0,0,$idtable,0,0,$remisePanier)";
        $insertRemisePanier = $conn->query($sql);
        if ($insertRemisePanier) {
            echo json_encode(array('response' => 1));
            die();
        }
    }else if(isset($request->remisePanier)){
        $remisePanier = $request->remisePanier;
        $session = $request->session;
        $idtable = $request->idtable;
        $id_caisse = $request->idcaisse;
        $totalPanier = $request->totalPanier;
        $montant = $totalPanier * ($remisePanier / 100);
        // var_dump($montant,$totalPanier,$remisePanier);
        $titre = "Remise Exceptionnelle de " . $remisePanier . "%";
        $date = time();
     $sql = "INSERT INTO table_client_panier 
     (`id_caisse`,`id_produit`,`session`,`ref`, `qte`,  `pu_euro`, `remise_euro`, `retour`,`promo`, `titre`,`taux_tva`,`date`,`remise`,`famille`,`idtable`,`statut`,`en_cuisine`,`r_globale`) 
     VALUES ( $id_caisse,'remise',$session,'remise', 1, $montant, 0, 'true' ,0,'$titre',8.5,$date,0,0,$idtable,0,0,$remisePanier)";
        $insertRemisePanier = $conn->query($sql);
        if ($insertRemisePanier) {
            echo json_encode(array('response' => 1));
            die();
        }
    }
}

?>