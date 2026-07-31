<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
include '../DBConfig.php';
include '../functions.php';

$postdata = file_get_contents('php://input');
if(isset($postdata)){
	$request = $request = json_decode($postdata);
	if(isset($request->startDate,$request->endDate)){
		$startDate = str_replace('/', '-', $request->startDate);
		$endDate = str_replace('/', '-', $request->endDate);
		$startDate = date('Y-m-d',strtotime($startDate));
		$endDate = date('Y-m-d',strtotime($endDate));

		$end = new DateTime($endDate);
		$end->setTime(0,0,1);
		$period = new DatePeriod(
			new DateTime($startDate),
			new DateInterval('P1D'),
			$end
		);
		$data = array();
		foreach ($period as $key => $value) {

			$startDate = $value->format('Y-m-d')." 00:00:00";
			$endDate = $value->format('Y-m-d')." 23:59:59";
			$sql = "SELECT date,p_espece_euro,p_cheque_euro,p_cb FROM table_client_ticket WHERE  date >= '$startDate' AND date <= '$endDate'";
			$query = $conn->query($sql);
			
			$total = 0 ;
			$total_espece = 0;
			$total_cb = 0;
			$total_cheque = 0;
			while($ligne = $query->fetch_assoc()){
				$total_espece += $ligne['p_espece_euro'];
				$total_cb += $ligne['p_cb'];
				$total_cheque += $ligne['p_cheque_euro'];
				$total += $ligne['p_espece_euro'] +  $ligne['p_cb'] + $ligne['p_cheque_euro'];
			}

			$data[] = array($value->format('d/m/Y'),$total_espece,$total_cb,$total_cheque,$total);

		}
		
		$filename = "Export_stat_du_".$startDate."_au_".$endDate.".csv";
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename);
		$output = fopen('php://output', 'w');
		fputcsv($output, array('Date', 'Espece', 'Carte Bancaire', 'Cheque','Total'));
		
		if (count($data) > 0) {
			foreach ($data as $row) {
				fputcsv($output, $row);
			}
		}
		
	}

}
?>
