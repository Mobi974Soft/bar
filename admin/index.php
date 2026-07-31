<?php
$title = 'Administration';
$page = '';
$accueil='index.php';
include('../template/header.php');  
?>

<div class="content-wrapper" style="min-height: 823px;">

	
	<?php include('../template/info-page.php') ?>

	<div class="content">
		<div class="container-fluid">
					
					<div class="row">
						<div class="col-sm-3" >
							<a class="btn btn-app bg-secondary" href="articles.php" style="padding: 50px 0;width: 100%;height: 100%;margin-left: 0;">
								<!-- <span class="badge bg-success">300</span> -->
								<i class="fas fa-barcode"></i> <p style="font-size:18px">GESTION DES ARTICLES</p>
							</a>
							
						</div>
						<div class="col-sm-3" >
							<a class="btn btn-app bg-danger" href="statistiques.php" style="padding: 50px 0;width: 100%;height: 100%;margin-left: 0;">
								<!-- <span class="badge bg-success">300</span> -->
								<i class="fas fa-inbox"></i> <p style="font-size:18px">STATISTIQUES</p>
							</a>
							
						</div>
<!--                        <div class="col-sm-3" >-->
<!--                            <a class="btn btn-app bg-success" href="graphiques.php" style="padding: 50px 0;width: 100%;height: 100%;margin-left: 0;">-->
<!--                                 <span class="badge bg-success">300</span> -->
<!--                                <i class="fas fa-inbox"></i> <p style="font-size:18px">GRAPHIQUES</p>-->
<!--                            </a>-->
<!---->
<!--                        </div>-->
<!--                        <div class="col-sm-3" >-->
<!--                            <a class="btn btn-app bg-indigo" href="statistique_annuel.php" style="padding: 50px 0;width: 100%;height: 100%;margin-left: 0;">-->
<!--                                <span class="badge bg-success">300</span> -->
<!--                                <i class="fas fa-inbox"></i> <p style="font-size:18px">STATISTIQUES ANNUELLE</p>-->
<!--                            </a>-->
<!---->
<!--                        </div>-->
					</div>
				</div>
			</div>

		</div>


		<?php include('../template/footer.php') ?>
		<?php include('../template/script.php') ?>
	</body>
	</html>
