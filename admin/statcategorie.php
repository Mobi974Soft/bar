<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../DBConfig.php');

$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info');
$nom_magasin = $nom_magasin->fetch_assoc()['nom_magasin'];

date_default_timezone_set('Indian/Reunion');
$today_date = date('Y-m-d');
$type = "";
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

if(isset($_GET['startDate'],$_GET['endDate']) && $_GET['endDate'] != "" && $_GET['startDate'] != ""){

	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$type = $_GET['type'];

	
	$sql_categorie = "SELECT $select as totalByCat,nomcategorie FROM `table_client_commandes` c 
	INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
	WHERE c.date >= '$startDate' AND c.date <= '$endDate'
	GROUP BY famille $orderBy";



	$sqlhc = "SELECT $select as totalHC FROM `table_client_commandes` c where date >= '$startDate' and date <= '$endDate' and famille NOT IN (SELECT id_categorie FROM table_client_categorie)";
	
}
elseif(isset($_GET['startDate']) && $_GET['startDate'] != "" ){
	$startDate = $_GET['startDate'];
	$sql_categorie = "SELECT $select as totalByCat,nomcategorie FROM `table_client_commandes` c 
	INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
	WHERE c.date = '$startDate' 
	GROUP BY famille $orderBy";



	$sqlhc = "SELECT $select as totalHC FROM `table_client_commandes` c where date = '$startDate'  and famille NOT IN (SELECT id_categorie FROM table_client_categorie)";
}
else{
	
	$sql_categorie = "SELECT $select as totalByCat,nomcategorie FROM `table_client_commandes` c 
	INNER JOIN table_client_categorie cat ON c.famille = cat.id_categorie
	WHERE c.date >= '$today_date' 
	GROUP BY famille $orderBy";



	$sqlhc = "SELECT $select as totalHC FROM `table_client_commandes` c where date LIKE '$today_date' and famille NOT IN (SELECT id_categorie FROM table_client_categorie)";
	
}


$commandes = $conn->query($sql_categorie);
$horscategorie = $conn->query($sqlhc);
$total_hc = $horscategorie->fetch_assoc()["totalHC"];



$filename = "csv_stat_cat/$nom_magasin.csv";
$output = fopen($filename, 'w');
foreach ($commandes as $commande) {
	$total_par_cat = $commande['totalByCat'];
	$nomcategorie = $commande['nomcategorie'];

	if ($total_par_cat > 5 ) {
		$data = array(date('Y-m-d'),$nomcategorie,$total_par_cat);
		fputcsv($output, $data);

	}
}

if ($total_hc>0) {
	$data = array(date('Y-m-d'),"horscategorie",$total_hc);
	fputcsv($output, $data);
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

			var titre = startDate != "" && endDate != "" && startDate != endDate ? startDate + " au " + endDate : (startDate != "" && endDate != "" && startDate == endDate ? startDate : (startDate != "" && endDate == "" ? startDate : today))
			var chart = new CanvasJS.Chart("chartContainer", {
				animationEnabled: true,
				exportEnabled: true,
				title:{
					text: "Statistiques par catégorie du " + titre,
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
	// data: [{
	// 	type: "column",
	// 	toolTipContent: "{y} €",
	// 	dataPoints: dataPoints
	// }]
				data: [{
					type: "bar",
					yValueFormatString: "##0",
					toolTipContent: "{y} ",
					// indexLabel: "{y}",
					// indexLabelFontSize: 15,
					// indexLabelPlacement: "inside",
					// indexLabelFontWeight: "bolder",
					// indexLabelFontColor: "white",
					dataPoints: dataPoints
				}]
			});

			var nom_magasin = '<?php echo $nom_magasin ?>';
			var base_url = document.location.origin;
			$.get(base_url+"/restaurant/admin/csv_stat_cat/"+nom_magasin+".csv?nocache=" + (new Date()).getTime() , getDataPointsFromCSV);
			
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
								<li class="breadcrumb-item">Statistiques Catégorie</li>
							</ol>
						</div>
					</div>
				</div>
			</div> 
			<div class="content">
				<div class="container ">
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
						<div id="chartContainer" style="height: 1500px; width: 100%;overflow-y: hidden; 
						overflow-x: hidden; "></div>

					</div>
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
															<option value="<?php echo $id_cat ?>" >
																&nbsp;&nbsp;&nbsp;&nbsp;
																<?php echo strtoupper($nom_cat) ?></option>
																<?php
                                                                // echo "<option value=".$id_cat.">".strtoupper($nom_cat)."</option>";
																foreach($child as $subcat){
																	if($subcat['id_parent'] == $cat["id_categorie"]){
																		?>
																		<option value="<?php echo $subcat["id"] ?>" 
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
                            <!-- <div class="col-md-3">
                                <input type="submit" style="margin-top: 27px;" name="btnAction" id="btnAction" class="btn btn-lg btn-success" value="Valider" />
                            </div> -->
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
            		var categorieChoix = $('#famille').val()
            		var categorieNom = $("#famille :selected").text();
            		$.ajax({
            			url: "statproduit.php?cat=" + categorieChoix + "&nomcat=" + categorieNom ,
            			type: "GET",
            			success: function (response) {
            				window.location.href = "statproduit.php?cat=" + categorieChoix + "&nomcat=" + categorieNom
            			}
            		})
            	});
            	$('#validDate').click(function(){
            		var startDate = $('#startdate').val()
            		var endDate = $('#enddate').val()

            		if (startDate != "" && endDate == "") {
            			$.ajax({
            				url: "statcategorie.php?startDate=" + startDate ,
            				type: "GET",
            				success: function (response) {
            					window.location.href = "statcategorie.php?startDate=" + startDate 
            				}
            			})
            		}else if(startDate != "" && endDate != ""){
            			$.ajax({
            				url: "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate,
            				type: "GET",
            				success: function (response) {
            					window.location.href = "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate 
            				}
            			})
            		}
            	})
            	function switchChart(id){
            			var startDate = '<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : "" ?>'
            			var endDate = '<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : "" ?>'

            			var type = id;
            			if(startDate == "" && endDate == ""){
            				$.ajax({
            					url: "statcategorie.php?type=" + type,
            					type: "GET",
            					success: function (response) {
            						window.location.href = "statcategorie.php?type=" + type
            					}
            				})
            			}
            			else if (startDate != "" && endDate == "") {
            				$.ajax({
            					url: "statcategorie.php?startDate=" + startDate + "&type=" + type,
            					type: "GET",
            					success: function (response) {
            						window.location.href = "statcategorie.php?startDate=" + startDate + "&type=" + type
            					}
            				})
            			}else if(startDate != "" && endDate != ""){
            				$.ajax({
            					url: "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate + "&type=" + type,
            					type: "GET",
            					success: function (response) {
            						window.location.href = "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate + "&type=" + type
            					}
            				})
            			}else{
            				$.ajax({
            					url: "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate + "&type=" + type,
            					type: "GET",
            					success: function (response) {
            						window.location.href = "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate + "&type=" + type
            					}
            				})
            			}
            	}
            	

            	$('#resetDate').click(function(){
            		$('#startdate').val("")
            		$('#enddate').val("")
            	})

			// $('#periode').daterangepicker({
			// 	locale: {
			// 		format: 'DD/MM/YYYY',
			// 		"applyLabel": "Valider",
			// 		"cancelLabel": "Annuler",
			// 		"fromLabel": "De",
			// 		"toLabel": "A",
			// 		"daysOfWeek": [
			// 			"Dim",
			// 			"Lun",
			// 			"Mar",
			// 			"Mer",
			// 			"Jeu",
			// 			"Ven",
			// 			"Sam"
			// 			],
			// 		"monthNames": [
			// 			"Janvier",
			// 			"Février",
			// 			"Mars",
			// 			"Avril",
			// 			"Mai",
			// 			"Juin",
			// 			"Juillet",
			// 			"Août",
			// 			"Septembre",
			// 			"Octobre",
			// 			"Novembre",
			// 			"Décembre"
			// 			],
			// 	}
			// }).on('apply.daterangepicker', function (e, picker) {
			// 	var startDate = picker.startDate.format('DD/MM/YYYY');
			// 	var endDate = picker.endDate.format('DD/MM/YYYY');
			// 	$.ajax({
			// 		url: "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate,
			// 		type: "GET",
			// 		success: function (response) {
			// 			window.location.href = "statcategorie.php?startDate=" + startDate + "&endDate=" + endDate 
			// 			console.log(response)
			// 		}
			// 	})

			// })
            </script>
            <script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
            <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


        </body></html>