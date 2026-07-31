<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$today = date('Y-m-d');
$sql = "SELECT * FROM clients ORDER BY created_at DESC";
$clients = $conn->query($sql);
?>
<div class="col-11">
	<div class="row"  style="height: 100%;">
		<div class="col-7">
			<div class="row" style="width: 100%;background: #FFFFFF;padding: 10px;">
				<!-- <button type="button" class="btn btn-block bg-gradient-info btnTableStatus" onclick="changeActiveStatus(this)" style="margin-top: 7px;">Terminées</button>
				<button type="button" class="btn btn-block btn-outline-info btnTableStatus" onclick="changeActiveStatus(this)">En cours</button> -->
			</div>
			<div class="row" style="width: 100%;padding: 20px;">
				<form action="" style="width:60%;">
					<div class="input-group">
						<input type="search" class="form-control form-control-lg" placeholder="Rechercher un client">
						<div class="input-group-append">
							<button type="submit" class="btn btn-lg btn-default">
								<i class="fa fa-search"></i>
							</button>
						</div>
					</div>
				</form>
			</div>	

			<div class="row" id="encours" >
				<div class="col-12">
					<?php 
					if ($clients->num_rows>0) {
						while($client = $clients->fetch_assoc()){
							$id = $client['id'];
							$statut = $client['statut'];
							?>
							<div id="client-<?php echo $id ?>" class="callout " style="margin-bottom:5px !important" >
								<div class="row">
									<div class="col-8">
										<h5 class="text-warning" style="font-family:'OpenSans', sans-serif;font-weight: bold;font-size:18px"><?php echo ucfirst($client['prenom']) . " " . ucfirst($client['nom']) ?></h5>
										<p style="font-family:'OpenSans', sans-serif;color:#8394a5;font-size:14px">
											<i class="fa-solid fa-envelope"></i> <?php echo $client['email'] ?><br>
											<i class="fa-solid fa-phone"></i> <?php echo $client['telephone'] ?>
										</p>
									</div>
									<div class="col-4 text-center" style="margin:auto">
										<button type="button" class="btn btn-outline-dark">
											<i class="fas fa-edit"></i>
										</button>

										<button type="button" class="btn btn-outline-danger" onclick="deleteClient('<?php echo $id ?>')">
											<i class="fas fa-trash"></i>
										</button>

										<button type="button" class="btn btn-outline-primary" onclick="selectClient('<?php echo $id ?>','<?php echo $statut ?>')">
											<i class="fas fa-check"></i> Choisir
										</button>
										<?php if($statut==1): ?>
										<button type="button" class="btn btn-outline-danger" onclick="unSelectClient('<?php echo $id ?>')">
											<i class="fas fa-close"></i>
										</button>
										<?php endif; ?>
									</div>
								</div>

							</div>

							<?php

						}
					}
					?>
				</div>

			</div>
		</div>
		<div class="col-5 cart" style="padding:0 30px">
			<div class="row panierTitle">
				<div class="col-lg-5"> <span style="font-weight: 600;font-size:24px;margin-left: 5px">Ajouter un client</span></div>
				
			</div>
			<div  >
				<form action="ajax/addClient.php" id="formRegister" method="post">
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="nom" placeholder="Nom" value="Test">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-user"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="prenom" placeholder="Prénom" value="Test">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-user"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="email" class="form-control" name="email" placeholder="Email"value="Test@test.com">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-envelope"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="tel" placeholder="Téléphone" value="0692323232">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-phone"></span>
							</div>
						</div>
					</div>
					<div class="input-group mb-3">
						<input type="text" class="form-control" name="adresse" placeholder="Adresse" value="26 rue test">
						<div class="input-group-append">
							<div class="input-group-text">
								<span class="fas fa-location"></span>
							</div>
						</div>
					</div>
					
					<div class="row">


						<div class="col-4">
							<button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
						</div>

					</div>
				</form>
			</div>
			
			
		</div>
	</div>
</div>


</div>
