<div class="col-lg-7" id="plateau">
				<div class="row" style="margin-top: 50px">
					<div class="col-lg-3 blockPay">
						<div class="callout">
							<h5>Total dû</h5>
							<p style="font-size:26px;font-weight: 600" class="text-info" id="totalDu">0.00 €</p>
						</div>
					</div>

					<div class="col-lg-3 blockPay">
						<div class="callout">
							<h5>Total encaissé</h5>
							<p class="text-success" style="font-size:26px;font-weight: 600" id="paiementTotal">0.00 €</p>
						</div>
					</div>

					<div class="col-lg-3 blockPay">
						<div class="callout">
							<h5>A payer</h5>
							<p class="text-danger" style="font-size:26px;font-weight: 600" id="ResteAPayer">0.00 €</p>
						</div>
					</div>

					<div class="col-lg-3 blockPay">
						<div class="callout">
							<h5>Monnaie a rendre</h5>
							<p class="text-danger" style="font-size:26px;font-weight: 600" id="monnaieArendre">0.00 €</p>
						</div>
					</div>


				</div>
				<div class="row" id="paiementBoard">

					<div class="col-6">
						<div class="card card-danger blocs-paiement"  >
							<div class="card-header">
								
									
							</div>
							<div class="card-body" id="sousPanier">

							</div>
						</div>
					</div>	
					<div class="col-6 " >
						<div class="card card-info  blocs-paiement" >
						<div class="card-header"></div>
							<div class="card-body " id="paiementsBody">
								<div class="row" >
									<div class="col-lg-3">
										<label id="labelMontant">Montant</label>
										<input type="number" step="any" class="form-control inputMontantPaiement" onclick="this.select()" value="0" name="montantPaiement" id="montantPaiement">
									</div>
									<div class="col-lg-8">
										<label id="labelMethod">Méthode de Paiement</label>
										<select class="form-control methodePaiement" id="methodePaiement">
											<option>Espèce</option>
											<option>Carte Bancaire</option>
											<option>Chèque</option>
											<option>Chèque Restaurant</option>
										</select>
									</div>
									<!-- <div class="col-lg-3" style="position:relative">
										<button class="btn btn-default" style="position:absolute;bottom: 0;"  onclick="pay('<?php echo $id_caisse ?>',true,this)"> <i class="fa fa-euro-sign"></i> Payer</button>
									</div> -->
								</div>

							</div>
							<div class="row" style="text-align: center;width: 100%;margin: 0;">
								<button style="width:100%;border-radius: unset;" class="btn btn-info  btn-lg" onclick="addPaiementMethod('<?php echo $id_caisse ?>')" >Ajouter un paiement</button>
							</div>
						</div>

						<div class="col-12" style="display:flex">
							<button class="btn btn-primary btnPartagerStyle btn-lg btnPaiementPage" data-toggle="modal" data-target="#modal-partage"  >Partager</button>
							<button class="btn btn-danger btnPayerStyle btn-lg btnPaiementPage" data-toggle="modal" data-target="#modal-confirmation"  style="margin-left:20px;">Payer</button>
							
						</div>
						
					</div>

					

					

				</div>
			</div>
