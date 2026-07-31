<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include('../../DBConfig.php');
include('../../functions.php');
$today = date('Y-m-d');
$sqlorder = "SELECT * FROM table_order t ORDER BY t.date DESC";
$orders = $conn->query($sqlorder);


$current_table = $conn->query('SELECT numero FROM restaurant_tables WHERE status = 1')->fetch_assoc()['numero'];
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Caisse</title>
	<link rel="stylesheet" href="../lib/dist/plugins/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="../lib/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="../lib/dist/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css" />
	<link rel="stylesheet" href="../lib/dist/plugins/chart.js/Chart.min.css" />
	<link rel="stylesheet" href="../lib/dist/css/adminlte.min.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
		integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="../template/style.css?random=<?php echo uniqid(); ?>" />
	<link rel="stylesheet" href="restaurant.css?random=<?php echo uniqid(); ?>" />
	<?php
	if (isset($_GET['paiement'])): ?>
		<style>
			#produitsBlock {
				display: none;
			}
		</style>

	<?php endif; ?>
</head>

<body>
	<div class="row" style="padding:30px;height:100%">
		<div class="col-12">
			<div class="row" style="height: 100%;">
				<div class="col-7 scrollable-div">
					<div class="row" style="width: 100%;background: #FFFFFF;padding: 10px;">
						<!-- <button type="button" class="btn btn-block bg-gradient-info btnTableStatus" onclick="changeActiveStatus(this)" style="margin-top: 7px;">Terminées</button> -->
						<button type="button" class="btn btn-block btn-outline-info btnTableStatus" onclick="changeActiveStatus(this)">En cours</button>
					</div>
					<div class="row" style="width: 100%;padding: 20px;">
						<!-- <form action="" style="width:60%;">
					<div class="input-group">
						<input type="search" class="form-control form-control-lg" placeholder="Rechercher une commande">
						<div class="input-group-append">
							<button type="submit" class="btn btn-lg btn-default">
								<i class="fa fa-search"></i>
							</button>
						</div>
					</div>
				</form> -->


					</div>

					<div class="row" id="encours">
						<div class="col-12" id="reloadcommande">
							<?php
							if ($orders->num_rows > 0) {
								$count = 0;
								while ($order = $orders->fetch_assoc()) {
									if ($order['statut'] == 0) {
										$numero_table = $order['numero_table'];
										$id_caisse = $order['id_caisse'];
										$count++;
										$clientID = $order['client_id'];
										$json = $order['info_order'];
										$numero_commande = $order['numero_commande'];
										$data = json_decode($json, true);
										$sql = "SELECT nom,prenom FROM clients WHERE id = $clientID";
										$client = $conn->query($sql);
										if ($client->num_rows > 0) {
											$clientData = $client->fetch_assoc();
											$nom = $clientData['nom'];
											$prenom = $clientData['prenom'];
										} else {
											$nom = "";
											$prenom = "";
										}
							?>
										<div id="order-<?php echo $numero_commande ?>" class="callout <?php echo $count == 1 ? "commande_active" : "" ?>" style="margin-bottom:5px !important" onclick="changeCommandes('<?php echo $numero_commande ?>',this.id,'<?php echo $id_caisse ?>')">
											<div class="row">
												<div class="col-8">
													<h5 class="text-warning" style="font-family:'OpenSans', sans-serif;font-weight: bold;font-size:18px">Commande #<?php echo $order['numero_commande'] ?></h5>
													<p style="font-family:'OpenSans', sans-serif;color:#8394a5;font-size:14px">
														<i class="fa-solid fa-clock"></i> <?php echo date('Y/m/d H:i:s', strtotime($order['date'])) ?><br>
														<!-- <i class="fa-solid fa-user"></i> <?php echo ucfirst($prenom) . " " . strtoupper($nom) . " - Table " . $order['numero_table'] ?> -->
													</p>
												</div>
												<div class="col-2">
													<?php
													$numero_table = $order['numero_table'];
													$id_caisse = $order['id_caisse'];
													$total_commande = $data['total'];
													?>
													<h4 class="text-warning"><?php echo formatNumber($total_commande[0], 2) ?> €</h4>
													<p><?php echo $total_commande[1] > 1 ? $total_commande[1] . " articles" : $total_commande[1] . " article" ?> </p>

												</div>
												<div class="col-2" style="margin:auto">
													<?php
													$en_cuisine = $order['en_cuisine'];

													if ($en_cuisine == 1) {
													?>
														<button type="button" onclick="valideOrder('<?php echo $numero_commande ?>')" class="btn btn-block btn-outline-danger btn-sm">Valider</button>
													<?php
													}
													?>
												</div>
											</div>

										</div>

							<?php
									}
								}
							}
							?>
						</div>

					</div>
				</div>
				<div class="col-5 cart" style="padding:0 30px">
					<div class="row panierTitle">
						<div class="col-lg-8"><i class="fa-solid fa-cart-shopping"></i> <span style="font-weight: 600;font-size:24px;margin-left: 5px">Détails Commande</span></div>
						<!-- <div class="col-lg-3">

				</div> -->
						<div class="col-lg-3">
							<!-- <button type="button" class="btn btn-dark btn-block" disabled id="btnNumeroTable"><i class="fa fa-bell"></i> -->
							<?php
							$sql = "SELECT numero FROM restaurant_tables WHERE status = 1";
							$table = $conn->query($sql);
							// if ($table->num_rows > 0) {
							// 	$numerotable = $table->fetch_assoc()["numero"];
							// 	echo "Table n° " . $numerotable;
							// } else {
							// 	echo "Choisir une table";
							// }
							?>
							<!-- </button> -->

						</div>
					</div>
					<div id="detailsCommande" style="height: 60%;">
						<?php
						$orders = $conn->query($sqlorder);
						$active_order = $orders->fetch_all(MYSQLI_ASSOC);
						if ($orders->num_rows > 0) {
							$active_order = $active_order[0];
							$numerotable = $active_order['numero_table'];
							$id_caisse = $active_order['id_caisse'];
							$json = $active_order['info_order'];
							$data = json_decode($json, true);
							$articles = $data['data'];
							// if ($articles->num_rows>0) {
							foreach ($articles as $article) {
								$titre = $article['titre'];
								$qteLigne = $article['qte'];
								$promoLigne = $article['promo'];
								$prixLigne = $article['pu_euro'];
								$ref = $article['ref'];
								$id_caisse = $article['id_caisse'];
								$num = $article['num'];
								$options = $article['options'];
								$options = str_replace('u0022', '"', $options);
								$options = json_decode($options, true);

								// if($article['options'] !== null){
								// 	$sql = "SELECT * FROM options_produit WHERE id_produit = "
								// }
						?>
								<div class="callout produit" style="margin-bottom:10px;    padding: 10px;">
									<div class="row">
										<div class="col-lg-8">
											<p style="font-weight: 600;margin: 0;font-size: 18px;"><?php echo $titre ?></p>
											<p class="text-muted" style="font-size:17px;margin-bottom: 0;">

												<?php
												$count = 0;
												if (is_array($options)) {
													echo "Options : ";
													foreach ($options as $item) {
														if ($count > 0) {
															echo " + ";
														}
														$count++;
														echo strtoupper($item[1]);
													}
												}
												?>
											</p>
											<!-- <small><?php echo $promoLigne ?></small> -->
										</div>
										<div class="col-lg-3 offset-lg-1" style="margin: auto;">
											<p class="text-danger totalPrice" style="font-size:18px;margin-bottom: 0;font-weight:600">Quantité x<?php echo $qteLigne ?></p>
										</div>
										<!-- <div class="col-lg-1" style="margin: auto;">
									<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle('<?php echo $ref ?>', <?php echo $id_caisse ?>  )"></i> 
								</div> -->
									</div>
								</div>
						<?php
							}
							// }
						}

						?>
					</div>

					<div id="paiementBlock">
						<?php
						if ($orders->num_rows > 0) {
							if (isset($current_table) && $current_table != 0) {
								$idtable =  $current_table;
								$sql = "SELECT pu_euro,promo,qte,remise,remise_euro,date,taux_tva FROM table_client_panier WHERE   idtable = $idtable ORDER BY date";
							} else {
								$sql = "SELECT pu_euro,promo,qte,remise,remise_euro,date,taux_tva FROM table_client_panier  ORDER BY date";
							}

							$query = $conn->query($sql);
							if ($query->num_rows > 0) {
								$totalPanier = 0;
								$totalHT = 0;
								$cumul_tva = 0;
								while ($row = $query->fetch_assoc()) {
									$promo = $row['promo'];
									$pu_euro  = $row['pu_euro'];
									$qte = $row['qte'];
									$prix = $pu_euro;
									$tauxtva = $row['taux_tva'];
									if ($promo > 0) {
										$prix = $pu_euro;
									}
									$totalPanier += $prix * $qte;
									$cumul_tva += ($prix - ($prix / (1 + $tauxtva / 100))) * $qte;
								}
								$totalHT = $totalPanier - $cumul_tva;
							}
						}

						?>
						<div class="row">
							<div class="col-lg-10">
								<!-- <p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">Total HT</p> -->
							</div>
							<!-- <div clas="col-lg-2">
						<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted" id="sous-total"><?php echo isset($totalHT) ? formatNumber($totalHT)  : "0.00" ?> €</p>
					</div> -->

							<div class="col-lg-10">
								<!-- <p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">TVA</p> -->
							</div>
							<!-- <div clas="col-lg-2">
						<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted"><?php echo isset($cumul_tva) ? formatNumber($cumul_tva) : "0.00" ?> €</p>
					</div> -->

							<!-- <div class="col-lg-10">
						<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">Remise</p>
					</div>
					<div clas="col-lg-2">
						<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">0.00 €</p>
					</div> -->

							<!-- <div class="col-lg-10">
						<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">TOTAL</p>
					<div clas="col-lg-2">
						<p style="padding: 10px 10px 0 10px;margin: 0;font-weight:600"><?php echo isset($totalPanier) ? formatNumber($total_commande[0], 2) : "0.00" ?> €</p>
					</div> -->
						</div>

						<!-- <div class="row" style="padding: 15px;" id="btnPaiement" onclick=""> -->
							<!-- <input type="hidden" id="commande_en_cours" value="">
				<button class="btn btn-block btn-default btn-lg" id="" style="width: 100%;" onclick="sendToKitchen($('#commande_en_cours').val() != '' ? $('#commande_en_cours').val() : '<?php echo $current_table ?>' ,'<?php echo $id_caisse ?>')">Envoyer à la cuisine</button>
				<div class="col-6">
					<button class="btn btn-block bg-gradient-primary btn-lg" style="width: 100%;margin: 5px 5px 5px 0;"> <i class="fa fa-cart-shopping"></i> Ajouter au panier
					</button>
				</div>
				<div class="col-6"><button class="btn btn-block bg-gradient-danger btn-lg" style="width: 100%;margin: 5px 5px 5px 0;" onclick="supprimeCommande('<?php echo $current_table ?>','<?php echo $id_caisse ?>')"> <i class="fa fa-trash"></i> Supprimer</button></div> -->
						<!-- </div> -->
					</div>
				</div>
			</div>
		</div>


	</div>
	</div>


</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
	integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
	crossorigin="anonymous"></script>
<script src="../lib/dist/js/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
	integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
	crossorigin="anonymous"></script>
<script src="../lib/dist/plugins/moment/moment.min.js"></script>
<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../lib/dist/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../lib/dist/js/adminlte.min.js?v=3.2.0"></script>
<script src="../plugin-ticket-js/Impresora.js"></script>
<script src="restaurant.js?random=<?php echo uniqid(); ?>"></script>

</html>