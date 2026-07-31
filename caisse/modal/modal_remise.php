<!-- MODAL REMISE  -->
<?php $table_active = $conn->query('SELECT numero FROM restaurant_tables WHERE status = 1')->fetch_assoc()['numero']; ?>
<div class="modal fade" id="modal-remise" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Remise sur le panier</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="padding:50px">
                <div id="remise-globale" style="display:none;text-align: center;">
                    <p class="text-center text-paiement text-danger" style="font-size: 20px;font-weight: 800;letter-spacing: 3px;"> REMISE EN %</p>
                    <div class="col-md-12 text-center">
                        <input type="text"  id="inputMontantRemisePanier" autofocus style="width:100px;font-size:24px;letter-spacing: 1px;" />
                    </div>
                    <button class="btn btn-dark" style="margin-top: 25px;width: 100px;" onclick="backToRemiseChoix()">Annuler</button>
                    <button type="button" class="btn btn-success" id="btnRemisePanier" style="margin-top: 25px;width: 100px;" onclick="remisePanier('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse']; ?>','<?php echo $table_active; ?>')">Confirmer
                    </button>
                </div>
                <div id="remise-globale-euro" style="display:none;text-align: center;">
                    <p class="text-center text-paiement text-danger" style="font-size: 20px; font-weight: 800;letter-spacing: 3px;"> REMISE EN €</p>
                    <div class="col-md-12 text-center">
                        <input type="text"  id="inputMontantRemisePanierEuro" autofocus style="width:100px;font-size:24px;" />
                    </div>
                    <button class="btn btn-dark" style="margin-top: 25px;width: 100px;" onclick="backToRemiseChoix()">Annuler</button>
                    <button type="button" class="btn btn-success" id="btnRemisePanierEuro" style="margin-top: 25px;width: 100px;" onclick="remisePanierEuro('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse']; ?>','<?php echo $table_active; ?>')">Confirmer
                    </button>
                </div>
                <div class="row" id="btnRemiseGlobale">
                   <div class="col-md-6">
                    <button type="button" class="btn btn-block btn-primary" style="height: 70px;font-size: 18px;" id="showRemisePourcent">Remise %
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-block btn-success" style="height: 70px;font-size: 18px;"  id="showRemiseEuro">Remise en €

                    </button>
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
            
        </div>
    </div>

</div>
</div>
