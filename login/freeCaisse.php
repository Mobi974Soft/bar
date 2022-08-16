<?php
include('dbconfig.php');

// LIBERE TOUTES LES CAISSES APRES FERMETURE DU MAGASIN
session_destroy();
$sql = "DELETE FROM id_caisse_used";
$delete = $con->query($sql);
