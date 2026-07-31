<?php
include('dbconfig.php');
include('authenticate.php');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="../lib/dist/css/adminlte.min.css"/>
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="login">
        <h1>Caisse Connexion</h1>
        <p id="msgLogin" style="text-align:center"></p>
        <?php if(isset($_GET['info'])): ?>

            <p class="text-danger text-center">Magasin Fermé ! Accès à l'administration non autorisé.</p>
        <?php endif; ?>
        <form action="authenticate.php" id="formLogin">

            <label for="password">
                <i class="fas fa-lock"></i>
            </label>
            <input type="password" name="password"  placeholder="Code d'accès" id="password" required>
            <input type="submit" value="Connexion">
        </form>
    </div>
</body>
<script src="../lib/dist/js/jquery.js"></script>
<script src="login.js?random=<?php echo uniqid(); ?>"></script>
</html>
