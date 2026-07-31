<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include('../DBConfig.php');



$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info');
$nom_magasin = $nom_magasin->fetch_assoc()['nom_magasin'];
if (isset($_GET['startDate'],$_GET['endDate']) && $_GET['endDate'] != "" && $_GET['startDate'] != "") {
	$current_day = $_GET['endDate']." 23:59:59";
	$previous_week = $_GET['startDate']." 00:00:00";
}else{
	$current_day = date('Y-m-d')." 23:59:59";
	$previous_week = strtotime("-1 week +1 day");
	$previous_week = date('Y-m-d') . " 00:00:00";
}
$sql = "SELECT d , h,  sum(p_espece_euro+p_cb+p_cheque_euro) as total from ( select dayname(date) d, hour(date) h, p_espece_euro,p_cb,p_cheque_euro from table_client_ticket WHERE date >= '$previous_week' and date <= '$current_day' ) dt group by d, h order by d ASC";
$sql_cat = "SELECT h from ( select  hour(date) h from table_client_ticket WHERE date >= '$previous_week' and date <= '$current_day' ) dt group by h order by h ASC";
$query = $conn->query($sql);
$query_cat = $conn->query($sql_cat);
$cats = [];
while ($row = $query_cat->fetch_assoc()) {
	$cats[] = $row['h'];
}


$lundi = [];
$mardi = [];
$mercredi = [];
$jeudi = [];
$vendredi = [];
$samedi = [];
$dimanche = [];



while ($ligne = $query->fetch_assoc()) {
	$montant = $ligne['total'];
	$heure = $ligne['h'];
	if ($ligne['d'] == "Monday") {
		$lundi[$heure] = $montant;

	}

	if ($ligne['d'] == "Tuesday") {
		$mardi[$heure] = $montant;
	}

	if ($ligne['d'] == "Wednesday") {
		$mercredi[$heure] = $montant;
	}

	if ($ligne['d'] == "Thursday") {
		$jeudi[$heure] = $montant;
	}

	if ($ligne['d'] == "Friday") {
		$vendredi[$heure] = $montant;
	}

	if ($ligne['d'] == "Saturday") {
		$samedi[$heure] = $montant;
	}

	if ($ligne['d'] == "Sunday") {
		$dimanche[$heure] = $montant;
	}
}





// Tableau initial

function completeArray($tableau){
	// Obtenir la première et la dernière clé du tableau
	$premiereCle = min(array_keys($tableau));
	$derniereCle = max(array_keys($tableau));

// Créer un tableau avec les clés manquantes dans la suite de nombres
	$clesManquantes = range($premiereCle, $derniereCle);

// Compléter le tableau initial avec les clés manquantes
	$tableauComplet = array_fill_keys($clesManquantes, 0);
	$tableauComplet = array_replace($tableauComplet, $tableau);

	return $tableauComplet;
}



// Afficher le tableau complet
$lundi = array_values(completeArray($lundi));
$mardi = array_values(completeArray($mardi));
$mercredi = array_values(completeArray($mercredi));
$jeudi = array_values(completeArray($jeudi));
$vendredi = array_values(completeArray($vendredi));
$samedi = array_values(completeArray($samedi));
$dimanche = array_values(completeArray($dimanche));

?>
<html style="height: 2000px;"><head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Administration</title>
	<link rel="stylesheet" href="../lib/dist/plugins/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="../lib/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="../lib/dist/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
	<link rel="stylesheet" href="../lib/dist/plugins/chart.js/Chart.min.css">
	<link rel="stylesheet" href="../lib/dist/css/adminlte.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer">

	<link rel="stylesheet" href="../template/style.css?random=6450c3442b825">
	<link rel="stylesheet" href="sitemap.css?random=6450c3442b827">

<!-- <script type="text/javascript">


	function getDayName(dateStr, locale)
	{
		var date = new Date(dateStr);
		return date.toLocaleDateString(locale, { weekday: 'long' });        
	}


	google.charts.load("current", {packages:["corechart"]});
	google.charts.setOnLoadCallback(drawChart);
	function drawChart() {
		var arrayData = '<?php echo json_encode($data) ?>'
		arrayData = JSON.parse(arrayData)
		var result = [['Jour','Heure']];
		var days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
		var count = 0

		arrayData.forEach(function(item){
			var dateStr = item.date
			var d = new Date(dateStr);
			var dayName = days[d.getDay()];
			var hour = d.getHours()
			result.push([dayName,hour]);
		})
		console.log(result)
		var data = google.visualization.arrayToDataTable(result)
       

		var options = {
			title: 'Details Vente par heure',
			legend: { position: 'none' },
		};

		var chart = new google.visualization.Histogram(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script> -->

</head>
<body class="sidebar-mini" cz-shortcut-listen="true" style="height: auto;">
	<div class="wrapper">
		<nav class="main-header navbar navbar-expand navbar-white navbar-light">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<li class="nav-item d-none d-sm-inline-block">
					<a href="index.php" class="nav-link">Accueil</a>
				</li>

			</ul>
		</nav>
		<aside class="main-sidebar sidebar-dark-primary elevation-4 ">
			<a href="index3.html" class="brand-link">
				<?php echo ucfirst($nom_magasin) ?>
				<span class="brand-text font-weight-light text-center"></span>
			</a>
			<div class="sidebar">
				<div class="user-panel mt-3 pb-3 mb-3 d-flex">
					<div class="image">

					</div>
					<div class="info">

					</div>
				</div>
				<nav class="mt-2navbar-expand-lg ">
					<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
						<li class="nav-item">
							<a href="../admin/articles.php" class="nav-link">
								<i class="nav-icon fas fa-barcode"></i>
								<p>
									Gestion des articles

								</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="../admin/statistiques.php" class="nav-link">
								<i class="nav-icon fas fa-inbox"></i>
								<p>
									Statistiques

								</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="../admin/profil.php" class="nav-link">
								<i class="nav-icon fas fa-user"></i>
								<p>
									Profil
								</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="../admin/manageCaisse.php" class="nav-link">
							</a><a href="../login/logout.php?userid=1&amp;action=admin" class="btn btn-block btn-danger btn-lg">Déconnexion</a>

						</li>
					</ul>
				</nav>
			</div>
		</aside>
		<div class="content-wrapper d-print-block" style="min-height: 855px;">
			<div class="content-header border-bottom mb-3">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1 class="m-0">Heatmap</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
								<li class="breadcrumb-item"><a href="statistiques.php">Statistiques</a></li>
								<li class="breadcrumb-item">Heatmap vente/h</li>
							</ol>
						</div>
					</div>
				</div>
			</div> 
			<div class="content">
				<div class="container ">
					<div class="row"  style="padding: 30px 0">
						<div class="col-md-4">
							<div class="form-group">
								<label>Date début:</label>
								<input class="form-control" type="date" id="startdate" name="startdate"
								value="<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : date('d/m/Y') ?>"
								>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label>Date Fin:</label>
								<input class="form-control" type="date" id="enddate" name="enddate"
								value="<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : "" ?>"
								>
							</div>
						</div>
						<div class="col-md-4" style="margin:auto">
							<button class="btn btn-md btn-primary" id="validDate" style="margin-top: 16px;">
								Valider
							</button>
							<button class="btn btn-md btn-danger" id="resetDate" style="margin-top: 16px;">
								Réinitialiser
							</button>
						</div>
					</div>
					<div class="row">	
						<div id="chart" style="width: 900px; height: 500px;"></div>
					</div>	
					
				</div>
			</div>
			<aside class="control-sidebar control-sidebar-dark" style="display: none;">
				<div class="p-3">
					<h5>Title</h5>
					<p>Sidebar content</p>
				</div>
			</aside>

			<!-- <footer class="main-footer">
				<div class="float-right d-none d-sm-inline">
				</div>
				<strong>Copyright © 2022 <a href="https://mobisoft.fr">MOBISOFT</a>.</strong>
			</footer> -->
			<div id="sidebar-overlay"></div></div>
			<script src="../lib/dist/js/jquery.slim.min.js"></script>
			<script src="../lib/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

			<script src="../lib/dist/plugins/moment/moment.min.js"></script>
			<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
			<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
			<script type="text/javascript">
				$('#validDate').click(function(){
					var startDate = $('#startdate').val()
					var endDate = $('#enddate').val()

					if (startDate != "" && endDate == "") {
						$('#message').hide()
						$.ajax({
							url: "histogramme.php?startDate=" + startDate ,
							type: "GET",
							beforeSend: function() {

								$('#loader').show();
							},
							complete: function(){
								$('#loader').hide();

							},
							success: function (response) {
								window.location.href = "histogramme.php?startDate=" + startDate 
							}
						})
					}else if(startDate != "" && endDate != ""){
						$.ajax({
							url: "histogramme.php?startDate=" + startDate + "&endDate=" + endDate,
							type: "GET",
							beforeSend: function() {
								$('#loader').show();
							},
							complete: function(){

								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "histogramme.php?startDate=" + startDate + "&endDate=" + endDate 
							}
						})
					}
				})

				$('#resetDate').click(function(){
					$('#startdate').val("")
					$('#enddate').val("")
				})


			</script>
			<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
			<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
			<script type="text/javascript">
				function generateData(count, yrange) {
					var i = 0;
					var series = [];
					while (i < count) {
						var x = (i + 1).toString();
						var y =
						Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

						series.push({
							x: x,
							y: y
						});
						i++;
					}
					return series;
				}


				var lundi = '<?php echo json_encode($lundi) ?>'
				lundi = JSON.parse(lundi)

				var mardi = '<?php echo json_encode($mardi) ?>'
				mardi = JSON.parse(mardi)

				var mercredi = '<?php echo json_encode($mercredi) ?>'
				mercredi = JSON.parse(mercredi)

				var jeudi = '<?php echo json_encode($jeudi) ?>'
				jeudi = JSON.parse(jeudi)

				var vendredi = '<?php echo json_encode($vendredi) ?>'
				vendredi = JSON.parse(vendredi)

				var samedi = '<?php echo json_encode($samedi) ?>'
				samedi = JSON.parse(samedi)

				var dimanche = '<?php echo json_encode($dimanche) ?>'
				dimanche = JSON.parse(dimanche)

				var cats = '<?php echo json_encode($cats) ?>'
				cats = JSON.parse(cats)

				var options = {
					series: [{
						name: 'Dimanche',
						data: dimanche
					},
					{
						name: 'Samedi',
						data: samedi
					},
					{
						name: 'Vendredi',
						data: vendredi
					},
					{
						name: 'Jeudi',
						data: jeudi
					},
					{
						name: 'Mercredi',
						data: mercredi
					},
					{
						name: 'Mardi',
						data: mardi
					},
					{
						name: 'Lundi',
						data: lundi
					},
					],
					chart: {
						height: '100%',
						type: 'heatmap',
					},
					dataLabels: {
						enabled: true,
						style: {
							fontSize: '14px',
							fontFamily: 'Helvetica, Arial, sans-serif',
							fontWeight: 'bold',
							colors: ['#000000']
						},
					},
					colors: ["#008FFB"],
					xaxis: {
						type: 'Heure',
						categories: cats
					},
					title: {
						text: 'HeatMap vente par heure',

					},
				};

				var chart = new ApexCharts(document.querySelector("#chart"), options);
				chart.render();
			</script>


		</body></html>