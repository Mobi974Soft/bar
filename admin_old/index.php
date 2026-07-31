<?php
$title = 'Administration';
$page = '';
$accueil='/admin/';
include('../template/header.php');  ?>

<div class="content-wrapper" style="min-height: 823px;">

	
	<?php include('../template/info-page.php') ?>

	<div class="content">
		<div class="container-fluid">
					<!-- <div class="row">
						<div class="col-lg-3 col-6">

							<div class="small-box bg-info">
								<div class="inner">
									<h3>150</h3>
									<p>Articles</p>
								</div>
								<div class="icon">
									<i class="ion ion-bag"></i>
								</div>
								<a href="#" class="small-box-footer">En savoir plus<i class="fas fa-arrow-circle-right"></i></a>
							</div>
						</div>

						<div class="col-lg-3 col-6">

							<div class="small-box bg-success">
								<div class="inner">
									<h3>53<sup style="font-size: 20px">%</sup></h3>
									<p>Tickets</p>
								</div>
								<div class="icon">
									<i class="ion ion-stats-bars"></i>
								</div>
								<a href="#" class="small-box-footer">En savoir plus<i class="fas fa-arrow-circle-right"></i></a>
							</div>
						</div>

						<div class="col-lg-3 col-6">

							<div class="small-box bg-warning">
								<div class="inner">
									<h3>44</h3>
									<p>Ventes</p>
								</div>
								<div class="icon">
									<i class="ion ion-person-add"></i>
								</div>
								<a href="#" class="small-box-footer">En savoir plus<i class="fas fa-arrow-circle-right"></i></a>
							</div>
						</div>

						<div class="col-lg-3 col-6">

							<div class="small-box bg-danger">
								<div class="inner">
									<h3>65</h3>
									<p>Unique Visitors</p>
								</div>
								<div class="icon">
									<i class="ion ion-pie-graph"></i>
								</div>
								<a href="#" class="small-box-footer">En savoir plus<i class="fas fa-arrow-circle-right"></i></a>
							</div>
						</div>

					</div> -->

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
