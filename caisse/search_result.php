<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');

include('../DBConfig.php');
if (isset($_GET['q'])) {
   $search = htmlspecialchars($_GET['q']);
   $sql = "SELECT * FROM table_client_catalogue WHERE titre like '%$search%' LIMIT 10";
   $query = $conn->query($sql);
   if ($query->num_rows>0) {
      $produits = [];
      while($row = $query->fetch_assoc()){
         $produits[] = $row;
     }
     echo json_encode(array("response"=>1,"produits" => $produits));
 } 
}
 ?>