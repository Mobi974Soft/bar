
const modal_body_cheque = $('#modal-body-cheque').html();
const modal_body_cb = $('#modal-body-cb').html();
const modal_body_espece = $('#modal-body-espece').html();

function togglePromoCode(idCaisse, idTable, active) {
	var action = active ? 'undo' : 'apply';
	var title = active ? 'Retirer le code promo ?' : 'Appliquer le code promo ?';
	var text = active
		? 'Les prix initiaux seront restaurés sur ce panier.'
		: 'Une remise de 1 € sera appliquée à chaque article du panier. Un seul code promo est autorisé par panier.';

	Swal.fire({
		title: title,
		text: text,
		icon: 'question',
		showCancelButton: true,
		confirmButtonText: active ? 'Oui, retirer' : 'Oui, appliquer',
		cancelButtonText: 'Annuler',
		confirmButtonColor: active ? '#dc3545' : '#6c5ce7'
	}).then(function (result) {
		if (!result.isConfirmed) {
			return;
		}

		$('#btnCodePromo').prop('disabled', true);
		$.ajax({
			url: 'promo-code.php',
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({ action: action, id_caisse: idCaisse, id_table: idTable }),
			success: function (response) {
				Toast.fire({ icon: 'success', title: response.message });
				window.location.reload();
			},
			error: function (xhr) {
				var response = xhr.responseJSON || {};
				Toast.fire({ icon: 'error', title: response.message || 'Impossible de modifier le code promo.' });
				$('#btnCodePromo').prop('disabled', false);
			}
		});
	});
}

$('#showRemisePourcent').click(function(e){
    $('#remise-globale').show()
    $('#remise-globale-euro').hide()
    $('#inputMontantRemisePanier').focus()
    $('#btnRemiseGlobale').hide()
})

$('#showRemiseEuro').click(function(e){
    $('#remise-globale-euro').show()
    $('#remise-globale').hide()
    $("#inputMontantRemisePanierEuro").focus()
    $('#btnRemiseGlobale').hide()
})

function backToRemiseChoix(){
    $('#btnRemiseGlobale').show()
    $('#remise-globale').hide()
    $('#remise-globale-euro').hide()
}


function reloadModalContent(){
    $('#modal-espece > .modal-dialog > .modal-content > .modal-body').html(modal_body_espece);
    $('#modal-cheque > .modal-dialog > .modal-content > .modal-body').html(modal_body_cheque);
    $('#modal-cb > .modal-dialog > .modal-content > .modal-body').html(modal_body_cb);
}
$('#modal-confirmation').on('shown.bs.modal', function() {
    $('#focusleurre').select();
})
$('#modal-espece').on('shown.bs.modal', function() {
    $('#inputMontantEspece').select();
})

$('#modal-cb').on('shown.bs.modal', function() {
    $('#inputMontantCB').select();
})
$('#modal-resto').on('shown.bs.modal', function() {
    $('#inputMontantResto').select();
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
$('#modal-nouveau-produit').on('hidden.bs.modal', function () {
  $('#searchArticle').val("")
  $('#searchArticle').focus()
})
$('#showTotalPoints').click(function (e) {
    $('#bloc-fidelite-points').show()
    $('#bloc-creation-client').hide()
    $('#bloc-fidelite-scan').hide()
    $("#inputScannerQrCode").focus()
    $('#usePoint').hide()
}) 
const elementProduit = document.getElementById("caddie");

if ($("#caddie").children().length == 0) {
    $('#showScanQr').click(function (e) {
        $('#bloc-fidelite-scan').show()
        $('#usePoint').hide()
        $('#bloc-creation-client').hide()
        $('#bloc-fidelite-points').hide()
        $("#inputScannerQrCode").focus()
    })
}
$('#showScanQrPoints').click(function (e) {
    $('#usePoint').show()
    $('#bloc-fidelite-scan').hide()
    $('#bloc-creation-client').hide()
    $('#bloc-fidelite-points').hide()
    $("#inputScannerQrCodePoint").focus()
})

function backToChoixFidelite() {
    $('#bloc-fidelite-scan').hide()
    $('#bloc-fidelite-points').hide()
    $('#usePoint').hide()
    $('#bloc-creation-client').show()
}

function onCloseFideliteModal() {
    $('#bloc-fidelite-scan').hide()
    $('#bloc-creation-client').show()
    $('#usePoint').show()
    $('#bloc-fidelite-points').hide()
    $('#modal-fidelite').modal('hide')
	$('#bloc-fidelite-points').html('')
	window.location.reload();
}

// FIDELIAS AJOUT POINT FIDELITE
function scanQrCode(value,session,id_caisse, id_client, event) {

    if (event.which == 13) {

		if( !value.includes("https")){
			var qrCode = value
        // console.log(qrCode)
        // throw "stop execution";
        // var url = "https://www.fidelias.fr/fidelite/caisse/increment.php";
        var url = "fidelite/fidelite.php";
        var total = getTotal();

        $.ajax({
            url: url,
            type: "POST",
            contentType: "application/json",
            // async:false,
            data: JSON.stringify({
                "qrCode": qrCode,
                "total": total,
                "session": session,
                "id_caisse": id_caisse,
                "id_client": id_client
            }),
            success: function (data) {
                console.log("DATA=>" + data)
                var result = JSON.parse(data)
                if (result.response == 1) {
                    var client = result.client;
                    var pointActuel = result.solde
                    var totalEnPoint = pointActuel + total;
                    var ancienSolder = pointActuel;
                    var newpoint = result.percentage != 0 ? total * (result.percentage / 100) : total;
                    var soldeClient = parseFloat(ancienSolder) + parseFloat(newpoint);
                    var remise = result.remise;
                    var codemagasin = result.codemagasin
                    var uuid = result.uuid
                    var formule = result.formule_points;
                    if (remise > 0) {

                        $.ajax({
                            url: '../panier.php',
                            type: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                remisePanier: remise,
                                totalPanier: total,
                                session: session,
                                idcaisse: id_caisse,
                            }),
                            success: function (data) {
                                console.log(data)
                                var result = JSON.parse(data)
                                if (result.response === 1) {
                                    localStorage.removeItem('monnaieArendre')
                                }
                            }

                        })
                    }
                    console.log("EN NB POINT =>" + result.solde)
                    console.log("ANCIEN POINT => " + ancienSolder)
                    console.log("NOUVEAU POINT => " + newpoint)
                    console.log("NOUVEAU total => " + soldeClient)
					var articleRestant = 10 - soldeClient;
					$('#affichageSolde').html('<h3>Client ' + client + '</h3>' +
                        '<p class="text-danger">Nombre de points: ' + soldeClient  + ' </p>'
                    );
					if(soldeClient >= 10){
						$('#affichageSolde').append("<button class='btn btn-success' onclick=\"convertirPoint("+id_client+ "," + session + "," + id_caisse + "," + formule + "," + soldeClient + ",'" + encodeURIComponent(uuid) + "'," + total + ")\">Ajouter article offert</button>")
					}
                    
                    console.log("TOTAL POINT " + totalEnPoint)
                }
            }
        })
		}else{
			Toast.fire({
				icon: 'error',
				title: "Le codebarre n'a pas été scanné"
			})
		}
        


    }


}
function showTotalPoints(value,session,id_caisse,id_client,event){
    if (event.which == 13) {
        var qrCode = value
        var url = "fidelite/afficheSoldeBar.php";
        $.ajax({
            url: url,
            type: "POST",
            contentType: "application/json",
            // async:false,
            data: JSON.stringify({
                "qrCode": qrCode,
                "session": session,
                "id_caisse": id_caisse,
                "id_client": id_client
            }),
            success: function (data) {
                console.log("DATA=>" + data)
                var result = JSON.parse(data)

                if (result.response == 1){
                    $('#affichageSoldePoint').html(result.data)
                }
            }
        });

    }
}

function utiliserPoints(value, session, id_caisse, id_client, event) {

    if (event.which == 13) {
        var qrCode = value
        // console.log(qrCode)
        // throw "stop execution";
        // var url = "https://www.fidelias.fr/fidelite/caisse/increment.php";
        var url = "fidelite/utiliserPoints.php";
        var total = getTotal();

        $.ajax({
            url: url,
            type: "POST",
            contentType: "application/json",
            // async:false,
            data: JSON.stringify({
                "qrCode": qrCode,
                "total": total,
                "session": session,
                "id_caisse": id_caisse,
                "id_client": id_client
            }),
            success: function (data) {
                console.log("DATA=>" + data)
                var result = JSON.parse(data)
                if (result.response == 1) {
                    var client = result.client;
                    var pointActuel = result.solde
                    var totalEnPoint = pointActuel + total;
                    var ancienSolder = pointActuel;
                    var newpoint = result.percentage != 0 ? total * (result.percentage / 100) : total;
                    var soldeClient = parseFloat(ancienSolder) ;
                    var remise = result.remise;
                    var codemagasin = result.codemagasin
                    var uuid = result.uuid
                    var formule = result.formule_points;
                    if (remise > 0) {

                        $.ajax({
                            url: '../panier.php',
                            type: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                remisePanier: remise,
                                totalPanier: total,
                                session: session,
                                idcaisse: id_caisse,
                            }),
                            success: function (data) {
                                console.log(data)
                                var result = JSON.parse(data)
                                if (result.response === 1) {
                                    localStorage.removeItem('monnaieArendre')
                                }
                            }

                        })
                    }
                    
					var articleRestant = 10 - soldeClient;
                    $('#affichageSoldeConsultation').html('<h3>Client ' + client + '</h3>' +
                        '<p class="text-danger">Nombre de points  : ' + soldeClient  + ' </p>'
                    );

                        if (soldeClient >= 10) {
                            $('#affichageSoldeConsultation').append("<button class='btn btn-success' onclick=\"convertirPoint("+id_client+ "," + session + "," + id_caisse + "," + formule + "," + soldeClient + ",'" + encodeURIComponent(uuid) + "'," + total + ")\">Ajouter article offert</button>")
    
                        } else if (result.currency == "EUR" && soldeClient >= 1.15) {
                            $('#affichageSoldeConsultation').append("<button class='btn btn-success' onclick=\"convertirPoint( "+id_client+ "," + session + "," + id_caisse + "," + formule + "," + soldeClient + ",'" + encodeURIComponent(uuid) + "'," + total + ")\">Ajouter article offert</button>")
    
                        }

                    
                }
            }
        })


    }


}

function convertirPoint(idclient,session, id_caisse, formule, soldeClient, uuid, total) {
    var total = getTotal();
    $.ajax({
        url: 'fidelite/remisePanierFidelite.php',
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            remisePanierFidelite: soldeClient,
            totalPanier: total,
            session: session,
            idcaisse: id_caisse,
            uuid: uuid,
            formule: formule,
            currency: "",
            idclient:idclient
        }),
        success: function (data) {
            console.log(data)
            var result = JSON.parse(data)
			
			$('#modal-fidelite').modal('hide')
				$('.btn-offrir').prop('disabled', false);
            if (result === 1) {
				
                localStorage.removeItem('monnaieArendre')
                
            }
        }

    })
}
// FIN FIDELEITE
// function getTotal() {
//     var total = $('#total').text()
//     total = total.replace(/\s/g, '');
//     total = total.replace('€', '');
//     return total;
// }
$('#searchArticle').off('keyup').keyup(function(e){
        console.log("ok")
        if (e.repeat) { return }
            if(e.which ==  107){
                printLastTicket()
            }
        });
		function printLastTicket() {
			$.ajax({
				url: "dernier_ticket.php",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({ dernier_ticket: "true" }),
				success: function (data) {
					console.log(data)
					var result = JSON.parse(data)
					if (result.response === 1) {
						if (result.plugin == 1) {
		
						}
						$('#modal-reimpression').modal('hide')
						$('#inputNumeroReimpression').val('')
						$('#searchArticle').val('')
					}
				}
			})
		}


// CHOIX IMPRESSION TICKET
function printTicketOrNot(){
    var result = "";
    $.ajax({
        url: "../tickets/printTicketOrNot.php",
        type: "POST",
        contentType: "application/json",
        async:false,
        data: JSON.stringify({
            "id_caisse":"id_caisse",
        }),
        success: function (data) {
            result = data;
            
        }
    })
    return result;
}


function reimpressionTicket(id_caisse){
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
                id_caisse: id_caisse
            }),
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    console.log(result)
                    console.log(result.espece)
                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(result.espece),parseFloat(result.cb),parseFloat(result.ticketresto),parseFloat(result.cheque),result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)

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

                    imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(result.espece),parseFloat(result.cb),parseFloat(result.ticketresto),parseFloat(result.cheque),result.ticket_pied,false,true,result.header,client,result.multiple,result.detailspayment)

                    $('#modal-facture').modal('hide')
                    $('#inputNumeroTicket').val('')
                    $('#inputNomClient').val('')
                }
            }

        })
    }

}

function imprimeFactureA4(){
    var numero_ticket = $('#inputNumeroTicket').val()
    var client = $('#inputNomClient').val()
    if(numero_ticket == "" && client == ""){
        Toast.fire({
            icon: 'error',
            title: "Veuillez remplir les champs avant d'imprimer la facture"
        })

    }else{
        $.ajax({
            url: 'factureA4.php',
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                numero_ticket: numero_ticket,
                client:client,

            }),
            success: function (data) {
                console.log(data)
                if (data != "") {
                    var baseurl = document.location.origin;
                    window.open(baseurl+"/caisse-backend/facture/"+data+"/FA-"+numero_ticket+".pdf");
                }



                $('#modal-facture').modal('hide')
                $('#inputNumeroTicket').val('')
                $('#inputNomClient').val('')
                // }
            }

        })
    }

}

function ouvreTirroirCaisse(){
   
}

function totalCaisse(id_caisse, options = null) {

        // let autorisation = prompt('Entrez mot de passe');
        // if (autorisation == "090583") {
            $.ajax({
                url: "../print_total_caisse.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    "id_caisse": id_caisse
                }),
                success: function (data) {
                    console.log(data)
					var result = typeof data === 'string' ? JSON.parse(data) : data
					if (result.response === 1) {
						Toast.fire({
							icon: 'success',
							title: "Ticket du total caisse imprimé !"
						})
					} else {
						Toast.fire({ icon: 'error', title: result.message || 'Impression impossible' })
					}
                }
            })
        // }
} 


function viderPanier(id_caisse,session){
    $.ajax({
        url: "../panier/videPanier.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            "videTout":true,
            "id_caisse":id_caisse,
            "session":session
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


function encaisseTicket(session,id_caisse,arendre=null){
	passerCommande(id_caisse,1)
	// setTimeout(function() {
	// 	if (newWindow && !newWindow.closed) {
	// 		newWindow.location.reload();
	// 	}
	// }, 1000);
	console.log("IDCAISSE APRES ECAISSEMENT 2 => " + id_caisse)
    if (arendre>0) {
        $('#bloc-arendre').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:30px;">RENDU MONNAIE: ' + arendre.toFixed(2) + ' €</h1>')
        clearPanier(id_caisse ,session, true)
    }else{
        clearPanier(id_caisse,session)
    }
	
    $('#total').html('0.00 €')
    $('#btnEspece').attr("disabled", false);
    $('#btnPaiementCB').attr("disabled", false);
    $('#btnCheque').attr("disabled", false);
    $('#totalQte').text("")
    console.log(arendre)
    setTimeout(function(){
        $('#searchArticle').val("")
        
    },200);
    $("#searchArticle").load(location.href + " #searchArticle");
    
}
// localStorage.clear()
// PAIEMENT EN PLUSIEURS FOIS 
function paiementSuite(typeSuite, typePaiement, montantPaiement, resteAPayer) {


    var nouveauMontant = $('#nouveauMontant').val()
    var montantAPayer = $('#resteAPayer').text().split(" ")
    var imprime = 0;
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
                    rendu:"true",
                    imprimerTicket:imprimeTicket,
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
                    if(result.response == 1){
                        imprime = 1
                        if (printTicketOrNot()==1) {
                            if (espece!=0) {
                                ouvreTirroirCaisse()
                            }
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                        }else{
                         if(espece!=0){
                            imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,parseFloat(nouveauMontant),result.cb,result.cheque,result.ticket_pied,true)
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                        }else{
                            imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                        }
                    }

                }

                localStorage.removeItem('paiement')
                localStorage.removeItem('montantPaiement')
                reloadModalContent()
                $('#caddie').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:36px;">RENDU MONNAIE: ' + monnaieArendre.toFixed(2) + ' €</h1>')


            }

        });

        }else{
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
                    if (result.response == 1) {
                        if (printTicketOrNot()==1) {
                            if(espece!=0){
                                ouvreTirroirCaisse()
                            }                                  
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                            
                        }else{
                            if(espece!=0){
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true,false,"","",result.multiple,result.detailspayment)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }else{
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }
                        }

                        $('#caddie').html('<h1 class="text-center text-danger" id="rendu" style="margin-top:50px;font-weight: 600;font-size:50px;">RENDU MONNAIE: ' + monnaieArendre.toFixed(2) + ' €</h1>')
                        reloadModalContent()



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
                    total:total,
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
                    if(result.response == 1){
                        if (printTicketOrNot()==1) {
                            if(espece!=0){
                                ouvreTirroirCaisse()
                            }                                 
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                        }else{
                            if(espece!=0){
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true,false,"","",result.multiple,result.detailspayment)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }else{
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,false,"","",result.multiple,result.detailspayment)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }
                        }


                        localStorage.removeItem('paiement')
                        localStorage.removeItem('montantPaiement')
                        reloadModalContent()

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
                    if (result.response == 1) {

                        if (printTicketOrNot()==1) {
                            if(espece!=0){
                                ouvreTirroirCaisse()
                            }                                 
                            encaisseTicket(result.session,result.id_caisse,result.arendre)
                        }
                        else{
                            if(espece!=0){
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }else{
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                                encaisseTicket(result.session,result.id_caisse,result.arendre)
                            }
                        }

                        reloadModalContent()

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


function imprimeTicket(fullticket,ticket,ticket_part2,arendre,footer,espece,cb,cheque,ticket_pied,cash=false,facture=false,ticketnum="",client="",paymentMultiple,detailspayment){
//     console.log(fullticket)
//     if(espece>0 || espece < 0){
//        var host = "ws://localhost:8080/pulse";
//    }else{
//        var host = "ws://localhost:8080/print";
//    } 
   
//    try{
//        var socket = new WebSocket(host);
//        console.log(socket)
//        socket.addEventListener('error', function(event) {
//            console.error('Erreur de connexion WebSocket :', event);
//        });
   
//        socket.onopen = function(e){
//            console.log('Socket Status: '+socket.readyState+' (open)')
//            socket.send(fullticket);
//        }
//        socket.onmessage = function(msg){
//            console.log("MESSAGE",msg.data)
//        }
       
//        socket.onclose = function(){
//            console.log('Socket Status: '+socket.readyState+' (closed)')
//        } 
//    }catch(exception){
//        console.log(exception)
//    }
   
   
   
   }




function imprimerTicketChoix(ticket,ticket_part2,arendre,footer,espece,cb,ticketresto,cheque,ticket_pied,cash=false,id_caisse,session,facture=false,ticketnum="",client="",paymentMultiple,detailspayment){
    $('#searchArticle').blur()
    $('#modal-confirmation').modal('show')
    $(document).off('keydown').keydown(function(e){
        if (e.repeat) { return }
            if(e.which ==  107){
                imprimeTicket(ticket,ticket_part2,arendre,footer,espece,cb,ticketresto,cheque,ticket_pied,cash)
                encaisseTicket(session,id_caisse,arendre)
                $('#modal-confirmation').modal('hide')

            }
            else if(e.which == 109){
                if (espece!=0) {
                    ouvreTirroirCaisse()
                }
                encaisseTicket(session,id_caisse,arendre)
                $('#modal-confirmation').modal('hide')
            }
        });

    $('#print-ticket').off('click').on("click", function(event){
        if(!event.detail || event.detail == 1){
            imprimeTicket(ticket,ticket_part2,arendre,footer,espece,cb,ticketresto,cheque,ticket_pied,cash)
            encaisseTicket(session,id_caisse,arendre)
            $('#modal-confirmation').modal('hide')
        }

    });

    $('#no-print-ticket').off('click').on("click", function(event){
        if(!event.detail || event.detail == 1){//activate on first click only to avoid hiding again on multiple clicks
            if (espece!=0) {
                ouvreTirroirCaisse()
            }
            encaisseTicket(session,id_caisse,arendre)
            $('#modal-confirmation').modal('hide')
        }

    })

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
                $('#btnEspece').attr('disabled', true);
            },
            success: function (data) {
                // console.log(data)
                var result = JSON.parse(data)
                // console.log(result.ticket+result.ticket_part2+"\n"+"A RENDRE=>"+result.arendre+"\n"+"ESPECES=>"+result.espece+"\n"+"CB=>"+result.cb)
                if (result.response == 1) {
					console.log("IDCAISSE APRES ECAISSEMENT => " + result.id_caisse)
                    if (printTicketOrNot()==1) {
                        $('#searchArticle').blur()
                        $('.modal-title ').focus()
                        // imprimerTicketChoix(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,true,result.id_caisse,result.session)
                        ouvreTirroirCaisse();
                        encaisseTicket(result.session,result.id_caisse)
                    }else{
                        encaisseTicket(result.session,result.id_caisse)
                        return;
                    }


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
        $('#searchArticle').blur()
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
                $('#btnEspece').attr('disabled', true)
            },
            success: function (data) {
                console.log(data)
                var result = JSON.parse(data)
                if (result.response === 1) {
                    $('#searchArticle').blur()
                    if (printTicketOrNot()==1) {
                        // imprimerTicketChoix(result.ticket,result.ticket_part2,monnaieArendre,result.footer,montantEspece,result.cb,result.cheque,result.ticket_pied,true,result.id_caisse,result.session)
                        ouvreTirroirCaisse()
                        encaisseTicket(result.session,result.id_caisse,monnaieArendre)
                    }else{
                        imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,montantEspece,result.cb,result.cheque,result.ticket_pied,true)
                        encaisseTicket(result.session,result.id_caisse,monnaieArendre)
                    }   


                    
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

var pressed = false;
$("#inputMontantEspece").on('keyup', function (e) {
    if (!pressed) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            pressed = true
            paiementEspece()
            setTimeout(function(){
                pressed = false;  
            },500)
        }
    }
});


// PAIEMENT CB 
$('#paiementCB').click(function () {
    var total = getTotal();
    $('#inputMontantCB').select()
    $('#inputMontantCB').val(total)
    $('#montantCB').html(total)

})

$('#paiementTicketResto').click(function () {
    var total = getTotal();
    $('#inputMontantResto').select()
    $('#inputMontantResto').val(total)
    $('#montantResto').html(total)

})

function paiementTicketResto(){
    var montantResto = $('#inputMontantResto').val();
    var total = parseFloat(getTotal());

    console.log(montantResto, total)

    if (montantResto == total ) {
        $('#modal-resto').modal('hide')
        $.ajax({
            url: "../tickets/ajoutTicket.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                "espece": 0,
                "cb": 0,
                "cheques": 0,
                "ticket_restaurant": montantResto,
                "total": total,
            }),
            beforeSend: function() {
                $('#modal-espece').modal('hide')
                $('#modal-cheque').modal('hide')
                $('#modal-cb').modal('hide')
                $('#modal-ticket-resto').modal('hide')
                $('#btnPaiementResto').attr('disabled', true)
            },
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    if (printTicketOrNot()==1) {
                        // imprimerTicketChoix(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,result.id_caisse,result.session)
                        encaisseTicket(result.session,result.id_caisse)
                    }else{
                        imprimeTicket(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.ticketresto,result.cheque,result.ticket_pied)
                        encaisseTicket(result.session,result.id_caisse)
                    }

                    
                }
            }
        })
    } else if (montantResto < total) {
        var resteAPayer = total - montantResto;
        resteAPayer = resteAPayer.toFixed(2)
        $('#modal-cb > .modal-dialog > .modal-content > .modal-header > .modal-title').html("SUITE PAIEMENT");
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .text-paiement')
        .html("<p>Reste a payer:  <span id='resteAPayer' style='font-size:22px;font-weight:600'>"+resteAPayer+"€</span> </br>Choisir une méthode de paiement ou choisir un nouveau montant pour payer en plusieurs fois.</p><br><input type='number' step=0.1  style='width:100px' id='nouveauMontant' class='resteAPayer' value='"+resteAPayer+"' />");
        $('#nouveauMontant').focus().select()
        $('#modal-cb > .modal-dialog > .modal-content > .modal-body > .inputPaiement')
        .html("<button type='button' class='btn btn-success btnPaiement' onClick='paiementSuite(1,2," + montantResto + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>Espece</button><button type='button' class='btn btn-info btnPaiement' onClick='paiementSuite(2,2," + montantResto + "," + resteAPayer + ")' id='btnPaiementSuiteCB'>CB</button><button type='button' onClick='paiementSuite(3,2," + montantResto + "," + resteAPayer + ")' class='btn btn-warning btnPaiement' id='btnPaiementSuiteCheques'>Cheque</button>");
    }
    else if(total === 0){
        Toast.fire({
            icon: 'error',
            title: "Impossible d'encaisser un panier à 0 €"
        })
    }
}

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
                $('#btnPaiementCB').attr('disabled', true)
            },
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    if (printTicketOrNot()==1) {
                        // imprimerTicketChoix(result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied,false,result.id_caisse,result.session)
                        encaisseTicket(result.session,result.id_caisse)
                    }else{
                        imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                        encaisseTicket(result.session,result.id_caisse)
                    }

                    
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

// $("#inputMontantCB").on('keyup', function (e) {
//     if (e.key === 'Enter' || e.keyCode === 13) {
//         paiementCB()
//     }
// });
$("#inputMontantCB").on('keyup', function (e) {
    if (!pressed) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            pressed = true
            paiementCB()
            setTimeout(function(){
                pressed = false;  
            },500)
        }
    }
});

$("#inputMontantResto").on('keyup', function (e) {
    if (!pressed) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            pressed = true
            paiementCB()
            setTimeout(function(){
                pressed = false;  
            },500)
        }
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
                $('#btnCheque').attr('disabled', true).html("En cours...");
            },
            success: function (data) {
                var result = JSON.parse(data)
                if (result.response === 1) {
                    $('#modal-cheque').modal('hide')
                    $('#total').text('0.00 €')
                    $("#searchArticle").load(location.href + " #searchArticle");
                    if (printTicketOrNot()==1) {
                        $('#modal-confirmation').modal('show')
                        $(document).off('keydown').keydown(function(e){
                            if (e.repeat) { return }
                                if(e.which ==  107){
                                    imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                                    $('#modal-confirmation').modal('hide')
                                    encaisseTicket(result.session,result.id_caisse)
                                }else if(e.which == 109 ){
                                    ouvreTirroirCaisse()
                                    encaisseTicket(result.session,result.id_caisse)
                                    $('#modal-confirmation').modal('hide')
                                }
                            });

                        $('#print-ticket').off('click').on("click", function(event){
                            if(!event.detail || event.detail == 1){
                                imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                                encaisseTicket(result.session,result.id_caisse)
                                $('#modal-confirmation').modal('hide')
                            }
                            
                        });

                        $('#no-print-ticket').off('click').on("click", function(event){
                            if(!event.detail || event.detail == 1){
                                encaisseTicket(result.session,result.id_caisse)
                                $('#modal-confirmation').modal('hide')
                            }
                            
                        })
                        
                    }else{
                        imprimeTicket(result.fullticket,result.ticket,result.ticket_part2,result.arendre,result.footer,result.espece,result.cb,result.cheque,result.ticket_pied)
                        encaisseTicket(result.session,result.id_caisse)
                    }

                    
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

// $("#inputMontantCheque").on('keyup', function (e) {
//     if (e.key === 'Enter' || e.keyCode === 13) {
//         paiementCheque()
//     }
// });
$("#inputMontantCheque").on('keyup', function (e) {
    if (!pressed) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            pressed = true
            paiementCheque()
            setTimeout(function(){
                pressed = false;  
            },500)
        }
    }
});




// PRODUIT DIVERS
function addProduitDivers(session, idcaisse,prix,qte=1,options) {

    localStorage.removeItem('monnaieArendre')

    var prixDivers = prix
    var tvaDivers = 8.5
    var qteDivers = qte
    console.log(options)
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
                console.log(options)
                var disabled = options == 1   ? "disabled" : "";
                prixDivers = parseFloat(prixDivers)
                $('#totalQte').text(result.qteTotal)
                $('#caddie').prepend(
                    '<div class="callout callout-info produit active" >\n' +
                    '<div class="row">' +
                    '<div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">' +
                    '<p class="designation">' +
                    produit.titre.toUpperCase() +
                    '<i class="fa fa-trash text-red" style="cursor:pointer;padding:10px;" onclick="deleteArticle(this.id,' + session + ',' + idcaisse + ')" id="deleteProduit-' + produit.ref + '"></i>' +
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
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseProduit"  '+disabled+' style="width: 40px !important;" id="remiseProduit-' + produit.ref + '" value="' + produit.remise + '" /><span>%</span>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">' +
                    '<p>' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseEuro" '+disabled+' style="width: 40px !important;" id="remiseEuro-' + produit.ref + '-'+prixDivers+'" value="' + produit.remise_euro + '" /><span>€</span>' +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>')
                if (typeof screen2 !== 'undefined') {
                    screen2.location.reload();
                }
                $('#caddie').children().eq(1).removeClass('active');
                $('#inputPrixDivers').val('')
                $('#inputQTEDivers').val('1')
                $('#inputTvaDivers').val('8.5')
                $('#modal-divers').modal('hide')
                // $("#searchArticle").load(location.href + " #searchArticle");
                $("#searchArticle").val("")
                $('#rendu').text("")
                //window.location.reload()
                
            }
        }

    })
}

function addProduitDiversOneEuro(session, idcaisse) {
    addProduitDiversSimple(idcaisse, 1);
}

function addProduitDiversNew(session, idcaisse,options=null,showPromo = null) {
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
                var disabled = options == 1  ? "disabled" : "" ;
                $('#caddie').prepend(
                    '<div class="callout callout-info produit active" >\n' +
                    '<div class="row">' +
                    '<div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">' +
                    '<p class="designation">' +
                    produit.titre.toUpperCase() +
                    '<i class="fa fa-trash text-red" style="cursor:pointer;padding:10px;" onclick="deleteArticle(this.id,' + session + ',' + idcaisse + ')" id="deleteProduit-' + produit.ref + '"></i>' +
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
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseProduit" '+disabled+' style="width: 40px !important;" id="remiseProduit-' + produit.ref + '" value="' + produit.remise + '" /><span>%</span>' +
                    '</div>' +
                    '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">' +
                    '<p>' +
                    '<input type="text" class="inputRemise" onclick="this.select()" name="remiseEuro" '+disabled+' style="width: 40px !important;" id="remiseEuro-' + produit.ref + '-'+prixDivers+'" value="' + produit.remise_euro + '" /><span>€</span>' +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>')
                if (typeof screen2 !== 'undefined') {
                    screen2.location.reload();
                }
                $('#caddie').children().eq(1).removeClass('active');
                $('#inputPrixDivers').val('')
                $('#inputQTEDivers').val('1')
                $('#inputTvaDivers').val('8.5')
                $('#modal-divers').modal('hide')
                $("#searchArticle").load(location.href + " #searchArticle");
                $('#rendu').text("")
                //window.location.reload()
                
            }
        }

    })
}

$("#inputPrixDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val(),$('#diversoptionPromo').val(),$('#diversShowPromo').val())
    }
});

$("#inputQTEDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {

        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val(),$('#diversoptionPromo').val(),$('#diversShowPromo').val())
    }
});

$("#inputTvaDivers").on('keyup', function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        addProduitDiversNew($('#diversSession').val(), $('#diversIDCAISSE').val(),$('#diversoptionPromo').val(),$('#diversShowPromo').val())
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



function showTable(parameterValue,elm){
	var parameterName = "status";
    // Get the current URL
     var currentURL = window.location.origin + '/restaurant/caisse/restaurant.php?tables';

    // Check if the URL already contains a query string
                if (currentURL.indexOf("?") === -1) {
                    // If no query string exists, add a "?" to the URL
                    currentURL += "?";
                } else {
                    // If a query string already exists, add an "&" to the URL
                    currentURL += "&";
                }

                // Add the new parameter to the URL
                currentURL += parameterName + "=" + parameterValue;

                // Navigate to the new URL
                window.location.href = currentURL;
}            

//hang on event of form with id=myform
$("#formRegister").submit(function(e) {
	
	//prevent Default functionality
	e.preventDefault();
	
	//get the action-url of the form
	var actionurl = e.currentTarget.action;
	console.log($("#formRegister").serialize())
	//do your own request an handle the results
	$.ajax({
		url: actionurl,
		type: 'post',
		data: $("#formRegister").serialize(),
		success: function(data) {
			var res = JSON.parse(data);
			if(res.response==1){
				location.reload();
				Toast.fire({
					icon: 'success',
					title: res.message
				})
			}else{
				Toast.fire({
					icon: 'error',
					title: res.message
				})
			}
		},error : function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR,textStatus,errorThrown)
		}
	});
	
});

function deleteClient(id){
	if (confirm('Êtes vous sur de vouloir supprimer ce client ?')) {
		$.ajax({
			url:"ajax/deleteClient.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				id:id,
			}),
			success:function(response){
				var res = JSON.parse(response)
				console.log(res)
				if (res.response == 1) {
					location.reload();
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
				}
			}
		})
	} 
}

function unSelectClient(id){
	// if (confirm('Êtes vous sur de vouloir supprimer ce client ?')) {
		$.ajax({
			url:"ajax/unSelectClient.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				id:id,
			}),
			success:function(response){
				var res = JSON.parse(response)
				console.log(res)
				if (res.response == 1) {
					location.reload();
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
				}
			}
		})
	// } 
}



function selectClient(id,statut){
	if(statut == 0){
		$.ajax({
			url:"ajax/selectClient.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				id:id,
				statut:statut,
				selectClient:true,
			}),
			success:function(response){
				var res = JSON.parse(response)
				console.log(res)
				if (res.response == 1) {
					window.location.href = "restaurant.php";
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
				}
			}
		})
	}
	
}


// Permet de faire apparaitre le champ d'entré quantité pour modifier la quantité d'un même produit lors de l'achat
function showHideQte(elm,table,prix,qte){
	if(qte>1){
		var ref = elm.value;
		var qteDiv = $('#qtyToPay-'+ref);
		console.log(elm.checked,qteDiv)
		
		if(elm.checked){
			qteDiv.css('display','block')
			qteDiv.html("<div class='input-group input-group-sm'><input type='number' id='qtetopay-"+ref+"' placeholder='Entrez une quantité' class='form-control'><span class='input-group-append'><button type='button' class='btn btn-info btn-flat' onclick='updatePaymentQte(\""+ref+"\","+table+","+prix+")'>Valider</button></span></div>")
		}else{
			qteDiv.css('display','none')
		}
	}
	
}

function sommeResteApayer(){
	var somme = 0;
	$('.sommeTemp').each(function() {
		somme += parseFloat($(this).text());
	});
	return somme;
}

function totalSousPanier(){
	var premierPaiementMtn = 0;
	$('.sommeTemp').each(function() {
		if ($('#paiementsBody').children().length === 1) {
			var produitPrix = parseFloat($(this).text())
			premierPaiementMtn+=produitPrix
			$('#montantPaiement').val(premierPaiementMtn.toFixed(2))
		}
	});
}

function augmenter(idproduit,max,prix) {
	console.log("augmenter=>"+prix,"IDPRODUIT=>"+idproduit)
	var input = document.getElementById('qteTemp-'+idproduit);
	var valeurActuelle = parseInt(input.value);
	if (valeurActuelle < max) {
		input.value = valeurActuelle + 1;
		var newPrix = input.value * prix
		$('#prixTemp-'+idproduit).text(newPrix.toFixed(2))
		var newResteApayer = sommeResteApayer()
		
		$("#ResteAPayer").text(newResteApayer.toFixed(2) + " €");
	}
	totalSousPanier()
}

function diminuer(idproduit,prix) {
	console.log("DIMINUER=>"+prix,"IDPRODUIT=>"+idproduit)
	var input = document.getElementById('qteTemp-'+idproduit);
	var valeurActuelle = parseInt(input.value);
	if (valeurActuelle > 0 ) {
		input.value = valeurActuelle - 1;
		var newPrix = input.value * prix
		$('#prixTemp-'+idproduit).text(newPrix.toFixed(2))
		var newResteApayer = sommeResteApayer()
		$("#ResteAPayer").text(newResteApayer.toFixed(2) + " €");
	}
	totalSousPanier()
}

function getProduitByRef(idproduit,idtable,id_caisse){
	var result = false;
	$.ajax({
		url:"ajax/getProduitByRef.php",
		type:"POST",
		contentType:"application/json",
		data: JSON.stringify({
			ref:idproduit,
			idtable:idtable,
			id_caisse:id_caisse,
		}),
		//line added to get ajax response in sync
        async: false,
		success:function(response){
			console.log(response)
			var res = JSON.parse(response)
			if (res.response == 1) {
				result = res.data;
			}
		}
	})
	return result;
}

function ajoutSousPanier(idproduit,ref,prix,idtable,titre,remise,remise_unique,id_caisse){
	
	var existe = 0;
	

	$('.sommeTemp').each(function() {
		var id = $(this).attr('id');
		id = id.split('-');
		refactif = id[1]
		
		if(idproduit == refactif){
			existe++;
		}

		
	});
	if(existe==0){
		var arrayRes = getProduitByRef(idproduit,idtable,id_caisse)
		var qte = arrayRes[0]
		var prixUnique = arrayRes[1]
		// var prixTemp = prix - ( prix * (remise / 100)) 
		// prixTemp = prixTemp * qte
		var prixTemp = prixUnique * qte
		// var prixUnique = prix / qte
		var id = remise > 0 ? idproduit : ref;
		var titre = titre.charAt(0).toUpperCase() + titre.slice(1)
		var INPUT_QUANTITE  = '<button onclick="diminuer(\''+id+'\','+prixUnique+')">-</button>'+
		'<input type="number" id="qteTemp-'+id+'" class="qteTemp-input-number" value="'+qte+'" min="1" max="'+qte+'">'+
		'<button onclick="augmenter(\''+id+'\','+qte+','+prixUnique+')">+</button>';
		$('#sousPanier').append('<div class="row" id="row-'+id+'" style="margin-top: 11px;"><div class="col-12" style="border-bottom: 1px solid lightgray;">'+INPUT_QUANTITE+'<span style="margin-left:15px;font-weight:600;">'+titre+'</span><span style="float:right" class="sommeTemp" id="prixTemp-'+idproduit+'">'+prixTemp.toFixed(2)+' </span> <span style="float:right">€</span></div></div>')
		
	}
	else{
		$('#row-'+idproduit).remove()
	}
	var premierPaiementMtn = 0;
	$('.sommeTemp').each(function() {
		if ($('#paiementsBody').children().length === 1) {
			var produitPrix = parseFloat($(this).text())
			premierPaiementMtn+=produitPrix
			$('#montantPaiement').val(premierPaiementMtn.toFixed(2))
		}
	});
	
}



function changeResteApayer(elm=null,ref,idtable,idproduit,id_caisse){
	if($(elm).is(':checked') && $('input[name="produitChoix[]"]:checked').length == 1  ){
		$.ajax({
			url:"ajax/changeResteApayer.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				idproduit:idproduit,
				idtable:idtable,
				id_caisse:id_caisse,
			}),
			success:function(response){
				console.log(response)
				var res = JSON.parse(response)
				if (res.response == 1) {
					var prix = parseFloat(res.prix);
					$('#ResteAPayer').text(prix.toFixed(2) + " €")
					
					
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
				}
			}
		})
	}else{
		var arrProduit = [];
		$('input[name="produitChoix[]"]:checked').each(function () {
			var id = $(this).attr('id');
			id = id.split('-');
			id = id[1]
			arrProduit.push(id);
		});
		if(arrProduit.length>0){
			$.ajax({
				url:"ajax/calculTotalPanier.php",
				type:"POST",
				contentType:"application/json",
				data: JSON.stringify({
					idtable:idtable,
					type:"id_produit",
					arrProduit:arrProduit,
					id_caisse:id_caisse
				}),
				success:function(response){
					var res = JSON.parse(response)
					if (res.response == 1) {
						var prix = parseFloat(res.total);
						$('#ResteAPayer').text(prix.toFixed(2) + " €")
					}
				}
			})
		}else{
			$('#ResteAPayer').text("0.00 €")
		}
		
	}
	
}

function calculTotalPanier(idtable){
	var arrProduit = [];
		$('input[name="produitChoix[]"]:checked').each(function () {
			arrProduit.push($(this).val());
		});
		if(arrProduit.length>0){
			$.ajax({
				url:"ajax/calculTotalPanier.php",
				type:"POST",
				contentType:"application/json",
				data: JSON.stringify({
					idtable:idtable,
					arrProduit:arrProduit
				}),
				success:function(response){
					console.log(response)
					var res = JSON.parse(response)
					if (res.response == 1) {
						var prix = parseFloat(res.total);
						$('##totalPanier').text(prix.toFixed(2) + " €")
					}
				}
			})
		}


}

function backToRemiseChoix(){
	$('#btnRemiseGlobale').show()
	$('#remise-globale').hide()
	$('#remise-globale-euro').hide()
}

$('#showRemisePourcent').click(function(e){
	$('#remise-globale').show()
	$('#remise-globale-euro').hide()
	$('#inputMontantRemisePanier').focus()
	$('#btnRemiseGlobale').hide()
})

$('#showRemiseEuro').click(function(e){
	$('#remise-globale-euro').show()
	$('#remise-globale').hide()
	$("#inputMontantRemisePanierEuro").focus()
	$('#btnRemiseGlobale').hide()
})

function remisePanierEuro(session,idcaisse,idtable){
	var montantRemisePanier = $('#inputMontantRemisePanierEuro').val();
	var totalPanier = $('#totalPanier').text()
	totalPanier =  totalPanier.replace(/\s/g, '').replace('€','');
	totalPanier = parseFloat(totalPanier)
	
	if(montantRemisePanier > 0){
		$.ajax({
			url: 'ajax/remise.php',
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({
				remisePanierEuro: montantRemisePanier,
				totalPanier:totalPanier,
				session: session,
				idcaisse: idcaisse,
				idtable: idtable
			}),
			success: function (data) {
				console.log(data)
				var result = JSON.parse(data)
				if (result.response === 1) {
					localStorage.removeItem('monnaieArendre')
					window.location.reload()
				}
			}
			
		})
	}
}

function remisePanier(session,idcaisse,idtable){
	var montantRemisePanier = $('#inputMontantRemisePanier').val();
	var totalPanier = $('#totalPanier').text()
	totalPanier =  totalPanier.replace(/\s/g, '').replace('€','');
	totalPanier = parseFloat(totalPanier)
	console.log(montantRemisePanier,totalPanier)
	if(montantRemisePanier > 0){
		$.ajax({
			url: 'ajax/remise.php',
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({
				remisePanier: montantRemisePanier,
				totalPanier:totalPanier,
				session: session,
				idcaisse: idcaisse,
				idtable: idtable
			}),
			success: function (data) {
				console.log(data)
				var result = JSON.parse(data)
				if (result.response === 1) {
					localStorage.removeItem('monnaieArendre')
					window.location.reload()
				}
			}
			
		})
	}
}



function updatePaymentQte(ref,table,prix){
	var qteToUpdate = $('#qtetopay-'+ref).val();
	var currentQte = $('#quantiteProduit-'+ref).val()
	
	if(qteToUpdate<=currentQte){
		$.ajax({
			url:"ajax/updatePaymentQte.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				ref:ref,
				table:table,
				qteToUpdate:qteToUpdate
			}),
			success:function(response){
				var res = JSON.parse(response)
				if (res.response == 1) {
					var newQte = res.newQte
					var newPrice = prix * newQte
					// changeResteApayer(null,ref,table)
					var currentQte = $('#quantiteProduit-'+ref).val()
					$('#quantiteProduit-'+ref).val(currentQte - newQte)
					$('#prixProduit-'+ref).text(newPrice.toFixed(2) + " €")
					console.log("log=>",prix,qteToUpdate)
					
					var totalapayer = 0;
					$('input[name="produitChoix[]"]:checked').each(function() {
						var refproduit = $(this).val()
						var mt = $("#prixProduit-"+refproduit).text().split(" ")
						mt = parseFloat(mt[0])
						if(ref !==refproduit ){
							totalapayer+=mt
						}else{
							totalapayer+=newPrice
						}
						console.log("mt=>"+mt)
						
					})
					$('#qteUpdated-'+ref).text("("+qteToUpdate+")")
					console.log("totalapayer=>"+totalapayer)
					$('#ResteAPayer').text(totalapayer.toFixed(2)+ " €")
					$('#qtyToPay-'+ref).css('display','none')
					Toast.fire({
						icon: 'success',
						title: "La quantité a été mis a jour"
					})
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
				}
			}
		})
	}else{
		Toast.fire({
			icon: 'error',
			title: "Impossible de choisir un quantié supérieure."
		})
	}
	
}

function sendToKitchen(table,id_caisse){
	$.ajax({
		url:"ajax/envoieCuisine.php",
		type:"POST",
		contentType:"application/json",
		data: JSON.stringify({
			id_caisse:id_caisse,
			table:table,
		}),
		success:function(response){
			var res = JSON.parse(response)
			
			if (res.response == 1) {
				// Impresora.getImpresoras()
				// .then(listaDeImpresoras => {
					
				// 	var impresora = new Impresora();
				// 	let imprimante = "";
				// 	for (let i=0; i<listaDeImpresoras.length; i++) {
				// 		console.log(listaDeImpresoras[i])
				// 		if(listaDeImpresoras[i].search('EPSON TM') >= 0 || listaDeImpresoras[i].search('lp') >= 0 || listaDeImpresoras[i].search('POS') >= 0){
				// 			imprimante = listaDeImpresoras[i]
				// 		}
				// 	}
					
				// 	impresora.setEmphasize(0)
				// 	impresora.setAlign("center")
				// 	impresora.write(res.ticket)
				// 	impresora.feed(10)
				// 	impresora.cut()
					
				// 	impresora.imprimirEnImpresora(imprimante)
				// 	.then(valor => {
				// 		console.log("Resultat: " + valor);
						
						
				// 	});
				// });
				// Toast.fire({
				// 	icon: 'success',
				// 	title: ""
				// })
			}else{
				Toast.fire({
					icon: 'error',
					title: res.message
				})
			}
		}
	})
}

// Change le contenu des commandes en cours
function changeCommandes(id,element,id_caisse){
	console.log(element)
	const elements = document.querySelectorAll('.commande_active');
		elements.forEach((element) => {
			$(element).removeClass('commande_active');
			// $('#'+element).css({'border':'none','border-color':'none'})
	});
	$('#'+element).addClass('commande_active')
	// $('#'+element).css({'border':'1px solid','border-color':'#d39e00'})
	$.ajax({
		url:"ajax/changeCommande.php",
		type:"POST",
		contentType:"application/json",
		data: JSON.stringify({
			id_caisse:id_caisse,
			session:id,
		}),
		success:function(response){
			var res = JSON.parse(response)
			
			if (res.response == 1) {
				
				var produits = res.data;
				$('#detailsCommande').html('')
				var produitsHTML = ''
				
				
				produits.forEach((item, index) => {
					console.log(item)
					var prix = item.pu_euro
					var options = item.options
					options = options.replace(/u0022/g, '"');
					options = JSON.parse(options);
    				var optionsHTMl = ""
					
					if(Array.isArray(options)){
						optionsHTMl += "Options : "
						options.forEach((option,i) => {
							if(i>0){
								optionsHTMl += " + "
							}
							optionsHTMl += option[1].toUpperCase()
						})
					}
					
					
					produitsHTML += '<div class="callout produit" style="margin-bottom:10px;    padding: 10px;">'+
					'<div class="row">'+
					'<div class="col-lg-8">'+
					'<p style="font-weight: 600;margin: 0;">'+item.titre+'</p>'+
					'<p class="text-muted" style="font-size:17px;margin-bottom: 0;">'+optionsHTMl+'</p>'+
					'<small></small>'+
					'</div>'+
					'<div class="col-lg-3 offset-lg-1" style="margin: auto;">'+
					'<p class="totalPrice text-danger" style="font-size:18px;margin-bottom: 0;font-weight:600">Quantité x'+item.qte+' €</p>'+
					'</div>'+
					'</div>'+
					'</div>';
				})
				$('#detailsCommande').html(produitsHTML)
				$('#commande_en_cours').val(res.sessionpanier)
				
				
			}else{
				Toast.fire({
					icon: 'error',
					title: res.message
				})
			}
		}
	})
}

function valideOrder(commande){
	$.ajax({
		url:"ajax/validateOrder.php",
		type:"POST",
		contentType:"application/json",
		data: JSON.stringify({
			commande:commande,
		}),
		success:function(response){
			console.log(res)
			var res = JSON.parse(response)
			if (res.response == 1) {
				Toast.fire({
					icon: 'success',
					title: res.message
				})
				window.setTimeout(function () {
					window.location.reload();
				}, 1000);
			}else{
				Toast.fire({
					icon: 'error',
					title: res.message
				})
			}
		}
	})
}
// modal commande
function passerCommande(id_caisse,numerotable){
	
	if (numerotable == 0 ) {
		Toast.fire({
			icon: 'error',
			title: "Vous devez selectionner une table pour passer une commande"
		})
	}else{
		var info_order = $('#order-info').val()
		$.ajax({
			url:"ajax/passerCommande.php",
			type:"POST",
			contentType:"application/json",
			data: JSON.stringify({
				id_caisse:id_caisse,
				order_info:info_order,
				numerotable:numerotable,
			}),
			success:function(response){
				console.log(res)
				var res = JSON.parse(response)
				if (res.response == 1) {
					sendToKitchen(numerotable,id_caisse);
					$('#modal-commande').modal('hide');
					Toast.fire({
						icon: 'success',
						title: res.message
					})
				}else{
					Toast.fire({
						icon: 'error',
						title: res.message
					})
				}
			}
		})
	}
	
	
}

function proceedToPayment(id_caisse){
	window.location.href='restaurant.php?paiement'; 
	$("#totalDu").load(location.href + " #totalDu");
	$("#ResteAPayer").load(location.href + " #ResteAPayer");
	$.ajax({
		url:"requestRestaurant.php",
		type:"POST",
		contentType: "application/json",
		data: JSON.stringify({
			id_caisse:id_caisse
		}),
		success:function(response){
			var res = JSON.parse(response)
			console.log("total => " + res)
			$('#montantPaiement').val(res.total)
		},
		
	})
}


// MONNAIRE A RENDRE PAR PAIEMENT
// $('.inputMontantPaiement').each(function(){
// 	var clientArgent = $(this).val()
// 	$(this).on('keyup change', function(){
// 		if(clientArgent)
// 	})
// });

function escapeHtml(unsafe)
{
	return unsafe
	.replace(/&/g, "&amp;")
	.replace(/</g, "&lt;")
	.replace(/>/g, "&gt;")
	.replace(/"/g, "&quot;")
	.replace(/'/g, "&#039;");
}

// TRIER LES TABLES PAR STATUS
function changeActiveStatus(element){
	$(element).removeClass('btn-outline-info').addClass('bg-gradient-info').siblings().removeClass('bg-gradient-info').addClass('btn-outline-info');
	
}

// MODIFIE LE NOMBRE DE TABLE
function editPlace(places_id,numtable,id){
	var nbplaces = $('#'+places_id).val()
	$.ajax({
		url:"ajax/editPlace.php",
		type:"POST",
		contentType: "application/json",
		data: JSON.stringify({
			nbplaces:nbplaces,
			numtable:numtable
		}),
		success:function(response){
			console.log(response)
			var res = JSON.parse(response)
			if(res.response==1){
				var identifiant = '#'+id;
				$(identifiant).text(nbplaces);
				$('#formUpdatePlace').css('display','none')
			}
		},
		
	})
}


// MODIFIE LE NOMBRE DE TABLE
function changeStatusPayment(ref,idtable){
	var result ;
	$.ajax({
		url:"ajax/changeStatusPayment.php",
		type:"POST",
		contentType: "application/json",
		async:false,
		data: JSON.stringify({
			ref:ref,
			idtable:idtable
		}),
		success:function(response){
			result = response
			console.log("inside=>"+result)
		},
		
	})
	console.log("resultat=>"+result)
	return result;
}

function newProduitPrice(ref,id_caisse){
	var result ;
	$.ajax({
		url:"ajax/newProduitPrice.php",
		type:"POST",
		contentType: "application/json",
		async:false,
		data: JSON.stringify({
			ref:ref,
			id_caisse:id_caisse
		}),
		success:function(response){
			result = response
			console.log("inside=>"+result)
		},
		
	})
	console.log("resultat=>"+result)
	return result;
}

// PAIEMENT 

function pay(id_caisse,multiple=false,element=null){
	
	var nbProduitCocher = $('input[name="produitChoix[]"]:checked').length;
	var paiementShared = false;

	if($('.inputMontantPaiement').val()==0 && nbProduitCocher == 0){
		Toast.fire({
			icon: 'error',
			title: "Veuillez choisir un moyen de paiement et entrez le montant" 
		})
		return;
	}
	
	if(nbProduitCocher>0 && multiple){
		var arrayMontant = []
		$('.inputMontantPaiement').each(function(){
			arrayMontant.push($(this).val())
		});
		var arrayPaiement = []
		$('.methodePaiement').each(function(){
			arrayPaiement.push($(this).val())
		});
		
		var arrProduit = [];
		$('input[name="produitChoix[]"]:checked').each(function () {
			var ref = $(this).val()
			var qteTemp = $('#qteTemp-'+ref).val()
			var prixTemp = $('#prixTemp-'+ref).text()
			arrProduit.push({"qte":qteTemp,"ref":ref,"prix":prixTemp});
		});
		
		var newArray = []
		for (var i = 0; i <= arrayMontant.length; i++) {
			if(arrayMontant[i] > 0 ){
				newArray.push(arrayMontant[i]+"|"+arrayPaiement[i])
			}
		}
		
	}else if(multiple){
		var montantUnique = $(element).parent().siblings().find('input').val();
		var typePaiement = $(element).parent().siblings().find('select').val();
		paiementShared = true;
		var newArray = [montantUnique+'|'+typePaiement];
		var infoTicket = "Repas";
	}
	else if(nbProduitCocher==0){
		


		
		$('input[type="checkbox"]').prop('checked', true);
		var infoTicket = "";
		var arrayMontant = []
		$('.inputMontantPaiement').each(function(){
			arrayMontant.push($(this).val())
		});

		$('#paiementTotal').text(arrayMontant[0])
		var arrayPaiement = []
		$('.methodePaiement').each(function(){
			arrayPaiement.push($(this).val())
		});
		
		var arrProduit = [];
		$('input[name="produitChoix[]"]:checked').each(function () {
			var ref = $(this).val()
			var qteTemp = $('#qteTemp-'+ref).val()
			var prixTemp = $('#prixTemp-'+ref).text()
			arrProduit.push({"qte":qteTemp,"ref":ref,"prix":prixTemp});
		});
		
		if($('input[name="produitChoix[]"]:checked').length>0){
			var infoTicket = "choixProduit";
		}
		
		
		
		var newArray = []
		for (var i = 0; i <= arrayMontant.length; i++) {
			console.log(arrayMontant[i])
			if(arrayMontant[i] > 0 ){
				newArray.push(arrayMontant[i]+"|"+arrayPaiement[i])
			}
		}
	}
	else{
		var infoTicket = "";
		var arrayMontant = []
		$('.inputMontantPaiement').each(function(){
			arrayMontant.push($(this).val())
		});
		var arrayPaiement = []
		$('.methodePaiement').each(function(){
			arrayPaiement.push($(this).val())
		});
		
		var arrProduit = [];
		$('input[name="produitChoix[]"]:checked').each(function () {
			var ref = $(this).val()
			var qteTemp = $('#qteTemp-'+ref).val()
			var prixTemp = $('#prixTemp-'+ref).text()

			var id_produit = $(this).attr('id');
			id_produit = id_produit.split('-');
			id_produit = id_produit[1]

			arrProduit.push({"qte":qteTemp,"ref":ref,"prix":prixTemp,"id_produit":id_produit});
		});
		console.log(arrProduit)
		// throw new error
		if($('input[name="produitChoix[]"]:checked').length>0){
			var infoTicket = "choixProduit";
		}
		
		
		
		var newArray = []
		for (var i = 0; i <= arrayMontant.length; i++) {
			console.log(arrayMontant[i])
			if(arrayMontant[i] > 0 ){
				newArray.push(arrayMontant[i]+"|"+arrayPaiement[i])
			}
		}
	}
	var resteAPayer = $('#ResteAPayer').text()
	resteAPayer = resteAPayer.slice(0, -1);
	resteAPayer = parseFloat(resteAPayer)
	
	
	
	if (newArray.length > 0 ) {
		// throw new Error("STOP");
		$.ajax({
			url:"requestRestaurant.php",
			type:"POST",
			contentType: "application/json",
			data: JSON.stringify({
				paiementDetails: newArray,
				id_caisse:id_caisse,
				id_table:parseInt($('#btnCodePromo').attr('data-id-table'), 10) || 0,
				multiple:multiple,
				resteAPayer:resteAPayer,
				produitChoix:arrProduit,
				infoTicket:infoTicket,
			}),
			success:function(response){
				// console.log(response)
				
				var res = JSON.parse(response)
				if (res.warning) {
					Toast.fire({ icon: 'warning', title: res.warning })
				}
				if (res.response == 1) {
					// $('#totalPanier').text("0.00 €")
					console.log("table=>"+res.arendre)
					if (res.arendre>0) {
						$('#monnaieArendre').text(res.arendre.toFixed(2) + " €")
					}else{
						$('#monnaieArendre').text("0.00 €")
					}
					var resteAPayer =  $('#ResteAPayer').text().split(" ")
					console.log(resteAPayer)
					resteAPayer = parseFloat(resteAPayer[0])
					var paiementEncaisse =  $('#paiementTotal').text().split(" ")
					paiementEncaisse = parseFloat(paiementEncaisse[0])
					console.log(paiementEncaisse)
					var totalEncaisse = paiementEncaisse + resteAPayer
					$('#paiementTotal').text(totalEncaisse.toFixed(2) + " €")
					$('input[name="produitChoix[]"]:checked').each(function() {
						// var ref = $(this).val()

						

						
						var id = $(this).attr('id');
						id = id.split('-');
						var ref = id[1]

						var nouvelleQuantite = $('#quantiteProduit-'+ref).val() -  $('#qteTemp-'+ref).val() 
						$('#quantiteProduit-'+ref).val(nouvelleQuantite)
						$(this).prop('checked', false);

						// RECALCUL LE TOTAL POUR CHAQUE PRODUIT ENCAISSE
						var prixProduit = $('#qteLigne-'+ref).text().split('€')[0]
						prixProduit = parseFloat(prixProduit) * nouvelleQuantite;
						$('#prixProduit-'+ref).text(prixProduit.toFixed(2)+' €')
						console.log(ref)
						if($('#quantiteProduit-'+ref).val()==0){
							$(this).hide()
							$('#quantiteProduit-'+ref).hide()
							$(this).prop('checked', false);
							$(this).prop('disabled', true);
							
						}
						
						
					})
					
					
					// $(element).parent().parent().find('*').attr('disabled', true);
					// throw new Error("STOP");
					// Impresora.getImpresoras()
					// .then(listaDeImpresoras => {
					
					// 	var impresora = new Impresora();
					// 	let imprimante = "";
					// 	for (let i=0; i<listaDeImpresoras.length; i++) {
					// 		console.log(listaDeImpresoras[i])
					// 		if(listaDeImpresoras[i].search('EPSON TM') >= 0 || listaDeImpresoras[i].search('lp') >= 0 || listaDeImpresoras[i].search('POS') >= 0){
					// 			imprimante = listaDeImpresoras[i]
					// 		}
					// 	}
					
					// 	impresora.setEmphasize(0)
					// 	impresora.cash()
					// 	impresora.write(res.ticket)
					// 	impresora.feed(1)
					// 	impresora.cut()
					
					// 	impresora.imprimirEnImpresora(imprimante)
					// 	.then(valor => {
					// 		console.log("Resultat: " + valor);
					
					// 	});
					// });
					var rendu = res.arendre > 0 ? true : false;
					$('#ResteAPayer').text("0.00 €")
					$('#montantPaiement').val(0)
					var totalDu = $('#totalDu').text().split(" ")
					totalDu = parseFloat(totalDu[0])
					var paiementEncaisse =  $('#paiementTotal').text().split(" ")
					paiementEncaisse = parseFloat(paiementEncaisse[0])
					console.log("avoir=>",paiementEncaisse , totalDu,"arendre=>",rendu)
					if (res.clear === true || paiementEncaisse >= totalDu) {
						if(paiementTotal > totalDu){
							var argentArendre = paiementTotal - totalDu ;
							console.log("ARGENT A RENDRE=>"+argentArendre)
							$('#monnaieArendre').text(argentArendre.toFixed(2) + " €")
						}
						clearPanier(id_caisse,rendu,res.table)
						$('#paiementBoard').css('display','none')
					}
					else{
						Toast.fire({
							icon: 'success',
							title: "Paiement Validé" 
						})
						$(".totalProduit").load(location.href+" .totalProduit>*","");
						$('#modal-confirmation').modal('hide')
						$('#sousPanier').empty()
						$('.parent').remove()
						
					}
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite" 
					})
				}
			}
			
		});
	}
	// }
	
	
	
}







function clearPanier( id_caisse, rendu = false,idtable) {
	console.log("IDCAISSE=>"+id_caisse)
	$.ajax({
		url: "../panier/videPanier.php",
		type: "POST",
		contentType: "application/json",
		data: JSON.stringify({"clear": true, table: idtable, id_caisse: id_caisse}),
		success: function (data) {
			console.log(data)
			if (data == 1 ) {
				Toast.fire({
					icon: 'success',
					title: "Achat validé ! Ticket en cours d'impression..."
				})
				$('#totalPanier').text("0.00 €")
				$('#modal-confirmation').modal('hide')
				$("#panierContent").load(location.href + " #panierContent");
				// window.location.href = "restaurant.php"
				
			} 
			
			
		}
	})
	
	
	
}

function splitAddition(id_caisse,table,client){
	var nbpartage = $('#partagenb').val()
	var atLeastOneChecked = false

	$("input[type=checkbox]").each(function () {
		if ($(this).prop('checked')) {
			atLeastOneChecked = true;
		}
	});

	if(!atLeastOneChecked){
		$('input[type="checkbox"]').prop('checked', true);
		addPaiementMethod(id_caisse,nbpartage,true)
	}else{
		addPaiementMethod(id_caisse,nbpartage,false)
	}

	$('#modal-partage').modal('hide')


	$(".inputMontantPaiement").each(function () {
		// Store the initial value
		$(this).data("oldValue", $(this).val());
	});


	$(".inputMontantPaiement").on("input", function () {
		var oldValue = parseFloat($(this).data("oldValue"));
		var inputValue = parseFloat($(this).val());
		console.log(inputValue>oldValue,inputValue,oldValue)
		if (inputValue>oldValue) {
			// The input value is a number
			var arendre = inputValue - oldValue
			var parent = $(this).parent().parent()
			if($(this).next().is('p')){
				$(this).next().empty()
			}
			$(parent).append('<p style="font-size: 16px;font-weight: 600;margin-bottom: 0;padding: 2.5px 0.75rem;" >Monnaie a rendre <i class="fa fa-arrow-right"></i> <span style="color:red"> '+arendre.toFixed(2)+' €</span></p>')
			console.log($(this).next())
		} else {
			// The input value is not a number
			console.log("Input value is not a number");
		}
	});


}


function viderPanier(id_caisse,idtable){
	$.ajax({
		url: "../panier/videPanier.php",
		type: "POST",
		contentType: "application/json",
		data: JSON.stringify({
			"clear":true,
			"id_caisse":id_caisse,
			table:idtable
		}),
		success: function (data) {
			console.log(data)
			if (data == 1) {
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

// PARTAGE ADDITION MONNAIE A RENDRE

// AJOUTE UNE METHODE DE PAIEMENT SUPPLEMENTAIRE
function addPaiementMethod(id_caisse,nbpartage=null,allchecked=null){
	var resteAPayer =  $('#ResteAPayer').text().split(" ")
	resteAPayer = parseFloat(resteAPayer[0])
	var mtnPaiement = 0
	$('.inputMontantPaiement').each(function(index) {
		// if (index < $('.inputMontantPaiement').length - 1) {
		// 	mtnPaiement += parseFloat($(this).val());
		// }
		mtnPaiement += parseFloat($(this).val());
		
	});
	console.log(mtnPaiement,resteAPayer)
	var prochainMontant = Math.abs(mtnPaiement - resteAPayer)
	if(nbpartage!=null){
		var total =  $('#totalDu').text().split(" ")
		total = parseFloat(total[0])


		var mtnPartage = allchecked ? total / nbpartage : resteAPayer / nbpartage;
		mtnPartage = parseFloat(mtnPartage)
		var count = allchecked ? total : resteAPayer
		
		for (let index = 1; index < nbpartage; index++) {
			$('#paiementsBody').append(
				'<div class="row parent" style="margin-top:10px">'+
				'<div class="col-3">'+
				'<input type="hidden" id="partage" value="1" />' +
				'<input type="number" name="montantPaiement" id="champ-'+index+'" value="'+mtnPartage.toFixed(2)+'" onclick="this.select()" class="form-control inputMontantPaiement"  >'+
				'</div>'+
				'<div class="col-lg-7 col-xl-8">'+
				'<select class="form-control methodePaiement" name="methodePaiement">'+
				'<option>Espèce</option>'+
				'<option>Carte Bancaire</option>'+
				'<option>Chèque</option>'+
				'<option>Chèque Restaurant</option>'+
				'</select>'+
				'</div>'+
				'<div class="col-lg-1 col-xl-1 bg-danger" style="margin: auto;text-align: center;padding: 5px;cursor:pointer;">'+
				'<i class="fa fa-trash text-white " onclick="$(this).parent().parent().remove()"></i>'+
				'</div>'+
				'</div>'
				);
			count = count - mtnPartage;
		}
		var parentElement = $("#montantPaiement"); 
		parentElement.val(parseFloat(count).toFixed(2))
		$('#paiementTotal').text(total.toFixed(2))
	}else{
		$('#paiementsBody').append(
			'<div class="row parent" style="margin-top:10px">'+
			'<div class="col-3">'+
			'<input type="hidden" id="partage" value="0" />' +
			'<input type="number" name="montantPaiement" value="'+prochainMontant.toFixed(2)+'" onclick="this.select()" class="form-control inputMontantPaiement"  >'+
			'</div>'+
			'<div class="col-lg-7 col-xl-8">'+
			'<select class="form-control methodePaiement" name="methodePaiement">'+
			'<option>Espèce</option>'+
			'<option>Carte Bancaire</option>'+
			'<option>Chèque</option>'+
			'<option>Chèque Restaurant</option>'+
			'</select>'+
			'</div>'+
			'<div class="col-lg-1 col-xl-1 bg-danger" style="margin: auto;text-align: center;padding: 5px;cursor:pointer;">'+
			'<i class="fa fa-trash text-white " onclick="$(this).parent().parent().remove()"></i>'+
			'</div>'+
			'</div>'
			);
	}
	
	}
	
	// CALCUL DU TOTAL A PAYER A PARTIR DES REPAS CHOISI DANS LE PANIER
	// $(':checkbox').change(function() {
	// 	var totalChoix = 0
	// 	$('input[name="produitChoix[]"]:checked').each(function () {
	// 		var prixItem = $(this).val()
	// 		totalChoix+= parseFloat(prixItem)
	// 	});
	// 	console.log(totalChoix)
	// 	$('#inputMontantPaiement').val(totalChoix.toFixed(2))
	
	// });
	
	
	function updateQuantiteProduit(e,num,idtable,id_caisse,options=false){
		if (e.key === 'Enter' || e.keyCode === 13) {
			var newQte = $('#quantiteProduit-'+num).val()
			$.ajax({
				url:"ajax/updatePanierQte.php",
				type:"POST",
				contentType:"application/json",
				data: JSON.stringify({
					idtable:idtable,
					num:num,
					newQte:newQte,
					id_caisse:id_caisse,
					options:options
				}),
				success:function(response){
					console.log(response)
					var res = JSON.parse(response)
					if (res.response == 1) {
						location.reload(true);
						// $('#quantiteProduit-'+ref).val(res.newQte)
						// $("#prixProduit-"+ref).load(location.href +" #prixProduit-"+ref);
						// $("#qteLigne-"+ref).load(location.href +" #qteLigne-"+ref);
						// $("#totalPanier").load(location.href +" #totalPanier");
					}else{
						Toast.fire({
							icon: 'error',
							title: "Une erreur c'est produite"
						})
					}
				}
			})
		}
	}
	
	// CALCUL DU PAIEMENT TOTAL
	$("input[name='montantPaiement']").keyup( function() {
		var totalDu = $('#totalDu').text()
		totalDu = totalDu.split(" ")
		totalDu = parseFloat(totalDu[0])
		var montantPaiementEntre = 0;
		var count = 0;
		$('.inputMontantPaiement').each(function(){
			montantPaiementEntre = parseFloat($(this).val());
			count++;
		});
		
	});
	
	$(document).delegate("input[name='montantPaiement']","keyup", function() {
		var totalDu = $('#totalDu').text()
		totalDu = totalDu.split(" ")
		totalDu = parseFloat(totalDu[0])
		var count = 0;
		var montantPaiementEntre = 0;
		$('.inputMontantPaiement').each(function(){
			montantPaiementEntre = parseFloat($(this).val());
			count++;
		});
		
	});
	

	// CHANGE LE CSS DE LA CATEGORIE ACTIVE
	var Toast = Swal.mixin({
		toast: true,
		position: 'top-end',
		showConfirmButton: false,
		timer: 3000
	});
	
	
	// CLASSE ACTIVE SUR LA TABLE SEELECTIONNER
	function selectTable(element,id,numero,places){
		$.ajax({
			url:"requestRestaurant.php",
			type:"POST",
			contentType: "application/json",
			data: JSON.stringify({
				id_table: id,
				selectTable:true
			}),
			beforeSend:function(){
				$(element).parent().siblings().removeClass('tableStyle').addClass('current-table')
				$(element).parent().parent().siblings().children('.current-table').removeClass('current-table').addClass('tableStyle')
			},
			success:function(response){
				console.log(response)
				var res = JSON.parse(response)
				if (res.response == 1) {
					Toast.fire({
						icon: 'success',
						title: "La table " + numero + " a bien été selectionnée ! " 
					});
					$("#table_page").load(location.href + " #table_page");
					window.location = "restaurant.php"
					// $('#modal-tables').modal('hide')
					
				}else{
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite" 
					})
				}
			}
			
		});
	}
	
	$('#categoryList > a ').click(function(e){
		e.preventDefault()
		$('#categoryList > a.bg-dark').removeClass('bg-dark');
		$(this).addClass("bg-dark");
		var id = $(this).attr('id');
		id = id.split('-');
		id = id[1]

		var id_caisse = $('#idcaisseCat').val()
		
		$.ajax({
			url : "requestRestaurant.php",
			type : "POST",
			contentType: "application/json",
			data: JSON.stringify({
				id_categorie: id
			}),
			success:function(response){
				var result = JSON.parse(response)
				if (result.response === 1) {
					var categoriesArray = result.data
					var categories = "";
					categoriesArray.forEach(function(item) {
						var prix = item.prixttc_promo_euro > 0 ? item.prixttc_promo_euro : item.prixttc_euro
						var options = item.options != 1 ? 	'<div class="col-lg-2" onclick="addToCart(\''+item.titre.trim()+'\','+item.prixttc_euro+','+item.prixttc_promo_euro+','+id_caisse+','+item.num+',\''+item.img+'\',\''+item.id.trim()+'\',\''+item.ref.trim()+'\','+item.code_tva+','+item.cath+','+item.unite+','+item.qte_unite+')">' : '<div class="col-2" onclick="setOptions(\''+item.titre.trim()+'\','+item.num+','+id_caisse+')">';

						var img = item.img != "" ? '<div class="col-md-6 boxImgProduit"><img src="'+item.img+'" alt="..."></div><div class="col-lg-6">' : '<div class="col-lg-12">'
						categories += 

						options+						
						'<div class="card cardProduits" >'+
						'<div class="row no-gutters">'+
						img+
						'<div class="card-body">'+
						'<h5 class="card-title">'+item.titre+'</h5>'+
						'<p class="card-text text-success prixStyle">'+prix+'</p>'+
						'<span class="identifiant">'+item.ref+'</span>'+
						'</div>'+
						'</div>'+
						'</div>'+
						'</div>'+
						'</div>';
					}); 
					
					$('#listeProduits').html(categories)
				}else{
					Toast.fire({
						icon: 'error',
						title: result.data
					})
				}
			}
		})
	})
	
	function showModalPesage(titre,unite,qte_unite,num,prixttc_euro,prixttc_promo_euro,id_produit,ref,code_tva,famille,img){
		
		$('#modal-poids').modal("show")
		$('#modal-poids').on('shown.bs.modal', function() {
			$('#poidsProduit').select();
		})
		
		if (prixttc_promo_euro > 0) {
			var prix = prixttc_promo_euro
			$('#promoPesage').val(prixttc_promo_euro)
		}else{
			var prix = prixttc_euro
			$('#promoPesage').val(0)
		}
		$('#prixProduit').val(prix.toFixed(2))
		$('#modal_unite').val(unite)
		$('#modal_qte_unite').val(qte_unite)
		$('#titrePesage').val(titre)
		$('#idPesage').val(id_produit)
		$('#refPesage').val(ref)
		$('#imgPesage').val(img)
		$('#codetva_Pesage').val(code_tva)
		$('#famille_Pesage').val(famille)
		
		
		
	}

	function unlockOffrir() {
		$('.btn-offrir').prop('disabled', false);
	}
	
	// PRODUIT DIVERS
// PRODUIT DIVERS
function addProduitDiversSimple( idcaisse,qte) {
	localStorage.removeItem('monnaieArendre')
	console.log("IDCAISSE=>"+idcaisse,"QTE=>"+qte)
	var titre = "Supplément";
	var session = 1;
	var prix = 1;
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
			console.log(result)
            if (result.response === 1) {
                var produit = result.data
                var total = result.total
                $('#total').html(total.toFixed(2) + " €")
                prixDivers = parseFloat(prixDivers)
                $('#totalQte').text(result.qteTotal)
                $('#panierContent').prepend(
					'<div class="accordion" id="accordion-99">'+
					'<div  class="callout callout produit" id="product-99">'+
					'<div class="row">'+
					'<div class="col-1">'+
					'<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-99" aria-expanded="true" aria-controls="collapse-99">'+
						'<i class="fa-solid fa-chevron-down"></i>'+
					'</button>'+
					'</div>'+
					// '<div class="col-lg-1" style="margin:auto">'+
					// '<div class="input-group">'+
					// '<input type="checkbox" name="produitChoix[]" value="'+prixLigne+'" class="form-control ProduitChoix" style="height:16px">'+
					// '</div>'+
					// '</div>'+
					'<div class="col-4">'+
					'<p style="font-weight: 600;margin: 0;">Supplément</p>'+
					'</div>'+
					'<div class="col-lg-2" style="margin: auto;">'+
					'<input type="text" onclick="this.select()" class="qteProduit" style="width: 40px !important;" onkeypress="updateQuantiteProduit(event,'+produit.ref+',1,'+idcaisse+')" name="quantiteProduit" id="quantiteProduit-' + produit.ref + '" value="' + produit.qte + '" />'+
					'</div>'+
					'<div class="col-lg-2 offset-lg-1" style="margin: auto;">'+
					'<p class="text-muted" style="font-size:20px;margin-bottom: 0;">'+prix+' €</p>'+
					'</div>'+
					'<div class="col-lg-1" style="margin: auto;">'+
					'<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(\'99\','+idcaisse+',1)"></i>' +
					'</div>'+
					'</div>'+
					'<div id="collapse-99" class="collapse" style="width:100%;background:#ffffff" aria-labelledby="heading-99" data-parent="#accordion-99">'+
					'<div class="card-body" style="padding: 0 1.25rem;">'+
					'<div class="row" id="produitcard-'+produit.ref+'">'+
						'<div class="col-6" >'+
							'<button onclick="setRemiseUnique('+produit.qte+',\''+produit.num+'\',\''+titre+'\',1,'+produit.date+')"  class="btn btn-primary btn-block"  >Ajouter une remise <i class="fa fa-plus"></i></button>'+
						'</div>'+
					'</div>'+
					'</div>'+
					'</div>'+
					'</div>'+
					'</div>'
					);
               
                $('#caddie').children().eq(1).removeClass('active');
                $('#inputPrixDivers').val('')
                $('#inputQTEDivers').val('1')
                $('#inputTvaDivers').val('8.5')
                $('#modal-divers').modal('hide')
                // $("#searchArticle").load(location.href + " #searchArticle");
                $("#searchArticle").val("")
                $('#rendu').text("")
                //window.location.reload()
                
            }
        }

    })
}
	
	// AJOUT ARTICLE DANS PANIER
	function addToCart(titre,prix,prixPromo,id_caisse,num,img,id_produit,ref,code_tva,famille,unite,qte_unite,table,options=false){
		$('#bloc-arendre').html('')
		$.ajax({
			url : "requestRestaurant.php",
			type : "POST",
			contentType: "application/json",
			data: JSON.stringify({
				titre: titre,
				prixttc_euro:prix,
				prixPromo:prixPromo,
				id_caisse:id_caisse,
				num:num,
				img:img,
				id_produit:id_produit,
				ref:ref,
				code_tva:code_tva,
				famille:famille,
				addToCart:true,
				options:options,
			}),
			success:function(response){
				console.log(response)
				var result = JSON.parse(response)
				if (result.response === 1) {
					var produit = result.message
					var promo = prixPromo > 0 ? 1 : 0;
					var qte = produit.qte
					
					prix = parseFloat(produit.pu_euro)
					var qteLigne = promo == 1 ? promo.toFixed(2) +"€ x"+qte : prix.toFixed(2)+"€ x"+qte;
					var prixLigne = promo == 1 ? promo * qte : prix * qte;
					var promoLigne = promo == 1 ? "Remise de -"+ parseFloat(prix-promo).toFixed(2) : "" ;
					var image = img == "" ? '<div class="col-4 offset-2">' : '<div class="col-lg-2"></div><div class="col-lg-4">'
					$("#totalPanier").load(location.href + " #totalPanier");

					// Liste optionss choisi
					var optionsHTML = ""
					if(options.length>0 && options != 0){
						console.log(options)
						options.forEach(function(item) {
							optionsHTML += '<p class="text-muted" style="font-size:13px;margin-bottom: 0;margin-top: 5px;font-style:italic"> + '+capitalizeFirstLetter(item[1])+' - '+item[2]+'€</p>';
						});
					}
					ref = ref.replace(/\s+/g, '');

					// setTimeout(function() {
						$('#panierContent').prepend(
							'<div class="accordion" id="accordion-'+produit.num+'">'+
							'<div  class="callout callout produit" id="product-'+produit.num+'">'+
							'<div class="row">'+
							'<div class="col-1">'+
							'<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-'+produit.num+'" aria-expanded="true" aria-controls="collapse-'+produit.num+'">'+
								'<i class="fa-solid fa-chevron-down"></i>'+
							'</button>'+
							'</div>'+
							// '<div class="col-lg-1" style="margin:auto">'+
							// '<div class="input-group">'+
							// '<input type="checkbox" name="produitChoix[]" value="'+prixLigne+'" class="form-control ProduitChoix" style="height:16px">'+
							// '</div>'+
							// '</div>'+
							'<div class="col-5">'+
							'<p style="font-weight: 600;margin: 0;" class="produit_titre">'+titre+'</p>'+
							optionsHTML+
							'<p class="text-muted " style="margin-bottom: 0;" class="produit_prix" id="qteLigne-'+ref+'">'+qteLigne+'</p>'+
							'<small>'+promoLigne+'</small>'+
							'</div>'+
							'<div class="col-lg-2" style="margin: auto;">'+
							'<input type="text" onclick="this.select()" class="qteProduit" style="width: 40px !important;" onkeypress="updateQuantiteProduit(event,\''+produit.num+'\','+table+','+id_caisse+')" name="quantiteProduit" id="quantiteProduit-' + produit.num + '" value="' + produit.qte + '" />'+
							'</div>'+
							'<div class="col-lg-3 offset-lg-1" style="margin: auto;">'+
							'<p class="text-muted" style="font-size:20px;margin-bottom: 0;">'+prixLigne.toFixed(2)+' €</p>'+
							'</div>'+
							'<div class="col-lg-1" style="margin: auto;">'+
							'<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(\''+produit.num+'\','+id_caisse+','+table+')"></i>' +
							'</div>'+
							'</div>'+
							'<div id="collapse-'+produit.num+'" class="collapse" style="width:100%;background:#ffffff" aria-labelledby="heading-'+produit.num+' ?>" data-parent="#accordion-'+produit.num+'">'+
							'<div class="card-body" style="padding: 0 1.25rem;">'+
							'<div class="row" id="produitcard-'+ref+'">'+
								'<div class="col-6" >'+
							'<button onclick="offrirArticle(\''+titre+'\','+id_caisse+','+produit.num+')" disabled  class="btn btn-primary btn-block btn-offrir"  >Offrir <i class="fa fa-plus"></i></button>'+
						'</div>'+
							'</div>'+
							'</div>'+
							'</div>'+
							'</div>'+
							'</div>'
							);
					// }, 200);


					
						}else if(result.response === 2){
							var newQte = result.data;
							$("#totalPanier").load(location.href + " #totalPanier");
							$('#quantiteProduit-'+result.message).val(newQte);
							$('#qteLigne-'+ref).text(prix.toFixed(2)+"€ x"+newQte)
						}
						$("#panierContent").load(location.href + " #panierContent>*");
					}
			})
		}
		function offrirArticle(titre,id_caisse,num){
			$.ajax({
				url: "requestRestaurant.php",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({"offrirArticle": num, "session": 1, "id_caisse": id_caisse , "idtable" : 1,"titre" : titre}),
				success: function (data) {
					
					var result = JSON.parse(data)
					console.log(result)
					if (result.response !== 1) {
						
						// Recalcul le total du panier
						calculTotalPanier()
					} else {
						
						window.location.reload()
						
					}
				}
			})
		}
		function addToCartPesage(titre,prix,prixPromo,id_caisse,img,id_produit,ref,code_tva,famille,unite,qte_unite,poidsTotal){
			$.ajax({
				url : "requestRestaurant.php",
				type : "POST",
				contentType: "application/json",
				data: JSON.stringify({
					titre: titre,
					prixttc_euro:prix,
					prixPromo:prixPromo,
					id_caisse:id_caisse,
					img:img,
					id_produit:id_produit,
					ref:ref,
					code_tva:code_tva,
					famille:famille,
					poids:poidsTotal,
					addToCartPesage:true
				}),
				success:function(response){
					console.log(response)
					var result = JSON.parse(response)
					if (result.response === 1) {
						var produit = result.message
						var promo = prixPromo > 0 ? 1 : 0;
						var qte = produit.qte
						
						var uniteValeur = (unite == 1 ? "Kg" : (unite == 2 ?  "G" : 0))
						uniteValeur = "G"
						
						prix = parseFloat(prix)
						var qteLigne = promo == 1 ? promo.toFixed(2) +"€ x"+qte + " / " + poidsTotal +" "+uniteValeur : prix.toFixed(2)+"€ x"+qte+ " / " + poidsTotal +" "+uniteValeur ;
						var prixLigne = promo == 1 ? promo * qte : prix * qte;
						var promoLigne = promo == 1 ? "Remise de -"+ parseFloat(prix-promo).toFixed(2) : "" ;
						var image = img == "" ? '<div class="col-lg-6">' : '<div class="col-lg-2"><img src="'+img+'" class="img-produit-panier" width="50" height="50"></div><div class="col-lg-4">'
						
						$('#modal-poids').modal('hide')
						// $("#panierContent").load(location.href + " #panierContent");
						$("#totalPanier").load(location.href + " #totalPanier");
						
						

						$('#panierContent').append(
							'<div class="callout produit">'+
							'<div class="row">'+
							
							image+
							'<p style="font-weight: 600;margin: 0;">'+titre+'</p>'+
							'<p class="text-muted" style="font-size:17px;margin-bottom: 0;">'+qteLigne+'</p>'+
							'<small>'+promoLigne+'</small>'+
							'</div>'+
							'<div class="col-lg-2" style="margin: auto;">'+
							'<input type="text" onclick="this.select()" class="qteProduit" style="width: 40px !important;" name="quantiteProduit" id="quantiteProduit-' + ref + '" value="' + produit.qte + '" />'+
							'</div>'+
							'<div class="col-lg-2 offset-lg-1" style="margin: auto;">'+
							'<p class="text-muted" style="font-size:20px;margin-bottom: 0;">'+prixLigne.toFixed(2)+' €</p>'+
							'</div>'+
							'<div class="col-lg-1" style="margin: auto;">'+
							'<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle("'+ref+'",'+ id_caisse + ')"></i>' +
							'</div>'+
							'</div>'+
							'</div>'
							);
							$('#poidsProduit').val('')
							$('#poidsContenant').val('')
							$('#poidsTotal').text('0.00 g')
							$('#prixTotal').text('0.00 €')
						}else if(result.response === 2){
							$("#totalPanier").load(location.href + " #totalPanier");
							$("#panierContent").load(location.href + " #panierContent");
						}
					}
				})
			}
			
			
			$("#searchArticle").keyup(
				function () {
					delay(function () {
						var keyword = $("#searchArticle").val();
						var id_caisse = $('#id_caisse_search').val()
						var URL = encodeURI("search_result.php?q=" + keyword);
						$.ajax({
							url: URL,
							cache: false,
							type: "GET",
							success: function(response) {
								// console.log("ok",response)
								var result = JSON.parse(response)
								if (result.response === 1) {
									var produitsArray = result.produits
									var produits = "";
									produitsArray.forEach(function(item) {
										var prix = item.prixttc_promo_euro > 0 ? item.prixttc_promo_euro : item.prixttc_euro
										var img = item.img != "" ? '<div class="col-md-6 >"><img src="'+item.img+'" alt="..."></div><div class="col-lg-6">' : '<div class="col-lg-12">'
										var image = item.img == "" ? "null" : item.img;
										var unite = (item.unite === 1 ? 'KG' : (item.unite === 2 ? "L" : (item.unite === 3 ? 'M' : "")))
										produits += 
										'<div class="col-lg-3" onclick="addToCart(\''+item.titre.trim()+'\','+item.prixttc_euro+','+item.prixttc_promo_euro+','+id_caisse+','+item.num+',\''+image.trim()+'\',\''+item.id.trim()+'\',\''+item.ref.trim()+'\','+item.code_tva+','+item.cath+','+item.unite+','+item.qte_unite+')">'+
										'<div class="card cardProduits">'+
										'<div class="row no-gutters">'+
										img+
										'<div class="card-body">'+
										'<h5 class="card-title">'+item.titre+'</h5>'+
										'<p class="card-text text-success prixStyle">'+prix +' € '+unite+'</p>'+
										'<span class="badge bg-teal">En stock '+item.stock+'</span>'+
										'</div>'+
										'</div>'+
										'</div>'+
										'</div>'+
										'</div>';
									}); 
									
									$('#listeProduits').html(produits)
								}
							},
						});
					}, 0);
				}
				);

				function setRemiseUnique(qte,ref,titre,idtable,date){
					$('#afficherRemise').empty()
					var newQte = $('#quantiteProduit-'+ref).val()
					for (let index = 0; index < 1; index++) {

						$('#afficherRemise').append('<li class="list-group-item itemRemiseUnique"><p>'+capitalizeFirstLetter(titre)+'</p> <input type="number" name="remiseChoix" id="article-'+index+'" style="width:100px" class="form-control" value="0"></li>')
					}
					$('#afficherRemise').append('<input type="hidden" id="reference_'+ref+'" value="'+ref+'" />')
					$('#afficherRemise').append('<input type="hidden" id="date_'+date+'" value="'+date+'" />')

					$('#modal-remise-unique').modal('show')
				}	

				function setRemiseOnProduit(id_caisse){
					// var valueRemise = $('#remiseSurProduit-'+ref).val();
					var ref = $('[id^="reference_"]').val();
					var date = $('[id^="date_"]').val();
					
					var remiseArray = []
					$('input[name="remiseChoix"]').each(function() {
						var remise = $(this).val()
						remiseArray.push(remise)
					})
					console.log(remiseArray)
					$.ajax({
						url: "ajax/setRemiseSurProduit.php",
						type: "POST",
						contentType: "application/json",
						data: JSON.stringify({"setRemiseSurProduit": ref,"remiseArray":remiseArray,"id_caisse":id_caisse,'date':date}),
						success: function (data) {
							console.log(data)
							var result = JSON.parse(data)
							console.log(result)
							if (result.response !== 1) {
								Toast.fire({
									icon: 'error',
									title: result.message
								})
								// Recalcul le total du panier
							} else {
								
								Toast.fire({
									icon: 'success',
									title: "Remise ajouté sur le produit"
								})
								
								$('#modal_remise_unique').modal('hide')
								$('#prixProduit-'+ref).text(result.prix.toFixed(2))
								// $('#produitcard-'+ref).append('<div class="col-6">'+result.remiseText+'</div>')
								window.location.reload()
								
							}
						}
					})
				}
				
				function deleteArticle(id,  id_caisse, table) {
					
					$.ajax({
						url: "requestRestaurant.php",
						type: "POST",
						contentType: "application/json",
						data: JSON.stringify({"deleteArticle": id, "session": 1, "id_caisse": id_caisse , "idtable" : table}),
						success: function (data) {
							
							var result = JSON.parse(data)
							console.log(result)
							if (result.response !== 1) {
								Toast.fire({
									icon: 'error',
									title: result.message
								})
								// Recalcul le total du panier
								calculTotalPanier()
							} else {
								Toast.fire({
									icon: 'success',
									title: "Article supprimé du panier"
								})
								window.location.reload()
								
							}
						}
					})
				}
				
				
				// LIVE SEARCH DANS LE FICHIER JSON CATALOGUE

function capitalizeFirstLetter(string) {
	return string.charAt(0).toUpperCase() + string.slice(1);
}

function selectionOption(elm){
	if ($(elm).css("border-color") === "rgb(255, 0, 0)") {
		$(elm).css("border-color", "");
		$(elm).css("border-width", "");
	  } else {
		$(elm).css("border-color", "red");
		$(elm).css("border-width", "2px");
	  }
}

function setOptions(titre,identifiant,id_caisse){
	$.ajax({
		url: "ajax/showOptions.php",
		type: "POST",
		contentType: "application/json",
		data: JSON.stringify({"identifiant": identifiant}),
		success: function (data) {
			console.log(data)
			var result = JSON.parse(data)
			
			if (result.response == 1) {
				var options = result.data;

// Séparer l'option "700mL" des autres
var firstOption = options.find(item => item.nom === "700mL");
var otherOptions = options.filter(item => item.nom !== "700mL");

// Recréer la liste avec "700mL" en premier
var sortedOptions = [];
if (firstOption) sortedOptions.push(firstOption);
sortedOptions = sortedOptions.concat(otherOptions);

var listoption = "";
sortedOptions.forEach(function(item) {
	let highlightClass = "";
	let lowerNom = item.nom.toLowerCase();

	if (item.nom === "700mL") {
		highlightClass = " highlight-option";
	} else if (lowerNom.includes("fruit litchi")) {
		highlightClass = " fruit-litchi";
	} else if (lowerNom.includes("fruit mangue")) {
		highlightClass = " fruit-mangue";
	} else if (lowerNom.includes("fruit myrtille")) {
		highlightClass = " fruit-myrtille";
	} else if (lowerNom.includes("fruit passion")) {
		highlightClass = " fruit-passion";
	} else if (lowerNom.includes("fruit peche")) {
		highlightClass = " fruit-peche";
	} else if (lowerNom.includes("fruit fraise")) {
		highlightClass = " fruit-fraise";
	}

	listoption += '<div class="box-options' + highlightClass + '" id="option-' + item.idoption + '-' + item.nom + '-' + item.prix + '" onclick="selectionOption(this)"><span>' + capitalizeFirstLetter(item.nom) + '</span><br><span>' + item.prix + '€</span></div>';
});

				
				$('#list-option').html(listoption)
				$('#titreProduitOption').text(capitalizeFirstLetter(titre))
				$('#identifiantProduit').val(identifiant)
				$('#idcaisseProduit').val(id_caisse)
				$('#modal-options').modal('show')
			} else {
				Toast.fire({
					icon: 'error',
					title: "Pas d'option disponible pour cette article"
				})
				
			}
		}
	})

	
}

function enleverAccents(str) {
	return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  }

$("#confirmationOption").click(function(e){
	var identifiant = $('#identifiantProduit').val()
	var id_caisse = $('#idcaisseProduit').val()

	var optionSelectionnes = [];
	
	$(".box-options").each(function() {
        // Vérifier si l'élément a une bordure rouge
        if ($(this).css("border-color") === "rgb(255, 0, 0)") {
			var id = $(this).attr('id');
			id = id.split('-');
          	optionSelectionnes.push([id[1],enleverAccents(id[2]),id[3]]);
        }
      });
	  console.log(optionSelectionnes)
		$.ajax({
			url: "ajax/confirmationOption.php",
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({"identifiant": identifiant}),
			success: function (data) {
				var result = JSON.parse(data)
				
				if (result.response == 1) {
					var produit = result.data[0];
					// throw new error;
					addToCart(produit.titre.trim(),produit.prixttc_euro,produit.prixttc_promo_euro,id_caisse,produit.num,produit.img.trim(),produit.id.trim(),produit.ref.trim(),produit.code_tva,+produit.cath,produit.unite,+produit.qte_unite,0,optionSelectionnes);
					
	
					$('#modal-options').modal('hide')
				} else {
					Toast.fire({
						icon: 'error',
						title: "Une erreur c'est produite"
					})
					
				}
			}
		})
		
})
