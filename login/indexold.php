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
    <?php if(!isset($_SESSION['loggedin'])){ ?>
        <?php if(isset($_GET['info'])): ?>
            <p class="text-danger text-center">Magasin Fermé ! Accès à l'administration non autorisé.</p>
        <?php endif; ?>
    <form action="authenticate.php" id="formLogin">
<!--        <label for="username">-->
<!--            <i class="fas fa-user"></i>-->
<!--        </label>-->
<!--        <input type="text" name="username" placeholder="Username" value="test" id="username" required>-->
        <label for="password">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="password"  placeholder="Code d'accès" id="password" required>
        <input type="submit" value="Connexion">
    </form>

    <?php
    } else { ?>
    <div id="chooseCaisse" style="padding:20px">
        <p class="text-center">Choisir une caisse</p>
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            $user_id = $_SESSION['user_id'];
            $client_id = $_SESSION['client_id'];
            $sql = "SELECT nbcaisse FROM clients WHERE id = $client_id";
            $caisses = $con->query($sql);
            $numligne = $caisses->num_rows;
            if ($numligne > 0) {
                $client = $caisses->fetch_assoc();
                $nbcaisse = (int)$client['nbcaisse'];
                
                for ($x = 1; $x <= $nbcaisse; $x++) {
                    $sql = "SELECT * FROM id_caisse_used WHERE id_caisse = $x AND client_id = $client_id";
                    $check = $con->query($sql);
                    
                    if ($check->num_rows == 1) {
                        ?>
                        <div class="row" style="margin: 20px">
                            <div class="col-md-7">
                                <button type="button" class="btn btn-block btn-dark" disabled
                                        onclick="setIdCaisse('<?php echo $x; ?>')"><?php echo "Caisse n° " . $x . " utilisé." ?></button>
                            </div>
                            <div class="col-md-5">
                                <a  class="btn btn-block btn-danger"  href="../login/logout.php?id_caisse=<?php echo $x ?>&user_id_deco=<?php echo $_SESSION['user_id'] ?>"
                                >Déconnexion</a>
                            </div>
                        </div>

                        <?php
                    } else {
                        ?>
                        <div class="row t">
                            <div class="col-md-8" style="margin: 20px auto">
                                <button type="button" class="btn btn-block btn-dark"
                                        onclick="setIdCaisse('<?php echo $x; ?>')"><?php echo "Caisse n° " . $x; ?></button>
                            </div>

                        </div>
                        <?php
                    }
                }

            }
            ?>

        <?php

        }
        ?>
    </div>
    <?php }  ?>
</div>
</body>
<script src="../lib/dist/js/jquery.js"></script>
<script src="login.js"></script>
</html>
