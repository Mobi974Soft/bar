<?php
// Configurations des bases de données
$localDb = [
    'host' => 'localhost',
    'dbname' => 'bar',
    'user' => 'caisse',
    'pass' => 'za2xY+MM1d_5fy#s'
];



try {
    $pdoLocal = new PDO("mysql:host={$localDb['host']};dbname={$localDb['dbname']};charset=utf8", $localDb['user'], $localDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);


    $table = 'client';
    $stmtClient = $pdoLocal->query("SELECT nom_base,userdatabase,password_database FROM $table LIMIT 1");
    $dataClient = $stmtClient->fetch(PDO::FETCH_ASSOC);

    if (!$dataClient) {
        throw new Exception("Aucun client trouvé dans la base locale.");
    }

    $remoteDb = [
        'host' => '81.181.199.133',
        'dbname' => $dataClient['nom_base'],
        'user' => $dataClient['userdatabase'],
        'pass' => $dataClient['password_database']
    ];

    $pdoRemote = new PDO("mysql:host={$remoteDb['host']};dbname={$remoteDb['dbname']};charset=utf8", $remoteDb['user'], $remoteDb['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $table = 'table_client_ticket';
    if (PHP_OS_FAMILY == "Windows") {
        require 'C://xampp/htdocs/caisse-backend/parametre.php';
    } else {
        require '/var/www/localhost/caisse-backend/parametre.php';
    }

    
    if ($id_caisse == 1) {
        $stmtLocal = $pdoLocal->query("SELECT * FROM $table WHERE sendserveur = 1 AND p_cb > 0 ");
    } else {
        $stmtLocal = $pdoLocal->query("SELECT * FROM $table WHERE sendserveur = 1  ");
    }

    $localData = $stmtLocal->fetchAll(PDO::FETCH_ASSOC);

    foreach ($localData as $row) {
        $id_ticket = $row['id_ticket'];
        $id_caisse = $row['id_caisse'];

        unset($row['id']); // Supprimer l'ID auto-incrémenté

        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($row))); // Protège les noms de colonnes
        $placeholders = ':' . implode(', :', array_keys($row));
        $insertQuery = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmtRemote = $pdoRemote->prepare($insertQuery);

        if ($stmtRemote->execute($row)) {
            // Mise à jour locale
            $updateLocal = $pdoLocal->prepare("UPDATE $table SET sendserveur = 0 WHERE id_ticket = :id_ticket AND id_caisse = :id_caisse");
            $updateLocal->execute([':id_ticket' => $id_ticket, ':id_caisse' => $id_caisse]);

            // Mise à jour distante
            $updateRemote = $pdoRemote->prepare("UPDATE $table SET sendserveur = 0 WHERE id_ticket = :id_ticket AND id_caisse = :id_caisse");
            $updateRemote->execute([':id_ticket' => $id_ticket, ':id_caisse' => $id_caisse]);

            echo "✔️ Ligne $id_ticket / caisse $id_caisse synchronisée.\n";
        } else {
            $errorInfo = $stmtRemote->errorInfo();
            echo "❌ Erreur insertion distante pour ticket $id_ticket : {$errorInfo[2]}\n";
        }
    }

    echo "✅ Synchronisation terminée.\n";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
