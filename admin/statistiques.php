<?php
if (isset($_GET['startDate'])) {
    $date = $_GET['startDate'];
    $dateFormated = strtotime(str_replace('-', '/', $date));
    $dateCloture = date('d/m/Y',$dateFormated);
} else {
    $dateFormated = date('d/m/Y');
    $dateCloture = date('d/m/Y');
    $date = date('Y-m-d');
}


$title = 'Statistiques';
$page = 'Statistiques';
$accueil = 'index.php';


include('../template/header.php');
include('../functions.php');
include('../DBConfig.php');
include('../infos.php');



if(isset($_GET['startDate'],$_GET['endDate'])){
    $startDate = $_GET['startDate']." 00:00:00";
    $endDate = $_GET['endDate']." 23:59:59";
    if (isset($_GET['id_caisse'])) {
        $id_caisse=$_GET['id_caisse'];
        $sql = "SELECT * FROM table_client_ticket WHERE  date > '$startDate' AND date < '$endDate' AND id_caisse = $id_caisse";
    } else {
        $sql = "SELECT * FROM table_client_ticket WHERE  date > '$startDate' AND date < '$endDate'";
    }

    // HEATMAP REQUETE
    $sql_heatmap = "SELECT d , h,  sum(p_espece_euro+p_cb+p_cheque_euro) as total from ( select dayname(date) d, hour(date) h, p_espece_euro,p_cb,p_cheque_euro from table_client_ticket WHERE date >= '$startDate' and date <= '$endDate' and id_caisse != 99 ) dt group by d, h order by d ASC";
    $sql_cat = "SELECT h from ( select  hour(date) h from table_client_ticket WHERE date >= '$startDate' and date <= '$endDate' and id_caisse != 99 ) dt group by h order by h ASC";
    // FIN REQUETE HEATMAP
}
elseif(isset($_GET['startDate'])){
    $date = $_GET['startDate'];
    if(isset($_GET['id_caisse'])){
        $id_caisse=$_GET['id_caisse'];
        $sql = "SELECT * FROM table_client_ticket WHERE date LIKE '$date%' AND id_caisse = $id_caisse";  
    }else{
        $sql = "SELECT * FROM table_client_ticket WHERE date LIKE '$date%' ";
    }

    // HEATMAP REQUETE
    $sql_heatmap = "SELECT d , h,  sum(p_espece_euro+p_cb+p_cheque_euro) as total from ( select dayname(date) d, hour(date) h, p_espece_euro,p_cb,p_cheque_euro from table_client_ticket WHERE date LIKE '%$date%' and id_caisse != 99  ) dt group by d, h order by d ASC";
    $sql_cat = "SELECT h from ( select  hour(date) h from table_client_ticket WHERE date LIKE '%$date%' and id_caisse != 99) dt group by h order by h ASC";
    // FIN REQUETE HEATMAP
}
else{
    if(isset($_GET['id_caisse'])){
        $id_caisse=$_GET['id_caisse'];
        $sql = "SELECT * FROM table_client_ticket WHERE date LIKE '$date%' AND id_caisse = $id_caisse";  
    }else{
        $sql = "SELECT * FROM table_client_ticket WHERE date LIKE '$date%' ";
    }

    // HEATMAP REQUETE
    $sql_heatmap = "SELECT d , h,  sum(p_espece_euro+p_cb+p_cheque_euro) as total from ( select dayname(date) d, hour(date) h, p_espece_euro,p_cb,p_cheque_euro from table_client_ticket WHERE date LIKE '%$date%' and id_caisse != 99  ) dt group by d, h order by d ASC";
    $sql_cat = "SELECT h from ( select  hour(date) h from table_client_ticket WHERE date LIKE '%$date%' and id_caisse != 99 ) dt group by h order by h ASC";
    // FIN REQUETE HEATMAP
}

$query_heatmap = $conn->query($sql_heatmap);
$query_cat = $conn->query($sql_cat);
$cats = [];
while ($row = $query_cat->fetch_assoc()) {
    $cats[] = $row['h'];
}
//HEATMAP CODE
$lundi = [];
$mardi = [];
$mercredi = [];
$jeudi = [];
$vendredi = [];
$samedi = [];
$dimanche = [];


while ($ligne = $query_heatmap->fetch_assoc()) {
    $montant = $ligne['total'];
    $heure = $ligne['h'];

    if ($ligne['d'] == "Monday") {
        $lundi[$heure] = round($montant,0);

    }

    if ($ligne['d'] == "Tuesday") {
        $mardi[$heure] = round($montant,0);
    }

    if ($ligne['d'] == "Wednesday") {
        $mercredi[$heure] = round($montant,0);
    }

    if ($ligne['d'] == "Thursday") {
        $jeudi[$heure] = round($montant,0);
    }

    if ($ligne['d'] == "Friday") {
        $vendredi[$heure] = round($montant,0);
    }

    if ($ligne['d'] == "Saturday") {
        $samedi[$heure] = round($montant,0);
    }

    if ($ligne['d'] == "Sunday") {
        $dimanche[$heure] = round($montant,0);
    }
}

// Tableau initial

function completeArray($tableau){
    // Obtenir la première et la dernière clé du tableau
    $premiereCle = min(array_keys($tableau));
    $derniereCle = max(array_keys($tableau));

// Créer un tableau avec les clés manquantes dans la suite de nombres
    $clesManquantes = range($premiereCle, $derniereCle);

// Compléter le tableau initial avec les clés manquantes
    $tableauComplet = array_fill_keys($clesManquantes, 0);
    $tableauComplet = array_replace($tableauComplet, $tableau);

    return $tableauComplet;
}


// Afficher le tableau complet
$lundi = array_values(completeArray($lundi));
$mardi = array_values(completeArray($mardi));
$mercredi = array_values(completeArray($mercredi));
$jeudi = array_values(completeArray($jeudi));
$vendredi = array_values(completeArray($vendredi));
$samedi = array_values(completeArray($samedi));
$dimanche = array_values(completeArray($dimanche));
;
// FIN HEATMAP CODE


$query = $conn->query($sql);
$nbticket = $query->num_rows;

$mois_fr = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août",
    "septembre", "octobre", "novembre", "décembre");
list($annee, $mois, $jour) = explode('-', $date);

$mois = $mois[0] == 0 ? $mois[1] : $mois;
$nameOfDay = date('D', strtotime($date));
$jours = array('Mon' => 'Lundi', 'Tue' => 'Mardi', 'Wed' => 'Mercredi', 'Thu' => 'Jeudi', 'Fri' => 'Vendredi', 'Sat' => 'Samedi', 'Sun' => 'Dimanche');
$fulldate = $jours[$nameOfDay] . " " . $jour . " " . $mois_fr[$mois] . " " . $annee;


$p_espece_euro = 0;
$p_cb = 0;
$p_cheque_euro = 0;
$ra = 0;
$ca = 0;
$ca_ht = 0;
$total_tva8 = 0;
$total_tva2 = 0;
$total_tva1 = 0;
$cumul_tva = 0;
$total_remise = 0;
$total_tva0 = 0;
$total_remise_tva = 0;

while ($ticket = $query->fetch_assoc()) {
    $total_euro_du = $ticket['total_euro_du'];
    $p_espece_euro += $ticket['p_espece_euro'] ;
    $p_cb += $ticket['p_cb'];
    $p_cheque_euro += $ticket['p_cheque_euro'];
    $ra += $ticket['retourarticle'];


}


if (isset($_GET['startDate']) and isset($_GET['endDate'])) {
    $startDate = str_replace('/', '-', $_GET['startDate']);
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = str_replace('/', '-', $_GET['endDate']);
    $endDate = date('Y-m-d', strtotime($endDate));
    if (isset($_GET['id_caisse'])) {
        $id_caisse = $_GET['id_caisse'];

        if($startDate!=$endDate){
            $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date >= '$startDate' AND date <= '$endDate' AND id_caisse = $id_caisse");
        }
        else{
         $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date = '$startDate' AND id_caisse = $id_caisse"); 
     }
 } else {
    if($startDate!=$endDate){
        $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date >= '$startDate' AND date <= '$endDate'");
    }else{
        $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date = '$startDate'");
    }

}

$fulldate = "Du " . date('d/m/Y',strtotime($_GET['startDate'])) . " au " . date('d/m/Y',strtotime($_GET['endDate']));
} else {
    if (isset($_GET['id_caisse'])) {
        $id_caisse = $_GET['id_caisse'];
        $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date LIKE '$date%' AND id_caisse = $id_caisse ");

    } else {
        $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date LIKE '$date%' ");
    }

}
// $commandes = $conn->query("SELECT * FROM table_client_commandes WHERE  date LIKE '$date%' ");
while ($commande = $commandes->fetch_assoc()) {
    if ($commande['id_produit'] == "remise") {
        $ca += $commande['pu_euro'] * -$commande['qte'];
        $ca_ht -= $commande['pu_euro']  / (1 + ($commande['taux_tva'] / 100) );
        $total_remise += $commande['pu_euro'];
    } else {
        $ca +=  $commande['qte'] * $commande['pu_euro']  - $commande['remise'] - $commande['promo'];
        $total_remise += $commande['remise'];
        $ca_ht +=  ( ($commande['qte'] * $commande['pu_euro']) - $commande['remise'] - $commande['promo'] ) / (1 + ($commande['taux_tva'] / 100) )  ;

    }

    $remise_tva = $remise + $commande['promo'] * $commande['qte'];
    if ($commande['taux_tva'] == 8.50) {
        $montantApresRemise = ($commande['pu_euro']* $commande['qte']) - $commande['remise'] - $commande['promo'];
        $total_tva8 += $commande['id_produit'] == "remise" ?  ($montantApresRemise  - ( $montantApresRemise   / (1 + (8.5 / 100)) )) * -1 : $montantApresRemise  - ( $montantApresRemise   / (1 + (8.5 / 100)) ) ;
        
    } else if ($commande['taux_tva'] == 2.10) {
        $montantApresRemise = ($commande['pu_euro']* $commande['qte']) - $commande['remise'] - $commande['promo'];
        $total_tva2 +=$montantApresRemise  - ( $montantApresRemise   / (1 + (2.1 / 100)) ) ;

    } else if ($commande['taux_tva'] == 1.05) {
        $montantApresRemise = ($commande['pu_euro']* $commande['qte']) - $commande['remise'] - $commande['promo'];
        $total_tva1 += $montantApresRemise  - ( $montantApresRemise   / (1 + (1.05 / 100)) ) ;
    }else {
        if($commande['id_produit'] != "remise"){
            $total_tva0 += $commande['qte'] * $commande['pu_euro'];
        }
        
    }

}
if($total_remise_tva != 0){
    $cumul_tva = $total_tva8 + $total_tva2 + $total_tva1;
    // $ca_ht += $total_remise_tva;
}
else{
    $cumul_tva = $total_tva8 + $total_tva2 + $total_tva1  ;
}
// $CA_TTC = $p
$logs = $conn->query("SELECT * FROM table_stat_lock");
$logs = $logs->fetch_assoc();
$id = $logs['user'];
$mdp = $logs['mdp'];



?>

<div class="content-wrapper d-print-block" style="">
    <?php include('../template/info-page.php') ?>
    <div class="content">
        <div class="container " >
            <div class="row">
                <?php if (isset($_POST['btnAccessStat'])) {
                    session_start();
                    $_SESSION['username'] = $_POST['username'];
                    $_SESSION['password'] = $_POST['password'];
                }
                if ($_SESSION['username'] == $id and $_SESSION['password'] == $mdp) {
                    ?>

                    <div class="col-md-4 d-print-none text-center">
                       
                        
                         <h5 class="bg-info" style="font-weight: 800;text-align: center;padding: 5px 0;">
                        Export CSV</h5>

                        <div class="form-group">
                            <label>Date début:</label>
                            <input class="form-control" type="date" id="startdatecsv" name="startdatecsv"
                            value=""
                            >
                        </div>
                        <div class="form-group">
                            <label>Date Fin:</label>
                            <input class="form-control" type="date" id="enddatecsv" name="enddatecsv"
                            value=""
                            >
                        </div>

                        <button class="btn btn-md btn-primary" id="exportCSV" style="margin: 16px 0;">
                            Exporter 
                        </button>

                        <h5 class="bg-info" style="font-weight: 800;text-align: center;padding: 5px 0;">
                        CALENDRIER</h5>
                        <div class="form-row mb-3">

                        <!-- <input type="date" style="width: 94%" class="hide-replaced" id="date"
                            value="<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : $date; ?>"/> -->
                        </div>

                        <div class="form-group">
                            <label>Date début:</label>
                            <input class="form-control" type="date" id="startdate" name="startdate"
                            value="<?php echo isset($_GET['startDate']) ? $_GET['startDate'] : date('d/m/Y') ?>"
                            >
                        </div>
                        <div class="form-group">
                            <label>Date Fin:</label>
                            <input class="form-control" type="date" id="enddate" name="enddate"
                            value="<?php echo isset($_GET['endDate']) ? $_GET['endDate'] : "" ?>"
                            >
                        </div>
                        <button class="btn btn-md btn-primary" id="validDate" style="margin-top: 16px;">
                            Valider
                        </button>
                        <button class="btn btn-md btn-default" id="resetDate" style="margin-top: 16px;">
                            Réinitialiser
                        </button>
                        <br>
                        <a href="statcategorie.php"  ><button class="btn btn-md btn-info" style="margin-top:15px">Détails</button></a>
                        <!-- <a href="histogramme.php"  ><button class="btn btn-md btn-success" style="margin-top:15px">Voir Fréquentation par heure</button></a> -->
                        <a href="logout_stats.php" ><button class="btn btn-md btn-danger" style="margin-top:15px">Déconnexion Statistiques</button></a>


                    </div>
                    <div class="col-12 col-md-6 col-sm-6 offset-md-1 d-print-block order-first order-md-last" >
                        <h5 class="bg-info" style="font-weight: 600;text-align: center;padding: 5px 0;">FILTRE
                        D'AFFICHAGE : </h5>
                        <p style="font-size:18px;text-align: center;text-decoration:underline"><a href=""
                          id="AllCaisse">Toutes
                      les caisses</a></p>
                      <p style="font-size:18px;text-align: center;text-decoration:underline">Caisse n°
                        <?php
                        for ($i = 1; $i <= $nbcaisse; $i++) {
                            echo "<a href='' id='caisse-$i' onclick=sortByCaisse(this.id,event);>" . $i . "</a> ";
                        }
                        ?>
                    </p>

                    <div class="mb-5"></div>
                    <div class="card card-warning" id="stats">
                        <div class="card-header">
                            <h3 style="width:100%;"> <?php echo isset($_GET['id_caisse']) ? "Caisse " . $_GET['id_caisse'] : "Toutes les caisses" ?>
                        </h3>
                            <span style="font-size: 20px;font-weight: normal;text-align: right;"> <?php echo $fulldate ?></span>
                        </h3>


                    </div>
                    <div class="card-body">
                        <div class="row border-bottom mb-3">
                            <div class="col-8 col-md-8">
                                <p class="stats-info">Espèces euro</p>
                            </div>

                            <div class="col-4 col-md-4">
                                <p class="stats-montant"><?php echo $p_espece_euro == "" ? "0.00" : formatNumber($p_espece_euro) ?>
                            €</p>
                        </div>
                    </div>

                    <div class="row border-bottom mb-3">
                        <div class="col-8 col-md-8">
                            <p class="stats-info">Chèques € </p>
                        </div>

                        <div class="col-4 col-md-4">
                            <p class="stats-montant"><?php echo $p_cheque_euro == "" ? "0.00" : formatNumber($p_cheque_euro) ?>
                        €</p>
                    </div>
                </div>
                <div class="row border-bottom mb-3">
                    <div class="col-8 col-md-8">
                        <p class="stats-info">Carte bancaire</p>
                    </div>

                    <div class="col-4 col-md-4">
                        <p class="stats-montant"><?php echo $p_cb == "" ? "0.00" : formatNumber($p_cb) ?> €</p>
                    </div>
                </div>
                <div class="row border-bottom mb-3">
                    <div class="col-8 col-md-8">
                        <p class="stats-info text-danger">Remise </p>
                    </div>

                    <div class="col-4 col-md-4 ">
                        <p class="stats-montant text-danger"><?php echo formatNumber($total_remise) . "€" ?></p>
                    </div>
                </div>
                <div class="row border-bottom mb-3">
                    <div class="col-8 col-md-8">
                        <p class="stats-info text-danger">Retour article</p>
                    </div>

                    <div class="col-4 col-md-4 ">
                        <p class="stats-montant text-danger"><?php echo $ra == "" ? "0.00" : $ra ?>
                    €</p>
                </div>
            </div>

            <div class="row border-bottom mb-3">
                <div class="col-8 col-md-8">
                    <p class="stats-info">C.A HT</p>
                </div>
                <div class="col-4 col-md-4">
                    <p class="stats-montant "><?php 
                                            $ca_ht = ($p_espece_euro + $p_cb + $p_cheque_euro - $cumul_tva) ; 
                    echo formatNumber($ca_ht) ?>
                €</p>
            </div>
        </div>
        <?php if($total_tva8>0): ?>
            <div class="row border-bottom mb-3">
                <div class="col-8 col-md-8">
                    <p class="stats-info">Total TVA 8.5%</p>
                </div>
                <div class="col-4 col-md-4">
                    <p class="stats-montant "><?php 
                    if ($_GET['changeDate']<="2023-03-01") {
                                                // code...
                    }

                    echo formatNumber($total_tva8) ?>
                €</p>
            </div>
        </div>
    <?php endif; ?>
    <?php if($total_tva2>0): ?>
        <div class="row border-bottom mb-3">
            <div class="col-8 col-md-8">
                <p class="stats-info">Total TVA 2.1%</p>
            </div>
            <div class="col-4 col-md-4">
                <p class="stats-montant "><?php echo formatNumber($total_tva2) ?>
            €</p>
        </div>
    </div>
<?php endif; ?>
<?php if($total_tva1>0): ?>
    <div class="row border-bottom mb-3">
        <div class="col-8 col-md-8">
            <p class="stats-info">Total TVA 1.05%</p>
        </div>
        <div class="col-4 col-md-4">
            <p class="stats-montant "><?php echo formatNumber($total_tva1); ?>
        €</p>
    </div>
</div>
<?php endif; ?>
<?php if($total_tva0>0): ?>
                                <?php endif; ?>


                                <div class="row border-bottom mb-3">
                                    <div class="col-8 col-md-8">
                                        <p class="stats-info font-weight-bold">CUMUL TVA</p>
                                    </div>
                                    <div class="col-4 col-md-4">
                                        <p class="stats-montant "><?php echo number_format((float)$cumul_tva, 2, '.', ''); ?>
                                    €</p>
                                </div>
                            </div>
                            <div class="row bg-warning border-bottom mb-3 align-middle pt-2 pb-2">
                                <div class="col-md-6 ">
                                    <p class="font-weight-bold m-0"
                                    style="font-family: 'Tahoma';font-size: 20px;">Chiffre d'affaires</p>
                                </div>

                                <div class="col-md-6 ">
                                    <?php 
                                   
                                    $client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : "";
                                    if ( $client_id == 10 || $client_id == 1 || $client_id == 16 ) {
                                        $ca = $p_espece_euro + $p_cb + $p_cheque_euro;
                                    }
                                    
                                    ?>
                                    <p class="text-danger m-0"
                                    style="font-family: 'Tahoma';font-size: 24px;font-weight: 800;text-align: right;"><?php echo formatNumber($ca) ?>
                                    € <span style="font-weight: normal;">TTC</span></p>
                                </div>
                            </div>
                            <div class="row border-bottom mb-3">
                                    <div class="col-8 col-md-8">
                                        <p class="stats-info font-weight-bold">Panier Moyen</p>
                                    </div>
                                    <div class="col-4 col-md-4">
                                        <p class="stats-montant "><?php 

                                        echo number_format((float)$ca/$nbticket, 2, '.', ''); ?>
                                    €</p>
                                </div>
                            </div>
                                    

                                    

                                </div>
                            </div>


                            

                            <?php
                            $valeurcaisse = $conn->query("SELECT date FROM table_client_valeurcaisse WHERE date like '%$date%' ");
                            if($valeurcaisse->num_rows>=1){
                                ?>
                                <p style="font-size:24px;text-align: center;text-decoration:underline;font-weight: 600;">La caisse est cloturé</p>

                                <?php
                            }else{
                                ?>
                                <p style="font-size:24px;text-align: center;text-decoration:underline;font-weight: 600;">
                                    <a href="cloture-caisse.php?date=<?php echo $dateCloture; ?>" target="_blank" >Faire la cloture de caisse du <?php echo $dateCloture ?></a></p>
                                    <?php
                                }
                                ?>

                            </div>

                            <?php

                        } else {
                            ?>
                            <div class="col-md-4 offset-4">
                                <form action="" method="post">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" name="username" placeholder="Utilisateur">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" name="password" placeholder="Mot de passe">
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="submit" name="btnAccessStat" class="btn btn-primary btn-block"
                                            value="Confirmer"/>
                                        </div>

                                    </div>
                                </form>
                            </div>

                            <?php
                        } ?>


                    </div>
                </div>
            </div>
            <hr>
            <?php if($_SESSION['username'] == $id and $_SESSION['password'] == $mdp): ?>
            <div class="row">
                <div class="col-12">
                    <div id="chart" style="height:600px"></div>
                </div>
            </div>
        <?php endif; ?>
            <div class="modal fade" id="modal-csv">
                <div class="modal-dialog">
                    <div class="modal-content ">
                        <div class="modal-header" style="display:none">
                            <h4 class="modal-title">Export CSV des stats</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <h5 class="bg-dark" style="font-weight: 800;text-align: center;padding: 5px 0;">Choisir
                                une période </h5>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="export">
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
        </div>


        <?php include('../template/footer.php') ?>



                <style>
            body {
                font-family: 'Tahoma';
            }

            .stats-info {
                font-family: 'Tahoma';
                font-size: 18px;
            }

            .stats-montant {
                font-family: 'Tahoma';
                font-size: 18px;
                font-weight: 600;
                text-align: right;
            }

            body > div > div.content-wrapper > div.content > div > div > div.col-md-3.offset-md-1 > div > input.ws-date.ws-inputreplace.hide-replaced.hide-inputbtns.wsshadow-1649417515035.user-success {
                visibility: hidden;
            }

        </style>

        <!-- <script src="../js-webshim/minified/polyfiller.js"></script> -->
        <script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <!-- Heatmap Script -->
        <script type="text/javascript">

                var periode = '<?php echo isset($_GET['startDate']) && isset($_GET['endDate']) ? "Du " . date('d/m/Y',strtotime($_GET['startDate'])) . " au " . date('d/m/Y',strtotime($_GET['endDate'])) : (isset($_GET['startDate']) ? "Du ". date('d/m/Y',strtotime($_GET['startDate'])) : "Du ".date('d/m/Y')) ?>'

                var lundi = '<?php echo json_encode($lundi) ?>'
                lundi = JSON.parse(lundi)

                var mardi = '<?php echo json_encode($mardi) ?>'
                mardi = JSON.parse(mardi)

                var mercredi = '<?php echo json_encode($mercredi) ?>'
                mercredi = JSON.parse(mercredi)

                var jeudi = '<?php echo json_encode($jeudi) ?>'
                jeudi = JSON.parse(jeudi)

                var vendredi = '<?php echo json_encode($vendredi) ?>'
                vendredi = JSON.parse(vendredi)

                var samedi = '<?php echo json_encode($samedi) ?>'
                samedi = JSON.parse(samedi)

                var dimanche = '<?php echo json_encode($dimanche) ?>'
                dimanche = JSON.parse(dimanche)

                var cats = '<?php echo json_encode($cats) ?>'
                cats = JSON.parse(cats)

                var options = {
                    series: [{
                        name: 'Dimanche',
                        data: dimanche
                    },
                    {
                        name: 'Samedi',
                        data: samedi
                    },
                    {
                        name: 'Vendredi',
                        data: vendredi
                    },
                    {
                        name: 'Jeudi',
                        data: jeudi
                    },
                    {
                        name: 'Mercredi',
                        data: mercredi
                    },
                    {
                        name: 'Mardi',
                        data: mardi
                    },
                    {
                        name: 'Lundi',
                        data: lundi
                    },
                    ],
                    chart: {
                        height: '100%',
                        type: 'heatmap',
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 'normal',
                            colors: ['#000000']
                        },
                    },
                    colors: ["#008FFB"],
                    xaxis: {
                        type: 'Heure',
                        categories: cats
                    },
                    title: {
                        text: 'Total Vente Par Heure ' + periode ,

                    },
                };

                var chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();
            </script>
            <!-- Fin Heatmap Script -->
        <script type="text/javascript">

            function sortByCaisse(id, event) {
                event.preventDefault();
                var id_caisse = $('#' + id).text();
                console.log("INDEX=>"+window.location.href+"&id_caisse=" + id_caisse)
                if (window.location.href.indexOf("startDate") > -1) {
                   window.location = window.location.href+"&id_caisse=" + id_caisse
               } else if(window.location.href.indexOf("startDate") > -1) {
                window.location = window.location.href+"&id_caisse=" + id_caisse
            }else{
                window.location = "?id_caisse=" + id_caisse

            }

        }

        $('#AllCaisse').click(function (e) {
            e.preventDefault()
            window.location.href = "statistiques.php";
        });

// CALENDRIER PERIODE POUR AFFICHAGE DES STATS
        $('#validDate').click(function(){
            var startDate = $('#startdate').val()
            var endDate = $('#enddate').val()

            if (startDate != "" && endDate == "") {
                $.ajax({
                    url: "statistiques.php?startDate=" + startDate ,
                    type: "GET",
                    success: function (response) {
                        window.location.href = "statistiques.php?startDate=" + startDate 
                    }
                })
            }else if(startDate != "" && endDate != ""){
                $.ajax({
                    url: "statistiques.php?startDate=" + startDate + "&endDate=" + endDate,
                    type: "GET",
                    success: function (response) {
                        window.location.href = "statistiques.php?startDate=" + startDate + "&endDate=" + endDate 
                    }
                })
            }
        })
$('#resetDate').click(function(){
                $('#startdate').val("")
                $('#enddate').val("")
            })
      
    

    $('#exportCSV').click(function(){
        var exportDateDebut = $('#startdatecsv').val()
        var exportDateFin = $('#enddatecsv').val()
        $.ajax({
                url: "export_csv.php",
                type: "POST",
                contentType: "application/json",
                data:JSON.stringify({
                    startDate:exportDateDebut,
                    endDate:exportDateFin,
                }),
                success: function (response) {
                   var blob=new Blob([response]);
                   var link=document.createElement('a');
                   link.href=window.URL.createObjectURL(blob);
                   link.download="Export_stats-"+exportDateDebut+"-"+exportDateFin+".csv";
                   link.click();
               }
           })
    })

        


    </script>


</body>

</html>