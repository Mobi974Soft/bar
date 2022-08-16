$('#modal-espece').on('shown.bs.modal', function() {
    $('#inputMontantEspece').select();
})

$('#modal-cb').on('shown.bs.modal', function() {
    $('#inputMontantCB').select();
})

$('#modal-cheque').on('shown.bs.modal', function() {
    $('#inputMontantCheque').select();
})





function getTotal() {
    var total = $('#total').text()
    total = total.replace(/\s/g, '');
    total = total.replace('€', '');
    return total;
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
            if (result.response === 1) {
                Toast.fire({
                    icon: 'success',
                    title: "Ticket du total caisse imprimé !"
                })
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


function paiementSuite(typeSuite, typePaiement, montantPaiement, resteAPayer) {

    if (typePaiement === 1) {
        var espece = typeSuite === 1 ? montantPaiement + resteAPayer : montantPaiement;
        var cb = typeSuite === 2 ? resteAPayer : 0;
        var cheque = typeSuite === 3 ? resteAPayer : 0;
    } else if (typePaiement === 2) {
        var espece = typeSuite === 1 ? resteAPayer : 0;
        var cb = typeSuite === 2 ? montantPaiement + resteAPayer : montantPaiement;
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
        }),
        success: function (data) {
            var result = JSON.parse(data)
            if (result.response === 1) {
                clearPanier(result.session, result.id_caisse)
            }
        }
    })


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




function paiementEspece(){
    var montantEspece = parseFloat($('#inputMontantEspece').val());
    var total = parseFloat(getTotal());

    if (montantEspece == total && montantEspece > 0) {
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
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    clearPanier(result.session, result.id_caisse)
                }
            }
        })
    } else if (montantEspece < total) {
        var resteAPayer = total - montantEspece;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-espece > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-espece > .modal-dialog > .modal-content > .modal-body > .text-paiement').html("<p>Reste a payer:  <span class='resteAPayer'>" + resteAPayer + " €</span> </br>Choisir une méthode de paiement</p>");
        $('#modal-espece > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
            .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,1," + montantEspece + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,1," + montantEspece + "," + resteAPayer + ")' id='btnPaiementSuiteEspece'>CB</button><button type='button' onClick='paiementSuite(3,1," + montantEspece + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
    } else if (montantEspece > total) {
        var monnaieArendre = montantEspece - total;
        $('#caddie').html("<h1>RENDU MONNAIE: " + monnaieArendre.toFixed(2) + " €</h1>")
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": monnaieArendre,
                "cb": 0,
                "cheques": 0,
                "ticket_restaurant": 0,
                "total": total,
                "id_caisse": id_caisse,
                "session": session,
            }),
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    clearPanier(session, id_caisse, true)
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
    if (montantCB == total  && montantCB > 0 ) {
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
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    clearPanier(result.session, result.id_caisse)
                }
            }
        })
    } else if (montantCB < total) {
        var resteAPayer = total - montantCB;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-cb > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .text-paiement').html("<p>Reste a payer:  <span class='resteAPayer'>" + resteAPayer + " €</span> </br>Choisir une méthode de paiement</p>");
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
            .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,2," + montantCB + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,1," + montantCB + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>CB</button><button type='button' onClick='paiementSuite(3,1," + montantCB + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
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

    if (montantCheque == total  && montantCheque > 0) {
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
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    clearPanier(result.session, result.id_caisse)
                }
            }
        })
    } else if (montantCheque < total) {
        var resteAPayer = total - montantCheque;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-body > .text-paiement').html("<p>Reste a payer:  <span class='resteAPayer'>" + resteAPayer + " €</span> </br>Choisir une méthode de paiement</p>");
        $('#modal-cheque > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
            .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,2," + montantCheque + "," + resteAPayer + ")' id='btnPaiementSuiteCheque'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,1," + montantCheque + "," + resteAPayer + ")' id='btnPaiementSuiteCheque'>CB</button><button type='button' onClick='paiementSuite(3,1," + montantCheque + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
    }
}

$("#inputMontantCheque").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        paiementCheque()
    }
});


// PRODUIT DIVERS

function addProduitDivers(session, idcaisse) {
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
            if (result.response === 1) {
                window.location.reload()
            }
        }

    })
}




// RETOUR ARTICLE
$('#showRetArticleCatalogue').click(function () {
    $('#retourArticleCatalogue').show()
    $('#retourArticleDivers').hide()
    $('#retourArticleChoix').hide();

})

function retourArticleCatalogue(session, idcaisse, event) {
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

