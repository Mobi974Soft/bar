<?php 
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'caisse';
$DATABASE_PASS = 'za2xY+MM1d_5fy#s';
$DATABASE_NAME = 'bar';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// session_start();
$idclient = $_SESSION['client_id'];
$sql = "SELECT * FROM client WHERE id = $idclient";
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
  
    $ticket_pied = "
    MERCI DE VOTRE VISITE A BIENTOT
ECHANGE SOUS 7 JOURS AVEC VOTRE TICKET 
    ";



$connexion = $con;

if ($connexion->connect_error) {

    echo json_encode(array('message'=>"Connection failed: " . $connexion->connect_error));
}

$sqlPos = "SELECT * FROM imprime_etiquette WHERE id = 1";
$pos_query = $connexion->query($sqlPos);
$postion = $pos_query->fetch_assoc();

$posXTitre = explode("|",$postion['posTitre'])[0];
$posYTitre = explode("|",$postion['posTitre'])[1];

$posXpackage = explode("|",$postion['posPackage'])[0];
$posYpackage = explode("|",$postion['posPackage'])[1];

$posXbarcode = explode("|",$postion['posBarcode'])[0];
$posYbarcode = explode("|",$postion['posBarcode'])[1];

$posXprixEntier = explode("|",$postion['posPrixEntier'])[0];
$posYprixEntier = explode("|",$postion['posPrixEntier'])[1];

$posXprixDecimal = explode("|",$postion['posPrixDecimal'])[0];
$posYprixDecimal = explode("|",$postion['posPrixDecimal'])[1];

$posXeuro = explode("|",$postion['posEuro'])[0];
$posYeuro = explode("|",$postion['posEuro'])[1];

$posXbarrePromo = explode("|",$postion['posBarrePromo'])[0];
$posYbarrePromo = explode("|",$postion['posBarrePromo'])[1];

$posXpromofin = explode("|",$postion['posPromoFin'])[0];
$posYpromofin = explode("|",$postion['posPromoFin'])[1];

$posXnouveauPrixEntier = explode("|",$postion['posNouveauPrixEntier'])[0];
$posYnouveauPrixEntier = explode("|",$postion['posNouveauPrixEntier'])[1];

$posXnouveauPrixDecimal = explode("|",$postion['posNouveauPrixDecimal'])[0];
$posYnouveauPrixDecimal = explode("|",$postion['posNouveauPrixDecimal'])[1];

$posXnouveaueuropromo = explode("|",$postion['posNouveaueuropromo'])[0];
$posYnouveaueuropromo = explode("|",$postion['posNouveaueuropromo'])[1];

$posXancienPrixEntier = explode("|",$postion['posAncienPrixEntier'])[0];
$posYancienPrixEntier = explode("|",$postion['posAncienPrixEntier'])[1];

$posXancienPrixDecimal = explode("|",$postion['posAncienPrixDecimal'])[0];
$posYancienPrixDecimal = explode("|",$postion['posAncienPrixDecimal'])[1];

$posXeuropromo = explode("|",$postion['prixEuroPromo'])[0];
$posYeuropromo = explode("|",$postion['prixEuroPromo'])[1];

// REGLAGE ETIQUETTE POUR PRIX AVEC ENTIER 2CHIFFRE (Pour 3 & 1 se repositionnera automatiquement)
// Titre produit
// $posXtitre = "20";
// $posYtitre = "0";
// // Titre produit
// $posXpackage = "23";
// $posYpackage = "38";
// // codebarre
// $posXbarcode = "35";
// $posYbarcode = "110";
// //prix entier
// $posXprixEntier = "255";
// $posYprixEntier = "90";
// //prix decimal
// $posXprixDecimal = "340";
// $posYprixDecimal = "137";
// // symbole euro
// $posXeuro = "315";
// $posYeuro = "92";

// // PROMO
// // Barre promo
// $posXbarrePromo = "275";
// $posYbarrePromo = "150";
// // Date promo fin
// $posXpromofin = "30";
// $posYpromofin = "80";


// // promo nouveau prix
// $posXnouveauPrixEntier = "295";
// $posYnouveauPrixEntier = "48";
// // promo nouveau prix decimal
// $posXnouveauPrixDecimal = "342";
// $posYnouveauPrixDecimal = "92";

// // euro nouveau prix promo
// $posXnouveaueuropromo = "320";
// $posYnouveaueuropromo = "55";

// // promo ancien prix
// $posXancienPrixEntier = "280";
// $posYancienPrixEntier = "155";
// // promo ancien prix decimal
// $posXancienPrixDecimal = "340";
// $posYancienPrixDecimal = "160";
// //symbol euro promo
// $posXeuropromo = "365";
// $posYeuropromo = "164";


$arrayPos = array(
    "titre"=>array($posXTitre,$posYTitre),
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
