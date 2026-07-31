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
    if(isset($request->setRemiseSurProduit)){
        $ref = $request->setRemiseSurProduit;
        $id_caisse = $request->id_caisse;
        $date = $request->date;
        // var_dump($request);
        $remiseArray = json_encode($request->remiseArray);
       
        $sql = "UPDATE table_client_panier SET remise_unique = '$remiseArray' WHERE num = '$ref' AND id_caisse = $id_caisse AND date = '$date'";
        // var_dump($sql);
        $updateArticle = $conn->query($sql);
       
        if ($updateArticle) {

            if(is_array($request->remiseArray) && count($request->remiseArray) == 1){
                $sqlselect = "SELECT pu_euro,qte,remise_unique,titre FROM table_client_panier WHERE num = '$ref' AND id_caisse = $id_caisse AND date = '$date'";
            }else{
                $sqlselect = "SELECT pu_euro,qte,remise_unique,titre FROM table_client_panier WHERE num = '$ref' AND id_caisse = $id_caisse ";
            }
            
            $query = $conn->query($sqlselect);

            $result = $query->fetch_assoc();
            
            if($result){
                $remise_unique = json_decode($result['remise_unique']);
                
                $prix = $result['pu_euro'];
                $qte = $result['qte'];
                $id_produit = random_strings(12);
                $remiseText = "";
                $remiseTotal = 0;
                $newdate = time();
                $nbRemise = 0;
                
                if(is_array($remise_unique)){
                    foreach ($remise_unique as $value) {
                        $id_produit = random_strings(12);
                        if($value>0 && count($remise_unique) > 1 ){
                            $nbRemise++;
                            $sql = "INSERT INTO table_client_panier ( `id_caisse`, `id_produit`, `session`, `ref`, `qte`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`, `date`, `remise`, `famille`, `idtable`, `statut`, `en_cuisine`, `r_globale`, `r_globale_eur`, `options`, `remise_unique`)
                            SELECT `id_caisse`, '$id_produit', `session`, `ref`, 1, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`, $newdate,$value, `famille`, `idtable`, `statut`, `en_cuisine`, `r_globale`, `r_globale_eur`, `options`, '0'
                            FROM table_client_panier
                            WHERE ref = '$ref' and  remise = 0 AND id_caisse = $id_caisse  LIMIT 1;
                            ";
                            $newRow = $conn->query($sql);
                            // var_dump($sql,$newRow);die();
                            if($newRow){
                                
                                $update = $conn->query("UPDATE table_client_panier SET remise_unique = '1' , qte = qte - 1 WHERE remise = 0 and ref = '$ref' AND id_caisse = $id_caisse ");
                                // echo "UPDATE table_client_panier SET remise_unique = '1' , qte = qte - 1 WHERE remise = 0 and ref = '$ref' ";
                                $remiseTotal += $prix * ($value / 100);
                            }
                            
                        }else{
                            $id_produit = random_strings(12);
                            if($value>0){
                                $sql = "UPDATE table_client_panier SET remise = $value , id_produit = '$id_produit' WHERE  ref = '$ref' AND id_caisse = $id_caisse AND date = '$date' ";
                                $remiseTotal += $prix * ($value / 100);
                                $update = $conn->query($sql);
                            }

                            
                            
                        }
                    }
                }
                
                if(is_array($remise_unique)){
                    if(count($remise_unique) == $nbRemise){
                        $conn->query("DELETE from table_client_panier WHERE remise = 0 and ref = '$ref' AND id_caisse = $id_caisse");
                    }
                }
                

                $nouveauPrix = ($prix*$qte) - $remiseTotal;
                    echo json_encode(array('response' => 1 , 'prix' => $nouveauPrix, 'remiseText' => $remiseText));
            }else{
                echo json_encode(array('response' => 0 , 'sql' => $sql));
            }
            
        }else{
            echo json_encode(array('response' => 0 , 'sql' => $sql));
        }

        
    }
}


?>