<div class="col-4 cart">

	<div class="row panierTitle">
		<div class="col-1 text-center" style="margin: auto;"><i class="fa-solid fa-cart-shopping "></i> <span
			id="panierlogo" style="font-weight: 600;margin-left: 5px"></span></div>
			<div class="col-lg-3">
				<?php
				$numerotable = 0;
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
				<!-- <button type="button" class="btn  btn-info" style="width: 100%;" id="btnClient"
				onclick="window.location.href = '?clients' ">
				<i class="fa fa-user"></i> Client
			</button> -->
			<?php
		}

		?>
	</div>
	<div class="col-lg-1">
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
			<div class="col-2">
				<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modal-fidelite">Fidélité</button>
			</div>
			<?php
			$promoStatus = PromoCode::getStatus($id_caisse, $numerotable);
			$promoButtonDisabled = !$promoStatus['available'] && !$promoStatus['active'];
			?>
			<div class="col-3 promo-code-action">
				<button type="button"
					id="btnCodePromo"
					class="btn btn-block <?php echo $promoStatus['active'] ? 'btn-danger promo-active' : 'btn-promo'; ?>"
					<?php echo $promoButtonDisabled ? 'disabled' : ''; ?>
					title="<?php echo htmlspecialchars($promoStatus['message'], ENT_QUOTES, 'UTF-8'); ?>"
					onclick="togglePromoCode('<?php echo $id_caisse ?>','<?php echo $numerotable ?>',<?php echo $promoStatus['active'] ? 'true' : 'false'; ?>)">
					<i class="fa-solid <?php echo $promoStatus['active'] ? 'fa-rotate-left' : 'fa-tag'; ?>"></i>
					<?php echo $promoStatus['active'] ? 'Annuler promo' : 'Code promo'; ?>
				</button>
			</div>
			<div class="col-lg-2">
				<button type="button" class="btn btn-default"
				onclick="viderPanier('<?php echo $id_caisse ?>','<?php echo isset($table_active) ? $table_active : 0 ?>')">
				<i class="fa-solid fa-arrows-rotate"></i>
			</button>
		</div>
	</div>
	<!-- MODAL FIDELITE -->
    <div class="modal fade" id="modal-fidelite" style="display: none;" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Points Fidélité</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="onCloseFideliteModal()">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" style="padding:50px">
                    <div id="bloc-fidelite-scan" style="display:none;text-align: center;">
                        <!-- <p class="text-center text-paiement text-danger" style="font-size: 18px; font-weight: 800;font-family: roboto;"> </p> -->
                        <div class="col-md-12 text-center" id="affichageSolde">
                            <input type="text" id="inputScannerQrCode"
                                onkeydown="scanQrCode(this.value,'<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',<?php echo $_SESSION['client_id'] ?>,event);"
                                placeholder="Scanner" style="width: 100%;" />
                        </div>

                    </div>
                    <div id="usePoint" style="display:none;text-align: center;">
                    <div class="col-md-12 text-center" id="affichageSoldeConsultation">
                    <input type="text" id="inputScannerQrCodePoint"
                                onkeydown="utiliserPoints(this.value,'<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',<?php echo $_SESSION['client_id'] ?>,event);"
                                placeholder="Scanner puis utiliser les points fidélité" style="width: 100%;" />  </div>                                                       
                    </div>
                    <div id="bloc-fidelite-points" style="display:none;text-align: center;">
                         <p class="text-center text-paiement text-danger" style="font-size: 18px; font-weight: 800;font-family: roboto;">Scanner le carte de fidélité pour le nombre d'article achetés</p>
                        <div class="col-md-12 text-center" id="affichageSoldePoint">
                            <input type="text" id="inputScanToShowPoint"
                                onkeydown="showTotalPoints(this.value,'<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',<?php echo $_SESSION['client_id'] ?>,event);"
                                placeholder="Scanner" style="width: 100%;" />
                        </div>

                    </div>
                    <div class="row" id="bloc-creation-client">
                        <div class="col-md-3">
                            <a type="button" class="btn btn-block btn-primary"
                                href="https://www.fidelias.fr/fidelite/QR/genererQr.php?id_client=<?php echo $_SESSION['client_id']; ?>"
                                target="_blank" style="    padding: 19px;;font-size: 18px;">Créer un compte client
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-block btn-success"
                                style="    padding: 19px;;font-size: 18px;" id="showScanQr">Scanner

                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-block btn-danger"
                                style="    padding: 19px;;font-size: 18px;" id="showScanQrPoints">Utiliser les points

                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-block btn-danger"
                                style="    padding: 19px;;font-size: 18px;" id="showTotalPoints" >Voir points

                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" onclick="onCloseFideliteModal()">Fermer</button>

                </div>
            </div>

        </div>
    </div>
	<!-- <div class="row" style="margin:0 !important">
		<div class="col-12">
			<div class="card">
				<div class="card-body" style="padding: 5px;">
					<div class="row" style="margin:0 !important;">
						<div class="col-md-6  btnLeft"  >
							<button type="button" class="btn btn-block btn-danger btn-lg btnCaisse"
							onclick="viderPanier('<?php echo $_SESSION['id_caisse'] ?>','<?php echo $_SESSION['session'] ?>')">Vider
						</button>
					</div>

					<div class="col-md-6  btnRight">
						<button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
						data-toggle="modal"
						data-target="#modal-cb" id="paiementCB">
						CB
					</button>
				</div>
			</div>

			<div class="row"  style="margin:0 !important;">
				<div class="col-md-6  btnLeft" >
					<button type="button" style="color: white;" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
					data-target="#modal-cheque" id="paiementCheque">
					Chèques
				</button>
			</div>


			<div class="col-md-6 btnRight">
				<button type="button" class="btn btn-block btn-success btn-lg btnCaisse" data-toggle="modal"
				data-target="#modal-espece" id="paiementEspece">
				Espèces
			</button>
		</div>
	</div>

	<div class="row"  style="margin:0 !important;">
		<div class="col-md-6 btnLeft" >
			<button type="button" class="btn btn-block btn-dark btn-lg btnCaisse"
			onclick="totalCaisse('<?php echo $_SESSION['id_caisse'] ?>',<?php echo isset($blocage_total_caisse) && $blocage_total_caisse == 1 ? 1 : 0 ?>)">Total caisse
		</button>
	</div>
	<div class="col-md-6 btnRight" >
		<button type="button"   class="btn btn-block btn-primary btn-lg btnCaisse " data-toggle="modal"
		data-target="#modal-retour" id="retourArticle" >Retour article
	</button>
</div>

</div>

<div class="row"  style="margin:0 !important;">
	<div class="col-md-6 btnLeft" >
		<button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" id="modalRemise" data-toggle="modal"
		data-target="#modal-remise" <?php echo isset($_SESSION['optionPromo']) && $_SESSION['optionPromo'] == 1 && !isset($_SESSION['showPromo']) ? "disabled" : (isset($_SESSION['optionPromo']) && $_SESSION['optionPromo'] == 1 && $_SESSION['showPromo'] == 1 ? ""  : "") ?> >
		Remise
	</button>
</div>
<div class="col-md-6 btnRight" >
	<button type="button"  style="background-color:orange !important;color:white;border:1px solid orange; " class="btn btn-block  btn-lg btnCaisse " data-toggle="modal"
	data-target="#modal-divers" id="produitDivers">
	Divers
</button>

</div>
</div>

</div>



</div>
</div>
</div> -->
<div class="row" style="margin-left: 5px;">
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
<div id="bloc-arendre"></div>
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
			$remiseEuro = (float) $row['remise_euro'];
			$id_produit = $row['id_produit'];
			$remise_unique = json_decode($row['remise_unique']);
			$remise_unique = $remise_unique != NULL ? $remise_unique[0] : 0;
			$date = $row['date'];
			$options = json_decode($row['options'], false, 512, JSON_UNESCAPED_UNICODE);
			// $img = $ref == "remise" ? "" : $conn->query("SELECT img FROM table_client_catalogue WHERE ref = '$ref'")->fetch_assoc()['img'];

			$promo = $prixPromo > 0 ? 1 : 0;
			$prix = $prixPromo > 0 ? $prixPromo : $pu_euro;


			$prixUnitaireNet = max(0, $prix - ($prix * ($remise_unique / 100)) - $remiseEuro);
			$prixLigne = $prixUnitaireNet * $qte;
			$promoLigne = $promo == 1 ? "Prix promotionnel" : "";
			$remiseLigne = ($remise_unique >0 ? "Remise de " . $remise_unique . "%" : ($remise_unique == 100 ? "Article Offert" : ""));
			$qteLigne = formatNumber($prixUnitaireNet) . "€ x" . $qte;
			$promoItem = PromoCode::itemFromState($promoStatus['state'], $num);

			?>
			<div class="accordion" id="accordion-<?php echo $num ?>">
				<div class="card produit" id="product-<?php echo $num ?>">

					<div class="row">

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
						<div class="col-5">
							<p style="font-weight: 600;margin: 0;">
								<?php echo $titre ?>
							</p>
							<?php
							if (is_array($options)) {
								if (count($options) > 0) {
									foreach ($options as $option) {
										echo '<p class="text-muted" style="font-size:13px;margin-bottom: 0;font-style:italic">+' . ucfirst($option[1]) . ' - ' . $option[2] . '€</p>';
									}
								}
							}
							if ($remiseLigne != "" ) {
								echo '<p class="text-muted" style="font-size:13px;margin-bottom: 0;font-style:italic">' . $remiseLigne . '</p>';
							}
							if ($promoItem) {
								echo '<p class="promo-line"><i class="fa-solid fa-tag"></i> Code promo : -' . formatNumber($promoItem['unit_discount']) . ' € / article</p>';
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
						name="quantiteProduit" id="quantiteProduit-<?php echo $num ?>" value="<?php echo $qte ?>"
						onkeypress="updateQuantiteProduit(event,'<?php echo $num ?>','<?php echo $idtable ?>','<?php echo $id_caisse ?>')" />
					</div>
					<div class="col-3" style="margin: auto;">
						<p class="text-muted totalPrice" id="prixProduit-<?php echo $num ?>"
							style="font-size:20px;margin-bottom: 0;">
							<?php echo formatNumber($prixLigne) ?> €
						</p>
					</div>
					<div class="col-1" style="margin: auto;">
						<i class="fa fa-trash text-red" style="cursor:pointer;"
						onclick="deleteArticle('<?php echo trim($num) ?>',<?php echo $id_caisse ?>, '<?php echo $idtable ?>')"></i>
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
									<button disabled
									onclick="offrirArticle('<?php echo $titre ?>','<?php echo $id_caisse ?>','<?php echo $num ?>')"
									class="btn btn-primary btn-block btn-offrir">Offrir <i
									class="fa fa-plus"></i></button>

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
				$prix = $promo > 0 ? $promo : $pu_euro;
				$tauxtva = $row['taux_tva'];
				$remiseProduit = $row['remise'];
				$remise_unique = json_decode($row['remise_unique']);
				$remise_unique = $remise_unique != NULL ? $remise_unique[0] : 0;
				$prix = max(0, $prix - ($prix * ($remise_unique / 100)) - (float) $row['remise_euro']);
				$totalPanier += $prix * $qte;
				$cumul_tva += ($prix - ($prix / (1 + $tauxtva / 100))) * $qte;


			}

		}
		$totalHT = $totalPanier - $cumul_tva;
	}
	?>
	<?php if ($promoStatus['active']): ?>
		<div class="promo-summary">
			<span><i class="fa-solid fa-circle-check"></i> Code promo actif</span>
			<strong>-1 € par article</strong>
		</div>
	<?php elseif (!$promoStatus['available']): ?>
		<div class="promo-summary promo-unavailable">
			<span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($promoStatus['message'], ENT_QUOTES, 'UTF-8'); ?></span>
		</div>
	<?php endif; ?>
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
	<div class="row" style="padding: 0px;" id="btnPaiement" >
		<div class="col-lg-12">
			<div class="small-box bg-success" style="cursor:pointer;margin: 0;">
				<div class="inner">

					<div class="row">
						<div class="col-9"><h3>TOTAL</h3></div>
						<div class="col-3"><h3 id="totalPanier">
						<?php echo isset($totalPanier) && $totalPanier != 0 ? formatNumber($totalPanier) : 0.00 ?><sup
						style="font-size: 20px">€</sup>
					</h3></div>
						
					</div>
					
					
				</div>
				</div>
			</div>

		</div>
	</div>
</div>
