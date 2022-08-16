<?php
session_start();
//if (!isset($_SESSION['loggedin'],$_SESSION['id_caisse'])) { //if login in session is not set
//    $checkIDcaisse = $_SESSION['id_caisse'];
//    $sql = "SELECT * FROM id_caisse_used "
//
//
//}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Caisse</title>
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
overflow: hidden;">
<!--<div class="container">-->
<div class="row" style="height:100vh;overflow: hidden">
    <div class="col-md-1" style="background-color: rgb(48, 52, 86);padding-right: 0;position: relative">
        <p class="text-center text-white"
           style="margin-top: 30px;font-size: 22px ;border-bottom: solid;
    border-color: #fff;
    border-width: 1px;
    padding-bottom: 25px;"><?php echo isset($_SESSION['id_caisse']) ? "Caisse n° " . $_SESSION['id_caisse'] : "" ?></p>
        <p onclick="window.location.href='cloture-caisse.php'" class="text-center text-white" style="margin-top: 50px;font-size: 16px;border-bottom: solid;
    border-color: #fff;
    border-width: 1px;
    padding-bottom: 50px;cursor:pointer;"><i
                    class="fas fa-cash-register fa-3x"></i></p>
        <p class="text-center" style="position:absolute;bottom:0;margin: auto;width: 95%">
            <a  class="btn btn-block btn-danger"   href="../login/logout.php?id_caisse=<?php echo $_SESSION['id_caisse'] ?>&userid=<?php echo $_SESSION['id'] ?>"
               >Déconnexion</a>
        </p>
    </div>
    <div class="col-md-8 " style="padding: 30px;">
        <!--				<form action="../searchProduit.php" id="formSearchProduit" >-->
        <div class="input-group" style="margin-bottom: 30px">
            <input type="search" class="form-control form-control-lg" id="searchArticle"
                   placeholder="Scanner un article" autofocus>
            <div class="input-group-append">
                <button type="submit" class="btn btn-lg btn-default">
                    <i class="fa fa-barcode"></i>
                </button>
            </div>
        </div>
        <!--				</form>-->

        <div id="produits" class="card"
             style="padding-bottom: 30px;background-color: rgb(242, 247, 251);height: 80%;overflow-y:auto">
            <div class="card-header" style="background-color: rgb(70, 130, 180);color: #ffffff">
                <div class="row">
                    <div class="col-md-<?php echo isset($_SESSION['session']) && $_SESSION['session'] > 1 ? "8" : "10" ?>">
                        <h2 style="font-size: 25px;" class="card-title">
                            <i class="fas fa-shopping-cart"></i>
                            CADDIE <?php echo isset($_SESSION['session']) ? $_SESSION['session'] : "" ?>

                        </h2>
                    </div>
                    <div class="col-md-2" id="btnClientSuivant"
                         style="display: <?php echo isset($_SESSION['session']) && $_SESSION['session'] > 1 ? "block" : "none" ?>">
                        <button type="button" class="btn btn-block bg-gradient-danger" onclick="clientPrecedent()">
                            Client Precedent
                        </button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-block bg-gradient-danger" onclick="clientSuivant()">Client
                            Suivant
                        </button>
                    </div>
                </div>
            </div>
            <div class="row" style="margin:15px 30px 0 30px;border-bottom: 1px solid lightgrey">
                <div class="col-md-4">
                    <p>Désignation</p>
                </div>
                <div class="col-md-2">
                    <p>QTE</p>
                </div>
                <div class="col-md-1">
                    <p>PU €</p>
                </div>
                <div class="col-md-1">
                    <p>Montant (€)</p>
                </div>
                <div class="col-md-2">
                    <p>Remise (%)</p>
                </div>
                <div class="col-md-2">
                    <p>Remise (€)</p>
                </div>

            </div>
            <div id="caddie">
                <?php
                $json = file_get_contents('../jsons/panier.json');
                $parsedJson = json_decode($json, true);
                include('../functions.php');
                if (count($parsedJson) > 0) {
                    $session = isset($_SESSION['session']) ? $_SESSION['session'] : "";
                    $id_caisse = isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : "";
                    foreach ($parsedJson as $article) {
                        if ($session == $article['session'] and $id_caisse == $article['id_caisse']) {

                            ?>
                            <div class="callout callout-info" style="margin:15px 30px 0 30px">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p style="font-family: " Tahoma"><?php echo strtoupper($article['titre']) ?>
                                        <i class="fa fa-trash text-red" style="cursor:pointer;"
                                           onclick="deleteArticle(this.id,'<?php echo $_SESSION['session'] ?>','<?php echo $_SESSION['id_caisse'] ?>')"
                                           id="deleteProduit-<?php echo $article['ref'] ?>"></i>
                                        <?php echo $article['remise'] > 0 ? "<span style='margin-left:15px;'> Remise de (<span class='text-danger' style='font-weight: 600'>" . $article['remise'] . "%</span>)</span>" : "" ?>
                                        <?php
                                        echo $article['promo'] > 0 ? "<br><span class='text-gray remise-caddie'>Remise de -" . formatNumber((float)$article['pu_euro'] - (float)$article['promo']) . " €</span>" : "";
                                        ?>
                                        <?php
                                        echo $article['remise_euro'] > 0 ? "<br><span class='text-gray remise-caddie'>Remise de -" . formatNumber((float)$article['remise_euro']) . " €</span>" : "";
                                        ?>
                                        </p>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" style="width: 50px;" name="quantiteProduit"
                                               onclick="this.select()"
                                               id="quantiteProduit-<?php echo $article['ref'] ?>"
                                               value="<?php echo $article['qte'] ?>"/>
                                    </div>
                                    <?php $article['pu_euro'] = $article['promo'] > 0 ? $article['promo'] : $article['pu_euro']; ?>

                                    <div class="col-md-1"><p
                                                id="pu_euro-<?php echo $article['ref'] ?>">
                                            <?php if ($article['retour'] == "false"): ?>
                                                <?php
                                                if ($article['remise'] > 0) {
                                                    echo formatNumber((float)$article['pu_euro'] - ((float)$article['pu_euro'] * ((float)$article['remise'] / 100)));
                                                } elseif ($article['remise_euro'] > 0) {
                                                    echo formatNumber($article['pu_euro'] - $article['remise_euro']);
                                                } elseif ($article['remise'] > 0 and $article['remise_euro'] > 0) {
                                                    echo formatNumber(((float)$article['pu_euro'] - ((float)$article['pu_euro'] * ((float)$article['remise'] / 100))) - ((float)$article['pu_euro'] - (float)$article['remise_euro']));
                                                } else {
                                                    echo formatNumber($article['pu_euro']);
                                                }
                                                ?>
                                            <?php else: ?>
                                                <?php echo $article['remise'] > 0 ? -formatNumber($article['pu_euro'] - ($article['pu_euro'] * ($article['remise'] / 100))) : -formatNumber($article['pu_euro']) ?>
                                            <?php endif; ?>

                                            €</p></div>
                                    <div class="col-md-1" id="montantEuro-<?php echo $article['ref'] ?>"><p
                                                class="montantEuro">
                                            <?php if ($article['retour'] == "false"): ?>
                                            <?php echo $article['remise'] > 0 ? formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : formatNumber($article['pu_euro'] * $article['qte']) ?>
                                            €</p>
                                        <?php else: ?>
                                            <?php echo $article['remise'] > 0 ? -formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : -formatNumber($article['pu_euro'] * $article['qte']) ?>€</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" style="width: 50px;" name="remiseProduit"
                                               onclick="this.select()" id="remiseProduit-<?php echo $article['ref'] ?>"
                                               value="<?php echo $article['remise'] ?>"/> %
                                    </div>

                                    <div class="col-md-2">
                                        <p id="remiseEuro-<?php echo $article['ref'] ?>">
                                            <input type="text" style="width: 50px;" name="remiseEuro"
                                                   onclick="this.select()"
                                                   id="remiseEuro-<?php echo $article['ref'] ?>-<?php echo $article['pu_euro'] ?>"
                                                   value="<?php echo $article['remise_euro'] ?>"/> €


                                        </p>
                                    </div>


                                </div>
                            </div>

                            <?php
                        }
                    }
                }

                ?>
            </div>
        </div>
    </div>
    <div class="col-md-3" style="padding:50px">
        <h3 style="font-family: 'Tahoma' ;font-weight: 600">TOTAL A PAYER</h3>
        <div class="card">
            <!--                <div class="card-header">-->
            <!--                    <h3 class="card-title">-->
            <!--                        <i class="fas fa-text-width"></i>-->
            <!--                        TOTAL A PAYER-->
            <!--                    </h3>-->
            <!--                </div>-->

            <div class="card-body" style="border:3px solid #ffffff ;border-radius:10px; background-color: rgb(0, 0, 139);color:#ffffff;
                        padding: 0 15px !important;">
                <p id="total" class="float-right" style="font-size: 50px;font-weight:800;font-family: 'Tahoma' ">
                    <?php
                    include('../DBConfig.php');
                    $session = isset($_SESSION['session']) ? $_SESSION['session'] : "";
                    $id_caisse = isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : "";
                    $sql = "SELECT pu_euro,qte,remise,ref FROM table_client_panier WHERE session = $session AND id_caisse = $id_caisse";
                    $query = $conn->query($sql);
                    $total = 0;
                    if ($query->num_rows > 0) {
                        while ($row = $query->fetch_assoc()) {
                            $pu_euro = $row['pu_euro'];
                            $qte = $row['qte'];
                            $remise = $row['remise'];
                            if($row['ref'] == 'remise'){
                                $total -= $pu_euro;
                            }else{
                                $total += $remise > 0 ? $pu_euro * $qte - ($pu_euro * $qte * ($remise / 100)) : $pu_euro * $qte;
                            }
                        }

                        echo number_format((float)$total, 2, '.', '') . "€";
                    } else {
                        echo "0.00€";
                    }
                    ?>
                </p>
            </div>

        </div>

        <div class="card">
            <!--                <div class="card-header">-->
            <!--                    <h3 class="card-title">-->
            <!--                        <i class="fas fa-text-width"></i>-->
            <!--                        TOTAL A PAYER-->
            <!--                    </h3>-->
            <!--                </div>-->

            <div class="card-body" style="border: 1px solid steelblue;border-radius: 10px">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                                data-target="#modal-espece" id="paiementEspece">
                            Espèces
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
                                style="background-color: " data-toggle="modal"
                                data-target="#modal-cb" id="paiementCB">
                            CB
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                                data-target="#modal-cheque" id="paiementCheque">
                            Chèques
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                                data-target="#modal-retour" id="retourArticle">Retour article
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                                data-target="#modal-divers" id="produitDivers">
                            Divers
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
                                onclick="viderPanier('<?php echo $_SESSION['id_caisse'] ?>')">Vider
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                                data-target="#modal-remise" >
                            Remise
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
                                onclick="totalCaisse('<?php echo $_SESSION['id_caisse'] ?>')">Total caisse
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


<!-- MODAL TOTAL CAISSE -->
<!--<div class="modal fade" id="modal-espece" style="display: none;" aria-hidden="true">-->
<!--    <div class="modal-dialog">-->
<!--        <div class="modal-content">-->
<!--            <div class="modal-header">-->
<!--                <h4 class="modal-title">Paiement Espèce</h4>-->
<!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
<!--                    <span aria-hidden="true">×</span>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="modal-body">-->
<!--                <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span-->
<!--                            style="font-weight: 600;" id="montantEspece"></span> € en espèce</p>-->
<!--                <div class="col-md-12 text-center inputPaiement">-->
<!--                    <input type="text" id="inputMontantEspece"-->
<!--                           style="width:100px;font-size:24px;"/> <span> €</span>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="modal-footer justify-content-between">-->
<!--                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>-->
<!--                <button type="button" class="btn btn-primary" id="btnPaiementEspece" onclick="paiementEspece()">Confirmer</button>-->
<!--            </div>-->
<!--        </div>-->
<!---->
<!--    </div>-->
<!--</div>-->
<!-- MODAL REMISE  -->

<div class="modal fade" id="modal-remise" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Remise sur le panier</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-paiement" style="font-size: 18px"> Entrez le montant de la remise en pourcentage</p>
                <div class="col-md-12 text-center">
                    <input type="text" id="inputMontantRemisePanier" style="width:100px;font-size:24px;" />
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnRemisePanier" onclick="remisePanier('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse']; ?>')">Confirmer
                </button>
            </div>
        </div>

    </div>
</div>


<!-- MODAL ESPECE -->
<div class="modal fade" id="modal-espece" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Paiement Espèce</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                            style="font-weight: 600;" id="montantEspece"></span> € en espèce</p>
                <div class="col-md-12 text-center inputPaiement">
                    <input type="text" id="inputMontantEspece"
                           style="width:100px;font-size:24px;"/> <span> €</span>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnPaiementEspece" onclick="paiementEspece()">
                    Confirmer
                </button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL CB  -->

<div class="modal fade" id="modal-cb" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Paiement Carte Bancaire</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                            style="font-weight: 600;" id="montantCB"></span> € en cb</p>
                <div class="col-md-12 text-center inputPaiement">
                    <input type="text" id="inputMontantCB"
                           style="width:100px;font-size:24px;"/> <span> €</span>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnPaiementCB" onclick="paiementCB()">Confirmer
                </button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL CHEQUES  -->

<div class="modal fade" id="modal-cheque" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Paiement Cheque</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-paiement" style="font-size: 18px"> Choisir le montant ou payer <span
                            style="font-weight: 600;" id="montantCheque"></span> € en cheque</p>
                <div class="col-md-12 text-center inputPaiement">
                    <input type="text" id="inputMontantCheque" onClick="this.select();"
                           style="width:100px;font-size:24px;"/> <span> €</span>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary">Confirmer</button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL DIVERS  -->

<div class="modal fade" id="modal-divers" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Produit Divers</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center text-paiement" style="font-size: 18px"> Article divers: entrez le PRIX en
                    EURO:</p>
                <div class="row text-center inputPaiement" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <input type="text" id="inputPrixDivers" onClick="this.select();" placeholder="Prix"
                               style="width:100px;font-size:24px;"/> <span> €</span>
                    </div>
                    <div class="col-md-4"><input type="text" id="inputQTEDivers" onClick="this.select();"
                                                 placeholder="Qte" value="1" style="width:100px;font-size:24px;"/></div>
                    <div class="col-md-4"><input type="text" id="inputTvaDivers" onClick="this.select();"
                                                 placeholder="TVA" value="8.5" style="width:100px;font-size:24px;"/>
                        <span> %</span></div>
                </div>
                <div class="col-md-12 text-center inputPaiement">

                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary"
                        onClick="addProduitDivers('<?php echo $_SESSION['session']; ?>','<?php echo isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : exit("Vous n'êtes pas connecté!") ?>')">
                    Confirmer
                </button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL RETOUR ARTICLE  -->

<div class="modal fade" id="modal-retour" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Retour article</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="retourDiv" style="padding:50px">
                <div class="row text-center" id="retourArticleChoix" style="margin-bottom: 15px">
                    <p class="text-paiement" style="font-size: 18px;margin: auto;"> Choisir retour <b>article divers</b>
                        ou <b>article avec codebarre.</b></p>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-light" id="showRetArticleDivers">Article Divers
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-block btn-dark" id="showRetArticleCatalogue">Article avec
                            codebarre
                        </button>
                    </div>
                </div>
                <div class="row text-center" id="retourArticleDivers" style="display: none">
                    <div class="col-md-12">
                        <p class="text-paiement" style="font-size: 18px;margin: auto;"> RETOUR ARTICLE DIVERS: </p>
                        <p style="padding:10px" class="text-danger" id="erreurRetArticleDivers"></p>
                    </div>
                    <div class="col-md-4">
                        <input type="text" id="inputRetourPrixDivers" required onClick="this.select();"
                               placeholder="Prix" style="width:100px;font-size:24px;"/> <span> €</span>
                    </div>
                    <div class="col-md-4"><input type="text" id="inputRetourQTEDivers" required onClick="this.select();"
                                                 placeholder="Qte" value="1" style="width:100px;font-size:24px;"/></div>
                    <div class="col-md-4"><input type="text" id="inputRetourTvaDivers" required onClick="this.select();"
                                                 placeholder="TVA" value="8.5" style="width:100px;font-size:24px;"/>
                        <span> %</span></div>
                </div>
                <div class="col-md-12 text-center " id="retourArticleCatalogue" style="display: none">
                    <p class="text-paiement" style="font-size: 18px;margin: auto;"> RETOUR ARTICLE: Saisissez le
                        codebarre de l'article: </p>
                    <input type="text" id="inputRetourArticleCatalogue"
                           onkeydown="retourArticleCatalogue('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',event);"
                           placeholder="Entrez codebarre"/>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary"
                        onclick="retourArticleDivers('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',event)">
                    Confirmer
                </button>
            </div>
        </div>

    </div>
</div>

<!-- MODAL NOUVEAU PRODUIT  -->

<div class="modal fade" id="modal-nouveau-produit" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Ajouter un article</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card card-primary">
                    <!--                    <div class="card-header">-->
                    <!--                        <h3 class="card-title">Quick Example</h3>-->
                    <!--                    </div>-->
                    <form action="../catalogue.php" id="formAjoutArticle">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Catégorie</label>
                                <select class="form-control" name="famille">
                                    <?php
                                    $sql = "SELECT *  FROM table_client_categorie";
                                    $categories = $conn->query($sql);
                                    if ($categories->num_rows > 0) {
                                        while ($categorie = $categories->fetch_assoc()) {
                                            ?>
                                            <option value="<?php echo $categorie['id_categorie'] ?>"><?php echo htmlspecialchars($categorie['nomcategorie'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php
                                        }
                                    }

                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Nom de l'article</label>
                                <input type="text" class="form-control" id="ajoutArticle" name="ajoutArticle">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taux_tva">Taux tva</label>
                                        <input type="text" class="form-control" onclick="this.select()" value="8.5"
                                               id="newTauxTva" name="newTauxTva">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taux_tva">Quantité</label>
                                        <input type="number" class="form-control" onclick="this.select()" value="1"
                                               id="newQte" name="newQte">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taux_tva">Prix Unitaire</label>
                                        <input type="number" class="form-control" value="0.00" onclick="this.select()"
                                               id="newPu" name="newPu">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="taux_tva">Prix Promo</label>
                                        <input type="number" class="form-control" value="0.00" onclick="this.select()"
                                               id="newPromo" name="newPromo">
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="newRef" id="newRef"/>
                            <input type="hidden" name="newSession" id="newSession"
                                   value="<?php echo $_SESSION['session'] ?>"/>
                            <input type="hidden" name="newIdcaisse" id="newIdcaisse"
                                   value="<?php echo $_SESSION['id_caisse'] ?>"/>
                            <!--                            <div class="form-check">-->
                            <!--                                <input type="checkbox" class="form-check-input" id="exampleCheck1">-->
                            <!--                                <label class="form-check-label" for="exampleCheck1">Check me out</label>-->
                            <!--                            </div>-->
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                <!--                <button type="button" class="btn btn-primary">Confirmer</button>-->
            </div>
        </div>

    </div>
</div>


<!--</div>-->
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
<script src="paiement.js"></script>

<script type="text/javascript">

    // CALCUL DU TOTAL PANIER
    // var textValues = $('.montantEuro').map((i, el) => el.innerText.trim()).get();
    // var total = 0
    // textValues.forEach(function (item, index) {
    //     var montant = item.split(' ')[0]
    //     montant = parseFloat(montant)
    //     total += montant
    // });
    // $('#total').html(total.toFixed(2) + "€")
    function remisePanier(session,idcaisse){
        var montantRemisePanier = $('#inputMontantRemisePanier').val();
        var totalPanier = $('#total').text()
        totalPanier =  totalPanier.replace(/\s/g, '').replace('€','');
        totalPanier = parseFloat(totalPanier)

        if(montantRemisePanier > 0){
            $.ajax({
                url: '../panier.php',
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    remisePanier: montantRemisePanier,
                    totalPanier:totalPanier,
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



    function clientSuivant() {
        var actuelSession = '<?php echo isset($_SESSION['session']) ? $_SESSION['session'] : exit("Vous n'êtes pas connecté ! ") ?>';
        var id_caisse = '<?php echo isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : exit("Vous n'êtes pas connecté ! ") ?>';

        $.ajax({
            url: "changeSession.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"clear": true, clientSuivant: actuelSession, id_caisse: id_caisse}),
            success: function (data) {
                var result = JSON.parse(data)
                console.log(result.data)
                if (result.response === 1) {
                    window.location.reload()
                }
            }
        })
    }

    function clientPrecedent() {
        var actuelSession = '<?php echo isset($_SESSION['session']) ? $_SESSION['session'] : exit("Vous n'êtes pas connecté ! ") ?>';
        var id_caisse = '<?php echo isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : exit("Vous n'êtes pas connecté ! ") ?>';
        $.ajax({
            url: "changeSession.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"clear": true, clientPrecedent: actuelSession, id_caisse: id_caisse}),
            success: function (data) {
                var result = JSON.parse(data)
                console.log(result.data)
                if (result.response === 1) {
                    window.location.reload()
                }
            }
        })
    }

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

    // VIDER LE PANIER
    function clearPanier(session, id_caisse, rendu = false) {
        $.ajax({
            url: "../panier/videPanier.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"clear": true, session: session, id_caisse: id_caisse}),
            success: function (data) {
                if (data == 1 && rendu == false) {
                    Toast.fire({
                        icon: 'success',
                        title: "Achat validé ! Ticket en cours d'impression..."
                    })
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                    $('#caddie').html("")

                } else {
                    Toast.fire({
                        icon: 'success',
                        title: "Achat validé ! Ticket en cours d'impression..."
                    })
                    $('#caddie').html("<h1>RENDU MONNAIE: " + monnaieArendre + "</h1>")

                }
            }
        })
    }


    $(document).ready(function () {

        $('#searchArticle').keydown(function (e) {
            // $('#print-button').css('display','none');
            if (e.which == 13) {
                var ref = $(this).val();
                var qte = 1
                var session = '<?php echo $_SESSION['session'] ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
                $.ajax({
                    url: "../searchProduit.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({"search": ref, "session": session, "id_caisse": id_caisse}),
                    success: function (data) {
                        console.log(data)
                        var result = JSON.parse(data)

                        if (result.result === 2) {
                            Toast.fire({
                                icon: 'error',
                                title: "Erreur code barre invalide "
                            })
                            $('#searchArticle').val('')
                        } else if (result.result === 1) {
                            $('#total').html(result.total.toFixed(2) + " €")
                            $('#searchArticle').val("")
                            // GENERE PRODUIT
                            var increment = false;
                            $("input[name='quantiteProduit']").each(function () {
                                var id = $(this).attr('id');
                                var refForm = id.split('-')[1]
                                if (refForm === result.data.ref) {
                                    increment = true;
                                    return false;
                                }
                            });

                            var remise = parseInt(result.data.remise) > 0 ? parseFloat(result.data.pu_euro) * parseFloat(result.data.qte) * (parseInt(result.data.remise) / 100) : "0.00€"
                            var promo = parseFloat(result.data.promo) > 0 ? parseFloat(result.data.pu_euro) - parseFloat(result.data.promo) : 0;
                            var promo = parseFloat(result.data.promo) > 0 ? "<br><span class='text-gray remise-caddie' >Remise de - <span > " + promo.toFixed(2) + " €</span></span>" : "";
                            var pu_euro = parseFloat(result.data.promo) > 0 ? parseFloat(result.data.promo) : parseFloat(result.data.pu_euro)
                            if (increment === false) {
                                $('#caddie').prepend(
                                    '<div class="callout callout-info" style="margin:15px 30px 0 30px">\n' +
                                    '<div class="row">' +
                                    '<div class="col-md-4">' +
                                    '<p>' +
                                    result.data.titre +
                                    '<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(this.id,' + session + ',' + id_caisse + ')" id="deleteProduit-' + result.data.ref + '"></i>' +
                                    promo +
                                    '</p>' +
                                    '</div>' +
                                    '<div class="col-md-2">' +
                                    '<input type="text" onclick="this.select()" style="width: 50px;" name="quantiteProduit" id="quantiteProduit-' + result.data.ref + '" value="' + result.data.qte + '" />' +
                                    '</div>' +
                                    '<div class="col-md-1">' +
                                    '<p>' +
                                    pu_euro.toFixed(2) +
                                    '€</p>' +
                                    '</div>' +
                                    '<div class="col-md-1">' +
                                    '<p>' +
                                    parseFloat(pu_euro) * parseFloat(result.data.qte) +
                                    '€</p>' +
                                    '</div>' +
                                    '<div class="col-md-2">' +
                                    '<input type="text" style="width: 50px;" onclick="this.select()" name="remiseProduit" id="remiseProduit-' + result.data.ref + '" value="' + result.data.remise + '" /><span>%</span>' +
                                    '</div>' +
                                    '<div class="col-md-2">' +
                                    '<p>' +
                                    '<input type="text" style="width: 50px;" onclick="this.select()" name="remiseEuro" id="remiseEuro-' + result.data.ref + '-'+result.data.pu_euro+'" value="' + result.data.remise_euro + '" /><span>€</span>' +
                                    '</p>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>'
                                )
                            } else {
                                var newQte = parseInt($('#quantiteProduit-' + result.data.ref).val())
                                var updatedQTE = parseInt(result.data.qte)
                                var actualMontantEuro = $('#montantEuro-' + result.data.ref).text()
                                actualMontantEuro = parseFloat(actualMontantEuro)
                                var newMontantEuro = parseFloat(pu_euro) * updatedQTE + actualMontantEuro;
                                $('#montantEuro-' + result.data.ref).html(newMontantEuro.toFixed(2) + "€")
                                $('#quantiteProduit-' + result.data.ref).val(newQte + updatedQTE)
                            }
                        } else if (result.result === 0) {

                            if (confirm(result.message) == true) {
                                $('#modal-nouveau-produit').modal('show')
                                $('#newRef').val(result.ref)
                            }


                        }

                    }
                })

            }
        });
    });

    function deleteArticle(id, session, id_caisse) {
        var ref = id.split('-')[1]

        $.ajax({
            url: "../panier.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"deleteArticle": ref, "session": session, "id_caisse": id_caisse}),
            success: function (data) {

                var result = JSON.parse(data)
                console.log(result)
                if (result.response !== 1) {
                    Toast.fire({
                        icon: 'error',
                        title: result.message
                    })
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

    function checkIfCaisseConnected() {
        var id_caisse ='<?php echo isset($_SESSION["session"]) ? $_SESSION["id_caisse"] : 0 ?>'
        $.ajax({
            url: "../login/checkIfConnected.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({"id_caisse": id_caisse, "action": "check"}),
            success: function (data) {
                var result = JSON.parse(data)
                // if (result.response === 0) {
                //     Toast.fire({
                //         icon: 'error',
                //         title: result.message
                //     })
                // }
            }
        })
    }
    $(document).ready(checkIfCaisseConnected); // Call on page load

    $("input[type=text][name=quantiteProduit]").on("keypress", function (e) {
        if (e.which == 13) {
            var newQte = $(this).val()
            var id = $(this).attr('id')
            var ref = id.split('-')[1]
            var session = '<?php echo $_SESSION['session'] ?>';
            var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
            $.ajax({
                url: "../panier.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({"updateQTE": newQte, "refQte": ref, "session": session, 'id_caisse': id_caisse}),
                success: function (data) {
                    window.location.reload()
                    // var qte = data;
                    // var pu_euro = $('#pu_euro-'+ref).text()
                    // pu_euro = parseFloat(pu_euro)
                    // qte = parseInt(qte)
                    // var montant = pu_euro * qte
                    // var remise = $('#remiseProduit-'+ref).val()
                    // var montantRemise = pu_euro * qte * (remise/100)
                    // $('#montantEuro-'+ref).html("<p>"+montant.toFixed(2)+" €</p>")
                    // $('#montantRemise-'+ref).html("<p>"+montantRemise.toFixed(2)+" €</p>")
                }
            })
        }


    });

    $(document).ajaxComplete(function () {

        $("input[type=text][name=remiseEuro]").on("keypress", function (e) {
            if (e.which == 13) {
                var newRemise = $(this).val()
                var id = $(this).attr('id')
                var ref = id.split('-')[1]
                var pu_euro = id.split('-')[2]
                var session = '<?php echo $_SESSION['session'] ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
                if ( parseFloat(pu_euro) >= parseFloat(newRemise)) {
                    $.ajax({
                        url: "../panier.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            "ajoutRemiseEuro": newRemise,
                            "refRemiseEuro": ref,
                            "session": session,
                            "id_caisse": id_caisse
                        }),
                        success: function (data) {
                            console.log(data)
                            window.location.reload()
                        }
                    })
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: "Attention ! La remise en euro ne pas être supérieur au PRIX UNITAIRE"
                    })
                }
            }


        });

        $("input[type=text][name=remiseProduit]").on("keypress", function (e) {
            if (e.which == 13) {
                var newRemise = $(this).val()
                var id = $(this).attr('id')
                var ref = id.split('-')[1]
                var session = '<?php echo $_SESSION['session'] ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
                $.ajax({
                    url: "../panier.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        "ajoutRemise": newRemise,
                        "refRemise": ref,
                        "session": session,
                        "id_caisse": id_caisse
                    }),
                    success: function (data) {
                        console.log(data)
                        window.location.reload()
                        // var remise = data;
                        // var pu_euro = $('#pu_euro-'+ref).text()
                        // var qte = $('#quantiteProduit-'+ref).val()
                        // pu_euro = parseFloat(pu_euro)
                        // remise = parseInt(remise)
                        // var montantRemise = pu_euro * qte * (remise / 100)
                        // $('#montantRemise-'+ref).html("<p>"+montantRemise.toFixed(2)+" €</p>")

                        // pu_euro = pu_euro - (pu_euro * (remise/100))
                        // montantEuro = pu_euro*qte;
                        // $('#pu_euro-'+ref).html("<p>"+pu_euro.toFixed(2)+"</p>")
                        // $('#montantEuro-'+ref).html("<p>"+montantEuro.toFixed(2)+"</p>")

                    }
                })
            }


        });

        $("input[type=text][name=quantiteProduit]").on("keypress", function (e) {
            if (e.which == 13) {
                var newQte = $(this).val()
                var id = $(this).attr('id')
                var ref = id.split('-')[1]
                var session = '<?php echo $_SESSION['session'] ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
                $.ajax({
                    url: "../panier.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        "updateQTE": newQte,
                        "refQte": ref,
                        "session": session,
                        'id_caisse': id_caisse
                    }),
                    success: function (data) {
                        window.location.reload()
                        // var qte = data;
                        // var pu_euro = $('#pu_euro-'+ref).text()
                        // pu_euro = parseFloat(pu_euro)
                        // qte = parseInt(qte)
                        // var montant = pu_euro * qte
                        // var remise = $('#remiseProduit-'+ref).val()
                        // var montantRemise = pu_euro * qte * (remise/100)
                        // $('#montantEuro-'+ref).html("<p>"+montant.toFixed(2)+" €</p>")
                        // $('#montantRemise-'+ref).html("<p>"+montantRemise.toFixed(2)+" €</p>")
                    }
                })
            }


        });
    })

    $("input[type=text][name=remiseEuro]").on("keypress", function (e) {
        if (e.which == 13) {
            var newRemise = $(this).val()
            var id = $(this).attr('id')
            var ref = id.split('-')[1]
            var pu_euro = id.split('-')[2]
            var session = '<?php echo $_SESSION['session'] ?>';
            var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
            if ( parseFloat(pu_euro) >= parseFloat(newRemise)) {
                $.ajax({
                    url: "../panier.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        "ajoutRemiseEuro": newRemise,
                        "refRemiseEuro": ref,
                        "session": session,
                        "id_caisse": id_caisse
                    }),
                    success: function (data) {
                        console.log(data)
                        window.location.reload()
                    }
                })
            } else {
                Toast.fire({
                    icon: 'error',
                    title: "Attention ! La remise en euro ne pas être supérieur au PRIX UNITAIRE"
                })
            }
        }


    });

    $("input[type=text][name=remiseProduit]").on("keypress", function (e) {
        if (e.which == 13) {
            var newRemise = $(this).val()
            var id = $(this).attr('id')
            var ref = id.split('-')[1]
            var session = '<?php echo $_SESSION['session'] ?>';
            var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
            $.ajax({
                url: "../panier.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    "ajoutRemise": newRemise,
                    "refRemise": ref,
                    "session": session,
                    "id_caisse": id_caisse
                }),
                success: function (data) {
                    console.log(data)
                    window.location.reload()
                    // var remise = data;
                    // var pu_euro = $('#pu_euro-'+ref).text()
                    // var qte = $('#quantiteProduit-'+ref).val()
                    // pu_euro = parseFloat(pu_euro)
                    // remise = parseInt(remise)
                    // var montantRemise = pu_euro * qte * (remise / 100)
                    // $('#montantRemise-'+ref).html("<p>"+montantRemise.toFixed(2)+" €</p>")

                    // pu_euro = pu_euro - (pu_euro * (remise/100))
                    // montantEuro = pu_euro*qte;
                    // $('#pu_euro-'+ref).html("<p>"+pu_euro.toFixed(2)+"</p>")
                    // $('#montantEuro-'+ref).html("<p>"+montantEuro.toFixed(2)+"</p>")

                }
            })
        }


    });

</script>
</html>
