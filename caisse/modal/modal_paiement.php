
<!-- MODAL ESPECE -->
<div class="modal fade" id="modal-espece" style="display: none;" aria-hidden="true" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Paiement Espèce</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="modal-body-espece">
                <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                    style="font-weight: 600;" id="montantEspece"></span> € en espèce</p>
                    <div class="col-md-12 text-center inputPaiement">
                        <input type="text"   id="inputMontantEspece"
                        style="width:100px;font-size:24px;"/> <span> €</span>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                    <button type="button" class="btn btn-primary" id="btnEspece"  onclick="paiementEspece()">
                        Confirmer
                    </button>
                </div>
            </div>


        </div>
    </div>

    <!-- MODAL CB  -->

    <div class="modal fade" id="modal-cb" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Paiement Carte Bancaire</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location.reload()">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-body-cb">
                    <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                        style="font-weight: 600;" id="montantCB"></span> € en cb</p>
                        <div class="col-md-12 text-center inputPaiement">
                            <input type="text"  id="inputMontantCB"
                            style="width:100px;font-size:24px;"/> <span> €</span>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                        <button type="button" class="btn btn-primary" id="btnPaiementCB" onclick="paiementCB()">Confirmer
                        </button>
                    </div>
                </div>

            </div>
        </div>

    <!-- Modal ticket resto -->
        <div class="modal fade" id="modal-resto" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Paiement Ticket Resto</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location.reload()">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-body-resto">
                    <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                        style="font-weight: 600;" id="montantResto"></span> € </p>
                        <div class="col-md-12 text-center inputPaiement">
                            <input type="text"  id="inputMontantResto"
                            style="width:100px;font-size:24px;"/> <span> €</span>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                        <button type="button" class="btn btn-primary" id="btnPaiementResto" onclick="paiementTicketResto()">Confirmer
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL CHEQUES  -->

        <div class="modal fade" id="modal-cheque" style="display: none;" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Paiement Cheque</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location.reload()">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="modal-body-cheque">
                        <p class="text-center text-paiement" id= style="font-size: 18px"> Choisir le montant ou payer <span
                            style="font-weight: 600;" id="montantCheque"></span> € en cheque</p>
                            <div class="col-md-12 text-center inputPaiement">
                                <input type="text" id="inputMontantCheque" onClick="this.select();"
                                style="width:100px;font-size:24px;"/> <span> €</span>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                            <button type="button" class="btn btn-primary" id="btnCheque" onclick="paiementCheque()">Confirmer</button>
                        </div>
                    </div>

                </div>
            </div>

           

       

      