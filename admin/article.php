<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
include('../DBConfig.php');
include('../functions.php');
date_default_timezone_set('Indian/Reunion');
$dateDuJour = date('Y-m-d');
if (isset($_POST['famille']) && isset($_POST['designation'])) {
    $famille = $_POST['famille'];
    $gencode = htmlspecialchars($_POST['gencode']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $stock_actuel = (int) htmlspecialchars($_POST['stock_actuel']);
    $stock_alerte = (int) htmlspecialchars($_POST['stock_alerte']);
    $codetva = $_POST['codetva'];
    $colisage = $_POST['colisage'];
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
    $stock_initial = isset($_POST['stock_initial']) ? $_POST['stock_initial'] : 0;
    $stock_initial = $_POST['stock_initial'] == "" ? 0 : $_POST['stock_initial'];
    $promo_debut = str_replace('/', '-', $_POST['promo_debut']);
    $promo_debut = date('Y-m-d', strtotime($promo_debut));
    $raccourci = $_POST['shortcutHome'];
    $raccourci = $raccourci == "active" ? 1 : 0;
    $promo_fin = str_replace('/', '-', $_POST['promo_fin']);
    $promo_fin = date('Y-m-d', strtotime($promo_fin));


    $datemodif =  date('Y-m-d H:i:s');
    $dateajout = $_POST['dateajout'];
    $oldgencode = $_POST['oldgencode'];

    $options = $_POST['options'];
    $option_val = 0;
    if (count($options) > 1) {
        $option_val = 1;
    }

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
            echo json_encode(array('response' => 3, 'message' => 'Vous devez indiquer un prix', 'type' => 'mode_prix_3_achat_ttc'));
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
    if (!validate_EAN13Barcode($gencode)) {
        echo json_encode(array('response' => 3, 'message' => 'Codebarre invalide', 'type' => 'gencode'));
        exit;
    }


    $id_produit = random_strings(12);
    $designation = remove_accents($designation);

    // if ($_SESSION['client_id'] == 1 ) { // POUR CIDEAL 
    //     $sql = "UPDATE table_client_catalogue SET 
    //     `cath`= $famille, 

    //     `ref` = '$gencode',
    //     `titre` = '$designation',
    //     `prixttc_euro` = $prix,
    //     `prixttc_promo_euro` = $promottc,
    //     `code_tva` = $codetva,
    //     `promo_debut` = '$promo_debut',
    //     `promo_fin` = '$promo_fin',
    //     `choix_mode_prix` = $mode,
    //     `mode_prix_1_achat_ht` = $mode_prix_1_achat,
    //     `mode_prix_1_marge` = $marge,
    //     `mode_prix_2_fixe_ht`=$mode_prix_2,
    //     `mode_prix_3_fixe_ttc`=$mode_prix_3,
    //     `dateajout`='$dateajout',
    //     `datemodif`='$datemodif',
    //     `accueil` = $raccourci,
    //     `stock`=$stock_actuel,
    //     `stock_alerte`=$stock_alerte,
    //     `unite`=$unite,
    //     `qte_unite`=$quantite,
    //     `package`='$colisage',
    //     `prix_variable`=$prix_variable,
    //     `img` = NULL,
    //     `send_web` = 1,
    //     `stock_initial` = $stock_initial
    //     WHERE ref = '$oldgencode' " ;
    // }elseif ($_SESSION['client_id'] == 60) { // TEST UNIQUEMENT
    //     $sql = "UPDATE table_client_catalogue SET 
    //     `cath`= $famille, 

    //     `ref` = '$gencode',
    //     `titre` = '$designation',
    //     `prixttc_euro` = $prix,
    //     `prixttc_promo_euro` = $promottc,
    //     `code_tva` = $codetva,
    //     `promo_debut` = '$promo_debut',
    //     `promo_fin` = '$promo_fin',
    //     `choix_mode_prix` = $mode,
    //     `mode_prix_1_achat_ht` = $mode_prix_1_achat,
    //     `mode_prix_1_marge` = $marge,
    //     `mode_prix_2_fixe_ht`=$mode_prix_2,
    //     `mode_prix_3_fixe_ttc`=$mode_prix_3,
    //     `dateajout`='$dateajout',
    //     `datemodif`='$datemodif',
    //     `accueil` = $raccourci,
    //     `mise_a_jour` = 1,
    //     `stock`=$stock_actuel,
    //     `stock_alerte`=$stock_alerte,
    //     `unite`=$unite,
    //     `qte_unite`=$quantite,
    //     `package`='$colisage',
    //     `prix_variable`=$prix_variable,
    //     `img` = NULL,
    //     `creation` = 0,
    //     `stock_initial` = $stock_initial
    //     WHERE ref = '$oldgencode' " ;
    // }
    // else{
    $sql = "UPDATE table_client_catalogue SET 
        `cath`= $famille, 

        `ref` = '$gencode',
        `titre` = '$designation',
        `prixttc_euro` = $prix,
        `prixttc_promo_euro` = $promottc,
        `code_tva` = $codetva,
        `promo_debut` = '$promo_debut',
        `promo_fin` = '$promo_fin',
        `choix_mode_prix` = $mode,
        `mode_prix_1_achat_ht` = $mode_prix_1_achat,
        `mode_prix_1_marge` = $marge,
        `mode_prix_2_fixe_ht`=$mode_prix_2,
        `mode_prix_3_fixe_ttc`=$mode_prix_3,
        `dateajout`='$dateajout',
        `datemodif`='$datemodif',
        `formule` = 0,
        `options` = $option_val,
        `stock`=$stock_actuel,
        `stock_alerte`=$stock_alerte,
        `unite`=$unite,
        `qte_unite`=$quantite,
        `package`='$colisage',
        `prix_variable`=$prix_variable,
        `img` = '',
        `send_web` = 1 WHERE ref = '$oldgencode' ";
    // }

    $updateArticle = $conn->query($sql);
    if ($updateArticle) {

        if (count($options) > 0) {
            $last_id = $_POST['num'];
            foreach ($options as $option) {
                $nomoption = $option['nomoption'];
                $prixoption = $option['prixoption'];
                $sqloption = "INSERT INTO `options_produit`( `nom`, `prix`, `id_produit`, `ref`) VALUES ('$nomoption',$prixoption,$last_id,'$oldgencode')";
                $addoption = $conn->query($sqloption);
            }
        }
        if ($_SESSION['client_id'] == 1) { // POUR CIDEAL 
            $filename = "../synchro/cideal/catalogue/cideal_" . time() . ".json";
            file_put_contents($filename, json_encode($sql));
        } elseif ($_SESSION['client_id'] == 11) {
            $filename = "../synchro/test/catalogue/test_" . time() . ".json";
            file_put_contents($filename, json_encode($sql));
        }
        $sql = "UPDATE table_client_variable SET modif_serveur_modif_catalogue = '$datemodif' WHERE num = 1";
        $updateSync = $conn->query($sql);
        if ($updateSync) {

            echo json_encode(array('response' => 1, 'message' => 'Article mise jour dans le catalogue !'));
            exit;
        }
    } else {
        // echo json_encode(array('response' => 2, 'message' => 'Une erreur c\'est produite.' , 'error' => $sql ));
        echo $sql;
        exit;
    }

    exit;
}
if (isset($_GET['gencode'])) {
    $gencode = $_GET['gencode'];
    $sql = "SELECT * from table_client_catalogue WHERE ref = '$gencode' ";
    $query = $conn->query($sql);
    if ($query->num_rows == 1) {
        $resultat = $query->fetch_all(MYSQLI_ASSOC);
        $article = $resultat[0];
        $title = $page = $article['titre'];
        $idproduct = $article['num'];
    }


    $accueil = 'index.php';
    include('../template/header.php');

?>
    <style>
        .active {
            background-color: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        ul li a {
            color: #000;
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
                <div class="col-lg-9 col-md-12 col-sm-12 mx-auto">
                    <!-- form user info -->
                    <form autocomplete="off" class="form" role="form">
                        <div class="card card-olive">
                            <div class="card-header">
                                <h3 class="mb-0">Identite article</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Famille</label>
                                    <div class="col-lg-9" id="famille-select">
                                        <select class="form-control" id="famille" size="0" name="famille">
                                            <option value="0">Choisir la famille</option>
                                            <?php
                                            $sql = 'SELECT * FROM table_client_categorie WHERE LENGTH(nomcategorie) > 2 ORDER BY nomcategorie ASC';
                                            $familles = $conn->query($sql);
                                            $nbligne = $familles->num_rows;
                                            $categorie = [];
                                            $parent = [];
                                            $child = [];
                                            if ($nbligne > 0) {
                                                while ($famille = $familles->fetch_assoc()) {
                                                    $categorie[] = $famille;
                                                    if ($famille['id_parent'] == 0) {
                                                        $parent[] = $famille;
                                                    } else {
                                                        $child[] = $famille;
                                                    }
                                                }
                                            }

                                            foreach (range('A', 'Z') as $char) {
                                                echo $char . "\n";
                                            ?>
                                                <option class="optionGroup alphabet" disabled>
                                                    <?php echo "-" . $char ?>
                                                    <?php foreach ($categorie as $cat) {
                                                        $nom_cat = $cat["nomcategorie"];
                                                        $id_cat = $cat["id_categorie"];
                                                        $id_parent = $cat['id_parent'];
                                                        if (ucfirst($nom_cat[0]) == $char) {
                                                            if ($id_parent == 0) {
                                                    ?>
                                                <option value="<?php echo $id_cat ?>" <?php echo $article['cath'] == $id_cat ? "selected" : "" ?>>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                    <?php echo strtoupper($nom_cat) ?></option>
                                                <?php
                                                                foreach ($child as $subcat) {
                                                                    if ($subcat['id_parent'] == $cat["id_categorie"]) {
                                                ?>
                                                        <option value="<?php echo $subcat["id"] ?>"
                                                            <?php echo $article['cath'] == $subcat['id_categorie']  ? "selected" : "" ?>>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            <?php echo $subcat['nomcategorie'] ?>

                                                        </option>

                                                    <?php

                                                                    }
                                                                }
                                                            } else {
                                                                $found = 0;
                                                                foreach ($parent as $catParent) {
                                                                    if ($catParent['id_categorie'] == $id_parent) {
                                                                        $found += 1;
                                                                        break;
                                                                    }
                                                                }
                                                                if ($found == 0) {
                                                    ?>
                                                    <option value="<?php echo $id_cat ?>" <?php echo $article['cath'] == $id_cat ? "selected" : "" ?>>
                                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                                        <?php echo $nom_cat ?>

                                                    </option>

                                    <?php
                                                                    // echo "<option value=".$id_cat.">&nbsp;&nbsp;&nbsp;&nbsp;".$nom_cat."</option>";
                                                                }
                                                            }
                                                        }
                                                    } ?>
                                    </option>
                                <?php

                                            }



                                ?>
                                        </select>
                                        <!-- <span class="text-muted mt-3" style="cursor:pointer;" onclick="toggleFamille()">Creer une famille</span>
                                        <div class="row famille" id="familleBlock" style="margin: 10px 0 0 2px;display: none;">
                                            <input type="text" name="creerFamille" id="inputFamille" style="margin-right:10px" />
                                            <button type="button" class="btn btn-default btn-sm" id="creerFamille" disabled="disabled">Créer une famille</button>
                                        </div> -->


                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Gencode</label>
                                    <div class="col-lg-3">
                                        <input class="form-control" type="text" name="gencode" id="gencode" value="<?php echo $article['ref'] ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Désignation</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" type="text" id="designation" name="designation" value="<?php echo $article['titre'] ?>">
                                    </div>
                                </div>

                                <?php
                                $connClient = connClient();
                                $client_id = $_SESSION['client_id'];
                                $sql = "SELECT * FROM client_options WHERE client_id = $client_id AND option_id = 1 AND status = 1 LIMIT 1";
                                $options = $connClient->query($sql);
                                // var_dump($sql);
                                if ($options->num_rows > 0) {
                                    while ($option = $options->fetch_assoc()) {
                                        if ($option['option_id'] == 1) {
                                ?>
                                            <div class="form-group row">
                                                <label class="col-lg-3 col-form-label form-control-label">Stock</label>
                                                <div class="col-lg-2">
                                                    <input class="form-control" type="text" id="stock_actuel" disabled name="stock_actuel" value="<?php echo $article['stock'] ?>">
                                                </div>
                                                <div class="col-lg-1">
                                                    <input id="admin" class="btn  btn-danger" type="button" value="MAJ Stock" data-toggle="modal" data-target="#modal-stock" onclick="unlockStock()">
                                                </div>

                                            </div>

                                    <?php
                                        }
                                    }
                                } else {
                                    ?>
                                    <div class="form-group row">
                                        <label class="col-lg-3 col-form-label form-control-label">Stock</label>
                                        <div class="col-lg-2">
                                            <input class="form-control" type="text" id="stock_actuel" disabled name="stock_actuel" value="<?php echo $article['stock'] ?>">
                                        </div>

                                        <div class="col-lg-1">
                                            <input id="admin" class="btn  btn-danger" type="button" value="MAJ Stock" data-toggle="modal" data-target="#modal-stock">
                                        </div>
                                    </div>

                                <?php
                                }

                                ?>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Stock Alerte</label>
                                    <div class="col-lg-2">
                                        <input class="form-control" type="text" name="stock_alerte" id="stock_alerte" value="<?php echo $article['stock_alerte'] ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Code TVA</label>
                                    <div class="col-lg-2">
                                        <select class="form-control" size="0" name="code_tva" id="codetva">
                                            <option value="null">Choisir</option>
                                            <option value="0" <?php echo $article['code_tva'] == 0 ? "selected" : "" ?>>0.0 % Exo</option>
                                            <option value="1" <?php echo $article['code_tva'] == 1 ? "selected" : "" ?>>1.5 %</option>
                                            <option value="2" <?php echo $article['code_tva'] == 2 ? "selected" : "" ?>>2.1 %</option>
                                            <option value="8" <?php echo $article['code_tva'] == 8 ? "selected" : "" ?>>8.5 %</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Colisage</label>
                                    <div class="col-lg-9">
                                        <input class="form-control" type="text" name="package" id="package" value="<?php echo $article['package'] ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Quantité + Unité</label>
                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <input class="form-control" type="number" name="qteUnite" id="quantite" step="0.01" value="<?php echo $article['qte_unite'] ?>">
                                            </div>
                                            <div class="col-lg-6">
                                                <select class="form-control" id="unite" name="unite">
                                                    <option value="0" <?php echo $article['unite'] == 0 ? "selected" : "" ?>>Désactivé</option>
                                                    <option value="1" <?php echo $article['unite'] == 1 ? "selected" : "" ?>>Kg</option>
                                                    <option value="2" <?php echo $article['unite'] == 2 ? "selected" : "" ?>>Litre</option>
                                                    <option value="3" <?php echo $article['unite'] == 3 ? "selected" : "" ?>>Mètre</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Prix Variable</label>
                                    <div class="col-lg-3">
                                        <select class="form-control" name="prix_variable" id="prix_variable">
                                            <option value="0" <?php echo $article['prix_variable'] == 0 ? "selected" : "" ?>>Désactivé</option>
                                            <option value="1" <?php echo $article['prix_variable'] == 1 ? "selected" : "" ?>>Activé, en euros</option>
                                        </select>
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Raccourci accueil</label>
                                    <div class="col-lg-3">
                                        <input type="checkbox" name="shortcutHome" id="shortcutHome" class="form-control" value="<?php echo $article['accueil'] == 1 ? "active" : "" ?>" style="width: 50px;height: 20px;margin-top: 9px;"
                                            <?php echo $article['accueil'] == 1 ? "checked" : "" ?>>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label form-control-label">Options</label>
                                    <div class="col-lg-9">
                                        Options :
                                        <?php
                                        $idprod = $article['num'];
                                        $sql = "SELECT * FROM `options_produit` where id_produit = $idprod";
                                        
                                        $options = $conn->query($sql);
                                        
                                        if ($options->num_rows >= 1) {
                                            while ($ligne = $options->fetch_assoc()) {
                                                echo  "<div class='callout ' id='option-".$ligne['idoption']."'>" . $ligne['nom'] . " - " . $ligne['prix'] . "€ " . "<i class='fa fa-trash text-red' style='cursor:pointer;' onclick='deleteOption(" . $ligne['idoption'] . ")'></i>" . "</div>";
                                            }
                                        }
                                        ?>

                                        <a class="btn btn-danger" id="add-options-btn">Ajouter des options</a>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-3"></div>
                                    <div class="col-lg-9" id="options-form-container"></div>


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

                        <div class="card card-olive">
                            <div class="card-header">
                                <h3 class="mb-0">Fiche prix normal</h3><br>
                                <p style="color:#FFFFFF">Choisissez le mode de calcul du prix de vente normal (hors-promo) de l'article.
                            </div>
                            <div class="card-body">

                                <div class="form-group row" style="margin-top: 30px;">
                                    <div class="col-lg-8">
                                        <div class="form-check">
                                            <input class="form-check-input position-static" type="radio" name="mode" id="mode1" aria-label="..." <?php echo $article['mode_prix_1_achat_ht'] != 0.00 ?  "checked" : ""  ?>>
                                            <label class="form-check-label" for="mode1" style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                À partir du prix € HT d'achat fournisseur et du taux de marge
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" name="mode_prix_1_achat_ht" id="mode_prix_1_achat_ht" value="<?php echo $article['mode_prix_1_achat_ht'] ?>"> <span style="font-weight: 600;">€ HT</span>
                                        <input type="text" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent" name="mode_prix_1_marge" id="mode_prix_1_marge" value="<?php echo $article['mode_prix_1_marge'] ?>"> <span style="font-weight: 600;"> <span style="font-weight: 600;">%</span>
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
                                                        box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" type="radio" name="mode" id="mode2" aria-label="..." <?php echo $article['mode_prix_2_fixe_ht'] != 0.00 ?  "checked" : ""  ?>>
                                            <label class="form-check-label" for="mode1" style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                Fixée en € HT:
                                                <span style="font-weight:normal;font-size: 14px;">Le calcul € TTC se fait automatiquement <br>avec le code TVA</span>
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" name="mode_prix_2_achat_ht" id="mode_prix_2_achat_ht" style="border: 1px solid #ced4da;
                                                    border-radius: 0.25rem;
                                                    box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" value="<?php echo $article['mode_prix_2_fixe_ht'] ?>"> <span style="font-weight: 600;">€ HT</span>
                                    </div>
                                </div>
                                <hr style="margin-top: 1rem;
                                            margin-bottom: 1rem;
                                            border: 0;
                                            border-top: 1px solid rgba(0, 0, 0, 0.1);" />
                                <div class="form-group row" style="margin-top: 30px;">
                                    <div class="col-lg-8">
                                        <div class="form-check">
                                            <input class="form-check-input position-static" id="mode_prix_3_fixe_ttc" style="border: 1px solid #ced4da;
                                                        border-radius: 0.25rem;
                                                        box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" type="radio" name="mode" id="mode3" aria-label="..." <?php echo $article['mode_prix_3_fixe_ttc'] != 0.00 ?  "checked" : ""  ?>>
                                            <label class="form-check-label" for="mode1" style="margin-left: 15px;font-weight: 600;font-size: 17px;">
                                                Fixée en € TTC:
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <input type="text" name="mode_prix_3_fixe_ttc" id="mode_prix_3_achat_ttc" onclick="this.select()" value="<?php echo $article['mode_prix_3_fixe_ttc'] ?>"> <span style="font-weight: 600;">€ TTC</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-olive">
                            <div class=" card-header">
                                <h3 class="mb-0">FICHE PRIX PROMO (FALCULTATIF)</h3><br>
                                <p style="color:#ffffff">Remplissez ce formulaire si vous souhaitez programmer un prix promo.<br>Laisser le montant à 0€ pour ne pas programmer de promotion</p>
                            </div>
                            <div class="card-body">

                                <!-- <iframe src="test.php" style="display:none;" name="frame"></iframe>
                                    <input type="button" onclick="frames['frame'].print()" value="printletter"> -->
                                <div class="row" style="justify-content: center;margin-top: 20px;">
                                    <label class="col-lg-12 col-form-label form-control-label text-center" style="font-size:20px">Prix <span style='color:red;'>PROMO TTC EURO</span> à appliquer:</label>
                                </div>
                                <div class="form-group row" style="justify-content: center;">
                                    <div class="col-lg-4">
                                        <input type="text" style="border: 1px solid #ced4da;
                                            border-radius: 0.25rem;
                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" name="promottc" id="promottc" value="<?php echo  $article['prixttc_promo_euro']  ?>">
                                        <span style="font-weight: 600;font-size: 18px;color:red">€ TTC</span>
                                    </div>
                                </div>
                                <div class="row text-left">
                                    <div class="col-lg-6" style="    text-align: right;">
                                        <span style="font-weight:600">du</span> <input type="text" style="border: 1px solid #ced4da;
                                            border-radius: 0.25rem;
                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" name="promo_debut" id="promo_debut" value="<?php echo $article['promo_debut'] != "0000-00-00" ? date('d/m/Y', strtotime($article['promo_debut'])) : "00/00/0000" ?>" />
                                    </div>
                                    <div class="col-lg-6">
                                        <span style="font-weight:600">au</span> <input type="text" style="border: 1px solid #ced4da;
                                            border-radius: 0.25rem;
                                            box-shadow: inset 0 0 0 transparent;margin: 0 0 10px 0;" name="promo_fin" id="promo_fin" value="<?php echo $article['promo_fin'] != "0000-00-00" ? date('d/m/Y', strtotime($article['promo_fin'])) : "00/00/0000" ?>" /> <span style="font-weight:600">inclus</span>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-olive">
                            <div class="card-header" style="text-align: center;">
                                <h3 class="mb-0">IMPRIMER ETIQUETTE</h3>
                            </div>
                            <div class="row" style="padding:20px 0">
                                <div class="col-md-3">
                                    <div style="padding: 20px"><i class="fa fa-print fa-2x"></i></div>
                                </div>
                                <div class="col-md-9">
                                    <span>Imprimer</span><input type="number" name="printNumber" value="1" id="printNumber" /><span>exemplaire(s)</span><br>
                                    <input type="checkbox" name="print_prix_normal" checked value="0.00" id="prixNormal"> Imprimer l'étiquette <span style="font-weight: 600;">prix normal</span><br>
                                    <input type="checkbox" name="print_prix_promo" id="prixPromo"> Imprimer l'étiquette <span style="font-weight: 600;">prix promo</span>
                                    <?php
                                    // $zpl = formatLabel($article['titre'],$article['mode_prix_3_fixe_ttc'],$article['ref'],$arrayPos,$article['package'],$article['prixttc_promo_euro']);

                                    ?>
                                    <!-- <button class="btn btn-dark btn-lg" type="button"  onclick="imprimeArticle()">Imprimer</button> -->

                                </div>
                            </div>
                        </div>

                        <div class="form-groupt row" style="padding: 30px 0;justify-content: space-around;">
                            <!--                            <div class="col-md-4">-->
                            <!--                                    <i class="fas fa-envelope"></i> Imprimer Étiquettes-->
                            <!--                                </a>-->
                            <!--                            </div>-->
                            <div class="col-md-8 mx-auto">
                                <a href="<?php echo $_SERVER['HTTP_REFERER'] ?>" class="btn btn-dark btn-lg">Retour</a>
                                <button type="submit" class="btn btn-primary btn-lg" id="editBtn" name="save">Enregistrer</button>
                                <button type="button" class="btn btn-outline-danger btn-lg" onclick="deleteArticleAdmin('<?php echo $article['ref'] ?>','true')">Supprimer le produit</button>
                            </div>
                        </div>
                    </form>
                </div><!-- /form user info -->


            </div>
        </div>
    </div>
    </div>
    </div>
    <!-- MODAL CONFIRMATION IMPRESSION TICKET -->
    <div class="modal fade" id="modal-stock" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">MISE A JOUR DU STOCK</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4">
                            <div class=" card-tabs">
                                <div class="card-header p-0 pt-1 border-bottom-0">
                                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-modif" data-toggle="pill" href="#custom-modif" role="tab" aria-controls="custom-modif" aria-selected="false" style="color:blue;">Modification du stock</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="tab-ajout" data-toggle="pill" href="#custom-ajout" role="tab" aria-controls="custom-ajout" aria-selected="false" style="color:green;">Ajout de produits</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content" id="custom-tabs-three-tabContent">
                                        <div class="tab-pane fade" id="custom-modif" role="tabpanel" aria-labelledby="tab-modif">
                                            <div class="form-group">
                                                <label for="modifie_stock" style="font-size: 12px;">Modifier le nombre d'articles en stock</label>
                                                <input type="number" class="form-control" id="modif_stock" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="custom-ajout" role="tabpanel" aria-labelledby="tab-ajout">
                                            <div class="form-group">
                                                <label for="ajout_stock" style="font-size: 12px;">Ajouter une quantité au stock actuel</label>
                                                <input type="number" class="form-control" id="ajout_stock" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-7 offset-1">
                            <h5 class="text-center"></h5>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">5 derniers mouvements de stock</h5>
                                    <div class="card-tools">
                                        <div class="input-group input-group-sm" style="width: 150px;">
                                            <!-- <input type="text" name="table_search" class="form-control float-right" placeholder="Rechercher"> -->
                                            <!-- <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th style="font-size:13px">Date</th>
                                                <th style="font-size:13px">Mouvement Stock</th>
                                                <th style="font-size:13px">Stock Initial</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ref = $_GET['gencode'];
                                            $mouvements = $conn->query("SELECT * from stock_mouvement WHERE ref = '$ref' order by created_at DESC ");
                                            if ($mouvements->num_rows > 0) {
                                                while ($mouvement = $mouvements->fetch_assoc()) {
                                            ?>
                                                    <tr>
                                                        <td style="font-size:13px"><b><?php echo $mouvement['created_at']  ?></b></td>
                                                        <td style="font-size:13px"><?php echo $mouvement['mouvement_ajout'] > 0 ? "<span style='color:green;'>+" . $mouvement['mouvement_ajout'] . "</span>" : "<span style='color:blue;' >" . $mouvement['mouvement_qte'] . "</span>" ?></td>
                                                        <td style="font-size:13px"><span class="tag tag-success"><?php echo $mouvement['stock_initial'] ?></span></td>
                                                    </tr>
                                            <?php
                                                }
                                            }

                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn" style="background-color:#027491;color:#ffffff" onclick="mouvementStock('<?php echo $_GET['gencode'] ?>')">Valider </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Annuler
                    </button>
                </div>
            </div>


        </div>
    </div>
    </div>
    </div>
    </div>
    <?php include('../template/footer.php') ?>
    <?php include('../template/script.php') ?>
    <script src="../lib/dist/JsBarcode.ean-upc.min.js"></script>

    <script type="text/javascript">
        var countoptions = 0;
        $('#add-options-btn').on('click', function(e) {
            e.preventDefault()
            let formHtml = `
					<div class="product-form mb-3" style="display:flex;">
					<input type="text"  class="form-control mb-2 designation" name="nomoption-` + countoptions + `" placeholder="Désignation">
					<input type="number"   step="0.1" class="form-control mb-2 prix" name="prixoption-` + countoptions + `" style="width: 100px;margin-left: 15px;" placeholder="Prix">
					</div>
					`;
            countoptions++;
            $('#options-form-container').append(formHtml);
        });
        if ($('#ajout_stock').val() > 0) {
            $('#modif_stock').prop('disabled', true);
            $('#ajout_stock').prop('disabled', false);
        }

        if ($('#modif_stock').val() > 0) {
            $('#ajout_stock').prop('disabled', true);
            $('#modif_stock').prop('disabled', false);
        }

        function mouvementStock(ref) {
            var modif_stock = $('#modif_stock').val();
            var ajout_stock = $('#ajout_stock').val();

            var type = modif_stock > 0 ? "modif" : "ajout";
            var mouvement = modif_stock > 0 ? modif_stock : ajout_stock;
            $.ajax({
                url: "request.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    type: type,
                    mouvementStock: mouvement,
                    ref: ref
                }),
                success: function(data) {
                    console.log(data)
                    var result = JSON.parse(data)
                    if (result === 1) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Le stock a été mis a jour.'
                        })
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Une erreur est survenue. Veuillez réesayer'
                        })
                    }
                }
            })

        }

        function deleteOption(numoption){
            $.ajax({
                url: "request.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({
                    numoption: numoption,
                }),
                success: function(data) {
                    console.log(data)
                    var result = JSON.parse(data)
                    if (result.response === 1) {
                        Toast.fire({
                            icon: 'success',
                            title: result.message
                        })
                        $('#option-'+numoption).remove()
                        
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Une erreur est survenue. Veuillez réesayer'
                        })
                    }
                }
            })
        }

        function unlockStock() {
            let password = prompt('Mot de passe');
            if (password === "KELONIA" || password == "kelonia") {
                $('#stock_actuel').removeAttr('disabled');
            } else {
                alert('Mot de passe incorrect ! Veuillez réeesayer avec le bon mot de passe')
            }
        }

        function formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;

            return [year, month, day].join('-');
        }


        function imprimeArticle() {


            var ancienPrix = '<?php echo $article['prixttc_euro'] ?>'
            var anciendate_debut = '<?php echo date('d/m/Y', strtotime($article['promo_debut'])) ?>'
            var anciendate_fin = '<?php echo date('d/m/Y', strtotime($article['promo_fin'])) ?>'
            var ancienpromoTTC = '<?php echo $article['prixttc_promo_euro'] ?>'
            var ancienTitre = '<?php echo $article['titre'] ?>'
            var ancienBarcode = '<?php echo $article['ref'] ?>'
            var ancienPackage = '<?php echo $article['package'] ?>'



            var date_debut = $('#promo_debut').val();
            var date_fin = $('#promo_fin').val();
            var today = new Date()
            today = moment(today).format('YYYY-MM-DD');
            var promoTTC = $('#promottc').val();
            var nouveauprixTTC = parseFloat($('#mode_prix_3_achat_ttc').val()).toFixed(2)
            var nouveauTitre = $('#designation').val();
            var nouveauBarcode = $('#gencode').val();
            var nouveauPackage = $('#package').val() == "" ? null : $('#package').val();
            console.log(ancienPrix != nouveauprixTTC, anciendate_debut != date_debut, anciendate_fin != date_fin, ancienpromoTTC != promoTTC, ancienTitre != nouveauTitre, nouveauBarcode != ancienBarcode)

            // console.log(ancienPackage , nouveauPackage)
            // return;
            // if (ancienPrix != nouveauprixTTC || anciendate_debut != date_debut || anciendate_fin != date_fin || ancienpromoTTC != promoTTC || ancienTitre != nouveauTitre || nouveauBarcode != ancienBarcode ) {
            //     Toast.fire({
            //         icon: 'error',
            //         title: "Les modifications ne sont pas enregistrées, cliquez sur Enregistrer avant d'imprimer."
            //     })
            //     return;
            // }





            var [day, month, year] = date_debut.split('/');
            day++;
            date_debut = new Date(year, month - 1, day)
            date_debut = date_debut.toISOString().split('T')[0]
            console.log("date=>" + date_debut)
            var [dayfin, monthfin, yearfin] = date_fin.split('/');
            dayfin++;
            date_fin = new Date(yearfin, monthfin - 1, dayfin)
            date_fin = date_fin.toISOString().split('T')[0]

            var type = "";
            var qte = $('#printNumber').val();
            if ($('#prixNormal').is(':checked')) {
                type = "normal"
            } else if ($('#prixPromo').is(':checked')) {
                type = "promo";
            }
            if ($('#prixPromo').is(':checked')) {

                if (date_fin >= today) {
                    $.ajax({
                        url: "request.php",
                        type: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            imprimeArticle: type,
                            titre: nouveauTitre,
                            barcode: nouveauBarcode,
                            prix: nouveauprixTTC,
                            package: nouveauPackage,
                            promo: promoTTC,
                            promofin: date_fin,
                            promodebut: date_debut
                        }),
                        success: function(data) {
                            console.log(data)
                            var result = JSON.parse(data)
                            if (result.response === 1) {
                                writeToSelectedPrinter(result.message, qte)
                            } else {
                                alert('Une erreur est survenue. Veuillez réesayer')
                            }
                        }
                    })
                } else {
                    alert('Ce produit n\'est pas en promotion')
                }
            } else if ($('#prixNormal').is(':checked')) {

                $.ajax({
                    url: "request.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        imprimeArticle: type,
                        titre: nouveauTitre,
                        barcode: nouveauBarcode,
                        prix: nouveauprixTTC,
                        package: nouveauPackage,
                        promo: 0,
                        promofin: date_fin,
                        promodebut: date_debut
                    }),
                    success: function(data) {
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

        }

        var oldgencode = $('#gencode').val()
        $("#editBtn").click(function(event) {
            event.preventDefault();

            var options = []
            $('input[name^="nomoption"]').each(function() {
                // Obtenez le suffixe du nom du champ 'product_name'
                var nameSuffix = $(this).attr('name').replace('nomoption', '');
                // Trouvez l'input associé avec le même suffixe
                var priceInput = $('input[name="prixoption' + nameSuffix + '"]');

                // Récupérez les valeurs des deux champs
                var productName = $(this).val();
                var priceValue = priceInput.val();

                // Ajoutez les valeurs au tableau
                options.push({
                    nomoption: productName,
                    prixoption: priceValue
                });
            });
            var famille = $('#famille').val();
            var designation = $('#designation').val();
            var gencode = $('#gencode').val();
            var stock_actuel = $('#stock_actuel').val();
            var stock_alerte = $('#stock_alerte').val();
            var codetva = $('#codetva').val();
            var colisage = $('#package').val() == "" ? null : $('#package').val();
            var quantite = $('#quantite').val();
            var unite = $('#unite').val();
            var prix_variable = $('#prix_variable').val();
            var mode_choice = $('input[type=radio][name=mode]:checked').attr('id');
            var marge = $('#mode_prix_1_marge').val();
            var mode_prix_3 = $('#mode_prix_3_achat_ttc').val();
            var mode_prix_2 = $('#mode_prix_2_achat_ht').val();
            var mode_prix_1_achat = $('#mode_prix_1_achat_ht').val();
            var promottc = $('#promottc').val();
            var promo_debut = $('#promo_debut').val();
            var promo_fin = $('#promo_fin').val();
            var stock_initial = $('#stock_initial').val();
            var raccourci = $('#shortcutHome').val();
            var dateajout = '<?php echo $article['dateajout'] ?>'
            var num = '<?php echo $idproduct ?>'
            var submitData = {
                promo_debut: promo_debut,
                promo_fin: promo_fin,
                famille: famille,
                designation: designation,
                gencode: gencode,
                stock_actuel: stock_actuel,
                stock_alerte: stock_alerte,
                codetva: codetva,
                colisage: colisage,
                quantite: quantite,
                unite: unite,
                prix_variable: prix_variable,
                marge: marge,
                mode: mode_choice,
                mode_prix_3: mode_prix_3,
                mode_prix_2: mode_prix_2,
                mode_prix_1_achat: mode_prix_1_achat,
                promottc: promottc,
                dateajout: dateajout,
                oldgencode: oldgencode,
                stock_initial: stock_initial,
                raccourci: raccourci,
                options: options,
                num: num
            };
            $.ajax({
                url: 'article.php',
                type: "POST",
                data: submitData,
                beforeSend: function() {
                    $('#editBtn').attr('disabled', true).html("En cours...");
                },
                success: function(result, statusText, jqXHR) {
                    console.log(result);
                    var response = JSON.parse(result);
                    var type = "";
                    $('#editBtn').attr("disabled", false).html("Enregistrer");
                    if (response.response === 0 && response.element === 'famille') {
                        $('#famille').css('border-color', 'red');
                        document.getElementById("famille").scrollIntoView();
                        $("#famille").addClass("swalDefaultSuccess");
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        })
                    } else if (response.response === 0 && response.element === 'designation') {
                        $('#designation').css('border-color', 'red');
                        document.getElementById("designation").scrollIntoView();
                        $("#designation").addClass("swalDefaultSuccess");
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        })
                    } else if (response.response === 3) {
                        var type = response.type;
                        console.log(type)
                        $('#' + type).css('border-color', 'red');
                        document.getElementById(type).scrollIntoView();
                        $('#' + type).addClass('swalDefaultSuccess');
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        })
                    } else if (response.response === 1) {
                        // $('#'+type).addClass('swalDefaultSuccess');

                        var prix = (mode_prix_3 > 0 ? mode_prix_3 : (mode_prix_2 > 0 ? mode_prix_2 : (mode_prix_1_achat > 0 ? mode_prix_1_achat : 0)))
                        var package = $('#package').val()
                        var prix = parseFloat(prix);
                        if ($('#prixNormal').is(':checked')) {
                            // imprimeEtiquettes(gencode,designation,prix,package)
                            type = "normal"
                            imprimeArticle()
                        } else if ($('#prixPromo').is(':checked')) {
                            promottc = parseFloat(promottc)
                            type = "promo"
                            imprimeArticle()
                            // imprimeEtiquettes(gencode,designation,promottc,package)

                        }
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        })
                        window.setTimeout(function() {
                            location.href = document.referrer
                        }, 1000);
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

        // JsBarcode("#barcode2", "9780199532179", {
        //  format:"EAN13",
        //  width:1.3,
        //  height:30,
        //  displayValue:true,
        //  fontSize:13,
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



        $('.famille input').on('keyup', function() {
            let empty = false;

            $('').each(function() {
                empty = $(this).val().length == 0;
            });

            if (empty)
                $('.famille button').attr('disabled', 'disabled');
            else
                $('.famille button').attr('disabled', false);
        });
    </script>
<?php } ?>