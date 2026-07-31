<?php
// Configurations des bases de données
$localDb = [
    'host' => 'localhost',  // Serveur local
    'dbname' => 'bar', // Nom de la base locale
    'user' => 'caisse',       // Utilisateur local
    'pass' => 'za2xY+MM1d_5fy#s'            // Mot de passe local
];

$remoteDb = [
    'host' => '81.181.199.133',  // Serveur distant
    'dbname' => 'mobipos_test_staging',        // Nom de la base distante
    'user' => 'bUwspaXWewlxBalK',        // Utilisateur distant
    'pass' => 'eX3Q8m3MxCwo9DEF'     // Mot de passe distant
];


try {
    // Connexion aux bases de données
    $pdoLocal = new PDO("mysql:host={$localDb['host']};dbname={$localDb['dbname']};charset=utf8", $localDb['user'], $localDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdoRemote = new PDO("mysql:host={$remoteDb['host']};dbname={$remoteDb['dbname']};charset=utf8", $remoteDb['user'], $remoteDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Nom de la table à synchroniser
    $table = 'table_client_catalogue'; // Modifier avec le nom de votre table

    // Supprimer toutes les données locales
    $pdoLocal->exec("DELETE FROM $table");

    // Récupérer les données de la base distante
    $stmt = $pdoRemote->query("SELECT * FROM $table");
    $remoteData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Insérer les données dans la base locale
    foreach ($remoteData as $row) {
        $columns = implode(', ', array_keys($row));
        $placeholders = ':' . implode(', :', array_keys($row));
        $insertQuery = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $pdoLocal->prepare($insertQuery);
        $stmt->execute($row);
    }

    echo "Synchronisation terminée avec succès. La base locale a été remplacée.";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
