<?php
include('../DBConfig.php');

// Paramètres de pagination
$elementsParPage = 10;
$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($pageCourante - 1) * $elementsParPage;

// Requête pour récupérer les données paginées
$sql = "SELECT * FROM table_client_catalogue WHERE cath = 3157 LIMIT $elementsParPage OFFSET $offset";
$resultat = $conn->query($sql);

// Récupération des données
$donnees = array();
while ($ligne = $resultat->fetch_assoc()) {
    $donnees[] = $ligne;
}

// Requête pour compter le nombre total de lignes
$sqlTotal = "SELECT COUNT(*) AS total FROM table_client_catalogue WHERE cath = 3157";
$resultatTotal = $conn->query($sqlTotal);
$totalLignes = $resultatTotal->fetch_assoc()['total'];

// Calcul du nombre total de pages
$totalPages = ceil($totalLignes / $elementsParPage);

// Fermeture de la connexion à la base de données

// Retourner les données paginées au format JSON
echo json_encode([
    'donnees' => $donnees,
    'totalPages' => $totalPages
]);
?>




