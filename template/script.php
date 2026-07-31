<?php $client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : "" ?>
<script src="../lib/dist/js/jquery.slim.min.js" ></script> 
<script src="../lib/dist/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- <script src="../lib/dist/js/jquery.js"  ></script> -->
<script src="../lib/dist/plugins/moment/moment.min.js"></script>
<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../lib/dist/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../lib/dist/js/adminlte.min.js?v=3.2.0"></script>

<script src="../lib/label/BrowserPrint-3.0.216.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script type="text/javascript">



    function toggleCat(char,event){
        event.preventDefault()
        var id = "#bloc-"+char
        $(id).fadeToggle()
        if($(id).css('display') == 'none') {
            $('#icon-'+char).removeClass('fa-folder-open').addClass('fa-folder')
        }else{
            $('#icon-'+char).removeClass('fa-folder').addClass('fa-folder-open')
        }
    }
    if($(window).width()<=1024){ $('body').addClass('sidebar-collapse') } 
        var selected_device;
    function setup()
    {
        BrowserPrint.getDefaultDevice("printer", function(device)
        {
            selected_device = device;
            // var ele = document.getElementById("selected_device");
            // ele.innerHTML = device.name;
        }, function(error){
            // alert(error);
        });

    }

    <?php if( $client_id == 11 || $client_id == 35): ?>
        function writeToSelectedPrinter(dataToWrite,qte)
        {       
            dataToWrite = "^XA^CI27^PQ"+qte + dataToWrite;
            var host = "ws://localhost:8080/barcode";

            try{
                var socket = new WebSocket(host);
                console.log(socket)
                socket.addEventListener('error', function(event) {
                    console.error('Erreur de connexion WebSocket :', event);
                });

                socket.onopen = function(e){
                    console.log('Socket Status: '+socket.readyState+' (open)')
                    console.log(dataToWrite)
                    socket.send(dataToWrite);
                }
                socket.onmessage = function(msg){
                    console.log("MESSAGE",msg.data)
                }
                socket.onerror = function(event){
                    dataToWrite = "^XA^CI27^PQ"+qte + dataToWrite;
                    console.log(dataToWrite)
                    selected_device.send(dataToWrite, undefined, errorCallback);
                }
                socket.onclose = function(){
                    console.log('Socket Status: '+socket.readyState+' (closed)')
                } 
            }catch(exception){
                 dataToWrite = "^XA^CI27^PQ"+qte + dataToWrite;
            console.log(dataToWrite)
            selected_device.send(dataToWrite, undefined, errorCallback);
            }

            
            $('#searchArticle').focus();
        }
    <?php else: ?>
        function writeToSelectedPrinter(dataToWrite,qte)
        {       
            dataToWrite = "^XA^CI27^PQ"+qte + dataToWrite;
            console.log(dataToWrite)
            selected_device.send(dataToWrite, undefined, errorCallback);
        // dataToWrite ="";
        // qte=""
            $('#searchArticle').focus();
        }
    <?php endif; ?>

    
    var readCallback = function(readData) {
        if(readData === undefined || readData === null || readData === "")
        {
            alert("No Response from Device");
        }
        else
        {
            alert(readData);
        }

    }
    var errorCallback = function(errorMessage){
        alert("Error: " + errorMessage);    
    }
    function readFromSelectedPrinter()
    {

        selected_device.read(readCallback, errorCallback);

    }
    function getDeviceCallback(deviceList)
    {
        alert("Devices: \n" + JSON.stringify(deviceList, null, 4))
    }
    function selectDevice()
    {

    }
    function sendImage(imageUrl)
    {
        url = window.location.href.substring(0, window.location.href.lastIndexOf("/"));
        url = url + "/" + imageUrl;
        selected_device.sendUrl(url, undefined, errorCallback)
    }
    window.onload = setup;
</script>





<script src="../lib/dist/JsBarcode.ean-upc.min.js"></script>
<script type="text/javascript">
    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
    $('#select-all-search').click(function(event) {
        if(this.checked) {
            // Iterate each checkbox
            $(':checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $(':checkbox').each(function() {
                this.checked = false;
            });
        }
    });

     // CREATION D'UNE FAMILLE PAGE RECHERCHE D'ARTICLE
    $('#creerFamille').click(function(){
        var famille = $('#inputCreerFamille').val()
        $.ajax({
            url: "../admin/request.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"createFamille": famille}),
            success: function (data) {
                console.log(data)
                var result = JSON.parse(data)
                if (result.response !== 1) {
                    Toast.fire({
                        icon: 'error',
                        title: result.message
                    })
                } else {
                    Toast.fire({
                        icon: 'success',
                        title: result.message
                    })
                    setTimeout(() => window.location.reload(), 500);
                }
            }
        })
    })

    $('#btnAction').click(function () {
        var ids = []
        $('input[name="produitCheckbox[]"]:checked').each(function () {
            ids.push($(this).attr('id'))
        });
        var choix = $('#choixAction').val();
        console.log(choix)
        if(choix === "supp"){
            if(ids.length>0){
                if (confirm('Êtes vous sur de voulour supprimer ces articles ? ')) {
                    $.ajax({
                        url: "../admin/request.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({"deleteGroup": ids}),
                        success: function (data) {
                            console.log(data)
                            var result = JSON.parse(data)
                            if (result.response !== 1) {
                                Toast.fire({
                                    icon: 'error',
                                    title: result.message
                                })
                            } else {
                                Toast.fire({
                                    icon: 'success',
                                    title: result.message
                                })
                                setTimeout(() => window.location.reload(), 500);
                            }
                        }
                    })
                } else {
                    console.log('Rien');
                }
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Aucun produit n\'a été sélectionné'
                })
            }
        }else if(choix==='add_cat'){
            if(ids.length>0){
                $('#modal-categorie').modal('show');
                $('#btnAddGroupCat').click(function () {
                    var catID = $('#famille').val()
                    $.ajax({
                        url: "../admin/request.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({"addCatGroup": ids,'cat':catID}),
                        success: function (data) {
                            console.log(data)
                            var result = JSON.parse(data)
                            if (result.response !== 1) {
                                Toast.fire({
                                    icon: 'error',
                                    title: result.message
                                })
                            } else {
                                Toast.fire({
                                    icon: 'success',
                                    title: result.message
                                })
                                setTimeout(() => window.location.reload(), 500);
                            }
                        }
                    })
                })
            }else{
                Toast.fire({
                    icon: 'error',
                    title: 'Aucun produit n\'a été sélectionné'
                })
            }

        }else if(choix === "print"){
            if(ids.length>0){
                ids.forEach(function (item) {
                    var qte = $('#qteLabel-'+item).val();
                    var produitInfo = $('#produit-'+item).val()
                    console.log(qte)
                    console.log("PRODUIT INFO=>",$('#produit-'+item).val())

                    var split = produitInfo.split(',');
                    var barcode = split[0];
                    var titre = split[1];
                    var prix = split[2];
                    var package = split[3];
                    var promo = split[4];
                    var promoFin = split[5]
                    if (package == null || package == "") {
                        package = ""
                    }
                    $.ajax({
                        url: "../admin/request.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            printLabelGroup: qte,
                            titre:titre,
                            barcode:barcode,
                            prix:prix,
                            package:package,
                            promo:promo,
                            promoFin:promoFin
                        }),
                        success: function (data) {
                            var result = JSON.parse(data)
                            if(result.response === 1){
                                writeToSelectedPrinter(result.message,qte)
                            }else{
                                alert('Une erreur est survenue. Veuillez réesayer')
                            }
                        }
                    })
                });
            }
        }

    })





function deleteArticleAdmin(ref,redirect='false') {

    $.ajax({
        url: "../admin/request.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({"deleteArticleAdmin": ref}),
        success: function (data) {
            console.log(data)
            var result = JSON.parse(data)
            if (result.response !== 1) {
                Toast.fire({
                    icon: 'error',
                    title: result.message
                })
            } else {
                Toast.fire({
                    icon: 'success',
                    title: result.message
                })
                console.log(redirect)
                if(redirect=="true"){
                    setTimeout(() => window.location.href="articles.php", 500);
                }else{
                    setTimeout(() => window.location.reload(), 500);
                }


            }
        }
    })
}
function imprimeEtiquettes(gencode,titre,prix,colisage,promo){
    JsBarcode("#barcode2", gencode, {
        format:"EAN13",
        width:1.9,
        height:20,
        displayValue:true,
        fontSize:18,
        font:'monospace',
    });
    prix = prix.toFixed(2).toString();
    var splittedPrice = prix.split('.');
    var prixEntier = splittedPrice[0];
    var prixDecimal = splittedPrice[1];
    $('#titleEtiquette').html('') ;
    $('#prixEntier').text('');

    if(titre.length<6){
        $('#titleEtiquette').css('fontSize','26px')
    }
    if(titre.length>15 && titre.length<=20){
        $('#titleEtiquette').css('fontSize','22px')
    }
    if(titre.length>20 && titre.length<=28){
        $('#titleEtiquette').css('fontSize','20px')
    }
    if(titre.length>28){
        $('#titleEtiquette').css('fontSize','18px')
    }
    console.log(prix.length)
    if(prix.length==4){
        $('.price').css('left','23%')
    }
    if(prix.length==5){
        $('.price').css('left','20%')
    }
    if(prix.length==6){
        $('.price').css('left','18%')
    }

    $('#prixEntier').append(prixEntier).append('<span>.'+prixDecimal+'€</span>');
    if(colisage != ""){
        $('#titleEtiquette').append(titre).append('<span style="position:absolute;left:0;top:0px;font-size:18px;font-weight:normal;letter-spacing:0"><br>'+colisage+'</span>')
    }else{
        $('#titleEtiquette').append(titre);
    }
    window.print();
}


</script>

