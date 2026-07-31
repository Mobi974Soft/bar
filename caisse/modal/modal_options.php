
<!-- MODAL CONFIRMATION IMPRESSION TICKET -->

<div class="modal fade" id="modal-options" style="display: none;" aria-hidden="true" >
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>					
			<div class="modal-body text-center">
				<h4 class="text-left">Option(s) disponible pour <span id="titreProduitOption"></span></h4>
				<div id="list-option">
				<div class="box-options" ><span>Sans option</span></div>
				</div>
				<input type="hidden" id="identifiantProduit" />
				<input type="hidden" id="idcaisseProduit" />
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-primary" 
				id="confirmationOption"
				>Confirmer</button>
				<button type="button" class="btn" style="background-color:#027491;color:#ffffff" data-dismiss="modal">Annuler</button>
			</div>
		</div>


	</div>
</div>