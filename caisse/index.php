<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include('../DBConfig.php');
if (!isset($_SESSION['loggedin'],$_SESSION['id_caisse'])) { //if login in session is not set
    header("Location: ../login/");
}

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
    <link rel="stylesheet" href="../template/style.css?random=<?php echo uniqid(); ?>"/>

</head>
<body class="bodyCaisse">
    <!--<div class="container">-->
        <div class="row" style="overflow: hidden;height: 100vh">
            <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 menus" style="background-color: rgb(48, 52, 86);padding-right: 0;position: relative">
                <p class="text-center text-white" class="caisseNumber">
                    <?php echo isset($_SESSION['id_caisse']) ? "Caisse n° " . $_SESSION['id_caisse'] : "" ?></p>
                    <p id="clotureCaisse" class="text-center text-white" style="margin-top: 50px;font-size: 16px;border-bottom: solid;
                    border-color: #fff;
                    border-width: 1px;
                    padding-bottom: 50px;cursor:pointer;">
                    <i class="fas fa-cash-register fa-2x"></i>
                </p>
                <p  class="text-center text-white" style="margin-top: 50px;font-size: 16px;border-bottom: solid;
                border-color: #fff;
                border-width: 1px;
                padding-bottom: 50px;cursor:pointer;"
                data-toggle="modal"
                data-target="#modal-facture"
                >
                <i class="fas fa-file-invoice fa-2x"></i>
            </p>
            <p  class="text-center text-white" style="margin-top: 50px;font-size: 16px;border-bottom: solid;
            border-color: #fff;
            border-width: 1px;
            padding-bottom: 50px;cursor:pointer;"
            data-toggle="modal"
            data-target="#modal-reimpression"
            >

            <i class="fa-solid fa-print fa-2x"></i>
        </p>

        <!-- modif -->
        <p  class="text-center text-white" style="margin-top: 50px;font-size: 16px;border-bottom: solid;
        border-color: #fff;
        border-width: 1px;
        padding-bottom: 50px;cursor:pointer;"
        data-toggle="modal"
        data-target="#modal-journaux"
        >

        <i class="fa-solid fa-newspaper fa-2x"></i><br>
        Journaux
    </p>

    <p class="text-center" style="width: 100%">
        <a  class="btn btn-block btn-danger"   href="../login/logout.php?id_caisse=<?php echo $_SESSION['id_caisse'] ?>&userid=<?php echo $_SESSION['id'] ?>"
         ><i class="fa fa-sign-out" aria-hidden="true"></i></a>
     </p>
 </div>
 <div class="col-sm-9 col-md-9 col-lg-9 col-xl-8 panierGrid" >
    <!--                <form action="../searchProduit.php" id="formSearchProduit" >-->
        <div class="input-group" style="margin-bottom: 30px">
            <input type="search" class="form-control form-control-lg input" id="searchArticle" 
            placeholder="Scanner un article" autofocus>
            <!-- <div id="livesearch"></div> -->
            <div class="input-group-append">
                <button type="submit" class="btn btn-lg btn-default">
                    <i class="fa fa-barcode"></i>
                </button>
            </div>
        </div>
        <!--                </form>-->

        <div id="produits" class="card" style="background-color: rgb(242, 247, 251);overflow-y:auto">
            <div class="card-header" style="background-color: rgb(70, 130, 180);color: #ffffff">
                <div class="row">
                    <div class="col-md-<?php echo isset($_SESSION['session']) && $_SESSION['session'] > 1 ? "6" : "9" ?>">
                        <h2 class="card-title caddie">
                            <i class="fas fa-shopping-cart"></i>
                            CADDIE <?php echo isset($_SESSION['session']) ? $_SESSION['session'] : "" ?>

                        </h2>
                    </div>
                    <div class="col-sm-4 col-md-3 col-lg-3 col-xl-3" id="btnClientSuivant"
                    style="display: <?php echo isset($_SESSION['session']) && $_SESSION['session'] > 1 ? "block" : "none" ?>">
                    <button type="button" class="btn btn-block bg-gradient-danger" onclick="clientPrecedent()">
                        Client Precedent
                    </button>
                </div>
                <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
                    <button type="button" class="btn btn-block bg-gradient-danger" onclick="clientSuivant()">Client
                        Suivant
                    </button>
                </div>
            </div>
        </div>
        <div class="row enteteCaddie" >
            <div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">
                <p class="entete">Désignation</p>
            </div>
            <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 enteteQte">
                <p class="entete">QTE
                    <?php 
                    $session = isset($_SESSION['session']) ? $_SESSION['session'] : "";
                    $id_caisse = isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : "";
                    $sql = "SELECT sum(qte) as sumQte FROM table_client_panier WHERE id_caisse = $id_caisse AND session = $session";
                    $query = $conn->query($sql);

                    $sumQte = $query->fetch_assoc()["sumQte"];
                    echo " : <span class='totalQte' id='totalQte'>".$sumQte."</span>";

                    ?>
                </p>
            </div>
            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <p class="entete">PU €</p>
            </div>
            <div class="col-sm-1 col-md-2 col-lg-2 col-xl-2">
                <p class="entete">Montant (€)</p>
            </div>
            <div class=" col-sm-1 col-md-2 col-lg-2 col-xl-2 enteteRemise">
                <p class="entete">Remise %</p>
            </div>
            <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 enteteRemise">
                <p class="entete">Remise €</p>
            </div>

        </div>
        <div id="caddie" style="overflow-y: scroll;">
            <?php
            $sql = "SELECT * FROM table_client_panier WHERE id_caisse = $id_caisse AND session = $session  ORDER BY date DESC";
            $panier = $conn->query($sql);
            include('../functions.php');
            if ($panier->num_rows > 0) {
                while ($article = $panier->fetch_assoc()) {
                    ?>
                    <div class="callout callout-info produit" id="article-<?php echo $article['ref'] ?>" >
                        <div class="row">
                            <div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">
                                <p class="designation"><?php echo strtoupper($article['titre']) ?>
                                <i class="fa fa-trash text-red" style="cursor:pointer;"
                                onclick="deleteArticle(this.id,'<?php echo $_SESSION['session'] ?>','<?php echo $_SESSION['id_caisse'] ?>')"
                                id="deleteProduit-<?php echo $article['ref'] ?>"></i>
                                <?php echo $article['remise'] > 0 ? "<br><span  class='text-gray  style='margin-left:15px;'> Remise de (<span class='text-danger' style='font-weight: 600'>" . $article['remise'] . "%</span>)</span>" : "" ?>
                                <?php
                                $prix_avec_remise = ($article['pu_euro'] * $article['qte']) - ($article['promo']*$article['qte']) - ($article['remise_euro'] * $article['qte']);
                                if($article['remise']>0){
                                    $prix_avec_remise -= $prix_avec_remise * ($prix_avec_remise/100);  
                                }
                                if($article['promo']>0 or $article['remise_euro']>0){
                                    if($prix_avec_remise>0){
                                     echo "<br><span class='text-gray remise-caddie'>Remise de " . formatNumber($prix_avec_remise) . " €</span>";      
                                 }else{
                                    echo "";
                                }

                            }else{
                                echo "";
                            }

                            ?>
                        </p>
                    </div>
                    <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 qteBox">
                        <input type="text" class="qteProduit"  name="quantiteProduit"
                        onclick="this.select()"
                        id="quantiteProduit-<?php echo $article['ref'] ?>"
                        value="<?php echo $article['qte'] ?>"/>
                    </div>
                    <?php $article['pu_euro'] = $article['promo'] > 0 ? $article['promo'] : $article['pu_euro']; ?>

                    <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2"><p class="puEuroProduit puEuroProduitUnique"
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
                    <div class="col-sm-1 col-md-2 col-lg-2 col-xl-2" id="montantEuro-<?php echo $article['ref'] ?>"><p
                       class="montantEuro puEuroProduit">
                       <?php if ($article['retour'] == "false"): ?>

                        <?php

                        if ($article['remise'] > 0) {
                            echo formatNumber((float)$article['pu_euro'] * $article['qte'] - ((float)$article['pu_euro'] * $article['qte'] * ((float)$article['remise'] / 100)));
                        } elseif ($article['remise_euro'] > 0) {
                            echo formatNumber(($article['pu_euro'] * $article['qte']) - ($article['remise_euro']) * $article['qte']) . " €";
                        } elseif ($article['remise'] > 0 and $article['remise_euro'] > 0) {
                            echo formatNumber(((float)$article['pu_euro']* $article['qte'] - ((float)$article['pu_euro'] * $article['qte'] * ((float)$article['remise'] / 100))) - ((float)$article['pu_euro'] * $article['qte'] - (float)$article['remise_euro'])). " €";
                        } else {
                            echo formatNumber($article['pu_euro']*$article['qte']) . " €";
                        }
                        ?>
                                            <!-- <?php echo $article['remise'] > 0 ? formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : formatNumber($article['pu_euro'] * $article['qte']) ?>
                                        €</p> -->
                                    <?php else: ?>
                                        <?php echo $article['remise'] > 0 ? -formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : -formatNumber($article['pu_euro'] * $article['qte']) ?>€</p>
                                    <?php endif; ?>
                                </div>
                                    <!-- <div class="col-md-1" id="montantEuro-<?php echo $article['ref'] ?>"><p
                                                class="montantEuro">
                                            <?php if ($article['retour'] == "false"): ?>
                                            <?php echo $article['remise'] > 0 ? formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : formatNumber($article['pu_euro'] * $article['qte']) ?>
                                            €</p>
                                        <?php else: ?>
                                            <?php echo $article['remise'] > 0 ? -formatNumber($article['pu_euro'] * $article['qte'] - ($article['pu_euro'] * $article['qte'] * ($article['remise'] / 100))) : -formatNumber($article['pu_euro'] * $article['qte']) ?>€</p>
                                        <?php endif; ?>
                                    </div> -->
                                    <div class="col-sm-1 col-md-2 col-lg-2 col-xl-2 remisePourcent">
                                        <input type="text" class="inputRemise"  name="remiseProduit"
                                        onclick="this.select()" id="remiseProduit-<?php echo $article['ref'] ?>"
                                        value="<?php echo $article['remise'] ?>"/> %
                                    </div>

                                    <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">
                                        <p  id="remiseEuro-<?php echo $article['ref'] ?>">
                                            <input type="text" class="inputRemise"  name="remiseEuro"
                                            onclick="this.select()"
                                            id="remiseEuro-<?php echo $article['ref'] ?>-<?php echo $article['pu_euro'] ?>"
                                            value="<?php echo $article['remise_euro'] ?>"/> <span class="euroSymbol">€</span>


                                        </p>
                                    </div>


                                </div>
                            </div>

                            <?php
                        }
                    }

                    ?>

                </div>
            </div>
        </div>
        <div class="col-sm-2 col-md-3 col-lg-3 col-xl-3 totalCaddie" >
            <h3 class="titreTotal">TOTAL A PAYER</h3>
            <div class="card">
                <!--                <div class="card-header">-->
                    <!--                    <h3 class="card-title">-->
                        <!--                        <i class="fas fa-text-width"></i>-->
                        <!--                        TOTAL A PAYER-->
                        <!--                    </h3>-->
                        <!--                </div>-->

                        <div class="card-body" style="border:3px solid #ffffff ;border-radius:10px; background-color: rgb(0, 0, 139);color:#ffffff;
                        padding: 0 15px !important;">
                        <p id="total" class="float-right montant_total">
                            <?php

                            $session = isset($_SESSION['session']) ? $_SESSION['session'] : "";
                            $id_caisse = isset($_SESSION['id_caisse']) ? $_SESSION['id_caisse'] : "";
                            $sql = "SELECT pu_euro,qte,remise,ref,remise_euro,retour,promo FROM table_client_panier WHERE session = $session AND id_caisse = $id_caisse";
                            $query = $conn->query($sql);
                            $total = 0;
                            if ($query->num_rows > 0) {
                                while ($row = $query->fetch_assoc()) {
                                    $pu_euro = $row['pu_euro'];
                                    $qte = $row['qte'];
                                    $remise = $row['remise'];
                                    $remise_euro = $row['remise_euro'];
                                    $promo = $row['promo'];
                                    if($row['ref'] == 'remise'){
                                        $total -= $pu_euro;
                                    }
                                    elseif($row['retour'] != "false"){
                                        if($row['promo']>0){
                                            $total += $row['promo'] *  - $row['qte'];
                                        }else{
                                            $total -= $pu_euro *$qte;
                                        }
                                    }
                                    else{
                                        if($promo>0){
                                            if($remise>0){
                                                $total +=  $promo * $qte - ($promo * $qte * ($remise / 100));
                                            }
                                            elseif($remise_euro > 0 ){
                                                $total += $promo * $qte - $remise_euro*$qte;
                                            }
                                            else{
                                                $total += $promo * $qte;
                                            }
                                        }else{
                                            if($remise>0){
                                                $total +=  $pu_euro * $qte - ($pu_euro * $qte * ($remise / 100));
                                            }
                                            elseif($remise_euro > 0 ){
                                                $total += $pu_euro * $qte - $remise_euro*$qte;

                                            }
                                            else{
                                                $total += $pu_euro * $qte;
                                            }
                                        }

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
                <div>

                </div>
                <div class="card">

                    <div class="card-body" style="border: 1px solid steelblue;border-radius: 10px">
                        <div class="row">
                            <div class="col-md-6  btnLeft"  >
                                <button type="button" class="btn btn-block btn-danger btn-lg btnCaisse"
                                onclick="viderPanier('<?php echo $_SESSION['id_caisse'] ?>')">Vider
                            </button>
                        </div>

                        <div class="col-md-6  btnRight">
                            <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse"
                            data-toggle="modal"
                            data-target="#modal-cb" id="paiementCB">
                            CB
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6  btnLeft" >
                        <button type="button" style="color: white;" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
                        data-target="#modal-cheque" id="paiementCheque">
                        Chèques
                    </button>
                </div>


                <div class="col-md-6 btnRight">
                    <button type="button" class="btn btn-block btn-success btn-lg btnCaisse" data-toggle="modal"
                    data-target="#modal-espece" id="paiementEspece">
                    Espèces
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 btnLeft" >
               <button type="button" class="btn btn-block btn-dark btn-lg btnCaisse"
               onclick="totalCaisse('<?php echo $_SESSION['id_caisse'] ?>')">Total caisse
           </button>
       </div>
       <div class="col-md-6 btnRight" >
        <button type="button"  class="btn btn-block btn-primary btn-lg btnCaisse " data-toggle="modal"
        data-target="#modal-retour" id="retourArticle" >Retour article
    </button>
</div>

</div>

<div class="row">
    <div class="col-md-6 btnLeft" >
        <button type="button" class="btn btn-block btn-primary btn-lg btnCaisse" data-toggle="modal"
        data-target="#modal-remise" >
        Remise
    </button>
</div>
<div class="col-md-6 btnRight" >
 <button type="button"  style="background-color:orange !important;color:white;border:1px solid orange; " class="btn btn-block  btn-lg btnCaisse " data-toggle="modal"
 data-target="#modal-divers" id="produitDivers">
 Divers
</button>

</div>
</div>

</div>



</div>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
       Touches
   </div>
   <!-- /.card-header -->
   <div class="card-body">
    <dl class="row">
      <dt class="col-sm-4 text-danger">F12</dt>
      <dd class="col-sm-8">Espèce</dd>
      <dt class="col-sm-4 text-danger">F9</dt>
      <dd class="col-sm-8">Carte Bancaire</dd>
      <dt class="col-sm-4 text-danger">F7</dt>
      <dd class="col-sm-8">Chèques</dd>
      <dt class="col-sm-4 text-danger">F8</dt>
      <dd class="col-sm-8">Divers</dd>
      <!-- <dt class="col-sm-4 text-danger">F9</dt>
          <dd class="col-sm-8">Retour Article</dd> -->
      </dl>
  </div>


  <!-- /.card-body -->
</div>
</div>
</div>
<div class="row menusBottom" style="background-color: rgb(48, 52, 86);">
    <div class="col-md-2 col-md-2 borderBtn"  >
        <p class="text-center text-white"  >
            <?php echo isset($_SESSION['id_caisse']) ? "Caisse n° " . $_SESSION['id_caisse'] : "" ?>
        </p>
    </div>
    <div class="col-md-2 col-lg-2 borderBtn"  >
        <p id="clotureCaisseBottom" class="text-center text-white" style="font-size: 16px;margin: auto;
        cursor:pointer;">

        <i class="fas fa-cash-register fa-1x"></i>
    </p>
</div>
<div class="col-md-2 col-lg-2 borderBtn"  >
    <p  class="text-center text-white" style="font-size: 16px;margin: auto;
    cursor:pointer;"
    data-toggle="modal"
    data-target="#modal-facture"
    >
    <i class="fas fa-file-invoice fa-1x"></i>
</p>
</div>
<div class="col-md-2 col-lg-2 borderBtn" >
    <p  class="text-center text-white" style="font-size: 16px;cursor:pointer;margin: auto;"
    data-toggle="modal"
    data-target="#modal-reimpression"
    >

    <i class="fa-solid fa-print fa-1x"></i>
</p>
</div>
<div class="col-md-2 col-lg-2 " >

</div>
<div class="col-md-2 col-lg-2" style="margin:auto;padding: 10px 0">
    <p class="text-center" style="width: 90%;
    margin: 0;">
    <a  class="btn btn-block btn-danger"   href="../login/logout.php?id_caisse=<?php echo $_SESSION['id_caisse'] ?>&userid=<?php echo $_SESSION['id'] ?>"
       ><i class="fa fa-sign-out" aria-hidden="true"></i></a>
   </p>
</div>
    <!-- <div class="col-sm-12 col-md-12 col-lg-12 " >
    </div> -->
</div>






<!-- MODAL REIMPRESSION -->
<div class="modal fade" id="modal-reimpression" style="display: none;" aria-hidden="true" >
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
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                <button class="btn btn-dark" onclick="printLastTicket('<?php echo $_SESSION['id_caisse'] ?>')">Dernier ticket</button>
                <button type="button" class="btn btn-primary" onClick="reimpressionTicket()">
                    Confirmer
                </button>
            </div>
        </div>


    </div>
</div>

<!-- MODAL FACTURE -->
<div class="modal fade" id="modal-facture" style="display: none;" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">IMPRIMER UNE FACTURE</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Entrez n° du ticket</label>
                    <input type="text" class="form-control" id="inputNumeroTicket" style="font-size:24px;" />
                </div>
                <div class="form-group">
                    <label>Entrez nom du client</label>
                    <input type="text" class="form-control" id="inputNomClient" style="font-size:24px;" />
                </div>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.location.reload()">Annuler</button>
                <button type="button" class="btn btn-primary" onClick="imprimeFacture()">
                    Confirmer
                </button>
            </div>
        </div>


    </div>
</div>


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
                    <input type="text"  id="inputMontantRemisePanier" style="width:100px;font-size:24px;" />
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
                                 <input type="hidden" id="diversSession" value="<?php echo $_SESSION['session']; ?>" />
                                 <input type="hidden" id="diversIDCAISSE" value="<?php echo $_SESSION['id_caisse']; ?>" />
                                 <div class="col-md-12 text-center inputPaiement">

                                 </div>
                             </div>
                             <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                                    <button type="button" class="btn btn-primary"
                                    onclick="addProduitDivers('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>')">
                                    Confirmer
                                </button>
                                <button type="button" class="btn btn-primary"  style="display: none;"
                                onclick="window.location.reload()">
                                Confirmer
                            </button>

                        </div>
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
                                    <input type="text" id="inputRetourPrixDivers" autofocus required onClick="this.select();"
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
                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="$('#retourArticleChoix').show();$('#retourArticleCatalogue').hide();$('#retourArticleDivers').hide();">Annuler</button>
                                <button type="button" class="btn btn-primary" id="btnRetDivers"
                                onclick="retourArticleDivers('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>',event)">
                                Confirmer
                            </button>
                            <button type="button" class="btn btn-primary" id="btnRetCatalogue" style="display: none;"
                            onclick="retourArticleCatalogue('<?php echo $_SESSION['session'] ?>','<?php echo $_SESSION['id_caisse'] ?>',event)">
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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="taux_tva">Taux tva</label>
                                                    <input type="text" class="form-control" onclick="this.select()" value="8.5"
                                                    id="newTauxTva" name="newTauxTva">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="taux_tva">Quantité Panier</label>
                                                    <input type="number" class="form-control" onclick="this.select()" value="1"
                                                    id="newQte" name="newQte">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="newStock">Stock</label>
                                                    <input type="number" class="form-control" onclick="this.select()" value="1"
                                                    id="newStock" name="newStock">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="taux_tva">Prix Unitaire</label>
                                                    <input type="number" class="form-control" value="0.00" step="0.1" onclick="this.select()"
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


            <!-- MODAL JOURNAUX -->
            <div class="modal fade" id="modal-journaux">
                <div class="modal-dialog" id="modalDialog">
                    <div class="modal-content ">
                        <div class="modal-header">
                            <h4 class="modal-title">Journaux tickets</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body" id="journal">
                            <div class="form-group">
                                <h5 class="bg-dark" style="font-weight: 800;text-align: center;padding: 5px 0;">Choisir
                                une période </h5>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="journaux">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-outline-light" data-dismiss="modal">Annuler</button>
                            <!-- <button type="button" class="btn btn-outline-light">Save changes</button> -->
                        </div>
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
        <script src="../plugin-ticket-js/Impresora.js"></script>
        <script src="paiement.js?random=<?php echo uniqid(); ?>"></script>


        <script>
            // NEW
            //CALENDRIER POUR EXPORT CSV
            $('#journaux').daterangepicker({
                autoApply:true,
                minDate:'<?php echo '01/'.date('m/Y') ?>',
                maxDate:'<?php echo date('d/m/Y') ?>',
                singleDatePicker: true,
                locale: {
                    format: 'DD/MM/YYYY',
                    "applyLabel": "Valider",
                    "cancelLabel": "Annuler",
                    "fromLabel": "De",
                    "toLabel": "A",
                    "daysOfWeek": [
                        "Dim",
                        "Lun",
                        "Mar",
                        "Mer",
                        "Jeu",
                        "Ven",
                        "Sam"
                        ],
                    "monthNames": [
                        "Janvier",
                        "Février",
                        "Mars",
                        "Avril",
                        "Mai",
                        "Juin",
                        "Juillet",
                        "Août",
                        "Septembre",
                        "Octobre",
                        "Novembre",
                        "Décembre"
                        ],
                },

            }).on('apply.daterangepicker', function (e, picker) {
                var exportDateDebut = picker.startDate.format('DD/MM/YYYY');
                console.log(exportDateDebut)
                $.ajax({
                    url: "showJournaux.php",
                    type: "POST",
                    contentType: "application/json",
                    data:JSON.stringify({
                        startDate:exportDateDebut,
                    }),
                    success: function (response) {
                        console.log(response)
                        var res = JSON.parse(response)
                        if (res.response == 1) {
                            var journaux = res.journaux;
                            journaux = journaux.replace(/(?:\r\n|\r|\n)/g, '<br>')
                            $('#journal').append("<div style='margin-left:30px'>"+journaux+"</div>")
                        }
                    }
                })

            })


            $('#clotureCaisseBottom').click(function(){
                window.location.href = "cloture-caisse.php"
            });

// TOUCHES 
            $('#searchArticle').keydown(function (e) {
        // TOUCHE F12 = ESPECE
                if(e.which == 123){
                    $('#modal-espece').modal('show')
                    var total = getTotal();
                    $('#inputMontantEspece').select()
                    $('#inputMontantEspece').val(total)
                    $('#montantEspece').html(total)

                }
        // TOUCHE F11 = CB
                if(e.which ==  120){
                    $('#modal-cb').modal('show')
                    var total = getTotal();
                    $('#inputMontantCB').select()
                    $('#inputMontantCB').val(total)
                    $('#montantCB').html(total)
                }

    // TOUCHE F8 = divers
                if(e.which == 119){
                    var val = $(this).val();
                    var split = val.split('*')
                    if (split.length>1) {
                        var prix = split[1]
                        var qte = split[0]
                        addProduitDivers('<?php echo $_SESSION['session'] ?>','<?php echo $_SESSION['id_caisse'] ?>',parseFloat(prix),parseFloat(qte))
                    }else{
                        var prix = split[0]
                        addProduitDivers('<?php echo $_SESSION['session'] ?>','<?php echo $_SESSION['id_caisse'] ?>',parseFloat(prix))
                    }
        // $('#modal-divers').modal('show')
        // $('#inputPrixDivers').select()
       // addProduitDivers('<?php echo $_SESSION['session']; ?>','<?php echo $_SESSION['id_caisse'] ?>');
                }
        // TOUCHE F7 = CHEQUE
                if(e.which == 118){
                    $('#modal-cheque').modal('show')
                    var total = getTotal();
                    $('#inputMontantCheque').select()
                    $('#inputMontantCheque').val(total)
                    $('#montantCheque').html(total)
                }    
            });


        </script>
        <script type="text/javascript">

            $('#caddie').children().eq(0).addClass('active');
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
                var actuelSession = '<?php echo $_SESSION['session']  ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';

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
                var actuelSession = '<?php echo $_SESSION['session']  ?>';
                var id_caisse = '<?php echo $_SESSION['id_caisse'] ?>';
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
                            $('#caddie').html("")


                        } else {
                            Toast.fire({
                                icon: 'success',
                                title: "Achat validé ! Ticket en cours d'impression..."
                            })



                        }

                    }
                })
            }

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
                            // Toast.fire({
                            //     icon: 'error',
                            //     title: "Erreur code barre invalide "
                            // })
                                $('body').css('background-color','red');
                                if (confirm("Erreur ! Code barre invalide")) {
                                    $('#searchArticle').val('')
                                }
                                else{
                                    $('#searchArticle').val('')
                                }

                            } else if (result.result === 1) {
                                $('body').css('background-color','rgb(242, 242, 242)');
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

                                $('#totalQte').text(result.qteTotal)
                                var remise = parseInt(result.data.remise) > 0 ? parseFloat(result.data.pu_euro) * parseFloat(result.data.qte) * (parseInt(result.data.remise) / 100) : "0.00€"
                                var promo = parseFloat(result.data.promo) > 0 ? parseFloat(result.data.pu_euro) - parseFloat(result.data.promo) : 0;
                                if((result.data.pu_euro - result.data.promo) > 0){
                                    var promo = parseFloat(result.data.promo) > 0 ? "<br><span class='text-gray remise-caddie' >Remise de - <span > " + promo.toFixed(2) + " €</span></span>" : "";
                                }

                                var pu_euro = parseFloat(result.data.promo) > 0 ? parseFloat(result.data.promo) : parseFloat(result.data.pu_euro)
                                $('#rendu').text("")
                                if (increment === false) {
                                    var montantEuroLive = parseFloat(pu_euro) * result.data.qte
                                    $('#caddie').prepend(
                                        '<div class="callout callout-info produit active" id="article-'+ref+'" >\n' +
                                        '<div class="row">' +
                                        '<div class="col-sm-3 col-md-4 col-lg-4 col-xl-4">' +
                                        '<p class="designation">' +
                                        result.data.titre +
                                        '<i class="fa fa-trash text-red" style="cursor:pointer;" onclick="deleteArticle(this.id,' + session + ',' + id_caisse + ')" id="deleteProduit-' + result.data.ref + '"></i>' +
                                        promo +
                                        '</p>' +
                                        '</div>' +
                                        '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 qteBox">' +
                                        '<input type="text" onclick="this.select()" class="qteProduit" style="width: 40px !important;" name="quantiteProduit" id="quantiteProduit-' + result.data.ref + '" value="' + result.data.qte + '" />' +
                                        '</div>' +
                                        '<div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">' +
                                        '<p class="puEuroProduit">' +
                                        pu_euro.toFixed(2) +
                                        '€</p>' +
                                        '</div>' +
                                        '<div class="col-sm-1 col-md-2 col-lg-2 col-xl-2"  id="montantEuro-'+ref+'">' +
                                        '<p class="puEuroProduit" >' +
                                        montantEuroLive.toFixed(2)+
                                        '€</p>' +
                                        '</div>' +
                                        '<div class="col-sm-1 col-md-2 col-lg-2 col-xl-2 remisePourcent">' +
                                        '<input type="text" class="inputRemise" onclick="this.select()" name="remiseProduit"  id="remiseProduit-' + result.data.ref + '" value="' + result.data.remise + '" /><span>%</span>' +
                                        '</div>' +
                                        '<div class="col-sm-1 col-md-1 col-lg-1 col-xl-1 remiseEuro">' +
                                        '<p>' +
                                        '<input type="text" class="inputRemise" onclick="this.select()" name="remiseEuro"  id="remiseEuro-' + result.data.ref + '-'+result.data.pu_euro+'" value="' + result.data.remise_euro + '" /><span>€</span>' +
                                        '</p>' +
                                        '</div>' +
                                        '</div>' +
                                        '</div>'
                                        )
                                    $('#caddie').children().eq(1).removeClass('active');

                                } else {
                                   $('#caddie').prepend($('#article-'+result.data.ref));
                                   $('#caddie').children().eq(1).removeClass('active');
                                   $('#caddie').children().first().addClass('active');
                                   var newQte = parseInt($('#quantiteProduit-' + result.data.ref).val())
                                   var updatedQTE = parseInt(result.data.qte)
                                   var actualMontantEuro = $('#montantEuro-' + result.data.ref).text()
                                   actualMontantEuro = parseFloat(actualMontantEuro)
                                   var newMontantEuro = parseFloat(pu_euro) * updatedQTE + actualMontantEuro;
                                    // $('#montantEuro-' + result.data.ref).html(newMontantEuro.toFixed(2) + "€")
                                    // console.log(result.data.pu_euro,newQte + updatedQTE,$('#remiseProduit-'+result.data.ref).val(),result.data.promo )
                                    // console.log(result.data.pu_euro * (newQte + updatedQTE) * ($('#remiseProduit-'+result.data.ref).val()/100));

                                   if(result.data.promo>0){
                                    newMontantEuro = result.data.promo * (newQte + updatedQTE) * (1 - ($('#remiseProduit-'+result.data.ref).val()/100))
                                    $('#montantEuro-'+result.data.ref+' > p').text(newMontantEuro.toFixed(2))
                                    $('#quantiteProduit-' + result.data.ref).val(newQte + updatedQTE)
                                }else{
                                    newMontantEuro = result.data.pu_euro * (newQte + updatedQTE) * (1 - ($('#remiseProduit-'+result.data.ref).val()/100))
                                    $('#montantEuro-'+result.data.ref+' > p').text(newMontantEuro.toFixed(2))
                                    $('#quantiteProduit-' + result.data.ref).val(newQte + updatedQTE)
                                }




                                    // $("#montantEuro-"+ref).load(location.href + "#montantEuro-"+ref);
                                    // $("#montantEuro-"+ref).html($("#montantEuro-"+ref).html())
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
    var expiration = '<?php echo isset($_SESSION["expiration"]) ? $_SESSION["expiration"] : 0 ?>'
    var user_id = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0 ?>';
    $.ajax({
        url: "../login/checkIfConnected.php",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({"id_caisse": id_caisse, "action": "check","expiration":expiration,"user_id":user_id}),
        success: function (data) {
            console.log(data)
            var result = JSON.parse(data)
                // if(result.response === 0){
                //         Toast.fire({
                //             icon: 'error',
                //             title: result.message
                //         })
                //
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
                }
            })
        }


    });

    $(document).ajaxComplete(function () {
        $('#searchArticle').focus()
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
