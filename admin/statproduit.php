<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../DBConfig.php');



$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info');
$nom_magasin = $nom_magasin->fetch_assoc()['nom_magasin'];

$nomcat = isset($_GET['nomcat']) ? htmlspecialchars($_GET['nomcat']) : "";
date_default_timezone_set('Indian/Reunion');
$today_date = date('Y-m-d');
$cat = isset($_GET['cat']) ? $_GET['cat'] : false;
$error = array();

if (isset($_GET['type'])) {
	$type = $_GET['type'];
	if ($type == "article") {
		$select = "COUNT(*)";
		$orderBy = "ORDER BY totalByCat DESC";
	}else{
		$select = "sum(CASE WHEN c.id_produit != 'remise' THEN (c.pu_euro*c.qte - c.remise - c.promo) ELSE (pu_euro*-qte - remise - promo) END)";
		$orderBy = "ORDER BY totalByCat DESC";
	}
}else{
	$select = "sum(CASE WHEN c.id_produit != 'remise' THEN (c.pu_euro*c.qte - c.remise - c.promo) ELSE (pu_euro*-qte - remise - promo) END)";
	$orderBy = "ORDER BY totalByCat DESC";
}

if(isset($_GET['startDate'],$_GET['endDate'],$_GET['cat']) && $_GET['endDate'] != "" && $_GET['startDate'] != ""){

	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	
	if ($cat!=false) {
		$sql_categorie = "SELECT c.id_produit,$select as totalByCat FROM `table_client_commandes` c where famille = '$cat' and date >= '$startDate' and date <= '$endDate' GROUP BY c.id_produit ORDER BY totalByCat DESC LIMIT 50";
	}
}
elseif(isset($_GET['startDate'],$_GET['cat']) && $_GET['startDate'] != "" ){
	$startDate = $_GET['startDate'];
	if ($cat != false) {
		$sql_categorie = "SELECT c.id_produit,$select as totalByCat FROM `table_client_commandes` c where famille = '$cat' and date = '$startDate' GROUP BY c.id_produit ORDER BY totalByCat DESC LIMIT 50";
	}
}
else{
	if ($cat != false) {
		$sql_categorie = "SELECT c.id_produit,$select as totalByCat FROM `table_client_commandes` c where famille = '$cat' and date = '$today_date' GROUP BY c.id_produit ORDER BY totalByCat DESC LIMIT 50";
	}
}

$commandes = $conn->query($sql_categorie);
$nbligne = $commandes->num_rows > 0 ? $commandes->num_rows : 0;
if ($nbligne>0) {
	$filename = "csv_stat_prod/$nom_magasin.csv";
	$output = fopen($filename, 'w');
	foreach ($commandes as $commande) {
		$id_produit = $commande['id_produit'];
		$sql = "SELECT titre FROM table_client_catalogue WHERE id = '$id_produit'";
		$query = $conn->query($sql);
		$titre = $query->num_rows > 0 ? $query->fetch_assoc()['titre'] : 0;


		$total_par_produit = $commande['totalByCat'];

		// if ($total_par_produit > 10 ) {
			$data = array(date('Y-m-d'),$titre,$total_par_produit);
			fputcsv($output, $data);
		// }
	}
}else{
	$message = "Aucune vente trouvé sur ce produit";
}



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
	<script>
		window.onload = function () {
			
			var dataPoints = [];
			var today = '<?php echo date('d/m/Y',strtotime($today_date)) ?>'
			var startDate = '<?php echo isset($_GET['startDate']) ? date('d/m/Y',strtotime($_GET['startDate'])) : "" ?>'
			var endDate = '<?php echo isset($_GET['endDate']) ? date('d/m/Y',strtotime($_GET['endDate'])) : "" ?>'
			var categorieNom = $('#famille :selected').text()
			categorieNom = $.trim(categorieNom)
			var titre = startDate != "" && endDate != "" && startDate != endDate ? startDate + " au " + endDate : (startDate != "" && endDate != "" && startDate == endDate ? startDate : (startDate != "" && endDate == "" ? startDate : today))
			var chart = new CanvasJS.Chart("chartContainer", {
				animationEnabled: true,
				exportEnabled: true,
				title:{
					text: "Statistiques par produit pour " + categorieNom + " du " + titre,
					fontSize:30,
				},
				axisY: {
					title: "",
					fontSize:15,
					includeZero: false,
					prefix: "",
					suffix:  "",
					labelFontSize:13,
				},
				axisX:{
					interval: 1,
					labelFontSize:13,
				},
				data: [{
					type: "bar",
					yValueFormatString: "##0",
					toolTipContent: "{y} ",
					dataPoints: dataPoints
				}]
			});

			var nom_magasin = '<?php echo $nom_magasin ?>';
			var base_url = document.location.origin;
			$.get(base_url+"/restaurant/admin/csv_stat_prod/"+nom_magasin+".csv?nocache=" + (new Date()).getTime() , getDataPointsFromCSV);
			
//CSV Format
//Year,Volume
			function getDataPointsFromCSV(csv) {
				var csvLines = points = [];
				csvLines = csv.split(/[\r?\n|\r|\n]+/);
				for (var i = 0; i < csvLines.length; i++) {
					if (csvLines[i].length > 0) {
						points = csvLines[i].split(",");
						if (points[2]!=0) {
							dataPoints.push({
								label: points[1].replace(/["']/g, ""),
								y: parseFloat(points[2])
							});
						}
						
					}
				}
				dataPoints = dataPoints.reverse();
				chart.render();
			}
			
		}
	</script>
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

				<span class="brand-text font-weight-light text-center">Cideal</span>
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
							<h1 class="m-0">Statistiques Catégorie</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
								<li class="breadcrumb-item"><a href="statistiques.php">Statistiques</a></li>
								<li class="breadcrumb-item"><a href="statcategorie.php">Statistiques Catégorie</a> </li>
								<li class="breadcrumb-item">Statistiques Catégorie par produit</li>
							</ol>
						</div>
					</div>
				</div>
			</div> 
			<div class="content">
				<div class="container ">
					<div class="row" style="padding: 30px 0">
						<div class="col-md-8">
							<div class="form-group row">
								<!-- <label class="col-lg-3 col-form-label form-control-label">Famille</label> -->
								<div class="col-lg-6" id="famille-select">
									<label class="text-danger">Détails produit par catégorie</label>
									<select class="form-control" id="famille" size="0" name="famille">
										<option value="0">-- Choisir une catégorie --</option>
										<?php
										$sql = 'SELECT * FROM table_client_categorie WHERE LENGTH(nomcategorie) > 2 ORDER BY nomcategorie ASC';
										$familles = $conn->query($sql);
										$nbligne = $familles->num_rows;
										$categorie = [];
										$parent = [];
										$child = [];
										if ($nbligne > 0) {
											while ($famille = $familles->fetch_assoc()) {
												$categorie[] = $famille;
												if ($famille['id_parent'] == 0) {
													$parent[] = $famille;
												} else {
													$child[] = $famille;
												}
											}
										}

										foreach (range('A', 'Z') as $char) {
											echo $char . "\n";
											?>
											<option class="optionGroup alphabet" disabled >
												<?php echo "-".$char ?>
												<?php foreach ($categorie as $cat) {
													$nom_cat = $cat["nomcategorie"];
													$id_cat = $cat["id_categorie"];
													$id_parent= $cat['id_parent'];
													if(ucfirst($nom_cat[0]) == $char){
														if($id_parent == 0){
															?>
															<option value="<?php echo $id_cat ?>" 
																<?php echo isset($_GET['cat']) && $_GET['cat'] == $id_cat ? "selected" : "" ?>
																>
																&nbsp;&nbsp;&nbsp;&nbsp;
																<?php echo strtoupper($nom_cat) ?></option>
																<?php
                                                                // echo "<option value=".$id_cat.">".strtoupper($nom_cat)."</option>";
																foreach($child as $subcat){
																	if($subcat['id_parent'] == $cat["id_categorie"]){
																		?>
																		<option value="<?php echo $subcat["id"] ?>" 
																			<?php echo isset($_GET['cat']) && $_GET['cat'] == $subcat["id"] ? "selected" : "" ?>
																			>
																			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
																			<?php echo $subcat['nomcategorie'] ?>

																		</option>

																		<?php
                                                                        // echo "<option value=".$subcat["id"]." ".$article['cath'] == $subcat['id'] ? "selected" : ""."  >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$subcat['nomcategorie']."</option>";
																	}
																}
															}else{
																$found = 0;
																foreach($parent as $catParent){
																	if($catParent['id_categorie'] == $id_parent){
																		$found += 1;
																		break;
																	}

																}
																if($found == 0){
																	?>
																	<option value="<?php echo $id_cat ?>" >
																		&nbsp;&nbsp;&nbsp;&nbsp;
																		<?php echo $nom_cat ?>

																	</option>

																	<?php
                                                                        // echo "<option value=".$id_cat.">&nbsp;&nbsp;&nbsp;&nbsp;".$nom_cat."</option>";
																}

															}
														}

													} ?>
												</option>
												<?php

											}

											?>
										</select>
									</div>
								</div>
							</div>

						</div>
						<div class="row">
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
							<?php if(isset($_GET['type']) && $_GET['type'] == "article"): ?>
								<button class="btn btn-dark" id="ca" onclick="switchChart(this.id)" style="margin-bottom: 15px;">Basculer sur affichage CA</button>
							<?php else: ?>

								<button class="btn btn-dark" id="article" onclick="switchChart(this.id)" style="margin-bottom: 15px;">Basculer sur affichage nombre d'articles</button>
							<?php endif; ?>
							<div id="loader" class="mx-auto mt-5" style="display:none"><img src="../images/loading.gif" alt="loading_gif"></div>
							<?php if(isset($message) && $message != ""): ?>
								<h2 class="text-danger mx-auto mt-5" id="message"><?php echo $message ?></h2>
							<?php else: ?>
								<div id="chartContainer" style="height: 1500px; width: 100%;overflow-y: hidden; 
								overflow-x: hidden; "></div>
							<?php endif; ?>
							

						</div>

					</div>
				</div>
			</div>
			<aside class="control-sidebar control-sidebar-dark" style="display: none;">
				<div class="p-3">
					<h5>Title</h5>
					<p>Sidebar content</p>
				</div>
			</aside>

			<footer class="main-footer">
				<div class="float-right d-none d-sm-inline">
				</div>
				<strong>Copyright © 2022 <a href="https://mobisoft.fr">MOBISOFT</a>.</strong>
			</footer>
			<div id="sidebar-overlay"></div></div>
			<script src="../lib/dist/js/jquery.slim.min.js"></script>
			<script src="../lib/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

			<script src="../lib/dist/plugins/moment/moment.min.js"></script>
			<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
			<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
			<script type="text/javascript">
				$('#famille').change(function(){
					var categorieNom = $("#famille :selected").text();
					var categorieChoix = $('#famille').val()
					$('#chartContainer').hide()
					$('#message').hide()
					var startDate = $('#startdate').val() 
					var endDate = $('#enddate').val()
					console.log(startDate,endDate)
					if (startDate == "" && endDate == "") {
						$.ajax({
							url: "statproduit.php?cat=" + categorieChoix + "&nomcat=" + categorieNom,
							type: "GET",
							beforeSend: function() {

								$('#loader').show();
							},
							complete: function(){
								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat=" + categorieChoix + "&nomcat=" + categorieNom
							}
						})
					}else if (startDate != "" && endDate == "") {
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+categorieChoix+"&startDate=" + startDate + "&nomcat=" + categorieNom,
							type: "GET",
							beforeSend: function() {
								
								$('#loader').show();
							},
							complete: function(){
								$('#loader').hide();
								
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+categorieChoix+"&startDate=" + startDate + "&nomcat=" + categorieNom
							}
						})
					}else if(startDate != "" && endDate != ""){
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+categorieChoix+"&startDate=" + startDate + "&endDate=" + endDate+ "&nomcat=" + categorieNom,
							type: "GET",
							beforeSend: function() {
								$('#loader').show();
							},
							complete: function(){
								
								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+categorieChoix+"&startDate=" + startDate + "&endDate=" + endDate + "&nomcat=" + categorieNom
							}
						})
					}
					
				});
				$('#validDate').click(function(){
					var startDate = $('#startdate').val()
					var endDate = $('#enddate').val()
					var cat = $('#famille').val()
					var categorieNom = $("#famille :selected").text();

					if (startDate != "" && endDate == "") {
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+cat+"&startDate=" + startDate + "&nomcat=" + categorieNom,
							type: "GET",
							beforeSend: function() {
								
								$('#loader').show();
							},
							complete: function(){
								$('#loader').hide();
								
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+cat+"&startDate=" + startDate + "&nomcat=" + categorieNom
							}
						})
					}else if(startDate != "" && endDate != ""){
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+cat+"&startDate=" + startDate + "&endDate=" + endDate+ "&nomcat=" + categorieNom,
							type: "GET",
							beforeSend: function() {
								$('#loader').show();
							},
							complete: function(){
								
								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+cat+"&startDate=" + startDate + "&endDate=" + endDate + "&nomcat=" + categorieNom
							}
						})
					}
				})

				function switchChart(id){
					var startDate = '<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : "" ?>'
					var endDate = '<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : "" ?>'
					var cat = '<?php echo isset($_GET['cat']) ? $_GET['cat'] : "" ?>'
					var categorieNom = $("#famille :selected").text();
					if (startDate != "" && endDate == "") {
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+cat+"&startDate=" + startDate + "&nomcat=" + categorieNom + "&type=" +id,
							type: "GET",
							beforeSend: function() {
								
								$('#loader').show();
							},
							complete: function(){
								$('#loader').hide();
								
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+cat+"&startDate=" + startDate + "&nomcat=" + categorieNom + "&type=" +id
							}
						})
					}else if(startDate != "" && endDate != ""){
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+cat+"&startDate=" + startDate + "&endDate=" + endDate+ "&nomcat=" + categorieNom + "&type=" +id,
							type: "GET",
							beforeSend: function() {
								$('#loader').show();
							},
							complete: function(){
								
								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+cat+"&startDate=" + startDate + "&endDate=" + endDate + "&nomcat=" + categorieNom + "&type=" +id
							}
						})
					}else{
						$('#chartContainer').hide()
						$('#message').hide()
						$.ajax({
							url: "statproduit.php?cat="+cat+ "&nomcat=" + categorieNom + "&type=" +id,
							type: "GET",
							beforeSend: function() {
								$('#loader').show();
							},
							complete: function(){
								
								$('#loader').hide();
							},
							success: function (response) {
								window.location.href = "statproduit.php?cat="+cat + "&nomcat=" + categorieNom + "&type=" +id
							}
						})
					}
				}

				$('#resetDate').click(function(){
					$('#startdate').val("")
					$('#enddate').val("")
				})


			</script>
			<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
			<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


		</body></html>