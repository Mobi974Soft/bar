const modal_body_cheque = $('#modal-body-cheque').html();
const modal_body_cb = $('#modal-body-cb').html();
const modal_body_espece = $('#modal-body-espece').html();

function reloadModalContent(){
    $('#modal-espece > .modal-dialog > .modal-content > .modal-body').html(modal_body_espece);
    $('#modal-cheque > .modal-dialog > .modal-content > .modal-body').html(modal_body_cheque);
    $('#modal-cb > .modal-dialog > .modal-content > .modal-body').html(modal_body_cb);
}

$('#modal-espece').on('shown.bs.modal', function() {
    $('#inputMontantEspece').select();
})

$('#modal-cb').on('shown.bs.modal', function() {
    $('#inputMontantCB').select();
})

$('#modal-cheque').on('shown.bs.modal', function() {
    $('#inputMontantCheque').select();
})

$('#modal-divers').on('shown.bs.modal', function() {
    $('#inputPrixDivers').focus();
})

$('#modal-remise').on('shown.bs.modal', function() {
    $('#inputMontantRemisePanier').focus();
})

$('#modal-facture').on('shown.bs.modal', function() {
    $('#inputNumeroTicket').focus();
})

$('#modal-reimpression').on('shown.bs.modal', function() {
    $('#inputNumeroReimpression').focus();
})

$('#modal-retour').on('shown.bs.modal', function() {
    $('#inputRetourPrixDivers').focus();
    $('#inputRetourArticleCatalogue').focus();

})


function getTotal() {
    var total = $('#total').text()
    total = total.replace(/\s/g, '');
    total = total.replace('€', '');
    return total;
}

function printLastTicket(id_caisse){
    $.ajax({
        url: "ticket_reimpression.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({dernier_ticket: "true",  id_caisse: id_caisse}),
        success: function (data) {
            console.log(data)
			var result = typeof data === 'string' ? JSON.parse(data) : data
            if (result.response === 1) {
                var cash = false
                console.log(result.espece,result.espece>0)
                if (result.espece>0) {
                    var cash = true
                }
                imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(result.espece),parseFloat(result.cb),parseFloat(result.cheque),result.ticket_pied,cash,false,"","",result.multiple,result.detailspayment)

                $('#modal-reimpression').modal('hide')
                $('#inputNumeroReimpression').val('')
            }
        }
    })
}


function reimpressionTicket(){
    var numero_ticket = $('#inputNumeroReimpression').val()
    if(numero_ticket == ""){
        Toast.fire({
            icon: 'error',
            title: "Veuillez remplir les champs avant de lancer la réimpression!"
        })

    }else{
        $.ajax({
            url: 'ticket_reimpression.php',
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                numero_ticket: numero_ticket,
                dernier_ticket:"false",
            }),
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    console.log(result.espece)
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(result.espece),parseFloat(result.cb),parseFloat(result.cheque),result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)

                    $('#modal-reimpression').modal('hide')
                    $('#inputNumeroReimpression').val('')
                }
            }

        })
    }
}

$("#inputNumeroReimpression").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        reimpressionTicket()
    }
});


function imprimeFacture(){
    var numero_ticket = $('#inputNumeroTicket').val()
    var client = $('#inputNomClient').val()
    if(numero_ticket == "" && client == ""){
        Toast.fire({
            icon: 'error',
            title: "Veuillez remplir les champs avant d'imprimer la facture"
        })

    }else{
        $.ajax({
            url: 'facture.php',
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                numero_ticket: numero_ticket,
                client:client,

            }),
            success: function (data) {
                var result = JSON.parse(data)
                console.log(result.header , '\n',  result.ticket , '\n',  result.ticket_part2 , '\n',  result.detailspayment)
                if (result.response === 1) {

                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(result.espece),parseFloat(result.cb),parseFloat(result.cheque),result.ticket_pied,false,true,result.header,client,result.multiple,result.detailspayment)

                    $('#modal-facture').modal('hide')
                    $('#inputNumeroTicket').val('')
                    $('#inputNomClient').val('')
                }
            }

        })
    }

}


function totalCaisse(id_caisse){
    $.ajax({
        url: "../print_total_caisse.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            "id_caisse":id_caisse
        }),
        success: function (data) {
            var result = JSON.parse(data)
            console.log(result.ticket)
            if (result.response === 1) {
                Toast.fire({
                    icon: 'success',
                    title: "Ticket du total caisse imprimé !"
                })
                Impresora.getImpresoras()
                .then(listaDeImpresoras => {

                    var impresora = new Impresora();
                    let imprimante = "";
                    for (let i=0; i<listaDeImpresoras.length; i++) {
                        console.log(listaDeImpresoras[i])
                        if(listaDeImpresoras[i].search('EPSON TM') >= 0 || listaDeImpresoras[i].search('lp') >= 0 || listaDeImpresoras[i].search('POS') >= 0){
                            imprimante = listaDeImpresoras[i]
                        }
                    }

                    impresora.setEmphasize(0)
                    impresora.write(result.ticket)
                    impresora.feed(1)
                    impresora.cut()
                    impresora.cash()
                    impresora.imprimirEnImpresora(imprimante)
                    .then(valor => {
                        console.log("Resultat: " + valor);


                    });
                });


                // window.setTimeout(function () {
                //     window.location.reload();
                // }, 1000);
            }
        }
    })
}


function viderPanier(id_caisse){
    $.ajax({
        url: "../panier/videPanier.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            "videTout":true,
            "id_caisse":id_caisse
        }),
        success: function (data) {
            var result = JSON.parse(data)
            if (result.response === 1) {
                Toast.fire({
                    icon: 'success',
                    title: "Caddie est en train de se vider... !"
                })
                window.setTimeout(function () {
                    window.location.reload();
                }, 1000);
            }
        }
    })
}

function paiementMultiple(typePaiement,newPaiement){
    if(typePaiement === 1){
        localStorage.setItem("espece", newPaiement);
        console.log("Local paiement multiple=>"+localStorage.getItem("espece"))
    }

}
// localStorage.clear()
// PAIEMENT EN PLUSIEURS FOIS 
function paiementSuite(typeSuite, typePaiement, montantPaiement, resteAPayer) {


    var nouveauMontant = $('#nouveauMontant').val()
    var montantAPayer = $('#resteAPayer').text().split(" ")
    resteAPayer = parseFloat(montantAPayer[0])
    // console.log("NOUVEAU MONTANT => " + nouveauMontant + "/ RESTE A PAYER =>" + resteAPayer + " / ESPECE  ")
    var arrayPaiement = []
    if(nouveauMontant < resteAPayer){
        $('#nouveauMontant').focus().select()

        localStorage.removeItem('montantEspece')
        localStorage.setItem('montantPaiement',[typePaiement,montantPaiement])

        if(localStorage.getItem('paiement') != null){
            var existingPaiement = localStorage.getItem('paiement');
            existingPaiement += typeSuite+","+parseFloat(nouveauMontant)+"|";
            localStorage.setItem('paiement',existingPaiement)
        }else{
            localStorage.setItem('paiement',(typeSuite+","+parseFloat(nouveauMontant)+"|"))
        }
        var aPayer = 0
        if(resteAPayer>nouveauMontant){
            aPayer = parseFloat(resteAPayer) - parseFloat(nouveauMontant)
            $('#resteAPayer').text(aPayer.toFixed(2) + " €")
            aPayer = Math.round((aPayer + Number.EPSILON) * 100) / 100
            $('#nouveauMontant').val(aPayer.toFixed(2))

        }else if(resteAPayer<nouveauMontant){
            aPayer = parseFloat(nouveauMontant) - parseFloat(resteAPayer)
            $('#resteAPayer').text( aPayer.toFixed(2) + " €")
        }

    }
    else if(nouveauMontant>resteAPayer){
        var monnaieArendre = nouveauMontant - resteAPayer;
        if (typePaiement === 1) {
            var espece = typeSuite === 1 ? nouveauMontant : montantPaiement;
            var cb = typeSuite === 2 ? resteAPayer : 0;
            var cheque = typeSuite === 3 ? resteAPayer : 0;
        } else if (typePaiement === 2) {
            var espece = typeSuite === 1 ? nouveauMontant : 0;
            var cb = typeSuite === 2 ? montantPaiement + resteAPayer : montantPaiement;
            var cheque = typeSuite === 3 ? resteAPayer : 0;
        } else if (typePaiement === 3) {
            var espece = typeSuite === 1 ? nouveauMontant : 0;
            var cb = typeSuite === 2 ? resteAPayer : 0;
            var cheque = typeSuite === 3 ? montantPaiement + resteAPayer : montantPaiement;
        }
        var total = getTotal();
        if(localStorage.getItem('paiement') != null){
            var existingPaiement = localStorage.getItem('paiement');
            existingPaiement += typeSuite+","+parseFloat(nouveauMontant)+"|";
            localStorage.setItem('paiement',existingPaiement)
            var total = getTotal();
            var paiements =  localStorage.getItem('paiement')
            var premierPaiement = localStorage.getItem('montantPaiement')
            $.ajax({
                url: "../tickets/ajoutTicket.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    paiements: paiements,
                    premierPaiement:premierPaiement,
                    total:total,
                    arendre:monnaieArendre,
                    rendu:"true"
                }),
                beforeSend: function() {
                    $('#modal-espece').modal('hide')
                    $('#modal-cheque').modal('hide')
                    $('#modal-cb').modal('hide')
                    $('#btnEspece').attr("disabled", true);
                    $('#btnPaiementCB').attr("disabled", true);
                    $('#btnCheque').attr("disabled", true);
                },
                success: function (data) {
                    console.log(data)
                    var result = JSON.parse(data)
                    if(result.response === 1){

                        if(espece>0){
                            imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true,false,"","",result.multiple,result.detailspayment)
                        }else{
                            imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)
                        }
                        localStorage.removeItem('paiement')
                        localStorage.removeItem('montantPaiement')
                        $("#searchArticle").load(location.href + " #searchArticle");
                        reloadModalContent()
                        $('#total').html('0.00 €')
                        clearPanier(result.session, result.id_caisse,true)
                        $('#caddie').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:36px;">RENDU MONNAIE: ' + monnaieArendre.toFixed(2) + ' €</h1>')
                        $('#btnEspece').attr("disabled", false);
                        $('#btnPaiementCB').attr("disabled", false);
                        $('#btnCheque').attr("disabled", false);
                        $('#totalQte').text("")
                    }

                }
            }) 
        }
        else{
            $.ajax({
                url: "../tickets/ajoutTicket.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    "espece": espece,
                    "arendre":monnaieArendre,
                    "rendu":"true",
                    "cb": cb,
                    "cheques": cheque,
                    "ticket_restaurant": 0,
                    "total": total,
                }),
                beforeSend: function() {
                    $('#modal-espece').modal('hide')
                    $('#modal-cheque').modal('hide')
                    $('#modal-cb').modal('hide')
                    $('#btnEspece').attr("disabled", true);
                    $('#btnPaiementCB').attr("disabled", true);
                    $('#btnCheque').attr("disabled", true);
                },
                success: function (data) {
                // console.log(data)
                var result = JSON.parse(data)
                // console.log(result.ticket+result.ticket_part2+"\n"+"A RENDRE=>"+result.arendre+"\n"+"ESPECES=>"+result.espece+"\n"+"CB=>"+result.cb+"\n"+"CHEQUE=>"+result.cheque)
                if (result.response === 1) {
                    if(espece>0){
                        imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(nouveauMontant),result.cb,result.cheque,result.ticket_pied,true)

                    }else{
                        imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                    }
                    $('#caddie').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:50px;">RENDU MONNAIE: ' + monnaieArendre.toFixed(2) + ' €</h1>')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    $('#total').html('0.00 €')
                    clearPanier(result.session, result.id_caisse, true)
                    reloadModalContent()
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                    $('#totalQte').text("")


                }
            }
        })
        }


    }else{
        if(localStorage.getItem('paiement') != null){
            var existingPaiement = localStorage.getItem('paiement');
            existingPaiement += typeSuite+","+parseFloat(nouveauMontant)+"|";
            localStorage.setItem('paiement',existingPaiement)
            console.log("PAIEMENT => " + localStorage.getItem('paiement'),"PREMIER PAIEMENT => " + localStorage.getItem('montantPaiement'))

            var total = getTotal();
            var paiements =  localStorage.getItem('paiement')
            var premierPaiement = localStorage.getItem('montantPaiement')
            $.ajax({
                url: "../tickets/ajoutTicket.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    paiements: paiements,
                    premierPaiement:premierPaiement,
                    total:total
                }),
                beforeSend: function() {
                    $('#modal-espece').modal('hide')
                    $('#modal-cheque').modal('hide')
                    $('#modal-cb').modal('hide')
                    $('#btnEspece').attr("disabled", true);
                    $('#btnPaiementCB').attr("disabled", true);
                    $('#btnCheque').attr("disabled", true);
                },
                success: function (data) {
                    console.log(data)
                    var result = JSON.parse(data)
                    if(result.response === 1){

                        if(espece>0){
                            imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true,false,"","",result.multiple,result.detailspayment)
                        }else{
                            imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)
                        }
                        localStorage.removeItem('paiement')
                        localStorage.removeItem('montantPaiement')
                        $("#searchArticle").load(location.href + " #searchArticle");
                        $('#total').html('0.00 €')
                        clearPanier(result.session, result.id_caisse)
                        reloadModalContent()
                        $('#btnEspece').attr("disabled", false);
                        $('#btnPaiementCB').attr("disabled", false);
                        $('#btnCheque').attr("disabled", false);
                        $('#totalQte').text("")
                    }

                }
            })


        }else{
            var paiement2xCB = 0;
            var cb2= 0;
            if (typePaiement === 1) {
                var espece = typeSuite === 1 ? montantPaiement + resteAPayer : montantPaiement;
                var cb = typeSuite === 2 ? resteAPayer : 0;
                var cheque = typeSuite === 3 ? resteAPayer : 0;
            } else if (typePaiement === 2) {
                var espece = typeSuite === 1 ? resteAPayer : 0;
                var cb = typeSuite === 2 ? montantPaiement + resteAPayer : montantPaiement;
                if(typeSuite === 2){
                    var cb = montantPaiement
                    var cb2 = resteAPayer;
                    paiement2xCB = 1
                }
                var cheque = typeSuite === 3 ? resteAPayer : 0;
            } else if (typePaiement === 3) {
                var espece = typeSuite === 1 ? resteAPayer : 0;
                var cb = typeSuite === 2 ? resteAPayer : 0;
                var cheque = typeSuite === 3 ? montantPaiement + resteAPayer : montantPaiement;
            }
            var total = getTotal();
            $.ajax({
                url: "../tickets/ajoutTicket.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    "espece": espece,
                    "cb": cb,
                    "cheques": cheque,
                    "ticket_restaurant": 0,
                    "total": total,
                    "paiement2xCB":paiement2xCB,
                    "cb2":cb2
            // "compteurPaiement":
        }),
                beforeSend: function() {
                    $('#modal-espece').modal('hide')
                    $('#modal-cheque').modal('hide')
                    $('#modal-cb').modal('hide')
                    $('#do-login').attr("disabled", true);
                },
                success: function (data) {

                // console.log(data)
                var result = JSON.parse(data)
                // console.log(result.ticket+result.ticket_part2+"\n"+"A RENDRE=>"+result.arendre+"\n"+"ESPECES=>"+result.espece+"\n"+"CB=>"+result.cb+"\n"+"CHEQUE=>"+result.cheque)
                if (result.response === 1) {
                    if(espece>0){
                        imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true)
                    }else{
                        imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                    }
                    
                    $("#searchArticle").load(location.href + " #searchArticle");

                    $('#total').html('0.00 €')
                    clearPanier(result.session, result.id_caisse)
                    reloadModalContent()
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                    $('#totalQte').text("")
                }
            }
        })
        }
    }

    // 1 = ESPECE / 2 = CB / 3 = CHEQUES
}

// AJOUT ARTICLE DEPUIS CAISSE
$("#formAjoutArticle").submit(function(e) {

    e.preventDefault();

    var form = $(this);
    var actionUrl = form.attr('action');

    $.ajax({
        type: "POST",
        url: actionUrl,
        data: form.serialize(), // serializes the form's elements.
        success: function(data)
        {
            console.log(data)
            var result = JSON.parse(data);
            if(result.response === 1){
                Toast.fire({
                    icon: 'success',
                    title: "Article ajouté avec succès !"
                })
                window.setTimeout(function () {
                    window.location.reload();
                }, 500);
            }else{
                Toast.fire({
                    icon: 'error',
                    title: "Une erreur c'est produite...Veuillez recommencer."
                })
            }
        }
    });

});



// PAIEMENTS ESPECE
$('#paiementEspece').click(function () {
    var total = getTotal();
    $('#inputMontantEspece').select()
    $('#inputMontantEspece').val(total)
    $('#montantEspece').html(total)


})

$('#clotureCaisse').click(function(){
    window.location.href = "cloture-caisse.php";

})


function imprimeTicket(ticket,ticket_part2,arendre,footer,espece,cb,cheque,ticket_pied,cash=false,facture=false,ticketnum="",client="",paymentMultiple,detailspayment){
    Impresora.getImpresoras()
    .then(listaDeImpresoras => {

        var impresora = new Impresora();
        let imprimante = "";
        for (let i=0; i<listaDeImpresoras.length; i++) {
            console.log(listaDeImpresoras[i])
            if(listaDeImpresoras[i].search('EPSON TM') >= 0 || listaDeImpresoras[i].search('lp') >= 0 || listaDeImpresoras[i].search('POS') >= 0){
                imprimante = listaDeImpresoras[i]
            }
        }

        arendre = parseFloat(arendre)
        impresora.setEmphasize(0);
        impresora.setFontSize(1,1)
        impresora.setAlign("center")
        
        if(cash){
            impresora.cash()
            impresora.feed(1);
        }
        if(facture){
            impresora.write('--------------------------------------')
            impresora.write('\n')
            impresora.write(ticketnum)
            impresora.write('\n')
            impresora.write('POUR '+client)
            impresora.write('\n')
            impresora.write('--------------------------------------')
            impresora.write('\n')
        }
        impresora.write(ticket)
        impresora.write("\n")
        impresora.setAlign('left')
        impresora.write(ticket_part2)
        impresora.setAlign("center")
        impresora.write('--------------------------------------')
        impresora.write("\n")

        impresora.feed(1)
        impresora.setAlign('left')
        impresora.write('  Details du paiement:')
        if(paymentMultiple === "true"){
            impresora.write(detailspayment)
        }else{
            if(espece>0){
                impresora.write("\n")
                impresora.write("    > "+espece.toFixed(2)+" EUR EN ESPECES")
            }
            if(typeof cb === 'string'){
                if( cb.indexOf('-') != -1 ){
                    cb = cb.split('-');
                    impresora.write('\n')
                    impresora.write("    > "+parseFloat(cb[0]).toFixed(2)+" EUR EN CB")
                    impresora.write('\n')
                    impresora.write("    > "+parseFloat(cb[1]).toFixed(2)+" EUR EN CB")
                }
            }
            else{
                if(cb>0){
                    impresora.write('\n')
                    impresora.write("    > "+cb.toFixed(2)+" EUR EN CB")
                }
            }
            
            if(cheque>0){
                impresora.write('\n')
                impresora.write("    > "+cheque.toFixed(2)+" EUR EN CHEQUE")
            }
            if(arendre>0){
                impresora.write('\n')
                impresora.write("    > MONNAIE RENDU "+arendre.toFixed(2)+" EUR")
            }
        }


        impresora.write("\n")
        impresora.feed(1)
        impresora.setAlign("center")
        impresora.write(ticket_pied)
        impresora.write('\n')
        impresora.feed(1)
        impresora.write('======================================')
        impresora.write('\n')
        impresora.write(footer)
        impresora.feed(1)
    // imprimeTicket(ticket,num,ttc,total_ht,total_tva8,true)
    impresora.cut();
    // impresora.cutPartial();
    const encoded = JSON.stringify(impresora.operaciones);
    $.ajax({
        url:"imprimante.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            "log": encoded,
            "infoTicket":footer
        }),
        success:function(response){
            console.log(response)
        }
    });
    impresora.imprimirEnImpresora(imprimante)
    .then(valor => {
        // console.log("Resultat: " + valor);


    });
});



}


function paiementEspece(){
    var montantEspece = parseFloat($('#inputMontantEspece').val());
    var total = parseFloat(getTotal());
    console.log(montantEspece,total)


    if (montantEspece == total) {
        $('#modal-espece').modal('hide')
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": montantEspece,
                "cb": 0,
                "cheques": 0,
                "ticket_restaurant": 0,
                "total": total,
            }),
            beforeSend: function() {
                $('#modal-espece').modal('hide')
                $('#modal-cheque').modal('hide')
                $('#modal-cb').modal('hide')
            },
            success: function (data) {
                console.log(data)
                var result = JSON.parse(data)
                console.log(result.ticket+result.ticket_part2+"\n"+"A RENDRE=>"+result.arendre+"\n"+"ESPECES=>"+result.espece+"\n"+"CB=>"+result.cb)
                if (result.response == 1) {
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true)
                    $('#total').html('0.00 €')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    clearPanier(result.session, result.id_caisse)
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                    $('#totalQte').text("")
                }
            }
        })
    } else if (montantEspece < total) {

        var resteAPayer = total - montantEspece;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-espece > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-espece > .modal-dialog > .modal-content > .modal-body > .text-paiement')
        .html("<p>Reste a payer:  <span id='resteAPayer' style='font-size:22px;font-weight:600'>"+resteAPayer+"€</span> </br>Choisir une méthode de paiement ou choisir un nouveau montant pour payer en plusieurs fois.</p><br><input type='number' step=0.1  style='width:100px' id='nouveauMontant' class='resteAPayer' value='"+resteAPayer+"' />");
        $('#nouveauMontant').focus().select()

        // $('#modal-espece > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
        // .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,1," + montantEspece + "," + resteAPayer + ","+$('#resteAPayer').val()+")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,1," + montantEspece + "," + resteAPayer + ","+$('#resteAPayer').val()+")' id='btnPaiementSuiteEspece'>CB</button><button type='button' onClick='paiementSuite(3,1," + montantEspece + "," + resteAPayer + ","+$('#resteAPayer').val()+")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
        $('#modal-espece > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
        .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,1," + montantEspece + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,1," + montantEspece + "," + resteAPayer + ","+$('#resteAPayer').val()+")' id='btnPaiementSuiteEspece'>CB</button><button type='button' onClick='paiementSuite(3,1," + montantEspece + "," + resteAPayer + ","+$('#resteAPayer').val()+")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
        
    } else if (montantEspece > total) {

        var monnaieArendre = montantEspece - total;
        // $('#caddie').html("<h1 id='rendu'>RENDU MONNAIE: " + monnaieArendre.toFixed(2) + " €</h1>")
        $('#modal-espece').modal('hide')
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": montantEspece,
                "arendre":monnaieArendre,
                "rendu":"true",
                "cb": 0,
                "cheques": 0,
                "ticket_restaurant": 0,
                "total": total,

            }),
            beforeSend: function() {
                $('#modal-espece').modal('hide')
                $('#modal-cheque').modal('hide')
                $('#modal-cb').modal('hide')
            },
            success: function (data) {
                console.log(data)
                var result = JSON.parse(data)
                if (result.response === 1) {
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,montantEspece,result.cb,result.cheque,result.ticket_pied,true)
                    $('#caddie').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:50px;">RENDU MONNAIE: ' + monnaieArendre.toFixed(2) + ' €</h1>')
                    $('#total').text('0.00 €')
                    $('#modal-espece').modal('hide')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    clearPanier(result.session, result.id_caisse, true)
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                }
            }
        })
    }
    else if(total === 0){
        Toast.fire({
            icon: 'error',
            title: "Impossible d'encaisser un panier à 0 €"
        })
    }
}

$("#inputMontantEspece").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        paiementEspece()
    }
});

// PAIEMENT CB 
$('#paiementCB').click(function () {
    var total = getTotal();
    $('#inputMontantCB').select()
    $('#inputMontantCB').val(total)
    $('#montantCB').html(total)

})



function paiementCB(){
    var montantCB = $('#inputMontantCB').val();
    var total = parseFloat(getTotal());

    console.log(montantCB, total)

    if (montantCB == total ) {
        $('#modal-cb').modal('hide')
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": 0,
                "cb": montantCB,
                "cheques": 0,
                "ticket_restaurant": 0,
                "total": total,
            }),
            beforeSend: function() {
                $('#modal-espece').modal('hide')
                $('#modal-cheque').modal('hide')
                $('#modal-cb').modal('hide')
            },
            success: function (data) {
                var result = JSON.parse(data)
                console.log(result.ticket+result.ticket_part2+"\n"+"A RENDRE=>"+result.arendre+"\n"+"ESPECES=>"+result.espece+"\n"+"CB=>"+result.cb)
                if (result.response === 1) {
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                    $('#modal-cb').modal('hide')
                    $('#total').html('0.00 €')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    clearPanier(result.session, result.id_caisse)
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                    $('#totalQte').text("")
                }
            }
        })
    } else if (montantCB < total) {
        var resteAPayer = total - montantCB;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-cb > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .text-paiement')
        .html("<p>Reste a payer:  <span id='resteAPayer' style='font-size:22px;font-weight:600'>"+resteAPayer+"€</span> </br>Choisir une méthode de paiement ou choisir un nouveau montant pour payer en plusieurs fois.</p><br><input type='number' step=0.1  style='width:100px' id='nouveauMontant' class='resteAPayer' value='"+resteAPayer+"' />");
        $('#nouveauMontant').focus().select()
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
        .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,2," + montantCB + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,2," + montantCB + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>CB</button><button type='button' onClick='paiementSuite(3,2," + montantCB + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
    }
    else if(total === 0){
        Toast.fire({
            icon: 'error',
            title: "Impossible d'encaisser un panier à 0 €"
        })
    }
}

$("#inputMontantCB").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        paiementCB()
    }
});


// PAIEMENT CHEQUES
$('#paiementCheque').click(function () {
    var total = getTotal();
    $('#inputMontantCheque').select()
    $('#inputMontantCheque').val(total)
    $('#montantCheque').html(total)

})

function paiementCheque(){
    var montantCheque = $('#inputMontantCheque').val();
    var total = getTotal();
    if (montantCheque == total ) {
        $('#modal-cheque').modal('hide')
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": 0,
                "cb": 0,
                "cheques": montantCheque,
                "ticket_restaurant": 0,
                "total": total,
            }),
            beforeSend: function() {
                $('#modal-espece').modal('hide')
                $('#modal-cheque').modal('hide')
                $('#modal-cb').modal('hide')
            },
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    $('#modal-cheque').modal('hide')
                    $('#total').text('0.00 €')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                    clearPanier(result.session, result.id_caisse)
                    $('#btnEspece').attr("disabled", false);
                    $('#btnPaiementCB').attr("disabled", false);
                    $('#btnCheque').attr("disabled", false);
                    $('#totalQte').text("")
                }
            }
        })
    } else if (montantCheque < total) {
        var resteAPayer = total - montantCheque;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-body > .text-paiement')
        .html("<p>Reste a payer:  <span id='resteAPayer' style='font-size:22px;font-weight:600'>"+resteAPayer+"€</span> </br>Choisir une méthode de paiement ou choisir un nouveau montant pour payer en plusieurs fois.</p><br><input type='number' step=0.1  style='width:100px' id='nouveauMontant' class='resteAPayer' value='"+resteAPayer+"' />");
        $('#nouveauMontant').focus().select()
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
        .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,3," + montantCheque + "," + resteAPayer + ")' id='btnPaiementSuiteCheque'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,3," + montantCheque + "," + resteAPayer + ")' id='btnPaiementSuiteCheque'>CB</button><button type='button' onClick='paiementSuite(3,3," + montantCheque + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
    }
}

$("#inputMontantCheque").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        paiementCheque()
    }
});


// PRODUIT DIVERS
// PRODUIT DIVERS
function addProduitDivers(idcaisse, qte=1) {

    localStorage.removeItem('monnaieArendre')

    var prixDivers = prix
    var tvaDivers = 8.5
    var qteDivers = qte

    $.ajax({
        url: '../panier.php',
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            articleDivers: prixDivers,
            tvaDivers: tvaDivers,
            qteDivers: qteDivers,
            session: session,
            idcaisse: idcaisse,
        }),

        success: function (data) {
            var result = JSON.parse(data)
            console.log(prixDivers)
            if (result.response === 1) {
                var produit = result.data
                var total = result.total
                var montantDiver = parseFloat(prixDivers) * produit.qte
                $('#total').html(total.toFixed(2) + " €")
                prixDivers = parseFloat(prixDivers)
                $('#totalQte').text(result.qteTotal)
                $('#caddie').prepend(
                    '<div class="callout callout-info produit active" >\n' +
                    '<div class="row">' +
                    '<div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">' +
                    '<p class="designation">' +
                    produit.titre.toUpperCase() +
                    '<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(this.id,' + session + ',' + idcaisse + ')" id="deleteProduit-' + produit.ref + '"></i>' +
                    '</p>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 qteBox">' +
                    '<input type="text" onclick="this.select()" class="qteProduit" name="quantiteProduit" style="width: 40px !important;" id="quantiteProduit-' + produit.ref + '" value="' + produit.qte + '" />' +
                    '</div>' +
                    '<div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">' +
                    '<p class="puEuroProduit">' +
                    prixDivers.toFixed(2)+
                    '€</p>' +
                    '</div>' +
                    '<div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">' +
                    '<p class="puEuroProduit">' +
                    montantDiver.toFixed(2) +
                    '€</p>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-2 col-lg-2 col-xl-2 remisePourcent">' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseProduit" style="width: 40px !important;" id="remiseProduit-' + produit.ref + '" value="' + produit.remise + '" /><span>%</span>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">' +
                    '<p>' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseEuro" style="width: 40px !important;" id="remiseEuro-' + produit.ref + '-'+prixDivers+'" value="' + produit.remise_euro + '" /><span>€</span>' +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>')
                $('#caddie').children().eq(1).removeClass('active');
                $('#inputPrixDivers').val('')
                $('#inputQTEDivers').val('1')
                $('#inputTvaDivers').val('8.5')
                $('#modal-divers').modal('hide')
                // $("#searchArticle").load(location.href + " #searchArticle");
                //window.location.reload()

            }
        }

    })
}
function addProduitDiversNew(session, idcaisse) {
    localStorage.removeItem('monnaieArendre')
    var prixDivers = $('#inputPrixDivers').val()
    var tvaDivers = $('#inputTvaDivers').val()
    var qteDivers = $('#inputQTEDivers').val()

    $.ajax({
        url: '../panier.php',
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            articleDivers: prixDivers,
            tvaDivers: tvaDivers,
            qteDivers: qteDivers,
            session: session,
            idcaisse: idcaisse,
        }),

        success: function (data) {
            var result = JSON.parse(data)
            console.log(prixDivers)
            if (result.response === 1) {
                var produit = result.data
                var total = result.total
                var montantDiver = parseFloat(prixDivers) * produit.qte
                $('#total').html(total.toFixed(2) + " €")
                prixDivers = parseFloat(prixDivers)
                $('#totalQte').text(result.qteTotal)
                $('#caddie').prepend(
                    '<div class="callout callout-info produit active" >\n' +
                    '<div class="row">' +
                    '<div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">' +
                    '<p class="designation">' +
                    produit.titre.toUpperCase() +
                    '<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(this.id,' + session + ',' + idcaisse + ')" id="deleteProduit-' + produit.ref + '"></i>' +
                    '</p>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 qteBox">' +
                    '<input type="text" onclick="this.select()" class="qteProduit" name="quantiteProduit" style="width: 40px !important;" id="quantiteProduit-' + produit.ref + '" value="' + produit.qte + '" />' +
                    '</div>' +
                    '<div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">' +
                    '<p class="puEuroProduit">' +
                    prixDivers.toFixed(2)+
                    '€</p>' +
                    '</div>' +
                    '<div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">' +
                    '<p class="puEuroProduit">' +
                    montantDiver.toFixed(2) +
                    '€</p>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-2 col-lg-2 col-xl-2 remisePourcent">' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseProduit" style="width: 40px !important;" id="remiseProduit-' + produit.ref + '" value="' + produit.remise + '" /><span>%</span>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">' +
                    '<p>' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseEuro" style="width: 40px !important;" id="remiseEuro-' + produit.ref + '-'+prixDivers+'" value="' + produit.remise_euro + '" /><span>€</span>' +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>')
                $('#caddie').children().eq(1).removeClass('active');
                $('#inputPrixDivers').val('')
                $('#inputQTEDivers').val('1')
                $('#inputTvaDivers').val('8.5')
                $('#modal-divers').modal('hide')
                window.location.reload()

            }
        }

    })
}

$("#inputPrixDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val())
    }
});

$("#inputQTEDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val())
    }
});

$("#inputTvaDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val())
    }
});


// RETOUR ARTICLE
$('#showRetArticleCatalogue').click(function () {
    $('#retourArticleCatalogue').show()
    $('#inputRetourArticleCatalogue').focus();
    $('#retourArticleDivers').hide()
    $('#retourArticleChoix').hide();
    $('#btnRetCatalogue').show()
    $('#btnRetDivers').hide()

})


function retourArticleCatalogue(session, idcaisse, event) {
    console
    if (event.which == 13) {
        var ref = event.target.value;
        $.ajax({
            url: '../panier.php',
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                retourArticle: true,
                retourArticleCatalogue: ref,
                session: session,
                idcaisse: idcaisse,
            }),
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    window.location.reload()
                }
            }

        })
    }
}

$('#showRetArticleDivers').click(function () {
    $('#retourArticleDivers').show()
    $('#inputRetourPrixDivers').focus();
    $('#retourArticleCatalogueur').hide()
    $('#retourArticleChoix').hide()

})

function retourArticleDivers(session, idcaisse, event) {
    var retourDiversPrix = $('#inputRetourPrixDivers').val()
    var retourQteDivers = $('#inputRetourQTEDivers').val()
    var retourTvaDivers = $('#inputRetourTvaDivers').val()

    if (retourDiversPrix !== "") {
        $.ajax({
            url: '../panier.php',
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                retourArticle: true,
                retourArticleDivers: retourDiversPrix,
                retourQteDivers: retourQteDivers,
                retourTvaDivers: retourTvaDivers,
                session: session,
                idcaisse: idcaisse,
            }),
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    window.location.reload()
                }
            }

        })

    }
    else{
        $('#erreurRetArticleDivers').text("Le champs prix ne peut pas être vide ! Veuillez entrez un prix.")
    }
}
