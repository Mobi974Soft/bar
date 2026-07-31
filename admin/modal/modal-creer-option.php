
<?php

?>
<div class="modal fade" id="modal-creer-option" style="display: none;" aria-hidden="true" >
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-center">Ajouter une option</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div> 					
			<div class="modal-body text-center">
				<div class="product-form mb-3" style="display:flex;">
					<input type="text"  class="form-control mb-2 designation " id="nomoption" placeholder="Désignation">
					<input type="number"  step="0.1" class="form-control mb-2 prix prixoption" id="prixoption" style="width: 100px;margin-left: 15px;" placeholder="Prix">
					</div>
                
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn" style="background-color:#027491;color:#ffffff"  onClick="ajoutOption('<?php echo $_GET['gencode'] ?>')">Valider <i class="fa fa-plus" ></i> </button>
				<button type="button" class="btn btn-default"  data-dismiss="modal">
					Annuler
				</button>
			</div>
		</div>


	</div>
</div>