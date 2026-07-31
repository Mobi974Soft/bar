<div class="col-4 cart">

	<div class="row panierTitle">
		<div class="col-1 text-center" style="margin: auto;"><i class="fa-solid fa-cart-shopping "></i> <span
				id="panierlogo" style="font-weight: 600;margin-left: 5px"></span></div>
		<div class="col-lg-4">
			<?php
			$sql = "SELECT numero FROM restaurant_tables WHERE status = 1";
			$table = $conn->query($sql);
			if ($table->num_rows > 0) {
				$numerotable = $table->fetch_assoc()["numero"];
			}
			$client = $conn->query("SELECT * FROM clients WHERE statut = 1 AND idtable = $numerotable");
			if ($client->num_rows > 0) {
				while ($client_info = $client->fetch_assoc()) {
					$nom = $client_info['nom'];
					$prenom = $client_info['prenom'];
				}
				?>
				<button type="button" class="btn  btn-info" style="width: 100%;" id="btnClient"
					onclick="window.location.href = '?clients' ">
					<?php
					echo ucfirst($prenom) . " " . ucfirst($nom);
					?>
					<i class="fa fa-edit"></i>
				</button>

				<?php
			} else {
				?>
				<button type="button" class="btn  btn-info" style="width: 100%;" id="btnClient"
					onclick="window.location.href = '?clients' ">
					<i class="fa fa-user"></i> Client
				</button>
				<?php
			}

			?>
		</div>
		<div class="col-lg-5">
			<?php if ($restaurant == 1): ?>
				<!-- <button type="button" style="width: 100%;" class="btn btn-dark btn-block" id="btnNumeroTable"
					onclick="window.location.href = '?tables' "><i class="fa fa-bell"></i> -->
					<?php
					// $sql = "SELECT numero FROM restaurant_tables WHERE status = 1";
					// $table = $conn->query($sql);
					// if ($table->num_rows > 0) {
					// 	$numerotable = $table->fetch_assoc()["numero"];
					// 	echo "Table n° " . $numerotable;
					// } else {
					// 	echo "Choisir une table";
					// }
					?>
				<!-- </button> -->
			<?php endif ?>

		</div>
		<div class="col-lg-2">
			<button type="button" class="btn btn-default"
				onclick="viderPanier('<?php echo $id_caisse ?>','<?php echo isset($table_active) ? $table_active : 0 ?>')">
				<i class="fa-solid fa-arrows-rotate"></i>
			</button>
		</div>
	</div>
	<div class="row" style="margin-left: 5px;margin-top: 5px;">
		<!-- <div class="col-8 ">
			<button type="button" class="btn btn-default btn-block" data-toggle="modal" data-target="#modal-commande">
				Envoyer en cuisine
			</button>
		</div> -->
		<!-- <div class="col-md-4">
		<button type="button" class="btn btn-default btn-block" onclick="window.location.href = '?commandes' " >
			Voir commande
		</button>
	</div> -->
	<?php if(isset($_GET['paiement'])): ?>
		<div class="col-4 ">
			<button type="button" class="btn btn-default btn-block" data-toggle="modal" data-target="#modal-remise">
				<i class="fa fa-percent"></i>
				Remise
			</button>
		</div>
	<?php endif; ?>
	</div>
	<div id="panierContent">
		<?php
		$idtable = $conn->query("SELECT numero FROM restaurant_tables WHERE status = 1");
		if ($idtable->num_rows == 1) {
			$idtable = $idtable->fetch_assoc()['numero'];
			$sql = "SELECT * FROM table_client_panier  WHERE id_caisse = $id_caisse AND idtable = $idtable";
		} else {
			$sql = "SELECT * FROM table_client_panier WHERE id_caisse = $id_caisse AND idtable = 0";
		}
		$query = $conn->query($sql);
		if ($query->num_rows > 0) {
			$collapseCount = 0;
			while ($row = $query->fetch_assoc()) {
				$collapseCount++;
				$ref = $row['ref'];
				$titre = $row['titre'];
				$qte = $row['qte'];
				$prixPromo = $row['promo'];
				$pu_euro = $row['pu_euro'];
				$ref = $row['ref'];
				$num = $row['num'];
				$idtable = $row['idtable'];
				$remise = $row['remise'];
				$id_produit = $row['id_produit'];
				$remise_unique = json_decode($row['remise_unique']);
				$date = $row['date'];
				$options = json_decode($row['options'], false, 512, JSON_UNESCAPED_UNICODE);
				$img = $ref == "remise" ? "" : $conn->query("SELECT img FROM table_client_catalogue WHERE ref = '$ref'")->fetch_assoc()['img'];

				$promo = $prixPromo > 0 ? 1 : 0;
				$prix = $prixPromo > 0 ? $prixPromo : $pu_euro;

				
				$prixLigne = $prix - ($prix * ($remise / 100));
				$prixLigne = $prixLigne * $qte;
				$promoLigne = $promo == 1 ? "Remise de -" + formatNumber($prix) : "";
				$remiseLigne = ($remise >0 ? "Remise de " . $remise . "%" : ($remise == 100 ? "Article Offert" : ""));
				$qteLigne = $remise >0 ? formatNumber($prixLigne) . "€ x" . $qte : formatNumber($prix) . "€ x" . $qte;

				?>
				<div class="accordion" id="accordion-<?php echo $num ?>">
					<div class="card produit" id="product-<?php echo $num ?>">

						<div class="row">

							<?php if (isset($_GET['paiement'])): ?>
								<div class="col-1" style="margin:auto">
									<div class="input-group">
										<input type="checkbox" id="produitChoix-<?php echo $id_produit; ?>" name="produitChoix[]" value="<?php echo $ref; ?>"
											class="form-control ProduitChoix" onchange="ajoutSousPanier('<?php echo $id_produit ?>','<?php echo $ref ?>','<?php echo $prixLigne ?>','<?php echo $idtable ?>','<?php echo $titre ?>','<?php echo $remise ?>','<?php echo !is_array($remise_unique) ? $row['remise_unique'] : ''?>','<?php echo $id_caisse ?>')
								,changeResteApayer(this,'<?php echo $ref ?>','<?php echo $idtable ?>','<?php echo $id_produit ?>','<?php echo $id_caisse ?>')" style="height:16px">
									</div>
								</div>
							<?php endif ?>
							<div class="col-1">
								<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
									data-target="#collapse<?php echo numberToWords($collapseCount) ?>" aria-expanded="true"
									aria-controls="collapse<?php echo numberToWords($collapseCount) ?>">
									<i class="fa-solid fa-chevron-down"></i>
								</button>
							</div>
							<!-- <div class="col-2">
						<?php
						if ($img !== "") {
							?>
							<img src="<?php echo $img != "" ? $img : "" ?>" class="img-produit-panier" width="50" height="50">
							<?php
						}
						?>
						<p class="text-success" style="margin-top:16px" id="qteUpdated-<?php echo $ref ?>"><?php
						   $sql = "SELECT qteUpdated FROM paiement_quantite WHERE `table` = $idtable AND refupdated = '$ref' ";
						   $quantiteApayer = $conn->query($sql);
						   if ($quantiteApayer->num_rows > 0) {
							   echo "(-" . $quantiteApayer->fetch_assoc()['qteUpdated'] . ")";
						   } ?></p>
					</div> -->
							<div class="col-4">
								<p style="font-weight: 600;margin: 0;">
									<?php echo $titre ?>
								</p>
								<?php
								if (is_array($options)) {
									if (count($options) > 0) {
										foreach ($options as $option) {
											echo '<p class="text-muted" style="font-size:13px;margin-bottom: 0;margin-top: 5px;font-style:italic">+' . ucfirst($option[1]) . ' - ' . $option[2] . '€</p>';
										}
									}
								}
								if ($remiseLigne != "" ) {
									echo '<p class="text-muted" style="font-size:13px;margin-bottom: 0;margin-top: 5px;font-style:italic">' . $remiseLigne . '</p>';
								}
								?>
								<p class="text-muted" style="font-size:17px;margin-bottom: 0;"
									id="qteLigne-<?php echo $ref; ?>">
									<?php echo $qteLigne ?>
								</p>
								<small>
									<?php echo $promoLigne ?>
								</small>
							</div>
							<div class="col-2" style="margin: auto;">
								<input type="text" <?php echo isset($_GET['paiement']) ? "disabled" : "" ?>
									onclick="this.select()" class="qteProduit" style="width: 40px !important;"
									name="quantiteProduit" id="quantiteProduit-<?php echo is_array($options) || $remise > 0 ? $id_produit : $ref ?>" value="<?php echo $qte ?>"
									onkeypress="updateQuantiteProduit(event,'<?php echo $ref ?>','<?php echo $idtable ?>','<?php echo $id_caisse ?>')" />
							</div>
							<div class="col-3 offset-1" style="margin: auto;">
								<p class="text-muted totalPrice" id="prixProduit-<?php echo $ref ?>"
									style="font-size:20px;margin-bottom: 0;">
									<?php echo formatNumber($prixLigne) ?> €
								</p>
							</div>
							<div class="col-1" style="margin: auto;">
								<i class="fa fa-trash text-red" style="cursor:pointer;"
									onclick="deleteArticle('<?php echo trim($ref) ?>',<?php echo $id_caisse ?>, '<?php echo $idtable ?>')"></i>
							</div>

							<div id="collapse<?php echo numberToWords($collapseCount) ?>" class="collapse"
								style="width:100%;background:#ffffff"
								aria-labelledby="heading<?php echo numberToWords($collapseCount) ?>"
								data-parent="#accordion-<?php echo $num ?>">
								<div class="card-body" style="padding: 0 1.25rem;">
									<div class="row">
										<!-- <div class="col-4">
								<label for="" style="font-size:12px;" >Remise (%)</label>
								<input type="number" class="form-control" id="remiseSurProduit-<?php echo $ref ?>" value="<?php echo $remise > 0 ? $remise : 0 ?>" >
							</div> -->
										<?php if(!isset($_GET['paiement'])): ?>
										<div class="col-6">
											<button
												onclick="setRemiseUnique('<?php echo $qte ?>','<?php echo $ref ?>','<?php echo $titre ?>','<?php echo $idtable ?>','<?php echo $date ?>')"
												class="btn btn-primary btn-block">Ajouter une remise <i
													class="fa fa-plus"></i></button>

											<!-- <input type="button"  style="position:absolute;bottom:0px;" class="btn btn-primary" onclick="setRemiseOnProduit('<?php echo $ref ?>','<?php echo $id_caisse ?>','<?php echo $idtable ?>')" value="Valider" /> -->
										</div>
										<?php endif; ?>
									</div>

								</div>
							</div>


						</div>
					</div>
				</div> <!--  FIN accordion -->
				<div id="qtyToPay-<?php echo $ref; ?>" style="display:none;width:250px;padding:10px;"></div>

				<?php
			}
		}

		?>

	</div>
	<div id="paiementBlock">
		<?php
		$idtable = $conn->query("SELECT numero FROM restaurant_tables WHERE status = 1");
		if ($idtable->num_rows == 1) {
			$idtable = $idtable->fetch_assoc()['numero'];
			$sql = "SELECT pu_euro,promo,qte,remise,remise_euro,date,taux_tva,ref,remise_unique FROM table_client_panier WHERE id_caisse = $id_caisse AND idtable = $idtable ORDER BY date";
		} else {
			$sql = "SELECT pu_euro,promo,qte,remise,remise_euro,date,taux_tva,ref,remise_unique FROM table_client_panier WHERE id_caisse = $id_caisse AND idtable = 0 ORDER BY date";
		}

		$query = $conn->query($sql);
		if ($query->num_rows > 0) {
			$totalPanier = 0;
			$totalHT = 0;
			$cumul_tva = 0;
			while ($row = $query->fetch_assoc()) {
				if ($row['ref'] != "remise") {
					$promo = $row['promo'];
					$pu_euro = $row['pu_euro'];
					$qte = $row['qte'];
					$prix = $pu_euro;
					$tauxtva = $row['taux_tva'];
					$remiseProduit = $row['remise'];
					$remise_unique = json_decode($row['remise_unique']);
					if ($promo > 0) {
						$prix = $pu_euro;
					}
					// var_dump($prix . "-(" . $prix .'*('.$remise.'/100))');
					$prix = $prix - ($prix * ($remiseProduit / 100));
					$prix = $prix * $qte;
					$totalPanier += $prix;
					$cumul_tva += ($prix - ($prix / (1 + $tauxtva / 100))) * $qte;

					
				}

			}
			$totalHT = $totalPanier - $cumul_tva;
		}
		?>
		<?php if(isset($_GET['paiement'])): ?>
		<div class="row">

			<div class="col-8">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">Remise</p>
			</div>

			<div clas="col-2">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">
					<?php
					$idtable = $conn->query("SELECT numero FROM restaurant_tables WHERE status = 1");
					if ($idtable->num_rows == 1) {
						$idtable = $idtable->fetch_assoc()['numero'];
						$sql = "SELECT remise,remise_euro,pu_euro,r_globale,r_globale_eur FROM table_client_panier WHERE idtable = $idtable AND ref = 'remise' ";
						$remise = $conn->query($sql);
						if ($remise->num_rows > 0) {
							while ($row = $remise->fetch_assoc()) {
								$remise_pourcent = $row['r_globale'];
								$remise_euro = $row['r_globale_eur'];
								$remiseEnEuro = $row['pu_euro'];
								$pu_euro = $row['pu_euro'];
							}
							$remiseAaffichier = 0;
							if ($remise_pourcent > 0) {
								$totalPanier = $totalPanier - ($totalPanier * ($remise_pourcent / 100));
								$remiseAaffichier += $remiseEnEuro;
							}

							if ($remise_euro > 0) {
								$totalPanier = $totalPanier - (float) $remise_euro;
								$remiseAaffichier += $remiseEnEuro;
							}



							echo formatNumber($remiseAaffichier) . " €";
							
							$totalHT = $totalPanier - $cumul_tva;

						}
					}


					?>
				</p>
			</div>
			
			<div class="col-8">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">Total HT</p>
			</div>
			<div clas="col-2 ">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted" id="sous-total">
					<?php echo isset($totalHT) ? formatNumber($totalHT) : "0.00" ?> €
				</p>
			</div>

			<div class="col-8">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">TVA</p>
			</div>
			<div clas="col-4">
				<p style="padding: 10px 10px 0 10px;margin: 0;" class="text-muted">
					<?php echo isset($cumul_tva) ? formatNumber($cumul_tva) : "0.00" ?> €
				</p>
			</div>
		


		</div>
		<?php endif; ?>
		<div class="row" style="padding: 15px;" id="btnPaiement" onclick="proceedToPayment('<?php echo $id_caisse ?>')">
			<div class="col-lg-12">
				<div class="small-box bg-success" style="cursor:pointer;">
					<div class="inner">

						<h3 id="totalPanier">
							<?php echo isset($totalPanier) && $totalPanier != 0 ? formatNumber($totalPanier) : 0.00 ?><sup
								style="font-size: 20px">€</sup>
						</h3>
						<p></p>
					</div>
					<div class="icon">
						<i class="fa-solid fa-cash-register"></i>
					</div>
					<p href="#" class="small-box-footer">Passer au paiement <i
							class="fas fa-arrow-circle-right"></i></a>
				</div>
			</div>

		</div>
	</div>
</div>