
<!-- MODAL CONFIRMATION IMPRESSION TICKET -->
<?php

?>
<div class="modal fade" id="modal-commande" style="display: none;" aria-hidden="true" >
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-center">ENVOI CUISINE</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div> 					
			<div class="modal-body">
				<textarea class="form-control"  rows="3" name="order-info" id="order-info" placeholder="Note pour commande"  ></textarea>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn" style="background-color:#027491;color:#ffffff"  onClick="passerCommande(<?php echo $id_caisse ?>,'<?php echo isset($table_active) && $table_active != "" ? $table_active : 0 ?>','<?php echo $clientActive ?>')">Envoyer <i class="fa fa-plus" ></i> </button>
				<button type="button" class="btn btn-default"  onclick="$('#modal-commande').modal('hide');">
					Annuler
				</button>
			</div>
		</div>


	</div>
</div>