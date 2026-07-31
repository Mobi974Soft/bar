 <!-- MODAL REIMPRESSION -->
 <div class="modal fade" id="modal-reimpression" style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">REIMPRIMER UN TICKET</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Entrez n° du ticket</label>
                        <input type="text" class="form-control" id="inputNumeroReimpression" style="font-size:24px;" />
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal"
                        onclick="window.location.reload()">Annuler</button>
                    <!-- <button class="btn btn-dark" onclick="printLastTicket('<?php echo $_SESSION['id_caisse'] ?>')">Dernier ticket</button> -->
                    <button type="button" class="btn btn-primary" onClick="reimpressionTicket()">
                        Confirmer
                    </button>
                </div>
            </div>


        </div>
    </div>