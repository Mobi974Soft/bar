<div class="col-lg-7" id="produitsBlock">
			<h3 class="text-title">Choisir une catégorie</h3>
			<div class="row" id="categoryList">
				<a class="btn btn-app bg-dark activeCategory" id="categorie-0" style="border-radius: 5px;">
					Tous
				</a>
				<?php 
				while ($categorie = $categories->fetch_assoc()) {
					?>
					<a class="btn btn-app box-cat" id="categorie-<?php echo $categorie['id_categorie'] ?>"style="border-radius: 5px;">
						<?php echo $categorie['nomcategorie'] ?>
					</a>
					<?php
				}
				?>
			</div>

			<div class="row" style="margin-top:30px ">	
				<div class="col-lg-10">
					<div class="input-group" style="margin-bottom: 30px">
						<input type="search" class="form-control form-control-lg input" id="searchArticle" 
						placeholder="Rechercher un article" autofocus>
						<input type="hidden" id="id_caisse_search" value="<?php echo isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : "" ?>">
						<!-- <div id="livesearch"></div> -->
						<div class="input-group-append">
							<button type="submit" class="btn btn-lg btn-default">
								<i class="fa fa-search"></i>
							</button>
						</div>
					</div>

				</div>
			</div>	


			<h3>Liste des produits</h3>
			<div class="row" id="listeProduits">
				<?php 	
				$sql = "SELECT * FROM table_client_catalogue ORDER BY num LIMIT 12";
				$query = $conn->query($sql);
				// $json = file_get_contents('catalogue.json');
				// $parsedJson = json_decode($json, true);
				if ($query->num_rows > 0) {
					while($produit = $query->fetch_assoc()){
						$promo = date('Y-m-d') < $produit['promo_fin'] ? $produit['prixttc_promo_euro'] : 0;
						$prix = $produit['prixttc_promo_euro'] > 0 ? $promo : $produit['prixttc_euro'];
						$unite = $produit['unite'];
						$qte_unite = $produit['qte_unite'];

						?>
						<?php if ($unite > 0 ) {
							?>
							<div class="col-lg-3" onclick="showModalPesage('<?php echo $produit['titre'] ?>',<?php echo $unite ?>,<?php echo $qte_unite ?>,<?php echo $produit['num'] ?>,<?php echo $produit['prixttc_euro'] ?>,<?php echo $promo ?>,'<?php echo $produit['id'] ?>','<?php echo $produit['ref'] ?>',<?php echo $produit['code_tva'] ?>,<?php echo $produit['cath'] ?>,'<?php echo $produit['img'] ?>')" >
								<?php
							}else{
								?>
								<div class="col-lg-3" onclick="addToCart('<?php echo addslashes($produit['titre']) ?>',<?php echo $produit['prixttc_euro'] ?>,<?php echo $promo ?>,<?php echo $id_caisse ?>,<?php echo $produit['num'] ?>,'<?php echo $produit['img'] ?>','<?php echo $produit['id'] ?>','<?php echo $produit['ref'] ?>',<?php echo $produit['code_tva'] ?>,<?php echo $produit['cath'] ?>,<?php echo $unite ?>,'<?php echo $qte_unite ?>')">
									<?php
								} ?>

								<div class="card cardProduits" style="height: 150px" >
									<div class="row no-gutters">
										<?php if (strlen($produit['img']) > 0 ) {
											?>
											<div class="col-md-6 boxImgProduit">
												<img src="<?php echo $produit['img'] ?>" class="img-produit" alt="" width="150" height="150">
												<span class="badge bg-teal">En stock <?php echo $produit['stock'] ?></span>
											</div>
											<div class="col-md-6">
												<div class="card-body">
													<h5 class="card-title"><?php echo $produit['titre'] ?></h5>
													<p class="card-text text-success prixStyle"><?php echo $prix ?> €</p>
												</div>
											</div>
											<?php
										} else{?>
											<div class="col-md-12">
												<div class="card-body">
													<h5 class="card-title"><?php echo $produit['titre'] ?></h5>
													<p class="card-text text-success prixStyle"><?php echo $prix ?> € <?php echo $unite == 1 ? " / 1 KG" : "" ; ?></p>
													<span class="badge bg-teal">En stock <?php echo $produit['stock'] ?></span>
												</div>
											</div>

										<?php } ?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>	
				</div>	
			</div>

			<div class="col-lg-4 cart">

				<div class="row panierTitle">
					<div class="col-lg-5"><i class="fa-solid fa-cart-shopping"></i> <span style="font-weight: 600;font-size:24px;margin-left: 5px">Panier</span></div>
					<div class="col-lg-3">
						<!-- <button type="button" class="btn btn-primary btn-block"> <i class="fa-solid fa-percent"></i> Remise</button> -->
					</div>
					<div class="col-lg-3">
						<?php if ($restaurant == 1): ?>
							<button type="button" class="btn btn-dark btn-block" id="btnNumeroTable" data-toggle="modal" data-target="#modal-tables" ><i class="fa fa-bell"></i> 
								<?php 
								$sql = "SELECT numero FROM restaurant_tables WHERE status = 1";
								$table = $conn->query($sql);
								if ($table->num_rows>0) {
									$numerotable = $table->fetch_assoc()["numero"];
									echo "Table n° " . $numerotable; 
								}else{
									echo "Choisir une table";
								}
								?>
							</button>
						<?php endif ?>
						
					</div>
					<div class="col-lg-1">
						<button type="button" class="btn btn-default" onclick="viderPanier('<?php echo $id_caisse ?>')">
							<i class="fa-solid fa-arrows-rotate"></i>
						</button>
					</div>
				</div>

				<div  id="panierContent">
					<?php 
					include('../functions.php');
					$sql = "SELECT p.num as num,p.titre as titre,p.qte as qte,p.promo as promo,p.pu_euro as pu_euro,p.ref as ref,c.img as img FROM table_client_panier p  INNER JOIN table_client_catalogue c ON c.ref = p.ref WHERE id_caisse = $id_caisse";
					$query= $conn->query($sql);

					if ($query->num_rows>0) {
						while($row = $query->fetch_assoc()){
							$titre= $row['titre'];
							$qte= $row['qte'];
							$prixPromo = $row['promo'];
							$pu_euro = $row['pu_euro'];
							$ref = $row['ref'];
							$num = $row['num'];


							$promo = $prixPromo > 0 ? 1 : 0;

							$qteLigne = $promo == 1 ? formatNumber($promo) ."€ x". $qte : formatNumber($pu_euro)."€ x".$qte;
							$prixLigne = $promo == 1 ? $promo * $qte : $pu_euro * $qte;
							$promoLigne = $promo == 1 ? "Remise de -"+ formatNumber($pu_euro-$promo) : "" ;
							?>
							<div class="callout produit">
								<div class="row">
									<div class="col-lg-1" style="margin:auto">
										<?php if (isset($_GET['paiement'])): ?>
											<div class="input-group">
												<input type="checkbox" name="produitChoix[]" value="<?php echo  $promo == 1 ? $promo * $qte : $pu_euro * $qte ?>" class="form-control ProduitChoix" style="height:16px">
											</div>
										<?php endif ?>
										
									</div>
									<?php 
									if ($row['img'] != "" ) {

										?>
										<div class="col-lg-2">
											<img src="<?php echo $row['img'] ?>" class="img-produit-panier" width="50" height="50">
										</div>
										<div class="col-lg-4">
											<?php
										}else
										{
											?>

											<div class="col-lg-6">
												<?php
											}

											?>
											<p style="font-weight: 600;margin: 0;"><?php echo $titre ?></p>
											<p class="text-muted" style="font-size:17px;margin-bottom: 0;"><?php echo $qteLigne ?></p>
											<small><?php echo $promoLigne ?></small>
										</div>
										<div class="col-lg-2" style="margin: auto;">
											<input type="text" onclick="this.select()" class="qteProduit" style="width: 40px !important;" name="quantiteProduit" id="quantiteProduit-<?php echo $ref ?>" 
											value="<?php echo $qte ?>" />
										</div>
										<div class="col-lg-2 offset-lg-1" style="margin: auto;">
											<p class="text-muted totalPrice" style="font-size:20px;margin-bottom: 0;"><?php echo formatNumber($prixLigne) ?> €</p>
										</div>
										<div class="col-lg-1" style="margin: auto;">
											<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle('<?php echo $ref ?>', <?php echo $id_caisse ?>  )"></i> 
										</div>
									</div>
								</div>

								<?php
							}
						}

						?>
					</div>

					<div id="paiementBlock">
						<?php 
						$sql = "SELECT pu_euro,promo,qte,remise,remise_euro,date,taux_tva FROM table_client_panier WHERE id_caisse = $id_caisse ORDER BY date";
						$query = $conn->query($sql);
						if ($query->num_rows>0) {
							$totalPanier = 0;
							$totalHT = 0;
							$cumul_tva = 0;
							while($row = $query->fetch_assoc()){
								$promo = $row['promo'];
								$pu_euro  = $row['pu_euro'];
								$qte = $row['qte'];
								$prix = $pu_euro;
								$tauxtva = $row['taux_tva'];
								if ($promo > 0) {
									$prix = $pu_euro;
								}
								$totalPanier += $prix * $qte;
								$cumul_tva += ($prix - ($prix / (1+ $tauxtva / 100 ))) * $qte;
							}
							$totalHT = $totalPanier - $cumul_tva;
						}
						?>
						<div class="row">
							<div class="col-lg-10">
								<p style="padding: 10px 10px 0 10px;margin: 0;"  class="text-muted">Total HT</p>
							</div>
							<div clas="col-lg-2" >
								<p style="padding: 10px 10px 0 10px;margin: 0;"  class="text-muted" id="sous-total" ><?php echo isset($totalHT) ? formatNumber($totalHT)  : "0.00" ?> €</p>
							</div>

							<div class="col-lg-10">
								<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">TVA</p>
							</div>
							<div clas="col-lg-2" >
								<p style="padding: 10px 10px 0 10px;margin: 0;"  class="text-muted"><?php echo isset($cumul_tva) ? formatNumber($cumul_tva) : "0.00" ?> €</p>
							</div>

							<div class="col-lg-10">
								<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">Remise</p>
							</div>
							<div clas="col-lg-2" >
								<p style="padding: 10px 10px 0 10px;margin: 0;"  class="text-muted">0.00 €</p>
							</div>
						</div>
						<div class="row" style="margin-left: 5px;margin-top: 5px;">
							<div class="col-md-4 " >
								<div class="box-cart">
									<i class="fa-solid fa-pause "></i>
									<p style="margin-top: 10px;">Prendre commandes</p>
								</div>
							</div>
							<div class="col-md-4">
								<div class="box-cart">
									<i class="fa-solid fa-percent "></i>
									<p style="margin-top: 10px;">Remise</p>
								</div>
							</div>
							<div class="col-md-4 ">
								<div class="box-cart"></div>
							</div>
						</div>
						<div class="row" style="padding: 15px;" id="btnPaiement"  onclick="proceedToPayment('<?php echo $id_caisse ?>')">
							<div class="col-lg-12">
								<div class="small-box bg-success">
									<div class="inner">

										<h3 id="totalPanier"><?php echo isset($totalPanier) && $totalPanier != 0 ? formatNumber($totalPanier) : 0.00 ?><sup style="font-size: 20px">€</sup></h3>
										<p></p>
									</div>
									<div class="icon">
										<i class="fa-solid fa-cash-register"></i>
									</div>
									<a href="#" class="small-box-footer">Passer au paiement <i class="fas fa-arrow-circle-right"></i></a>
								</div>
							</div>

						</div>
					</div> 