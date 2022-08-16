<?php
include('dbconfig.php');

// LIBERE TOUTES LES CAISSES APRES FERMETURE DU MAGASIN
session_start();
$client_id = $_SESSION['client_id'];
$sql = "DELETE FROM id_caisse_used";
$delete = $con->query($sql);
session_destroy();
