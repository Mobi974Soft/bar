<?php   
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include('../DBConfig.php');


$fh   = fopen('data.txt',"w");//php path

$tickets  = $conn->query("SELECT * FROM `table_client_ticket` where p_espece_euro > 0 and date LIKE '%2022-09-%'");


$outPut = "id\tcheque\tespece\tcb\ttotal\ttotal_euro_du\n";

//retrive records from database and write to file
while($row = $tickets->fetch_assoc())
{
 $outPut .= $row['id']."\t".$row['p_cheque_euro']."\t".  $row['p_espece_euro']."\t".$row['p_cb']."\t". $row['total_euro']."\t".$row['total_euro_du']."\n";
} 


fwrite($fh,$outPut);
fclose($fh);
 ?>