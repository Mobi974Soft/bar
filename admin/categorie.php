<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
if(isset($_GET['page']) && !empty($_GET['page'])){
	$currentPage = (int) strip_tags($_GET['page']);
}else{
	$currentPage = 1;
}

if (isset($_GET['id'],$_GET['nomCat'])) {
	include('../DBConfig.php');
	include('../functions.php');



	$cath = $_GET['id'];
	$nomcat = $_GET['nomCat'];

	$sql = "SELECT * FROM table_client_catalogue WHERE cath = $cath";
	$produit = $conn->query($sql);
	$nbarticle = $produit->num_rows;


	$parPage = $nbarticle>1000 ? 1000 : 50;
	$pages = ceil($nbarticle / $parPage);

	// Calcul du 1er article de la page
	$premier = ($currentPage * $parPage) - $parPage;
	$sql = "SELECT * FROM `table_client_catalogue` WHERE cath = $cath ORDER BY `titre` ASC LIMIT  $premier, $parPage";

	$query = $conn->query($sql);


	$articles = $query->fetch_all(MYSQLI_ASSOC);

	$title = 'Gestions des articles';
	$page = 'Gestion des articles';
	$accueil = 'index.php';
	include('../template/header.php');
	include('../codebarre/barcode.php');
	include('../infos.php');



	?>
	<style>
		.active{
			margin:0 !important;
			padding:0 !important;
			background-color: #fff;
		}
	</style>
	<div class="content-wrapper" style="min-height: 823px;">
		<?php include('../template/info-page.php') ?>
		<div class="content">
			<div class="col-sm-4 col-md-4 col-lg-6 col-xl-6">
				<form action="searchArticle.php" method="GET">
					<div class="input-group">
						<input type="search" class="form-control form-control-lg" id="searchArticle"
						name="searchArticle" placeholder="Rechercher un article">
						<div class="input-group-append">
							<button type="submit" class="btn btn-lg btn-default">
								<i class="fa fa-search"></i>
							</button>
						</div>
					</div>
				</form>
			</div>
			<div class="row">
				<div class="col-md-2 ml-auto">
					<button type="button" class="btn btn-block btn-dark" onclick="window.location.href='articles.php' ">Retour</button>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">

					<div class="card">
						<div class="card-header">
							<?php if (isset($nbarticle) && $nbarticle > 0) { ?>
								<h3 class="card-title" style="font-weight: 600;"><span style="font-weight: 600;"><?php echo $nbarticle ?> résultats listés dans la famille  <?php echo $nomcat ?>.</span></h3>
							<?php    } else { 
								$url = "https://" . $_SERVER['SERVER_NAME'] ;
								$gencode = htmlspecialchars($_GET['searchArticle']);
								$re = '/^(\d{13})?$/m';
								if (preg_match($re, $gencode) == 1 && isset($_GET['searchArticle'])) {
									$url = $url."/restaurant/admin/articleAjout.php?action=creercreer&gencode=".$gencode;
									echo "<script>window.location.href='".$url."';</script>";
									exit;
									?>
								}else{
								?>
								<h3 class="card-title" style="font-weight: 600;">Aucun articles trouvé.</h3>
							<?php } }?>
						</div>
						<div class="card-body">
							<div id="example1_wrapper" class="dataTables_wrapper dt-bootstrap4">
								<div class="row">
									<div class="col-sm-12 col-md-6">

									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<table id="example1" class="table table-bordered table-striped dataTable dtr-inline " aria-describedby="example1_info">
											<thead>
												<tr>
													<a>
														<th class="sorting sorting_asc" tabindex="0" aria-controls="example1"
														rowspan="1" colspan="1" aria-sort="ascending"
														aria-label="Rendering engine: activate to sort column descending">
														<input type="checkbox" name="select-all-search" id="select-all-search" />
													</th>
												</a>
												<a>
													<th class="sorting sorting_asc" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Rendering engine: activate to sort column descending">Gencode</th>
												</a><a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending">Désignation</th>
													</a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Platform(s): activate to sort column ascending">Stock</th>
													</a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending">Achat € HT</th>
													</a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending">Vente € TTC</th>
													</a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending">Promo € TTC</th>
													</a>
													<a>
														<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1"
														colspan="1" aria-label="CSS grade: activate to sort column ascending">
														Étiquettes
													</th>
												</a>
												<a>
													<th class="sorting" tabindex="0" aria-controls="example1" rowspan="1"
													colspan="1" aria-label="CSS grade: activate to sort column ascending">
													Quantité
												</th> 
											</a>
											<a>
												<th class="sorting sorting_asc" tabindex="0"
												aria-controls="example1" rowspan="1" colspan="1"
												aria-sort="ascending"
												aria-label="Rendering engine: activate to sort column descending">
												Action
											</th>
										</a>
									</tr>
								</thead>
								<?php
								if (isset($nbarticle) && $nbarticle > 0) {
									foreach ($articles as $row) {
										$tva = ($row['code_tva'] == 8 ? 8.5 : ($row['code_tva'] == 2  ? 2.1 : ($row['code_tva'] == 1 ? 1.05 : 0)));
										$numCatalogue = $row['num'];
										?>
										<tr class="odd">
											<td class="center"><input type="checkbox" name="produitCheckbox[]"
												class="form-check-input" id="<?php echo $row['num'] ?>">
											</td>
											<td class="dtr-control sorting_1" tabindex="0"><?php echo $row['ref'] ?></td>
											<td><a href="article.php?gencode=<?php echo $row['ref'] ?>"><?php echo $row['titre'] ?></a></td>
											<td><?php echo $row['stock'] ?></td>
											<td><?php echo $row['mode_prix_1_achat_ht'] == 0 ? "" : $row['mode_prix_1_achat_ht'] ?></td>
											<td><?php echo $row['prixttc_euro'] ?></td>
											<td><?php echo $row['prixttc_promo_euro'] ?></td>
											<td style="text-align: center;">  
												<?php 


												$zpl = formatLabel($row['titre'],$row['prixttc_euro'],$row['ref'],$arrayPos,$row['package'],$row['prixttc_promo_euro']);

												?>
												<a href="#"  onclick="writeToSelectedPrinter('<?php echo $zpl ?>', $('#qteLabel-<?php echo $numCatalogue ?>').val()  )" >
													<i class="fa fa-print"></i>
												</a>
											</td>
											<td>
												<input type="number" name="qteLabel" style="width: 50px;" value="1" id="qteLabel-<?php echo $numCatalogue ?>" />
												<input type="hidden" name="produitInfo" id="produit-<?php echo $numCatalogue ?>" value="<?php echo $row['ref'].",".$row['titre'].",".$row['prixttc_euro'].",".$row['package'].",".$row['prixttc_promo_euro'] ?>" />
											</td>
											<td>
												<i class="fa fa-trash" style="color:red;cursor:pointer;"
												onclick="deleteArticleAdmin('<?php echo $row['ref'] ?>')"
												></i></td>
											</tr>
											<?php
										}
									}
									?>
								</tbody>
								<tfoot>
									<tr>
										<th rowspan="1" colspan="1">#</th>
										<th rowspan="1" colspan="1">Gencode</th>
										<th rowspan="1" colspan="1">Désignation</th>
										<th rowspan="1" colspan="1">Stock(s)</th>
										<th rowspan="1" colspan="1">Achat € HT</th>
										<th rowspan="1" colspan="1">Vente € TTC</th>
										<th rowspan="1" colspan="1">Promo € TTC</th>
										<th rowspan="1" colspan="1">Étiquettes</th>
										<th rowspan="1" colspan="1">Quantité</th>
										<th rowspan="1" colspan="1">Action</th>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 col-md-5">
							<div class="dataTables_info" id="example1_info" role="status" aria-live="polite"></div>
						</div>
						<div class="col-sm-12 col-md-7">
							<div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
								<ul class="pagination">
									

									<li class="paginate_button page-item previous <?= ($currentPage == 1) ? "disabled" : "" ?>">
										<a href="?page=<?= $currentPage - 1 ?>&id=<?= $cath ?>&nomCat=<?= $nomcat ?>" class="page-link">Précédente</a>
									</li>
									<?php for($page = 1; $page <= $pages; $page++): ?>
										<!-- Lien vers chacune des pages (activé si on se trouve sur la page correspondante) -->
										<li class="paginate_button page-item <?= ($currentPage == $page) ? "active" : "" ?>">
											<a href="?page=<?= $page ?>&id=<?= $cath ?>&nomCat=<?= $nomcat ?>" class="page-link"><?= $page ?></a>
										</li>
									<?php endfor ?>
									<!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->
									<li class="paginate_button page-item next <?= ($currentPage == $pages) ? "disabled" : "" ?>">
										<a href="?page=<?= $currentPage + 1 ?>&id=<?= $cath ?>&nomCat=<?= $nomcat ?>" class="page-link">Suivante</a>
									</li>
									
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>
<hr>
<div class="row" style="padding: 30px 0">
	<div class="col-md-8">
		<div class="form-group row">
			<!-- <label class="col-lg-3 col-form-label form-control-label">Famille</label> -->
			<div class="col-lg-6" id="famille-select">
				<label class="text-danger">Action sur les articles sélectionnés</label>
				<select class="form-control" id="famille" size="0" name="famille">
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
                                        <!-- <span class="text-muted mt-3" style="cursor:pointer;" onclick="toggleFamille()">Creer une famille</span>
                                        <div class="row famille" id="familleBlock" style="margin: 10px 0 0 2px;display: none;">
                                            <input type="text" name="creerFamille" id="inputFamille" style="margin-right:10px" />
                                            <button type="button" class="btn btn-default btn-sm" id="creerFamille" disabled="disabled">Créer une famille</button>
                                        </div> -->
                                        <hr>
                                        <div class="row">
                                        	<div class="col-md-10">

                                        		<div class="form-group">
                                        			<label></label>
                                        			<select id="choixAction" class="form-control">
                                        				<option value="print">-- Imprimer Etiquette --</option>
                                        				<option value="supp">-- Supprimer article(s) -- </option>
                                        				<!--                            <option>option 3</option>-->
                                        				<!--                            <option>option 4</option>-->
                                        				<!--                            <option>option 5</option>-->
                                        			</select>
                                        		</div>


                                        	</div>
                                        	<div class="col-md-2">
                                        		<input type="submit" style="margin-top: 27px;" name="btnAction" id="btnAction" class="btn btn-md btn-dark" value="Valider" />
                                        	</div>
                                        </div>

                                    </div>
                                    <!-- <div class="col-md-6">
                                    	<span class="text-muted mt-3">Creer une sous-famille</span>
                                    	<div class="row famille" id="familleBlock" style="margin: 10px 0 0 2px;">
                                    		<input type="text" name="creerSousFamille" id="inputCreerSousFamille"  style="margin-right:10px;width: 300px" />
                                    		<button type="button" class="btn btn-dark btn-md" id="creerSousFamille" >Créer</button>
                                    	</div>
                                    </div> -->
                                </div>



                            </div>
                            <div class="col-md-2">
                            	<input type="submit" style="margin-top: 27px;" name="btnAction" id="btnAction" class="btn btn-lg btn-danger" value="Supprimer catégorie <?php echo $_GET['nomCat'] ?>" onClick="deleteCat(<?php echo $_GET['id'] ?>,<?php echo $nbarticle ?>)" />
                            </div>
                        </div>


                        <div class="row" style="padding: 20px 0;">

                        </div>

                    </div>
                </div>



                <?php include('../template/footer.php') ?>



                <?php include('../template/script.php') ?>
                <script type="text/javascript">
                	var ids = []
                	$('#famille').change(function(){
                		var categorieChoix = $('#famille').val()
                		$('input[name="produitCheckbox[]"]:checked').each(function () {
                			ids.push($(this).attr('id'))
                		});
                		if(ids.length > 0){
                			$.ajax({
                				url: "request.php",
                				type:"post",
                				contentType: "application/json",
                				data: JSON.stringify({
                					changeFamille: categorieChoix,
                					produits:ids
                				}), 
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
                					}
                				}
                			});
                		}
                		else{
                			Toast.fire({
                				icon: 'error',
                				title: 'Aucun produit n\'a été sélectionné'
                			})
                		}
                	});


                	function deleteCat(id,nbarticle){

                		if(nbarticle==0){
                			$.ajax({
                				url:"request.php",
                				type:"post",
                				contentType:"application/json",
                				data:JSON.stringify({
                					idcat:id,
                					deleteCategorie:true,
                				}),
                				success:function(data) {
                					console.log(data)
                					if (data==1) {
                						Toast.fire({
                							icon: 'success',
                							title: 'Catégorie supprimé'
                						})
                						var base_url = window.location.origin
                						window.setTimeout(function () {
                							window.location.href = base_url + "/restaurant/admin/articles.php";
                						}, 1000);
                					}

                				}
                			})
                		}else{
                			Toast.fire({
                				icon: 'error',
                				title: 'Supprimer ou déplacer les articles de cette catégorie avant de supprimer la catégorie'
                			})
                		}
                		
                	}
                </script>

















                <?php
            }

        ?>