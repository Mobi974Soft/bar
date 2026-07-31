<?php 
require '../vendor/autoload.php';

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

try {
    $connector = null;
    session_start();
    if (PHP_OS_FAMILY=="Windows") {
        include '../parametre.php';
        $connector = new WindowsPrintConnector($imprimante_nom);
        
    }else{
        $connector = new CupsPrintConnector($imprimante_nom);
        include '/var/www/localhost/caisse-backend/bar/parametre.php';
    }

    $printer = new Printer($connector);
    $printer->text("1");
    $printer->pulse();
    $printer->close();
} catch (Exception $e) {
    echo json_encode("Impossible d'imprimer sur cette imprimante: " . $e->getMessage() . "\n");
}
 ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cloture Caisse</title>
    <link rel="stylesheet" href="../lib/dist/plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="../lib/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="../lib/dist/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css"/>
    <link rel="stylesheet" href="../lib/dist/plugins/chart.js/Chart.min.css"/>
    <link rel="stylesheet" href="../lib/dist/css/adminlte.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../template/style.css"/>
</head>
<body style="background-color: rgb(242, 242, 242);
margin: 0;
">
<div class="container">
    <div class="row" style="width: 100%">
        <div class="card card-indigo" style="    width: 100%;">
            <div class="card-header">
                <h1 class="text-center" style="font-weight: 800;font-size: 30px">Calcul de caisse</h1>
            </div>
            <form>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 offset-1">
                            <h2 class="card-title" style="font-weight: 600">LES PIECES</h2><br>
                            <hr>
                            <div class="form-group">
                                <label for="cent2">1 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-1" name="calcul" onclick="this.select()" placeholder="1 cents">
                            </div>

                            <div class="form-group">
                                <label for="cent2">2 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-2" name="calcul"   onclick="this.select()"placeholder="2 cents">
                            </div>

                            <div class="form-group">
                                <label for="cent5">5 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-5" name="calcul"  onclick="this.select()" placeholder="5 cents">
                            </div>

                            <div class="form-group">
                                <label for="cent10">10 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-10"  onclick="this.select()" name="calcul"
                                placeholder="10 cents">
                            </div>

                            <div class="form-group">
                                <label for="cent20">20 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-20"  onclick="this.select()" name="calcul"
                                placeholder="20 cents">
                            </div>

                            <div class="form-group">
                                <label for="cent50">50 cents</label>
                                <input type="number" class="form-control" value="0" id="cent-50"  onclick="this.select()" name="calcul"
                                placeholder="50 cents">
                            </div>


                            <div class="form-group">
                                <label for="1euro">1 euro</label>
                                <input type="number" class="form-control" value="0" id="euro-1"  onclick="this.select()" name="calcul" placeholder="1 euro">
                            </div>

                            <div class="form-group">
                                <label for="2euro">2 euros</label>
                                <input type="number" class="form-control" value="0" id="euro-2"  onclick="this.select()" name="calcul" placeholder="2 euros">
                            </div>

                        </div>
                        <div class="col-md-4 offset-2"  >
                            <h2 class="card-title" style="font-weight: 600">LES BILLETS</h2><br>
                            <hr>
                            <div class="form-group">
                                <label for="5euros">5 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-5"  onclick="this.select()" name="calcul"
                                placeholder="5 euros">
                            </div>

                            <div class="form-group">
                                <label for="10euros">10 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-10"  onclick="this.select()" name="calcul"
                                placeholder="10 euros">
                            </div>

                            <div class="form-group">
                                <label for="20euros">20 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-20"   onclick="this.select()"name="calcul"
                                placeholder="20 euros">
                            </div>

                            <div class="form-group">
                                <label for="50euros">50 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-50"  onclick="this.select()" name="calcul"
                                placeholder="50 euros">
                            </div>

                            <div class="form-group">
                                <label for="100euros">100 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-100"   onclick="this.select()"name="calcul"
                                placeholder="100 euros">
                            </div>

                            <div class="form-group">
                                <label for="200euros">200 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-200" name="calcul"
                                placeholder="200 euros">
                            </div>


                            <div class="form-group">
                                <label for="500euros">500 euros</label>
                                <input type="number" class="form-control" value="0" id="euros-500"   onclick="this.select()"name="calcul"
                                placeholder="1 euro">
                            </div>


                        </div>

                    </div>


                </div>
                <hr>
                <div class="col-md-12" id="totalCalcul">
                    <h3 class="text-center" style="font-size: 26px;font-weight: 600;font-family: 'Tahoma'">TOTAL</h3>
                    <input type="text" class="form-control" id="calculTotal"  style="width: 200px;margin: 20px auto;text-align: center;font-size:24px;" />
                    <button type="button" id="btnPrintCaisse" class="btn btn-block btn-outline-success btn-lg" style="width: 300px;margin:20px auto">IMPRIMER LA PAGE</button>
                    <button type="button" onclick="history.back()" class="btn btn-block btn-outline-danger btn-lg" style="width: 300px;margin:20px auto">Retour</button>
                </div>
            </div>
        </form>
    </div>

</div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
crossorigin="anonymous"></script>
<script src="../lib/dist/js/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
crossorigin="anonymous"></script>
<script src="../lib/dist/plugins/moment/moment.min.js"></script>
<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../lib/dist/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../lib/dist/js/adminlte.min.js?v=3.2.0"></script>
<script src="https://parzibyte.github.io/plugin-ticket-js/Impresora.js"></script>
<script src="paiement.js"></script>
<script type="text/javascript">
    // $("#myTextBox").on("input", function() {
    //     alert($(this).val());
    // });
    // Impresora.getImpresoras()
    //             .then(listaDeImpresoras => {

    //                 var impresora = new Impresora();
    //                 let imprimante = "";
    //                 for (let i=0; i<listaDeImpresoras.length; i++) {
    //                     console.log(listaDeImpresoras[i])
    //                     if(listaDeImpresoras[i].search('EPSON TM') >= 0 || listaDeImpresoras[i].search('lp') >= 0 ){
    //                         imprimante = listaDeImpresoras[i]
    //                     }
    //                 }
    //                 impresora.setEmphasize(0)
    //                 // impresora.write("")
    //                 // impresora.feed(1)
    //                 // impresora.cut()
    //                 impresora.cash()
    //                 impresora.imprimirEnImpresora(imprimante)
    //                 .then(valor => {
    //                     console.log("Resultat: " + valor);


    //                 });
    //             });
    

    $("input[type=number][name=calcul]").on("input", function (e) {

        var sum = 0
        $('input[type=number][name=calcul]').each(function() {
            var id = $(this).attr('id')
            var montant = id.split('-')[1]
            var type = id.split('-')[0]
            var val = $(this).val()
            if(val > 0){
             if(type == "cent"){
                val = val/100
                sum += val * parseInt(montant)
            }else{
                sum += val * parseInt(montant)
            }
        }
    });
        $('#calculTotal').val(sum)
    })

    $('#btnPrintCaisse').click(function(){
        var ids = {};
        $('input[type=number][name=calcul]').each(function() {
            var id = $(this).attr('id')
            var val = $('#'+id).val()
            ids[id] = val
        });
        if(Object.keys(ids).length !== 0){
            var totalCaculCaisse = $('#calculTotal').val()
            $.ajax({
                url: "printCaisse.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    'printCaisse':ids,
                    'totalCaculCaisse':totalCaculCaisse
                }),
                success: function (data) {
                    var result = JSON.parse(data)
                    if(result.response === 1){
                        let nombreImpresora = "lp2";
                        var impresora = new Impresora()
                        impresora.setEmphasize(0);
                        impresora.setAlign("left")
                        impresora.write(result.message)
                        impresora.feed(2)
                        impresora.cut()
                        impresora.imprimirEnImpresora(nombreImpresora)
                        .then(valor => {
                            console.log("Resultat: " + valor);


                        });
                    }
                    

                }
            })
        }
    })
</script>
</html>
