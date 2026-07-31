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

$postdata = file_get_contents('php://input');
if (isset($postdata)) {
    $request = json_decode($postdata);
    if (isset($request->idproduit, $request->idtable)) {
        $idproduit = $request->idproduit;
        $idtable = $request->idtable;
        $id_caisse = $request->id_caisse;

        $sql = "SELECT pu_euro,promo,qte,qteUpdated,remise FROM table_client_panier p  INNER JOIN paiement_quantite pq ON p.idtable = pq.table WHERE id_produit = '$idproduit' AND idtable = $idtable AND id_caisse = $id_caisse ";
        $query = $conn->query($sql);
        if ($query && $query->num_rows > 0) {
            $prix = 0;
            while ($ligne = $query->fetch_assoc()) {
                $price = $ligne['promo'] > 0 ? $ligne['promo'] : $ligne['pu_euro'];
                $qte = $ligne['qteUpdated'] != null ? $ligne['qteUpdated'] : $ligne['qte'];
                $remise = $ligne['remise'];
                $prix = ($price - ($price * ($remise/100))) * $qte ;

            }

        } else {
            $sql = "SELECT (pu_euro - promo - (pu_euro * (remise/100))) * qte as prixTotal FROM table_client_panier WHERE id_produit = '$idproduit' AND idtable = $idtable AND id_caisse = $id_caisse ";
            $query = $conn->query($sql);
            $prix = $query->fetch_assoc()['prixTotal'];
            
        }
        // var_dump("PRIX=>".$prix);
        echo json_encode(array('response' => 1, 'prix' => $prix));
    }
}


?>