<?php

include 'infos.php';

$user = "caisse";
$password = "za2xY+MM1d_5fy#s";
$usebdd = "mobipos";



if (PHP_OS_FAMILY=="Windows") {
    $fichier = 'C://xampp/htdocs/caisse-backend/parametre.txt'; 
}else{
    $fichier = '/var/www/localhost/caisse-backend/parametre.txt'; 
}
if (!file_exists($fichier)) {
    die("Fichier non trouvé !");
}

$contenu = file_get_contents($fichier);
$variables = [];

// Analyser chaque ligne
$ligne_par_ligne = explode("\n", $contenu);

foreach ($ligne_par_ligne as $ligne) {
    // Supprimer les espaces en début/fin de ligne
    $ligne = trim($ligne);

    // Ignorer les lignes vides
    if (empty($ligne)) {
        continue;
    }

    // Vérifier si la ligne contient une déclaration de variable
    if (strpos($ligne, '=') !== false) {
        // Diviser la ligne en clé et valeur
        list($cle, $valeur) = explode('=', $ligne, 2);

        // Nettoyer les clés et les valeurs
        $cle = trim($cle);
        $valeur = trim($valeur, " \t\n\r\0\x0B\"");

        // Ajouter la clé et la valeur dans le tableau
        $configurations[$cle] = $valeur;
    }
}

// Stocker toutes les données dans une seule variable
$parametres = $configurations;

//Pour imprimante ticket
$ip_imprimante_ticket = $parametres['ip_imprimante_ticket'];
$port_ticket = $parametres['port_ticket'];
$imprimante_nom = $parametres['imprimante_nom'];

//Pour imprimante codebarre
$port = $parametres['port'];
$ip_imprimante = $parametres['ip_imprimante'];
$imprimante_codebarre = $parametres['imprimante_codebarre'];

$ip_serveur = $parametres['ip_serveur'];
$id_caisse = $parametres['id_caisse'];

$logo = false;
$mode_serveur = 0;

$ip_caisse = array('27');
// Choisir le type de caisse -- 1 => caisse avec codebarre / 2 => resto
$type_caisse = 1;

if(isset($_GET['action']) && $_GET['action'] == 'info_caisse'){
        echo json_encode(array(
                'id_caisse' => $id_caisse,
                'type_caisse' => $type_caisse
        ));
}



