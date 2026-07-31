<?php
include('../DBConfig.php');

if (isset($_POST['creerFamille'])) {
    $famille = htmlspecialchars($_POST['creerFamille']);
    if ($famille == "") {
        echo json_encode(array('response' => 0, 'message' => 'Champs vide'));
    }
    $categorie = $conn->query("SELECT * FROM table_client_categorie WHERE nomcategorie = '$famille' ");
    $nbligne = $categorie->num_rows;
    if ($nbligne == 0) {
        $insert = $conn->query("INSERT INTO table_client_categorie(nomcategorie,branche,id_parent) VALUES ('$famille',0,0) ");
        if ($insert) {
            $last_id = $conn->insert_id;
            $sql = "UPDATE table_client_categorie SET id_categorie=$last_id WHERE id = $last_id";
            $setCategorie = $conn->query($sql);
            if ($setCategorie) {
                echo json_encode(array('response' => 1, 'message' => 'La famille ' . $famille . ' a bien été créer ! '));
            }
        }
    } else {
        echo json_encode(array('response' => 0, 'message' => 'Cette famille existe déjà !'));
    }
    exit;
}
if (isset($_POST['famille']) && isset($_POST['designation'])) {
    $famille = $_POST['famille'];
    $designation = htmlspecialchars($_POST['designation']);
    $codetva = $_POST['codetva'];
    $quantite = (float) htmlspecialchars($_POST['quantite']);
    $unite = $_POST['unite'];
    $prix_variable = $_POST['prix_variable'];
    $marge = (float) htmlspecialchars($_POST['marge']);
    $mode_prix_3 = (float) htmlspecialchars($_POST['mode_prix_3']);
    $mode_prix_2 = (float) htmlspecialchars($_POST['mode_prix_2']);
    $mode_prix_1_achat = (float) htmlspecialchars($_POST['mode_prix_1_achat']);
    $mode = (int) substr($_POST['mode'], -1);
    $prix = (float) ($mode == 1 ? $mode_prix_1_achat : ($mode == 2 ? $mode_prix_2 : $mode_prix_3));
    $promottc = htmlspecialchars($_POST['promottc']);
    $promottc = (float) $promottc;
    $promo_debut = date('Y-m-d', strtotime($_POST['promo_debut']));
    $promo_fin = date('Y-m-d', strtotime($_POST['promo_fin']));
    $dateajout = date('Y-m-d H:i:s');

    


    
    if ($famille == 0) {
        echo json_encode(array('response' => 0, 'message' => 'Vous devez choisir une famille.', 'element' => 'famille'));
        exit;
    }
    if ($designation == '') {
        echo json_encode(array('response' => 0, 'message' => 'Vous devez indiquer un label article valide.', 'element' => 'designation'));
        exit;
    }
    if ($mode == 3) {
        if ($mode_prix_3 == 0 or $mode_prix_3 == '') {
            echo json_encode(array('response' => 3, 'message' => 'Vous devez indiquer un prix', 'type' => 'mode_prix_3_achat_ht'));
            exit;
        }
    }
    if ($mode == 2) {
        if ($mode_prix_2 == 0 or $mode_prix_2 == '') {
            echo json_encode(array('response' => 3, 'message' => 'Vous devez indiquer un prix', 'type' => 'mode_prix_2_achat_ht'));
            exit;
        }
    }
    if ($mode == 1) {
        if ($mode_prix_1_achat == 0 or $mode_prix_1_achat == '') {
            echo json_encode(array('response' => 3, 'message' => 'Vous devez indiquer un prix', 'type' => 'mode_prix_1_achat_ht'));
            exit;
        }
    }


    include('../functions.php');
    $designation = remove_accents($designation);
    $id_produit = random_strings(12);
    $gencode = $id_produit;
    $package = "";

    
        $sql = "INSERT INTO table_client_catalogue(`cath`,`id`,`ref`,`titre`,`prixttc_euro`,`prixttc_promo_euro`,`code_tva`,`promo_debut`,`promo_fin`,`choix_mode_prix`,`mode_prix_1_achat_ht`,`mode_prix_1_marge`,`mode_prix_2_fixe_ht`,`mode_prix_3_fixe_ttc`,`dateajout`,`datemodif`,`formule`,`stock`,`stock_alerte`,`unite`,`qte_unite`,`package`,`prix_variable`,`img`,`send_web`) 
		VALUES($famille,'$id_produit','$gencode','$designation',$prix,$promottc,$codetva,'$promo_debut','$promo_fin',$mode,$mode_prix_1_achat,$marge,$mode_prix_2,$mode_prix_3,'$dateajout','1000-01-01 00:00:00',1,0,99,$unite,$quantite,'$package',$prix_variable,'',1)";
    $ajoutArticle = $conn->query($sql);
    if ($ajoutArticle) {

        $produits = $_POST['produits'];
        foreach($produits as $produit){
            $sql = "INSERT INTO `articles_formules`(`id_formule`, `id_article`, `quantite`) VALUES ('$id_produits','$produit','1')";
            $conn->query($sql);
        }
        $sql = "UPDATE table_client_variable SET modif_serveur_ajout_catalogue = '$dateajout' WHERE num = 1";
        $updateSync = $conn->query($sql);
        if ($updateSync) {
            echo json_encode(array('response' => 1, 'message' => 'Nouvel article enregistré !', 'prix' => $prix));
            exit;
        }
    } else {
        echo json_encode(array('response' => 2, 'message' => 'Une erreur c\'est produite.', 'prix' => $sql));
        exit;
    }

    exit;
}



$title = 'Ajouter un article';
$page = 'Ajouter un article';
$accueil = 'index.php';
include('../template/header.php');


include('../functions.php');

// INFO PRODUIT
$barcode = $_GET['gencode'];


// Creation famille



?>
<style>
    #results {
        padding-left: 0;
        list-style: none;
        box-shadow: -1px 3px 0px 0px rgba(0, 0, 0, 0.55);
        -webkit-box-shadow: -1px 3px 0px 0px rgba(0, 0, 0, 0.55);
        -moz-box-shadow: -1px 3px 0px 0px rgba(0, 0, 0, 0.55);
    }
    #listeProduits{
        padding-left: 0;
    }
    @media print {

        .content-wrapper,
        footer {
            display: none;
        }

        @page {
            size: auto;
            margin: 0mm;
        }

        .etiquette {
            display: block;
            text-align: center;
        }

        svg {
            position: absolute;
            bottom: -5px;
            left: 10px;
        }

        .title {
            position: absolute;
            top: 0px;
            left: 14px;
            margin-top: 1px;
            font-weight: 800;
            font-size: 22px;
            text-transform: uppercase;
            font-family: "Tahoma";
            letter-spacing: -2px;
        }

        .price {
            position: absolute;
            top: 25%;
            left: 23%;
            margin-top: 0px;
            margin-left: 0px;
            font-weight: 800;
            font-size: 80px;

            font-family: "PT Sans monospace";
            letter-spacing: -5px;
        }

        .price span {
            font-size: 30px;
            letter-spacing: -2px;
            font-weight: 600;
            font-family: "Open Sans monospace";
        }


    }
</style>
<!--Etiquette template-->
<div class="etiquette" style="width: 200px;height: 140px;margin: auto">
    <div>
        <p id="titleEtiquette" class="title"></p>
        <p id="colisage"></p>
        <p id="prixEntier" class="price"><span id="prixDecimal"></span></p>
        <svg id="barcode2" jsbarcode-textmargin="1"></svg>
    </div>

</div>
<!--Fin etiquette template -->

<div class="content-wrapper" style="min-height: 823px;">
    <?php include('../template/info-page.php') ?>
    <div class="content">
        <div class="container">
            <div class="row ">

                <div class="col-lg-9 col-md-12 col-sm-12 mx-auto">
                    <!-- form user info -->
                    <form autocomplete="off" method="POST" class="form" role="form">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="mb-0">Identite formule</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Désignation</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" type="text" id="designation" name="designation">
                                    </div>
                                </div>


                                <!-- <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Stock</label>
                                    <div class="col-lg-2">
                                        <input class="form-control" type="text" value="0" id="stock_actuel" name="stock_actuel">
                                    </div>
                                </div> -->
                                <!-- <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Stock Alerte</label>
                                    <div class="col-lg-2">
                                        <input class="form-control" type="text" value="-99" name="stock_alerte" id="stock_alerte">
                                    </div>
                                </div> -->

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Code TVA</label>
                                    <div class="col-lg-2">
                                        <select class="form-control" size="0" name="code_tva" id="codetva">
                                            <option value="null">Choisir</option>
                                            <option value="0">0.0 % Exo</option>
                                            <option value="1">1.5 %</option>
                                            <option value="2">2.1 %</option>
                                            <option value="8" selected>8.5 %</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Reference</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" type="text" name="package" id="package" value="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Quantité + Unité</label>
                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <input class="form-control" type="number" name="qteUnite" id="quantite"
                                                    step="0.01" value="0.00">
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" id="unite" name="unite">
                                                    <option value="0">Désactivé</option>
                                                    <option value="1">Kg</option>
                                                    <option value="2">Litre</option>
                                                    <option value="3">Mètre</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Prix Variable</label>
                                    <div class="col-lg-3">
                                        <select class="form-control" name="prix_variable" id="prix_variable">
                                            <option value="0">Désactivé</option>
                                            <option value="1">Activé, en euros</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <h5 class="text-center">Produits inclus dans la formule</h5>
                                <div class="row mb-3">
                                    <div class="input-group ">
                                        <div class="col-10 mt-3" style="display:flex;margin:auto;">
                                            <input type="search" class="form-control form-control-lg input"
                                                id="searchProducts" placeholder="Rechercher un article" autofocus="">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-lg btn-default">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-10" style="margin: auto;">
                                        <ul id="results"></ul>
                                        <ul id="listeProduits"></ul>
                                    </div>
                                </div>










                                <!-- <div class="form-group row">
                                            <label class="col-lg-3 col-form-label form-control-label"></label>
                                            <div class="col-lg-9">
                                                <input class="btn btn-secondary" type="reset" value="Cancel"> 
                                                <input class="btn btn-primary" type="button" value="Save Changes">
                                            </div>
                                        </div> -->
                            </div>
                        </div>
                        <div class="margin"></div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="mb-0">Fiche prix normal</h3><br>
                                <p style="color:#FFFFFF">Choisissez le mode de calcul du prix de vente normal
                                    (hors-promo) de l'article.
                            </div>
                            <div class="card-body">

                                <div class="form-group row" style="margin-top: 30px;">
                                    <div class="col-lg-8">
                                        <div class="form-check">
                                            <input class="form-check-input position-static" type="radio" name="mode"
                                                id="mode1" aria-label="...">
                                            <label class="form-check-label" for="mode1"
                                                style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                À partir du prix € HT d'achat fournisseur et du taux de marge
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                            name="mode_prix_1_achat_ht" id="mode_prix_1_achat_ht" value="0.00"> <span
                                            style="font-weight: 600;">€ HT</span>
                                        <input type="text" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent" name="mode_prix_1_marge"
                                            id="mode_prix_1_marge" value="30.00"> <span
                                            style="font-weight: 600;">%</span>
                                    </div>
                                </div>
                                <hr style="margin-top: 1rem;
                                            margin-bottom: 1rem;
                                            border: 0;
                                            border-top: 1px solid rgba(0, 0, 0, 0.1);" />
                                <div class="form-group row" style="margin-top: 30px;">
                                    <div class="col-lg-8">
                                        <div class="form-check">
                                            <input class="form-check-input position-static" style="border: 1px solid #ced4da;
                                                        border-radius: 0.25rem;
                                                        box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                                type="radio" name="mode" id="mode2" aria-label="..."
                                                onclick="this.select()">
                                            <label class="form-check-label" for="mode1"
                                                style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                Fixée en € HT:
                                                <span style="font-weight:normal;font-size: 14px;">Le calcul € TTC se
                                                    fait automatiquement avec le code TVA</span>
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" name="mode_prix_2_achat_ht" id="mode_prix_2_achat_ht" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                            value="0.00" onclick="this.select()"> <span style="font-weight: 600;">€
                                            HT</span>
                                    </div>
                                </div>
                                <hr style="margin-top: 1rem;
                                            margin-bottom: 1rem;
                                            border: 0;
                                            border-top: 1px solid rgba(0, 0, 0, 0.1);" />
                                <div class="form-group row" style="margin-top: 30px;">
                                    <div class="col-lg-8">
                                        <div class="form-check">
                                            <input class="form-check-input position-static" style="border: 1px solid #ced4da;
                                                        border-radius: 0.25rem;
                                                        box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                                type="radio" name="mode" id="mode3" aria-label="..." checked
                                                onclick="this.select()">
                                            <label class="form-check-label" for="mode1"
                                                style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                Fixée en € TTC:
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" name="mode_prix_3_achat_ht" id="mode_prix_3_achat_ht"
                                            value="0.00" onclick="this.select()"> <span style="font-weight: 600;">€
                                            TTC</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="mb-0">FICHE PRIX PROMO (FALCULTATIF)</h3><br>
                                <p style="color:#ffffff">Remplissez ce formulaire si vous souhaitez programmer un prix
                                    promo.<br>Laisser le montant à 0€ pour ne pas programmer de promotion</p>
                            </div>
                            <div class="card-body">

                                <!-- <iframe src="test.php" style="display:none;" name="frame"></iframe>
                                                    <input type="button" onclick="frames['frame'].print()" value="printletter"> -->
                                <div class="row" style="justify-content: center;margin-top: 20px;">
                                    <label class="col-lg-12 col-form-label form-control-label text-center"
                                        style="font-size:20px">Prix <span style='color:red;'>PROMO TTC EURO</span> à
                                        appliquer:</label>
                                </div>
                                <div class="form-group row" style="justify-content: center;">
                                    <div class="col-lg-4">
                                        <input type="text" style="border: 1px solid #ced4da;
                                                            border-radius: 0.25rem;
                                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                            name="promottc" id="promottc" value="0.00"> <span
                                            style="font-weight: 600;font-size: 18px;color:red">€ TTC</span>
                                    </div>
                                </div>
                                <div class="row text-left">
                                    <div class="col-lg-6" style="    text-align: right;">
                                        <span style="font-weight:600">du</span> <input type="text" style="border: 1px solid #ced4da;
                                                            border-radius: 0.25rem;
                                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                            name="promo_debut" id="promo_debut"
                                            value="<?php echo date('d-m-Y', strtotime(' +1 day')) ?>" />
                                    </div>
                                    <div class="col-lg-6">
                                        <span style="font-weight:600">au</span> <input type="text" style="border: 1px solid #ced4da;
                                                            border-radius: 0.25rem;
                                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;"
                                            name="promo_fin" id="promo_fin"
                                            value="<?php echo date('d-m-Y', strtotime(' +8 day')) ?>" /> <span
                                            style="font-weight:600">inclus</span>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-primary">
                            <div class="card-header" style="text-align: center;">
                                <h3 class="mb-0">IMPRIMER ETIQUETTE</h3>
                            </div>
                            <div class="row" style="padding:20px 0">
                                <div class="col-md-3">
                                    <div style="padding: 20px"><i class="fa fa-print fa-2x"></i></div>
                                </div>
                                <div class="col-md-9">
                                    <span>Imprimer</span><input type="number" name="printNumber" value="1"
                                        id="printNumber" /><span>exemplaire(s)</span><br>
                                    <input type="checkbox" name="print_prix_normal" checked value="0.00"
                                        id="prixNormal"> Imprimer l'étiquette <span style="font-weight: 600;">prix
                                        normal</span><br>
                                    <input type="checkbox" name="print_prix_promo" id="prixPromo"> Imprimer l'étiquette
                                    <span style="font-weight: 600;">prix promo</span>


                                    <?php
                                    // $zpl = formatLabel($article['titre'],$article['mode_prix_3_fixe_ttc'],$article['ref'],$arrayPos,$article['package'],$article['prixttc_promo_euro']);
                                    
                                    ?>
                                    <!-- <button class="btn btn-dark btn-lg" type="button"  onclick="imprimeArticle()">Imprimer</button> -->

                                </div>
                            </div>
                        </div>

                        <div class="form-groupt row" style="padding: 30px 0;justify-content: space-around;">
                            <button type="button" onClick="window.location.href='articles.php';"
                                class="btn btn-danger btn-lg">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn"
                                name="save">Enregistrer</button>
                        </div>
                    </form>
                </div><!-- /form user info -->


            </div>
        </div>
    </div>
</div>
</div>


<!-- <div class="etiquette">
                <div class="col-md-8">
                    <h5>TITRE ARTICLE</h5>
                    <svg id="barcode2"

                    ></svg>
                </div>
                <div class="col-md-4">
                    <span></span>
                </div>
            </div> -->

<?php include('../template/footer.php') ?>
<?php include('../template/script.php') ?>
<script src="../lib/dist/JsBarcode.ean-upc.min.js"></script>

<script type="text/javascript">

    function addProductToFormule(num, titre, prix) {
        $('#listeProduits').append('<li class="list-group-item" id="p-'+num+'" >'+titre+' - '+prix.toFixed(2)+'€<span onclick="deleteProduit(this)" style="cursor:pointer;float: right;"><i class="fa fa-close" ></span></li>')
        $('#results').hide()
    
        
    }

    $("#searchProducts").focusin(function() {
        $("#results").show();
    })
   
    function deleteProduit(elm) {
        $(elm).parent().remove()
    }

    $(document).ready(function () {
        $('#searchProducts').on('keyup', function () {
            var searchText = $(this).val();
            if (searchText !== '') {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/searchProducts.php',
                    data: { query: searchText },
                    success: function (response) {
                        $('#results').html(response);
                    }
                });
            } else {
                $('#results').empty();
            }
        });

        $(document).on('click', '.delete', function () {
            var productId = $(this).data('id');
            $.ajax({
                type: 'POST',
                url: 'delete.php',
                data: { id: productId },
                success: function (response) {
                    // Actualiser les résultats après la suppression
                    $('#search').trigger('keyup');
                }
            });
        });
    });

    function imprimeArticle() {
        var type = "";
        var qte = $('#printNumber').val();
        if ($('#prixNormal').is(':checked')) {
            type = "normal"
        } else if ($('#prixPromo').is(':checked')) {
            type = "promo";
        }

        $.ajax({
            url: "request.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                imprimeArticle: type,
                titre: $('#designation').val(),
                barcode: $('#gencode').val(),
                prix: parseFloat($('#mode_prix_3_achat_ht').val()).toFixed(2),
                package: $('#package').val(),
                promo: $('#promottc').val(),
                promofin: $('#promo_fin').val(),
            }),
            success: function (data) {
                console.log(data)
                var result = JSON.parse(data)
                if (result.response === 1) {
                    writeToSelectedPrinter(result.message, qte)
                } else {
                    alert('Une erreur est survenue. Veuillez réesayer')
                }
            }
        })


    }

    $("#submitBtn").click(function (event) {




        event.preventDefault();

        var listeOFproducts = []
        $('#listeProduits li').each(function(index){
            var id = $(this).attr('id');
            id = id.split('-');
            num = id[1]
            listeOFproducts.push(num)
        });

        var famille = 16;
        var designation = $('#designation').val();
        
        var codetva = $('#codetva').val();
        
        var quantite = $('#quantite').val();
        var unite = $('#unite').val();
        var prix_variable = $('#prix_variable').val();
        var mode_choice = $('input[type=radio][name=mode]:checked').attr('id');
        var marge = $('#mode_prix_1_marge').val();
        var mode_prix_3 = $('#mode_prix_3_achat_ht').val();
        var mode_prix_2 = $('#mode_prix_2_achat_ht').val();
        var mode_prix_1_achat = $('#mode_prix_1_achat_ht').val();
        var promottc = $('#promottc').val();
        var promo_debut = $('#promo_debut').val();
        var promo_fin = $('#promo_fin').val();
        var submitData = {
            promo_debut: promo_debut,
            promo_fin: promo_fin,
            famille: famille,
            designation: designation,
            codetva: codetva,
            quantite: quantite,
            unite: unite,
            prix_variable: prix_variable,
            marge: marge,
            mode: mode_choice,
            mode_prix_3: mode_prix_3,
            mode_prix_2: mode_prix_2,
            mode_prix_1_achat: mode_prix_1_achat,
            promottc: promottc,
            produits:listeOFproducts
        };

        $.ajax({
            url: 'formules.php',
            type: "POST",
            data: submitData,
            beforeSend: function () {
                $('#submitBtn').attr('disabled', true).html("En cours...");
            },
            success: function (result, statusText, jqXHR) {
                // console.log("RESULT=>",result)
                var response = JSON.parse(result);
                var type = "";
                $('#submitBtn').attr("disabled", false).html("Enregistrer");
                
                if (response.response === 0 && response.element === 'designation') {
                    $('#designation').css('border-color', 'red');
                    document.getElementById("designation").scrollIntoView();
                    $("#designation").addClass("swalDefaultSuccess");
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    })
                }
                else if (response.response === 3) {
                    var type = response.type;
                    $('#' + type).css('border-color', 'red');
                    document.getElementById(type).scrollIntoView();
                    $('#' + type).addClass('swalDefaultSuccess');
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    })
                }
                else if (response.response === 1) {
                    // $('#'+type).addClass('swalDefaultSuccess');
                    console.log($('input[name=print_prix_normal]:checked'))
                    console.log($('input[name=print_prix_promo]:checked'))
                    if ($('#prixNormal').is(':checked')) {
                        // imprimeEtiquettes(gencode,designation,response.prix,package) 
                        type = "normal"
                        imprimeArticle()
                    }
                    else if ($('#prixPromo').is(':checked')) {
                        promottc = parseFloat(promottc)
                        // imprimeEtiquettes(gencode,designation,promottc,package) 	
                        type = "promo"
                        imprimeArticle()
                    }
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    })
                    window.setTimeout(function () {
                        window.location = "articles.php";
                    }, 1000);
                }
                else {
                    $("#creerFamille").addClass("swalDefaultError");
                    console.log(response.prix)
                    Toast.fire({
                        icon: 'error',
                        title: response.prix
                    })
                }

            }
        });
    });

    // JsBarcode("#barcode2", "9780199532179", {
    // 	format:"EAN13",
    // 	width:1.3,
    // 	height:30,
    // 	displayValue:true,
    // 	fontSize:13,
    // });
    // window.print();
    function toggleFamille() {
        var x = document.getElementById("familleBlock");
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
            x.style.display = "none";
        }
    }
    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });



    $('.famille input').on('keyup', function () {
        let empty = false;

        $('').each(function () {
            empty = $(this).val().length == 0;
        });

        if (empty)
            $('.famille button').attr('disabled', 'disabled');
        else
            $('.famille button').attr('disabled', false);
    });

    $("#creerFamille").click(function () {
        var famille = $('#inputFamille').val();
        console.log(famille)
        $.ajax({
            url: 'articleAjout.php',
            type: "POST",
            data: {
                creerFamille: famille
            },
            success: function (result) {

                var response = JSON.parse(result);
                console.log(response.response);
                if (response.response === 1) {
                    $("#creerFamille").addClass("swalDefaultSuccess");
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    })
                    $("#famille-select").load(location.href + " #famille-select");
                } else {
                    $("#creerFamille").addClass("swalDefaultError");
                    Toast.fire({
                        icon: 'error',
                        title: response.message
                    })
                }

            }
        });
    });
</script>

</body>

</html>