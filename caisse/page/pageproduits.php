
<div class="col-7" id="produitsBlock">
<!-- <h3 class="text-title">Choisir une catégorie</h3> -->
	<div class="row" id="categoryList">
		<!-- <a class="btn btn-app bg-dark activeCategory" id="categorie-0" style="border-radius: 5px;">
			Tous
		</a> -->
		<input type="hidden" id="idcaisseCat" value="<?php echo $id_caisse ?>" />
		<?php 
		while ($categorie = $categories->fetch_assoc()) {
			?>
			<a class="btn btn-app box-cat" id="categorie-<?php echo $categorie['id_categorie'] ?>"style="border-radius: 5px;font-weight: 700;
    font-size: 15px;">
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


	<!-- <h3>Liste des produits</h3> -->
	<div class="row" id="listeProduits" style="    overflow: scroll;">
		<?php 	
		$sql = "SELECT * FROM table_client_catalogue ORDER BY num DESC LIMIT 12";
		$queryProduit = $conn->query($sql);
		if ($queryProduit->num_rows > 0) {
			while($produit = $queryProduit->fetch_assoc()){
				if (trim($produit['ref']) == '#DIVERS') continue;
				
				$promo = date('Y-m-d') < $produit['promo_fin'] ? $produit['prixttc_promo_euro'] : 0;
				$prix = $produit['prixttc_promo_euro'] > 0 ? $promo : $produit['prixttc_euro'];
				$unite = $produit['unite'];
				$qte_unite = $produit['qte_unite'];
				$options = $produit['options'];
				$identifiant = $produit['num'];
				
				?>
				
						
                        
						<?php if($options!=1):  ?>
							<div class="col-2" 
							onclick="addToCart('<?php echo addslashes($produit['titre']) ?>',<?php echo $produit['prixttc_euro'] ?>,<?php echo $promo ?>,<?php echo $id_caisse ?>,<?php echo $produit['num'] ?>,'<?php echo $produit['img'] ?>','<?php echo $produit['id'] ?>','<?php echo trim($produit['ref']) ?>',<?php echo $produit['code_tva'] ?>,<?php echo $produit['cath'] ?>,<?php echo $unite ?>,'<?php echo $qte_unite ?>','<?php echo isset($table_active) ? $table_active : 0 ?>','<?php echo $options ?>')">
						<?php else: ?>
							<div class="col-2" onclick="setOptions('<?php echo addslashes($produit['titre']) ?>','<?php echo $identifiant ?>','<?php echo $id_caisse ?>')">
						<?php endif; ?>
						    <div class="card cardProduits"  >
							    <div class="row no-gutters">
								<?php if (strlen($produit['img']) > 0 ) {
									?>
									<!-- <div class="col-md-6 boxImgProduit">
										<img src="<?php echo $produit['img'] ?>" class="img-produit" alt="" width="150" height="150">
										<span class="badge bg-teal">En stock <?php echo $produit['stock'] ?></span>
									</div> -->
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
											<span class="identifiant"><?php echo $produit['ref'] ?></span>
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

	<div class="row" style="position: absolute;bottom: 0;width: 100%;">
		<div class="col-3">
			<button type="button" class="btn btn-block btn-lg btn-warning btnCaisse" onclick="addProduitDiversOneEuro('<?php echo isset($_SESSION['session']) ? $_SESSION['session'] : '' ?>','<?php echo $id_caisse ?>')">
				Supplément 1€
			</button>
		</div>
			<div class="col-3  btnRight">
				<button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
				data-toggle="modal"
				data-target="#modal-cb" id="paiementCB">
				CB
			</button>
		</div>
			<div class="col-3  btnLeft" >
			<button type="button" style="color: white;" class="btn btn-block btn-dark btn-lg btnCaisse" 
			onclick="unlockOffrir()" >
			Fidelité
		</button>
	</div>
	<div class="col-3 btnRight">
		<button type="button" class="btn btn-block btn-success btn-lg btnCaisse" data-toggle="modal"
		data-target="#modal-espece" id="paiementEspece">
		Espèces
	</button>
</div>
<!-- <div class="col-3  btnRight">
	<button type="button" class="btn btn-block btn-warning btn-lg btnCaisse"
	data-toggle="modal"
	data-target="#modal-resto" id="paiementTicketResto">
	Ticket Resto
</button>
</div> -->
</div>
</div>
