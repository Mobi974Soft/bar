<?php
// Configurations des bases de données
$localDb = [
    'host' => 'localhost',
    'dbname' => 'bar',
    'user' => 'caisse',
    'pass' => 'za2xY+MM1d_5fy#s'
];

$remoteDb = [
    'host' => '81.181.199.133',
    'dbname' => 'mobipos_test_staging',
    'user' => 'bUwspaXWewlxBalK',
    'pass' => 'eX3Q8m3MxCwo9DEF'
];

try {
    // Connexions
    $pdoLocal = new PDO("mysql:host={$localDb['host']};dbname={$localDb['dbname']};charset=utf8", $localDb['user'], $localDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdoRemote = new PDO("mysql:host={$remoteDb['host']};dbname={$remoteDb['dbname']};charset=utf8", $remoteDb['user'], $remoteDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $table = 'table_client_ticket'; // Table à synchroniser

    // Récupérer les données locales à envoyer (où sendserveur = 1)
    $stmtLocal = $pdoLocal->query("SELECT * FROM $table WHERE sendserveur = 1 ");
    $localData = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);

    foreach ($localData as $row) {
        // Sauvegarder l'ID ticket et caisse pour les mises à jour
        $id_ticket = $row['id_ticket'];
        $id_caisse = $row['id_caisse'];

        // Enlever l'ID pour éviter les conflits en insertion distante
        unset($row['id']);

        // Préparer l'insertion distante
        $columns = implode(', ', array_keys($row));
        $placeholders = ':' . implode(', :', array_keys($row));
        $insertQuery = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmtRemote = $pdoRemote->prepare($insertQuery);

        // Exécuter l'insertion distante
        if ($stmtRemote->execute($row)) {
            // ✅ Mise à jour locale
            $updateLocal = $pdoLocal->prepare("UPDATE $table SET sendserveur = 0 WHERE id_ticket = :id_ticket AND id_caisse = :id_caisse");
            $updateLocal->execute([':id_ticket' => $id_ticket, ':id_caisse' => $id_caisse]);

            // ✅ Mise à jour distante
            $updateRemote = $pdoRemote->prepare("UPDATE $table SET sendserveur = 0 WHERE id_ticket = :id_ticket AND id_caisse = :id_caisse");
            $updateRemote->execute([':id_ticket' => $id_ticket, ':id_caisse' => $id_caisse]);
        } else {
            echo "Erreur lors de l'envoi d'une ligne.\n";
        }
    }

    echo "Données envoyées avec succès.";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
