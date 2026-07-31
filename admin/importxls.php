<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require '../vendor/autoload.php'; // Chargez la bibliothèque PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;


include('../DBConfig.php');
include('../functions.php');


$title = $page = 'IMPORTER PRODUITS';
$accueil = 'index.php';
include('../template/header.php');
include('../infos.php');




if (isset($_FILES['fichier_excel']) && $_FILES['fichier_excel']['error'] === UPLOAD_ERR_OK) {
	$chemin_temporaire = $_FILES['fichier_excel']['tmp_name'];
	$nomFichierExcel = $_FILES['fichier_excel']['name'];
	$extension = pathinfo($nomFichierExcel, PATHINFO_EXTENSION);
	if (strtolower($extension) === 'xls') {
		$spreadsheet = IOFactory::load($chemin_temporaire);
		// Sélectionnez la feuille de calcul
		$feuille = $spreadsheet->getActiveSheet();
		$donnees = $feuille->toArray();
	}else{
		$errorMessage = 'Veuillez sélectionner un fichier XLS(excel) à uploader.';
	}

}


?>


<!--Fin etiquette template -->
<div class="content-wrapper" style="min-height: 823px;">
	<?php include('../template/info-page.php') ?>
	<div class="content" style="padding:20px;">
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file"  name="fichier_excel">
			<input type="submit" class="btn btn-dark" value="Uploader et Afficher">
		</form>

		<div class="resultat" style="padding: 50px;">
			

			<div class="row">
				<div class="col-12">
					<div class="callout callout-info">
						<h5> Instructions:</h5>
						Sélectionner a quoi correspond codebarre,titre,prix..etc dans la liste du excel.<br>
						Entrer le montant par lequel multipler le prix.<br>
						Entrer le chiffre arrondi. (exemple : 0.5 ou 0.9 ).<br>
						Choisir une catégorie pour les produits.<br>
						Cliquer Enregistrer pour terminer l'importation des produits.
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:20px;">
				<?php 
				$options  = "";
				$firstLine = true;
				foreach ($donnees as $ligne) {
					if ($firstLine) {
						for ($i=0; $i < count($ligne); $i++) { 
							$options .= '<option value="'.$i.'">'. $ligne[$i] .'</option>';
						}
						$firstLine = false;
					}
				}
				?>
				<div class="col-6">

					<div class="row">
						<div class="col-6"><b>Code barre</b></div>
						<div class="col-6"><select class="form-control"  name="select-gencode" id="select-gencode"><?php echo $options; ?></select></div>
					</div>

					<hr style="margin: 10px 0;">
					<div class="row">
						<div class="col-6"><b>Référence</b></div>
						<div class="col-6"><select class="form-control" name="select-ref" id="select-ref"><?php echo $options; ?></select></div>
					</div>
					<hr style="margin: 10px 0;">
					<div class="row">
						<div class="col-6"><b>Titre</b></div>
						<div class="col-6"><select class="form-control" name="select-titre" id="select-titre"><?php echo $options; ?></select></div>
					</div>
					<hr style="margin: 10px 0;">
					<div class="row">
						<div class="col-6"><b>Quantité</b></div>
						<div class="col-6"><select class="form-control" name="select-qte" id="select-qte"><?php echo $options; ?></select></div>
					</div>
					<hr style="margin: 10px 0;">
					<div class="row">
						<div class="col-6"><b>Prix</b></div>
						<div class="col-6"><select class="form-control" name="select-prix" id="select-prix"><?php echo $options; ?></select></div>
					</div>
					<hr style="margin: 10px 0;">
				</div>


				<div class="col-6">
					<div class="row">
						<div class="col-6">Multiplicateur</div>
						<div class="col-6"><input type="number" id="multiplicateur"></div>
					</div>
					<hr style="margin: 10px 0;">
					<div class="row">
						<div class="col-6">Arrondi</div>
						<div class="col-6"><input type="number" id="arrondi"></div>
					</div>
				</div>
				
			</div>

			<?php if(isset($donnees)): ?> 
				<?php echo isset($success) ? "<p>Article(s) importé(s) avec succès !</ p>" : "" ?>
				<table class="table table-head-fixed table-bordered table-hover" id="tableData" style="display: block;overflow-x: auto;white-space: nowrap;"> 
					<?php 
					$premiereLigne = true;
					$nbarticle = 0;
					$total = 0;
					foreach ($donnees as $ligne) {
						if ($premiereLigne) {
                // Ignorez la première ligne
							

							echo "<thead>";
							for ($i=0; $i < count($ligne); $i++) { 
								?>
								<th><?php echo $ligne[$i] ?></th>
								<?php
							}
							echo "</thead>";
							$premiereLigne = false;
							continue;
						}
						if ($total < 10) {
							echo '<tbody>';
							echo '<tr>';
							for ($i=0; $i < count($ligne); $i++) { 

								?>
								<td><?php echo $ligne[$i] ?></td>
								<?php

							}
							echo '</tr>';
							echo '</tbody>';
							$total++;
						}
						

					}

							// $prix = $ligne[3];
							// $prix = $prix * 3;
							// $partieEntiere = floor($prix);
							// $prix = $partieEntiere . ".90";
							// $prix = (float) $prix;
							// $designation = $conn->real_escape_string($ligne[1]);
							// $gencode = $ligne[4]; 
							// $package = $ligne[0];
							// $designation = $conn->real_escape_string($ligne[1]);
							// $gencode = $ligne[4]; 
							// $package = $ligne[0];
							// $stock_actuel = $ligne[2];
							// $stock_alerte = 99;
							// $codetva = 8;
							// $quantite = 0.0000;
							// $unite = 0;
							// $prix_variable = 0;
							// $marge = 0;
							// $mode_prix_3 = $prix;
							// $mode_prix_2 = 0.00;
							// $mode_prix_1_achat = 0.00;
							// $mode = 3;
							// $promottc = 0.00;
							// $promo_debut = "0000-00-00";
							// $promo_fin = "0000-00-00";
							// $dateajout =  date('Y-m-d H:i:s');
							// $check = $conn->query("SELECT * FROM table_client_catalogue WHERE ref = '$gencode' ");
							// if ($check->num_rows>0) {
							// 	while($row = $check->fetch_assoc()){
							// 		$dateajout = $row['dateajout'];
							// 		$cath = $row['cath'];
							// 		$codetva = $row['code_tva'];
							// 		$stock_actuel = $row['stock'] + $stock_actuel;
							// 		$famille = $row['cath'];
							// 	}

							// 	$colisage = "" ;
							// 	$datemodif =  date('Y-m-d H:i:s');
							// 	$raccourci = 0;
							// 	$sql = "UPDATE table_client_catalogue SET 
							// 	`cath`= $famille, 
							// 	`ref` = '$gencode',
							// 	`titre` = '$designation',
							// 	`prixttc_euro` = $prix,
							// 	`prixttc_promo_euro` = $promottc,
							// 	`code_tva` = $codetva,
							// 	`promo_debut` = '$promo_debut',
							// 	`promo_fin` = '$promo_fin',
							// 	`choix_mode_prix` = $mode,
							// 	`mode_prix_1_achat_ht` = $mode_prix_1_achat,
							// 	`mode_prix_1_marge` = $marge,
							// 	`mode_prix_2_fixe_ht`=$mode_prix_2,
							// 	`mode_prix_3_fixe_ttc`=$mode_prix_3,
							// 	`dateajout`='$dateajout',
							// 	`datemodif`='$datemodif',
							// 	`accueil` = $raccourci,
							// 	`stock`=$stock_actuel,
							// 	`stock_alerte`=$stock_alerte,
							// 	`unite`=$unite,
							// 	`qte_unite`=$quantite,
							// 	`package`='$colisage',
							// 	`prix_variable`=$prix_variable,
							// 	`img` = NULL,
							// 	`send_web` = 1 WHERE ref = '$gencode' " ;
							// 	if ($conn->query($sql) === TRUE) {
							// 		$addStockMouvement = $conn->query("INSERT INTO `stock_mouvement`(`ref`, `mouvement_ajout`, `mouvement_qte`, `stock_initial`, `created_at`) VALUES ('$gencode','$stock_actuel','0','0','$dateajout')");
							// 		$success = true;
							// 		$nbarticle++;
							// 	} else {
							// 		echo $sql;die();
							// 	}
							// }else{
							// 	$id_produit = random_strings(12);
							// 	$sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`accueil`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`) 
							// 	VALUES(3158,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',0,$stock_actuel,$stock_alerte,$unite,$quantite,'$package',$prix_variable,'',1)";


							// 	if ($conn->query($sql) === TRUE) {
							// 		$addStockMouvement = $conn->query("INSERT INTO `stock_mouvement`(`ref`, `mouvement_ajout`, `mouvement_qte`, `stock_initial`, `created_at`) VALUES ('$gencode','$stock_actuel','0','0','$dateajout')");
							// 		$success = true;
							// 		$nbarticle++;
							// 	} else {
							// 		echo $sql;die();
							// 	}
							// }

					?>

					<?php
							// }
					?>
				</table>
				<div id="loader" class="text-center" style="display:none;">
					<img src='loading.gif' />
				</div>
			<?php endif; ?>
			<hr>
			<h5 style="margin-bottom: 10px;"> Choisir la catégorie</h5 > 
			<select class="form-control" id="famille" size="0" name="famille" style="width: 300px;
			margin-bottom: 30px;">
			<option value="0">-- Déplacer vers --</option>
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
									foreach($child as $subcat){
										if($subcat['id_parent'] == $cat["id_categorie"]){
											?>
											<option value="<?php echo $subcat["id"] ?>" 
												>
												&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
												<?php echo $subcat['nomcategorie'] ?>
											</option>
											<?php
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
									}
								}
							}
						} ?>
					</option>
					<?php
				}
				?> 
			</select> 
			<button type="button" id="saveImport" class="btn btn-block btn-dark">Enregistrer</button>
		</div>
	</div>
</div>

<?php include('../template/footer.php') ?>
<?php include('../template/script.php') ?>
<script type="text/javascript">
	$('#saveImport').click(function(){
		// Categorie
		var categorieChoix = $('#famille').val()
		// CHAMPS
		var gencode = $('#select-gencode').val()
		var titre = $('#select-titre').val()
		var ref = $('#select-ref').val()
		var prix = $('#select-prix').val()
		var quantite = $('#select-qte').val()
		var multiplicateur = $('#multiplicateur').val()
		var arrondi = $('#arrondi').val()

		var data = '<?php echo json_encode($donnees,JSON_HEX_APOS); ?>'
		$.ajax({
				url: "request.php",
				type:"post",
				contentType: "application/json",
				data: JSON.stringify({
					changeFamilleImport: categorieChoix,
					gencode:gencode,
					titre:titre,
					ref:ref,
					prix:prix,
					quantite:quantite,
					multiplicateur:multiplicateur,
					arrondi:arrondi,
					produits:data
				}),
				beforeSend: function() {
					$('#loader').css("display","block");
					$('#tableData').css("display","none");
				},
				success: function (data) {
					console.log(data)
					var result = JSON.parse(data)
					if (result.response !== 1) {
						Toast.fire({
							icon: 'error',
							title: result.message
						})
					} else {
						Toast.fire({
							icon: 'success',
							title: result.message
						})
						$('#loader').css("display","none");
						$('#tableData').css("display","block");
					}
				}
			});
	})
	
	// $('#famille').change(function(){
	// 	var categorieChoix = $('#famille').val()
	// 	$('input[name="produitCheckbox[]"]:checked').each(function () {
	// 		ids.push($(this).attr('id'))
	// 	});
	// 	if(ids.length > 0){
	// 		$.ajax({
	// 			url: "request.php",
	// 			type:"post",
	// 			contentType: "application/json",
	// 			data: JSON.stringify({
	// 				changeFamilleImport: categorieChoix,
	// 				produits:ids
	// 			}),
	// 			beforeSend: function() {
	// 				$('#loader').css("display","block");
	// 				$('#tableData').css("display","none");
	// 			},
	// 			success: function (data) {
	// 				console.log(data)
	// 				var result = JSON.parse(data)
	// 				if (result.response !== 1) {
	// 					Toast.fire({
	// 						icon: 'error',
	// 						title: result.message
	// 					})
	// 				} else {
	// 					Toast.fire({
	// 						icon: 'success',
	// 						title: result.message
	// 					})
	// 					$('#loader').css("display","none");
	// 					$('#tableData').css("display","block");
	// 				}
	// 			}
	// 		});
	// 	}
	// 	else{
	// 		Toast.fire({
	// 			icon: 'error',
	// 			title: 'Aucun produit n\'a été sélectionné'
	// 		})
	// 	}
	// });
</script>