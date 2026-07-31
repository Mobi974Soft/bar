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
    if(isset($request->arrProduit, $request->idtable,$request->type)){
        $arrProduit = $request->arrProduit;
        $idtable = $request->idtable;
        $id_caisse = $request->id_caisse;
        $produits = "";
        foreach ($arrProduit as $produit) {
            $produits .= "'" . $produit . "'" . ',';
        }
        $produits = rtrim($produits, ',');
        $sql = "SELECT * FROM table_client_panier   WHERE idtable = $idtable AND id_caisse = $id_caisse AND id_produit IN ($produits)";
        // echo $sql;
        $result = $conn->query($sql);
        $total = 0;
        $qteTotal = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ref = $row['ref'];
                if ($ref != "totalpanier") {
                    $remise = $row['remise'];
                    $sql = "SELECT qteUpdated FROM paiement_quantite WHERE `table` = $idtable  AND refupdated = '$ref' ";
                    $check = $conn->query($sql);
                    $qte = $check->num_rows > 0 && $check ? $check->fetch_assoc()['qteUpdated'] : $row['qte'];
                    $remise_euro = $row['remise_euro'];
                    $pu_euro = $row['pu_euro'];
                    $promo = $row['promo'];
                    $retour = $row['retour'];
                    
                    $price = $promo>0 ? $promo : $pu_euro;
                    if ($ref != "remise") {
                        $qteTotal += $qte;
                    }
                    if ($ref == "remise") {
                        $total += $pu_euro * -$qte;
                    } else {
                        if ($promo > 0) {
                            if ($retour == 1) {
                                $total += $promo * -$qte;
                            } else {
                                $total += $promo * $qte - ($promo * $qte * ($remise / 100)) - ($remise_euro * $qte);
                            }
                        } else {
                            if ($retour == 1) {
                                $total += $pu_euro * -$qte;
                            } else {
                                $total += $pu_euro * $qte - ($pu_euro * $qte * ($remise / 100)) - ($remise_euro * $qte);
                                // var_dump("TOTAL=>".$total,"QTE=>",$qte,"PRIXUNIQUE=>".$pu_euro,"REMISE=>",$remise);
                            }
                        }

                        
                    }

                }
            }
            echo json_encode(array('response' => 1, 'total' => $total));
        }
    }
    elseif (isset($request->arrProduit, $request->idtable)) {
        $arrProduit = $request->arrProduit;
        $idtable = $request->idtable;
        $id_caisse = $request->id_caisse;
        $produits = "";
        foreach ($arrProduit as $produit) {
            $produits .= "'" . $produit . "'" . ',';
        }
        $produits = rtrim($produits, ',');
        $sql = "SELECT * FROM table_client_panier   WHERE idtable = $idtable AND id_caisse = $id_caisse AND ref IN ($produits)";
        // echo $sql;
        $result = $conn->query($sql);
        $total = 0;
        $qteTotal = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ref = $row['ref'];
                if ($ref != "totalpanier") {
                    $remise = $row['remise'];
                    $sql = "SELECT qteUpdated FROM paiement_quantite WHERE `table` = $idtable AND id_caisse = $id_caisse AND  refupdated = '$ref' ";
                    $check = $conn->query($sql);
                    $qte = $check->num_rows > 0 && $check ? $check->fetch_assoc()['qteUpdated'] : $row['qte'];
                    $remise_euro = $row['remise_euro'];
                    $pu_euro = $row['pu_euro'];
                    $promo = $row['promo'];
                    $retour = $row['retour'];
                    
                    $price = $promo>0 ? $promo : $pu_euro;
                    if ($ref != "remise") {
                        $qteTotal += $qte;
                    }
                    if ($ref == "remise") {
                        $total += $pu_euro * -$qte;
                    } else {
                        if ($promo > 0) {
                            if ($retour == 1) {
                                $total += $promo * -$qte;
                            } else {
                                $total += $promo * $qte - ($promo * $qte * ($remise / 100)) - ($remise_euro * $qte);
                            }
                        } else {
                            if ($retour == 1) {
                                $total += $pu_euro * -$qte;
                            } else {
                                $total += $pu_euro * $qte - ($pu_euro * $qte * ($remise / 100)) - ($remise_euro * $qte);
                            }
                        }

                        
                    }

                }
            }
            echo json_encode(array('response' => 1, 'total' => $total));
        }

    }
}




?>