<?php
include '../../DBConfig.php';
$query = $_POST['query'];
$query = '%' . $query . '%';

$sql = "SELECT num,titre,prixttc_euro,prixttc_promo_euro FROM table_client_catalogue WHERE titre LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $prix = $row['prixttc_promo_euro'] > 0 ? $row['prixttc_promo_euro'] : $row['prixttc_euro'];
        $titre = $row['titre'];
        $num = $row['num'];
        echo '<li style="padding:10px;border-bottom: 1px solid lightgray;cursor:pointer;" onclick="addProductToFormule('.$num.',\''.$titre.'\','.$prix.')">' . $titre . ' - ' . $prix . '€</li>';
    }
} else {
    echo "<li>Aucun résultat trouvé.</li>";
}

$stmt->close();
$conn->close();
