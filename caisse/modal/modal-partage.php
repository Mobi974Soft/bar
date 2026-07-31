
<!-- MODAL CONFIRMATION IMPRESSION TICKET -->
<?php

?>
<div class="modal fade" id="modal-partage" style="display: none;" aria-hidden="true" >
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-center">Partager l'addition</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div> 					
			<div class="modal-body text-center">
				<input type="number" id="partagenb" name="" style="height:50px;width:50px;" value="0">
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn" style="background-color:#027491;color:#ffffff"  onClick="splitAddition(<?php echo $id_caisse ?>,'<?php echo isset($table_active) && $table_active != "" ? $table_active : 0 ?>','<?php echo $clientActive ?>')">Valider <i class="fa fa-plus" ></i> </button>
				<button type="button" class="btn btn-default"  data-dismiss="modal">
					Annuler
				</button>
			</div>
		</div>


	</div>
</div>