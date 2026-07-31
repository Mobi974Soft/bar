<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

require('functions.php');
include 'DBConfig.php';


$postdata = file_get_contents('php://input');
if (isset($postdata)) {
    $request = json_decode($postdata);
    $search = $request->search;
    $check = explode('*', $search);
    $qte = 1;
    if (count($check) == 1) {
        $qte = 1;
    } else {
        $re = '/[^\*]*/';
        preg_match($re, $search, $matches);
        if (preg_match($re, $search, $matches)) {
            if (strlen($matches[0]) > 5) {
                echo json_encode(response("", 2));
                exit;
            }
        }
        $qte = $matches[0] == "" ? 1 : $matches[0];
        $search = explode('*', $search)[1];
    }
    if ($search!="") {

        $ref = htmlspecialchars(trim($search));
        $sql = "SELECT * FROM table_client_catalogue WHERE ref = '$ref'";
        // Vérifie l'article scanner existe dans la table catalogue
        if ($conn->query($sql)->num_rows > 0) {
            $catalogue = $conn->query($sql)->fetch_assoc();
            /* Verifie si deja dans panier */
            $session = $request->session;
            $id_caisse = $request->id_caisse;
            $sql2 = "SELECT ref,session,num,retour FROM table_client_panier WHERE ref='$ref' AND id_caisse = $id_caisse AND session = $session";

            $query2 = mysqli_query($conn, $sql2);

            $nbrow = $query2->num_rows;
            $checkIfInPanier = $conn->query($sql2)->fetch_assoc();


            
            $ref = $catalogue['ref'];
            $pu_euro = $catalogue['prixttc_euro'];
            $num = $catalogue['num'];
            $session = $request->session;
            $credit = 0;
            $tva = $catalogue['code_tva'];
            $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));
            $remise = 0;
//          $qte = 1;
            $titre = $catalogue['titre'];
            $date = time();
            $id_produit = $catalogue['id'];
            $remise_euro = 0;
            $promo_debut = $catalogue['promo_debut'];
            $promo_fin = $catalogue['promo_fin'];
            $famille = $catalogue['cath'];
            $prixttc_promo_euro = 0;
            if ($catalogue['prixttc_promo_euro'] > 0) {
                $actual_date = date('Y-m-d', $date);
                if ($promo_debut < $actual_date && $promo_fin > $actual_date) {
                    $prixttc_promo_euro = $catalogue['prixttc_promo_euro'];

                }
            }


            $data = array('titre' => $titre, 'pu_euro' => $pu_euro, 'remise' => $remise, 'remise_euro' => $remise_euro, 'session' => $session, 'qte' => 1, 'ref' => $ref, "qte" => $qte, "promo" => $prixttc_promo_euro);
            $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`,`date`, `remise`, `famille`) 
            VALUES ($session,'" . $id_produit . "','" . $ref . "', $qte, $id_caisse , $pu_euro, $remise_euro, 'false',$prixttc_promo_euro,'" . $titre . "',$taux_tva,$date, $remise,$famille)";

                


                if (!$nbrow > 0) {
                    $ajout = $conn->query($sql);
                } else {
                    if ($session == $checkIfInPanier['session']) {
                        $num = $checkIfInPanier['num'];
                        if($checkIfInPanier['retour'] == 1){
                            $ajout = $conn->query($sql);
                            
                        }else{
                            $sql = "UPDATE table_client_panier SET qte = qte + $qte, date = $date WHERE num = $num";
                            $update = $conn->query($sql);
                        }
                        
                    } else {
                        $ajout = $conn->query($sql);
                    }

                }
                $reponse = 1;
            } else {
               echo json_encode(array('result' => 0 , 'message' => "Ce produit n'est pas dans le catalogue. Souhaitez vous le créer ?", 'ref' => $search));

               exit;
           }

           $sql = "SELECT * FROM table_client_panier WHERE session = $session AND id_caisse = $id_caisse ORDER BY num DESC";

           $result = $conn->query($sql);
           $total = 0;
           $qteTotal = 0;
           $i = 0;
           if ($result->num_rows > 0) {
            while ($row[] = $result->fetch_assoc()) {
                if ($result->num_rows > $i) {
                    $pu_euro =$row[$i]['pu_euro'];
                    $qte = $row[$i]['qte'];
                    $remise = $row[$i]['remise'];
                    $remise_euro = $row[$i]['remise_euro'];

                    if($row[$i]['ref'] != "remise"){
                        $qteTotal += $qte;
                    }

                    if($row[$i]['ref'] == "remise"){
                        $total += $row[$i]['pu_euro'] * -$row[$i]['qte'];
                    }
                    elseif($row[$i]['promo']>0){
                        if($row[$i]['retour'] == 1){
                            $total += $row[$i]['promo'] *  - $row[$i]['qte'];
                        }else{
                            $total += $row[$i]['promo']*$row[$i]['qte'];
                        }
                        
                    }
                    elseif($row[$i]['remise']>0){
                        $total +=  ($pu_euro * $qte) - ($pu_euro * $qte * ($remise / 100));
                    }
                    elseif($row[$i]['remise_euro']>0){
                         $total += $pu_euro * $qte - $remise_euro;
                    }
                    else{
                       if($row[$i]['retour'] != 1){
                            $total += $row[$i]['pu_euro'] * $row[$i]['qte'];

                        }
                        else{
                            $total += $row[$i]['pu_euro'] * -$row[$i]['qte'];
                    }
                }
                $i++;
            }
            $tem = $row;
            $json = $tem;

        }
        echo json_encode(array("total" => round($total, 2), "data" => $data, "result" => 1,"qteTotal"=>$qteTotal));

        
    } else {
        $json = '{}';
        echo json_encode(response(json_decode($json), $reponse));
       
    }
        // Fin vérification article dans table catalogue
} else if (preg_match("/^\d+$/", $search) and strlen($search) == 13) {

    $ref = htmlspecialchars(trim($request->search));
    $sql = "SELECT * FROM table_client_catalogue WHERE ref = '$ref'";
    $query = $conn->query($sql);
    $nbligne = $query->num_rows;
    if ($nbligne == 0 and strlen("$ref") == 13) {
        $ref_1 = substr($ref, 0, 7) . "00000";

        $res_sql = mysqli_query($conn, "SELECT * FROM table_client_catalogue WHERE ref='$ref_1'");
        $nbligne2 = mysqli_num_rows($res_sql);
            // echo json_encode("SELECT * FROM table_client_catalogue WHERE ref='$ref_1'");die();

        if ($nbligne2 > 0) {
            $ref_2 = substr($ref, 7, 5);
            $row = $res_sql->fetch_assoc();
            $pv_valeur = $row['prix_variable'];
            $famille = $row['cath'];
            $promo_debut = $row['promo_debut'];
            $promo_fin = $row['promo_fin'];
            $promo = $promo_debut < date('Y-m-D') && $promo_fin > date('Y-m-d') ? $row['prixttc_promo_euro'] : 0;
            if ($pv_valeur == 0) $nbligne2 = 0;
            if ($pv_valeur == 1) $pv_prix_euro = round($ref_2 / 100);
            if ($pv_valeur == 2) $pv_prix_euro = round($ref_2 / 100 / 6.55957);

            $sql2 = "SELECT ref FROM table_client_panier WHERE ref=" . $ref_1;
            $query2 = mysqli_query($conn, $sql2);
            $checkIfInPanier = $conn->query($sql2)->fetch_assoc();
            if (!$checkIfInPanier) {
                $ref = $ref_1;
                $pu_euro = $pv_valeur;
                $session = $session;
                $tva = $row['code_tva'];
                $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));
                $remise = 0;
                $qte = 1;
                $titre = $row['titre'];
                $date = time();
                $id_produit = $row['id'];
                $id_caisse = $row['id_caisse'];
                $remise_euro = 0;

                $sql = "INSERT INTO table_client_panier (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `promo`, `titre`, `taux_tva`,`date`, `remise`, `famille`) 
                VALUES ('" . $session . "','" . $id_produit . "','" . $ref . "', $qte, $id_caisse , $pu_euro, $remise_euro, 'false',$promo,'" . $titre . "',$taux_tva,$date, $remise,$famille)";
                    $ajout = $conn->query($sql);
                } else {
                    $sql = "UPDATE table_client_panier SET qte = qte + 1 WHERE ref=" . $ref_1;
                    $update = $conn->query($sql);
                }
            }
        }

       
    } else {
        echo json_encode(response("", 2));
    }
//          else{
//              $sql = "SELECT * FROM table_client_catalogue WHERE titre LIKE '%$search%' ";
//              $result = $conn->query($sql);
//              if($result->num_rows > 0){
//                  while($row[] = $result->fetch_assoc()){
//                      $item = $row;
//                      $json = $item;
//                  }
//                  echo json_encode($json);
//              }
//              else{
//                  echo 0;
//              }
//          }
}
