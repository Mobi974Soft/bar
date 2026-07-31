<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

date_default_timezone_set('Indian/Reunion');
include '../../DBConfig.php';
$postdata = file_get_contents('php://input');
if (isset($postdata)) {
    $request = json_decode($postdata);
    if(isset($request->qrCode)){
        $uuid = $request->qrCode;
        $id_client = $request->id_client;
        $sql = "SELECT * FROM fidelite ORDER by created_at DESC LIMIT 1 ";
		$query = $conn->query($sql);
        $fidelite_data = $query->fetch_assoc();
        $codemagasin = $fidelite_data['codemagasin'];
        $data = array(
            'uuid' => $uuid."-".$codemagasin,
            'codemagasin' => $codemagasin
        );
        $url = 'https://fidelias.fr/fidelite/caisse/getSoldeBar.php';
    
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
        $response = curl_exec($ch);
        $result = json_decode($response,true);
        if ($result['response'] === 0) {
            echo 'Erreur cURL : ' . curl_error($ch);
        } else {
                // if($id_client == 25 || $id_client == 11){
                //     $percentage = 2.30;
                //     $points = $result['solde'] * ($percentage / 100) . "€";
                //     $points = round($points,2);
                // }
                // elseif($id_client == 26){
                //     $percentage = 2.50;
                //     $points = $result['solde'] * ($percentage / 100) . "€";
                //     $points = round($points,2);
                // }
                // else{
                //     $points = $result['solde'] . " points.";
                // }
    
                $article_restant = $result['solde'];
                $solde_en_ligne = " Nombre de points : " . $article_restant ;
                
        }
        curl_close($ch);
        echo json_encode(array('response' => 1, 'data' => $solde_en_ligne));
    }
        
}


?>