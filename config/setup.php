<?php
ini_set('max_execution_time', '2000'); // for infinite time of execution
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include('../functions.php');


function conDbclient(){
  session_start();
  $DATABASE_HOST = 'localhost';
  $DATABASE_USER = 'DjlQMvzTdYZwdmXP';
  $DATABASE_PASS = 'lAOy0feUOCvl0jFo';
  $DATABASE_NAME = 'mobipos_clients';
  $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

  return $con;
}

if (isset($_POST['startDate'],$_POST['endDate'],$_POST['garde'],$_POST['codesecret'],$_POST['client_id'])) {


  $con = conDbclient();
  $clientID = $_POST['client_id'];
  $codesecret = htmlspecialchars($_POST['codesecret']);



  $client_info = $con->query("SELECT * FROM clients WHERE id = $clientID");




  $client = $client_info->fetch_assoc();
  $siret = $client['siret'];
  $databasename = $client['nom_base'];
  $database_user = $client['userdatabase'];
  $database_pasword = $client['password_database'];


  $checkCodeSecret = $siret * date('Y') * date('m');
  $digitCode = substr($checkCodeSecret, 0, 6);
  if($digitCode != $codesecret){
    echo json_encode(array('response' => 0, 'message' => "CODE INCORRECT"));
    die();
  }

  if($clientID == $client['id']){

        $HostName = "161.97.64.133";
        $DatabaseName = $databasename;
        $HostUser = $database_user;
        $HostPass = $database_pasword;
        $conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);

    // $HostName = "localhost";
    // $DatabaseName = "mobiposclone";
    // $HostUser = "8085iB1vqUUsEoQz";
    // $HostPass = "jbV2ZfTHSeWjP92t";
    $conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);

    if ($conn->connect_error) {

      echo json_encode(array('message'=>"Connection failed: " . $conn->connect_error));
    }

    $startDate = str_replace('/', '-', $_POST['startDate']);
    $startDate = date('Y-m-d H:i:s', strtotime($startDate));
    $endDate = str_replace('/', '-', $_POST['endDate']);
    $endDate = date('Y-m-d', strtotime($endDate));

    $endDate = $endDate." 23:59:59";
    $garde = $_POST['garde'];
    $today_date = date('Y-m-d');
// On recupere tous les tickets du mois sur la caisse.
        // $debut = date('d',strtotime($startDate));
        // $fin = date('d',strtotime($endDate));
    $end = new DateTime($endDate);
    $end->setTime(0,0,1);
    $period = new DatePeriod(
      new DateTime($startDate),
      new DateInterval('P1D'),
      $end
    );

    foreach ($period as $key => $value) {
      
      
      $startDate = $value->format('Y-m-d')." 00:00:00";
      $endDate = $value->format('Y-m-d')." 23:59:59";
      $sql = "SELECT * FROM `table_client_ticket` 
      WHERE  date >= '$startDate' AND date <= '$endDate'
      AND p_espece_euro > 0
      AND p_cheque_euro = 0
      AND p_cb = 0
      AND p_restaurant = 0
      AND retourarticle = 0

      ORDER BY date DESC
      ";
      $query_res = $conn->query($sql);
      $tickets_caisse = $query_res->fetch_all(MYSQLI_ASSOC);

      $commandes_caisse = array();
      $count = 0;
      $count2=0;
      $count3=0;
      $countTicket = 0;

      foreach($tickets_caisse as $ticket){
       $id_ticket = $ticket['id_ticket'];
       $sql = "SELECT id_ticket,pu_euro,id_produit,qte,promo,remise,num FROM table_client_commandes WHERE id_ticket = $id_ticket  ORDER BY date DESC";
       $query_res = $conn->query($sql);
       $nbLigne = $query_res->num_rows;
       $p_espece_euro_ticket = $ticket['p_espece_euro'];
       $total_euro_ticket = $ticket['total_euro'] ;
       $total_euro_du_ticket = $ticket['total_euro_du'];
       $id = $ticket['id'];
       $p_espece_euro = 0;
       $total_euro_du = 0;
       $total_euro = 0;
       while($ligne = $query_res->fetch_assoc()){
         $id_produit = $ligne['id_produit'];
         $pu_euro = $ligne['pu_euro'];
         $promo = $ligne['promo'];
         $remise = $ligne['remise'];
         $qte = $ligne['qte'];
         $num = $ligne['num'];

         if($promo>0){
           $prixAchercher = ($promo/$qte - $remise) * $garde;
           $prixAchercher = round($prixAchercher,2);

         }elseif($promo==0){
           $prixAchercher = ($pu_euro-$remise) * $garde;
           $prixAchercher = round($prixAchercher,2);
         }
         $sql = "SELECT * FROM table_client_catalogue WHERE prixttc_euro = $prixAchercher AND prixttc_promo_euro = 0 order by rand() LIMIT 1";
         $produit = $conn->query($sql);

         $nbligne = $produit->num_rows;
         if($nbligne>0){
          $count++;
          $produit = $produit->fetch_assoc();
          $id_produit = $produit['id'];
          $prixUnique = $produit['prixttc_euro'];
          $tva = $produit['code_tva'];
          $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));

          $p_espece_euro+=$prixUnique*$qte;
          // echo "<pre>"." PRODUIT : ".$ligne['id_produit']. " - PU EURO => ".$pu_euro. " / PROMO => ".$promo. "  /  NEW PRIX => ".$produit['prixttc_euro'] ."  /  QTE => ".$qte . " / NEW PRODUIT=>".$produit['titre'] ." </br>"."</pre>";
          $sql = "UPDATE table_client_commandes SET id_produit = '$id_produit', promo = 0, pu_euro = $prixUnique, remise = 0, taux_tva = $taux_tva WHERE num = $num";
          $update = $conn->query($sql);
        //   if($update){
        //     echo "ok";
        // }
        // echo "<pre>"."PU EURO => ".$pu_euro. " / PROMO => ".$promo. "  /  PRIXTTC => ".$produit['prixttc_euro'] ."  /  PROMO PRODUIT => ".$produit['prixttc_euro'] . " / TITRE=>".$produit['titre'] ." </br>"."</pre>";
        }
        else{
         $sql = "SELECT * FROM table_client_catalogue WHERE prixttc_promo_euro = $prixAchercher  order by rand() LIMIT 1";
         $query=$conn->query($sql);
         if($query->num_rows>0){
           $count2++;
           $produit = $query->fetch_assoc();
           $id_produit = $produit['id'];
           $prixUnique = (float) $produit['prixttc_promo_euro'];
           $tva = $produit['code_tva'];
           $taux_tva = ($tva == 8 ? 8.5 : ($tva == 2 ? 2.1 : ($tva == 1 ? 1.05 : 0)));
           $p_espece_euro+=$prixUnique*$qte;
          // echo "<pre>"." PRODUIT : ".$ligne['id_produit']. " - PU EURO => ".$pu_euro. " / PROMO => ".$promo. "  /  NEW PRIX => ".$produit['prixttc_euro'] ."  /  NEW PROMO => ".$produit['prixttc_promo_euro'] . " / NEW PRODUIT=>".$produit['titre'] ." </br>"."</pre>";
           $sql = "UPDATE table_client_commandes SET id_produit = '$id_produit', promo = 0, pu_euro = $prixUnique, remise = 0, taux_tva = $taux_tva WHERE num = $num";
           $update = $conn->query($sql);
        //  if($update){
        //     echo "ok";
        // }
         }else{
           $count3++;
           $p_espece_euro+=$prixAchercher*$qte;
           $id_produit = "#DIVERS";
           $sql = "UPDATE table_client_commandes SET id_produit = '$id_produit', promo = 0, pu_euro = $prixAchercher, remise = 0, taux_tva = 8.5 WHERE num = $num";
           $update = $conn->query($sql);
    //  if($update){
    //     echo "ok";
    // }
         // echo "<pre>"." PRODUIT : ".$ligne['id_produit']. " - PU EURO => ".$pu_euro. " / PROMO => ".$promo. "  /  NEW PRIX => ".$prixAchercher ."  /  QTE => ".$qte . " / NEW PRODUIT=> DIVERS </br>"."</pre>";
         }
       }
     }
 // if($count>0){
 //     $sql = "UPDATE table_client_ticket SET p_espece_euro"
 // }
     $total_euro_du = $p_espece_euro;
     $random = rand(1,10);
     $total_euro = $total_euro_ticket * $garde;
     if($random >= 9 AND $rand <= 10){
      switch ($total_euro) {
        case $total_euro <= 20:
          $total_euro = 20;
          break;
        case $total_euro <= 50:
          $total_euro = $p_espece_euro >= 10 ? 50 : ceil( $total_euro / 5 ) * 5;
          break;
        case $total_euro <= 100:
          $total_euro = 100;
          break;
        case $total_euro <= 150:
          $total_euro = 150;
          break;
        default:
          $total_euro = $total_euro;
          break;
      }

     }elseif($random>=5 AND $random <= 8){
        $total_euro = ceil( $p_espece_euro / 5 ) * 5;
     }elseif($random>=0 AND $random <=4){
      $total_euro = ceil($p_espece_euro);
     }
     // $total_euro = ($total_euro_ticket * $garde) - $total_euro_du >= 10 ? ceil($p_espece_euro) : $total_euro_ticket * $garde;


     if($total_euro>0){
       $sql = "UPDATE table_client_ticket SET p_espece_euro = $p_espece_euro, total_remise = 0, total_euro_du = $total_euro_du, total_euro = $total_euro WHERE id = $id";
       $updateTicket = $conn->query($sql);
       if($updateTicket){
         $countTicket++;
         // $dateTicket = date('Y-m-d',strtotime($ticket['date']));
         // $base_url = $_SERVER['SERVER_NAME'];
         // $url = "https://".$base_url."/restaurant/config/journaux.php";
         // $ch = curl_init( $url );
         // $payload = json_encode( array( "numero_ticket"=> $id_ticket, 'dateTicket' => date('dmY',strtotime($dateTicket)) ) );
         // curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
         // curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
         // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
         // $result = curl_exec($ch);
         // curl_close($ch);
       }
     }
   }

 }


 



}


if($countTicket > 0){
  echo json_encode(array("response"=>1));



}


}


// On recupere les tickets du mois sur le serveur.
//$query_res = $connexion_serveur->query($sql);
//$tickets_serveur = $query_res->fetch_assoc();





 // echo "<pre>"." PRODUIT : ".$ligne['id_produit']. " - PU EURO => ".$pu_euro. " / PROMO => ".$promo. "  /  PRIXTTC => ".$produit['prixttc_euro'] ."  /  PROMO PRODUIT => ".$produit['prixttc_promo_euro'] . " / TITRE=>".$produit['titre'] ." </br>"."</pre>";

?>



