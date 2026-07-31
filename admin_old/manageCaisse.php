<?php
$title = $page = 'Gestion des caisses';
$accueil = 'index.php';
include('../template/header.php');
include('../infos.php');
//include "../login/dbconfig.php";
//
//$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
//var_dump($_SESSION);
//
//$sql = "SELECT * FROM id_caisse_used ";
//$query = $con->query($sql);



?>
<div class="content-wrapper" style="min-height: 823px;">
    <?php include('../template/info-page.php') ?>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
<!--                        <div class="card-header">-->
<!--                            <h3 class="card-title">Bordered Table</h3>-->
<!--                        </div>-->

                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>#Caisse</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                while($row = $query->fetch_assoc()){
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id_caisse'] ?></td>
                                        <td><?php echo $row['status'] == 1 ? "Caisse en cours d'utilisation" : "Caisse libre" ?></td>
                                        <td style="width: 200px"><?php echo $row['status'] == 1 ? '<button type="button" class="btn btn-block btn-danger">Déconnecter la caisse</button>': '' ?></td>

                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include('../template/footer.php') ?>



<?php include('../template/script.php') ?>
</body>

</html>
