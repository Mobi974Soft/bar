<!-- MODAL CALCUL POIDS PRODUIT -->
<div class="modal fade show" id="modal-poids" style="padding-right: 17px; display: none;" aria-modal="true" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Calcul après pesage</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-6" style="border-right: 1px solid lightgray">
						<div class="row">
							<div class="col-lg-6">
								<p>Poids sur balance:</p>
								<input type="number"  step="any" name="poidsProduit" id="poidsProduit" class="form-control inputPoids">
							</div>
							<div class="col-lg-6">
								<p>Unite:</p>
								<select class="form-control" id="uniteTotal" name="unite">
									<option value="0" >Désactivé</option>
									<option value="1" >Kg</option>
									<option value="2" selected>Grammes</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="row">
							<div class="col-lg-6">
								<p>Poids du contenant:</p>
								<input type="number" step="any" name="poidsContenant" id="poidsContenant" class="inputPoids form-control">
							</div>
							<div class="col-lg-6">
								<p>Unite:</p>
								<select class="form-control" id="uniteContenant" name="unite">
									<option value="0" >Désactivé</option>
									<option value="1" >Kg</option>
									<option value="2" selected>Grammes</option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-lg-6" style="margin: auto">
						<p>Poids trouvé :</p>
						<p id="poidsTotal">0.00 g</p>
					</div>
					<div class="col-lg-6" style="margin: auto">
						<p>Prix total :</p>
						<p id="prixTotal" class="text-danger">0.00 €</p>
					</div>
				</div>
			</div>
			<div class="modal-footer justify-content-between">
				<input type="hidden" name="prixProduit" id="prixProduit">
				<input type="hidden"  id="modal_unite">
				<input type="hidden" id="modal_qte_unite">
				<input type="hidden" id="titrePesage">
				<input type="hidden" id="refPesage">
				<input type="hidden" id="idPesage">
				<input type="hidden" id="imgPesage">
				<input type="hidden" id="codetva_Pesage">
				<input type="hidden" id="famille_Pesage">
				<input type="hidden" id="promoPesage">
				<input type="hidden" id="idcaissePesage" value="<?php echo isset($id_caisse) ? $id_caisse : 0 ?>">
				<button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetModal()">Fermer</button>
				<button type="button" class="btn btn-primary" onclick="addProduitPesageToCart()">Confirmer</button>
			</div>
		</div>

	</div>

</div>