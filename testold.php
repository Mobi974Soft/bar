<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$handle = fopen("PRN", "w"); fwrite ($handle, "dzadazdazdaz"); fclose ($handle);
 ?>
<script src="https://parzibyte.github.io/plugin-ticket-js/Impresora.js"></script>
