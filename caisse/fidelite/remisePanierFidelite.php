<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

function combienDeFois100($nombre) {
    return intdiv($nombre, 100); // Utilise intdiv pour la division entière
}

date_default_timezone_set('Indian/Reunion');
include '../../DBConfig.php';
$postdata = file_get_contents('php://input');
if (isset($postdata)) {
	$request = json_decode($postdata);

    if(isset($request->remisePanierFidelite)){
        $remisePanier = $request->remisePanierFidelite;
        $session = $request->session;
        $id_caisse = $request->idcaisse;
        $totalPanier = $request->totalPanier;
        $uuid = $request->uuid;
        $formule = $request->formule;
        $currency = $request->currency;
        $idclient = $request->idclient;
        if($remisePanier >= 10 ){
            $resteDesPoints = $remisePanier - 10;
        }else{
            $resteDesPoints = $remisePanier;
        }
        $montant = 0;
        $titre = "Offert";
        $date = time();
        $sql = "INSERT INTO table_client_panier 
    (`session`,`id_produit`,`ref`, `qte`, `id_caisse`, `pu_euro`, `remise_euro`, `retour`, `famille`,`idtable`, `titre`, `taux_tva`,`date`, `remise`,`r_globale_eur`) 
    VALUES ( $session,'remise','remise',1,  $id_caisse ,$montant, 0, 'true' ,0,1,'$titre',8.5,$date,0,$montant)";
        // $insertRemisePanier = $conn->query($sql);
        // if ($insertRemisePanier) {

            $data = array(
                'uuid' => $uuid,
                'reste' => $resteDesPoints
            );
            $url = 'https://fidelias.fr/fidelite/caisse/resetFideliteBar.php';

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $response = curl_exec($ch);

            if ($response === false) {
                echo 'Erreur cURL : ' . curl_error($ch);
            } else {
                echo $response;
                
            }

            curl_close($ch);


            
        // }
    }



}

?>