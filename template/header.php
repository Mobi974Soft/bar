<?php
include('../DBConfig.php');
if (!isset($_SESSION['loggedinAdmin'])) { //if login in session is not set

	header("Location: ../login/");
}

$checkHoraire = $conn->query("SELECT * FROM table_client_info");
if($checkHoraire->num_rows == 1){
	$infos = $checkHoraire->fetch_assoc();
	$heure_debut = $infos['heure_debut'].":00";
	$heure_fin = $infos['heure_fin'].":00";
	$heure_actuel = date('H:i:s');
	$nom_magasin = $infos['nom_magasin'];
	if($_SESSION['role'] != "superadmin"){
		if( !($heure_actuel >= $heure_debut && $heure_actuel <= $heure_fin)){
			echo "<script>alert('Magasin fermé ! ')</script>";
			session_destroy();
			header("Location: ../login/?info=close");
		}
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Administration</title>
	<link rel="stylesheet" href="../lib/dist/plugins/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="../lib/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="../lib/dist/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css" />
	<link rel="stylesheet" href="../lib/dist/plugins/chart.js/Chart.min.css" />
	<link rel="stylesheet" href="../lib/dist/css/adminlte.min.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<!-- <link rel="stylesheet" href="../template/style.css" /> -->
	<link rel="stylesheet" href="../template/style.css?random=<?php echo uniqid(); ?>"/>
	<link rel="stylesheet" href="sitemap.css?random=<?php echo uniqid(); ?>" />
	
</head>
<body class="sidebar-mini" cz-shortcut-listen="true" style="height: auto;">
	<div class="wrapper">

		<nav class="main-header navbar navbar-expand navbar-white navbar-light">

			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<?php 
				if ($title != 'Gestions des articles') {
					?>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="<?php 	echo $accueil; ?>" class="nav-link">Accueil</a>
					</li>

					<?php
				}else{
					?>	
					<li class="nav-item d-none d-sm-inline-block">
						<a href="<?php 	echo $accueil; ?>" class="nav-link">Accueil</a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="articles.php" class="nav-link">Gestion des Articles</a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="statistiques.php" class="nav-link">Statistiques</a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="raccourci.php" class="nav-link">Raccourcis</a>
					</li>
					<li class="nav-item d-none d-sm-inline-block">
						<a href="../login/logout.php?userid=<?php echo $_SESSION['id'] ?>&action=admin" class="btn btn-block btn-danger">Déconnexion</a>
					</li>
					<?php
				}
				?>
				
				<!-- <li class="nav-item d-none d-sm-inline-block">
					<a href="#" class="nav-link">Contact</a>
				</li> -->
			</ul>

			
		</nav>


		<aside class="main-sidebar sidebar-dark-primary elevation-4 ">

			<a href="index3.html" class="brand-link">
				<!-- <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
				<!--				--><?php //include('../parametre.php') ?>
				<span class="brand-text font-weight-light text-center"><?php echo isset($nom_magasin) ? ucfirst($nom_magasin) : "Mobisoft" ?></span>
			</a>

			<div class="sidebar">

				<div class="user-panel mt-3 pb-3 mb-3 d-flex">
					<div class="image">
						<!-- <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image"> -->
					</div>
					<div class="info">
						<!-- <a href="#" class="d-block">Alexander Pierce</a> -->
					</div>
				</div>

				

				<nav class="mt-2navbar-expand-lg " >
					<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

						<?php 

						if ($title != "Gestions des articles") {
							?>

							<li class="nav-item">
								<a href="../admin/articles.php" class="nav-link">
									<i class="nav-icon fas fa-barcode"></i>
									<p>
										Gestion des articles
										<!--									<span class="right badge badge-danger">New</span>-->
									</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="../admin/statistiques.php" class="nav-link">
									<i class="nav-icon fas fa-inbox"></i>
									<p>
										Statistiques
										<!--                                     <span class="right badge badge-danger">New</span>-->
									</p>
								</a>
							</li>
							<li class="nav-item">
								<a href="../admin/raccourci.php" class="nav-link">
									<i class="nav-icon fas fa-inbox"></i>
									<p>
										Raccourcis
									</p>
								</a>
							</li>

							<?php if($_SESSION['role'] == "superadmin"): ?>
								<li class="nav-item">
									<a href="../admin/profil.php" class="nav-link">
										<i class="nav-icon fas fa-gear"></i>
										<p>
											Options
										</p>
									</a>
								</li>
							<?php endif; ?>
							<li class="nav-item">
								<a href="../admin/manageCaisse.php" class="nav-link">
									<a href="../login/logout.php?userid=<?php echo $_SESSION['id'] ?>&action=admin" class="btn btn-block btn-danger btn-lg">Déconnexion</a>
								</a>
							</li>

							<?php
						}else

						{
							?>	

							<p style="color:#FFFFFF"><i class="fa-solid fa-folder"></i> Catégories</p>
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

							echo "<ul class='sitemap'>";
							foreach (range('A', 'Z') as $char) {
								?>
								<ul>
									<li>  <a href="" style="color:#FFFFFF;"  onclick="toggleCat('<?php echo $char ?>',event)"><i class="fa-solid fa-folder"  id="icon-<?php echo $char ?>"></i> <?php echo $char ?></a>
										<ul id="bloc-<?php echo $char ?>" style="display: <?php echo isset($_GET['nomCat']) && strtoupper($_GET['nomCat'][0]) == $char[0] ? "block" : "none"   ?>; " >
											<?php foreach ($categorie as $cat) {
												$nom_cat = $cat["nomcategorie"];
												$id_cat = $cat["id_categorie"];
												$id_parent= $cat['id_parent'];
												if(ucfirst($nom_cat[0]) == $char){
													if($id_parent == 0){
														?>
														<li><a href="categorie.php?page=1&id=<?php echo $id_cat ?>&nomCat=<?php echo $nom_cat ?>" class="catFirst"><?php echo ucfirst($nom_cat) ?></a>
															<?php
															echo "<ul>";
															foreach($child as $subcat){
																if($subcat['id_parent'] == $cat["id_categorie"]){
																	?>
																	<li>
																		<a href="categorie.php?page=1&id=<?php echo $id_cat ?>&nomCat=<?php echo $subcat['nomcategorie'] ?>" class="catFirst"><?php echo $subcat['nomcategorie'] ?></a>
																	</li>
																	<?php
																}
															}
															echo "</ul>";
															echo "</li>";

														}
													}

												} ?>
											</ul>
										</li>
									</ul>

									<?php
								}
								echo "</ul>";



								?>


								<?php
							}

							?>

						</ul>
					</nav>

				</div>

			</aside>
