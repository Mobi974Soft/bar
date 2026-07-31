<?php 

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'DjlQMvzTdYZwdmXP';
$DATABASE_PASS = 'lAOy0feUOCvl0jFo';
$DATABASE_NAME = 'mobipos_clients';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
$idclient = $_SESSION['client_id'];
$sql = "SELECT * FROM clients WHERE id = $idclient";
$info_query = $con->query($sql);
$client = $info_query->fetch_assoc();

$adresse = $client['adresse'];
$adresse = explode("|",$adresse);
$adresse1 = $adresse[0];
$adresse2 = $adresse[1];
// $imprimantes_ticket = "EPSON_TM-T88VI";
$nbcaisse = $client['nbcaisse'];
$magasin = $client['nom_magasin'];
$adresse =$adresse1;
$adresse2 = $adresse2;
$numero_telephone = $client['telephone'];
$siret = $client['siret'];
if ($idclient == 2) {
    $ticket_pied="       
    ";
    $ticket_pied = "
    ECHANGE MAXIMUM SOUS 8 JOURS

    MERCI DE VOTRE VISITE
    ET A TRES BIENTOT
    ";
}elseif($idclient == 1){
    $ticket_pied = "
    MERCI DE VOTRE VISITE
    ET A TRES BIENTOT
    ------------------------------------------

    ECHANGE OU AVOIR SOUS 72H
    MARCHANDISE AVEC EMBALLAGE D'ORIGINE 
    INTACT ET TICKET DE CAISSE
    ";
}
elseif($idclient == 9){
    $ticket_pied = "
    MERCI DE VOTRE VISITE
    ET A TRES BIENTOT
    --------------------------------------

    ARTICLES SOLDES NI REPRIS NI ECHANGES
    ";
}

// REGLAGE ETIQUETTE POUR PRIX AVEC ENTIER 2CHIFFRE (Pour 3 & 1 se repositionnera automatiquement)
// Titre produit
$posXtitre = "20";
$posYtitre = "0";
// Titre produit
$posXpackage = "23";
$posYpackage = "38";
// codebarre
$posXbarcode = "35";
$posYbarcode = "110";
//prix entier
$posXprixEntier = "255";
$posYprixEntier = "90";
//prix decimal
$posXprixDecimal = "340";
$posYprixDecimal = "137";
// symbole euro
$posXeuro = "315";
$posYeuro = "92";

// PROMO
// Barre promo
$posXbarrePromo = "275";
$posYbarrePromo = "150";
// Date promo fin
$posXpromofin = "30";
$posYpromofin = "80";


// promo nouveau prix
$posXnouveauPrixEntier = "295";
$posYnouveauPrixEntier = "48";
// promo nouveau prix decimal
$posXnouveauPrixDecimal = "342";
$posYnouveauPrixDecimal = "92";

// euro nouveau prix promo
$posXnouveaueuropromo = "320";
$posYnouveaueuropromo = "55";

// promo ancien prix
$posXancienPrixEntier = "280";
$posYancienPrixEntier = "155";
// promo ancien prix decimal
$posXancienPrixDecimal = "340";
$posYancienPrixDecimal = "160";
//symbol euro promo
$posXeuropromo = "365";
$posYeuropromo = "164";


$arrayPos = array(
    "titre"=>array($posXtitre,$posYtitre),
    "package"=>array($posXpackage,$posYpackage),
    "codebarre"=>array($posXbarcode,$posYbarcode),
    "prixEntier"=>array($posXprixEntier,$posYprixEntier),
    "prixDecimal"=>array($posXprixDecimal,$posYprixDecimal),
    "euro"=>array($posXeuro,$posYeuro),
    "barre_promo"=>array($posXbarrePromo,$posYbarrePromo),
    "promoAncienPrix"=>array($posXancienPrixEntier,$posYancienPrixEntier),
    "promoAncienDecimal"=>array($posXancienPrixDecimal,$posYancienPrixDecimal),
    "euroPromo"=>array($posXeuropromo,$posYeuropromo),
    'promoFin'=>array($posXpromofin,$posYpromofin),
    "nouveauPrixEntier"=>array($posXnouveauPrixEntier,$posYnouveauPrixEntier),
    "nouveauPrixDecimal"=>array($posXnouveauPrixDecimal,$posYnouveauPrixDecimal),
    "nouveauPrixEuro"=>array($posXnouveaueuropromo,$posYnouveaueuropromo)
);

 ?>
