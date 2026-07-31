<?php 
session_start();
unset($_SESSION['username']);
unset($_SESSION['password']);
$base_url = $_SERVER['SERVER_NAME'];
$url = "https://".$base_url."/restaurant/admin/";
header("Location: $url");

 ?>