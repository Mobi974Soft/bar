<?php
// Inclure l'autoloader de Composer
require '../vendor/autoload.php';

use setasign\Fpdi\Fpdf;

// Fonction pour générer le PDF à partir du texte
function generatePDF($text)
{
    // Créer une instance de FPDF
    $pdf = new Fpdf();
    $pdf->AddPage();
    
    // Définir la police et la taille du texte
    $pdf->SetFont('Arial', '', 12);
    
    // Écrire le texte dans le PDF
    $pdf->MultiCell(0, 10, $text);
    
    // Générer le PDF
    $pdf->Output();
}

function conDbclient(){
    session_start();
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'DjlQMvzTdYZwdmXP';
    $DATABASE_PASS = 'lAOy0feUOCvl0jFo';
    $DATABASE_NAME = 'mobipos_clients';
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

    return $con;
}
$connexion = conDbclient();
$client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : false;
if ($client_id==false) {
    exit;
}
$infos = $connexion->query("SELECT * FROM clients WHERE id = $client_id");
if ($infos->num_rows>0) {
    $data = $infos->fetch_assoc();
    $nom = "test";
    $prenom = "test";
    $raison_sociale = $data['nom_magasin'];
    $numero_version = "1.0";
}else{
    exit;
}
die();
// Texte à stocker dans une variable
$texte = "Volet 1 : Partie à remplir par l'éditeur ou intégrateur du logiciel ou du système de caisse

Je soussigné, ".strtoupper($nom)." ".ucfirst($prenom).", représentant légal de la société ".$raison_sociale.", éditeur du logiciel / système de caisse nom et références caractérisant le logiciel ou système, atteste que ce logiciel/système OU les fonctionnalités de caisse de ce logiciel/système (1), mis sur le marché à  compter du DATE, dans sa version n°".$numero_version." nom et références caractérisant la version du logiciel OU système, sous le numéro de licence (2), satisfait OU satisfont aux conditions d’inaltérabilité, de sécurisation, de conservation et d’archivage des données en vue du contrôle de l’administration fiscale, prévues au 3° bis du I de l’article 286 du code général des impôts.

[J'atteste que la dernière version majeure de ce logiciel ou système est identifiée avec la racine suivante : XXX et que les versions mineures développées ultérieurement à cette version majeure sont ou seront identifiées par les subdivisions suivantes de cette racine : XXX-aaa. Je m'engage à ce que ces subdivisions ne soient utilisées par RAISON SOCIALE de l'éditeur que pour l'identification des versions mineures ultérieures, à l'exclusion de toute version majeure. Les versions majeures et mineures du logiciel ou système s'entendent au sens du III-A § 340 du BOI-TVA-DECLA-30-10-30 ] (3)

Le périmètre couvert par cette attestation concerne les fonctionnalités suivantes : (1)

Les fonctionnalités suivantes ne sont pas couvertes par cette attestation : (1)

Fait à (VILLE) ,                                           Le (DATE) ,

Signature du représentant légal de l’éditeur du logiciel ou système de caisse :

Il est rappelé que l’établissement d’une fausse attestation est un délit pénal passible de 3 ans d’emprisonnement et de 45 000 € d’amende (code pénal, art. 441-1). L'usage d'une fausse attestation est passible des mêmes peines.

----------------

Volet 2 : Partie à remplir par l'entreprise qui utilise le logiciel  ou le système de caisse

Je soussigné, NOM Prénom, représentant légal de la  société  RAISON SOCIALE, certifie avoir acquis ou téléchargé le DATE, auprès de RAISON SOCIALE du distributeur, le logiciel  / système de caisse mentionné au volet 1 de cette attestation.

J'atteste utiliser ce logiciel / système de caisse pour enregistrer les règlements de mes clients particuliers, conformément à la réglementation fiscale en vigueur, depuis le DATE.

Fait à (Ville),                                           Le (DATE),

Signature du représentant légal :

Il est rappelé que l’établissement d’une fausse attestation est un délit pénal passible de 3 ans d’emprisonnement et de 45 000 € d’amende (code pénal, art. 441-1). L'usage d'une fausse attestation est passible des mêmes peines.

Les volets 1 et 2 de cette attestation doivent être présentés à l’administration fiscale en cas de contrôle. Elle n’a de valeur que si son volet 2 est dûment complété et signé par l’entreprise utilisatrice du logiciel / système.

(1) à adapter et à compléter selon le cas  ;

(2) quand il existe une licence  ;

(3) Mention facultative à stipuler  par l'éditeur pour permettre l'application de la tolérance prévue au III-B § 380 du BOI-TVA-DECLA-30-10-30.";

// Appeler la fonction pour générer le PDF
generatePDF($texte);
?>
