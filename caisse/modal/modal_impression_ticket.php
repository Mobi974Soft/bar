<!-- MODAL CONFIRMATION IMPRESSION TICKET -->
					<div class="modal fade" id="modal-confirmation" style="display: none;" aria-hidden="true" >
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<!-- <h4 class="modal-title text-center"></h4> -->
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">×</span>
									</button>
								</div>
								<div class="modal-body">
									<h3 class="text-center">IMPRIMER TICKET DE CAISSE ?</h3>
								</div>
								<div class="modal-footer justify-content-between">
									<button type="button" class="btn" style="background-color:#027491;color:#ffffff" data-dismiss="modal" onClick="pay(<?php echo $id_caisse ?>)">Imprimer <i class="fa fa-print" ></i> </button>
									<button type="button" class="btn btn-default" onclick="pay(<?php echo $id_caisse ?>)" >
										Non
									</button>
								</div>
							</div>


						</div>
					</div>