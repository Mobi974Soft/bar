<?php
$title = $page = 'Profil';
$accueil = 'index.php';
include('../template/header.php');
include('../infos.php');
include ('../DBConfig.php');


$check = $conn->query("SELECT * FROM table_client_info");
if($check->num_rows == 1) {
    $info = $check->fetch_assoc();
    $id = $info['id'];
    $nom = $info['nom_magasin'];
    $horairedebut = $info['heure_debut'];
    $horairefin = $info['heure_fin'];
}
if(isset($_POST['btnSaveProfil'])) {
    $nom_magasin = htmlspecialchars($_POST['nom_magasin']);
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'] ;
    if($check->num_rows == 1) {
        $sql = "UPDATE `table_client_info` 
SET `nom_magasin`='$nom_magasin',`heure_debut`='$heure_debut',`heure_fin`='$heure_fin',`nbcaisse`='3' WHERE $id";
    }
    elseif($check->num_rows != 1){
        $sql = "INSERT INTO `table_client_info`( `nom_magasin`, `heure_debut`, `heure_fin`, `nbcaisse`) 
            VALUES ('$nom_magasin','$heure_debut','$heure_fin','3')";
    }
    $query = $conn->query($sql);
}

?>
<div class="content-wrapper" style="min-height: 823px;">
    <?php include('../template/info-page.php') ?>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-2">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="m-0">Profil</h5>
                        </div>
                        <div class="card-body">
                           <form action="" method="POST">
                               <div class="form-group">
                                   <label for="exampleInputBorder">Nom de l'enseigne</code></label>
                                   <input type="text" class="form-control form-control-border" name="nom_magasin" id="exampleInputBorder" placeholder=""
                                          value="<?php echo isset($nom) ? $nom : "" ?>"
                                   >
                               </div>
<!--                               <div class="form-group">-->
<!--                                   <label for="exampleInputBorder">Adresse</code></label>-->
<!--                                   <input type="text" class="form-control form-control-border" name="adresse" id="exampleInputBorder" placeholder="">-->
<!--                               </div>-->
<!--                               <div class="row">-->
<!--                                   <div class="col-md-6">-->
<!--                                       <div class="form-group">-->
<!--                                           <label for="exampleInputBorder">Code postal</code></label>-->
<!--                                           <input type="text" class="form-control form-control-border" name="code_postal" id="exampleInputBorder" placeholder="">-->
<!--                                       </div>-->
<!--                                   </div>-->
<!--                                   <div class="col-md-6">-->
<!--                                       <div class="form-group">-->
<!--                                           <label for="exampleInputBorder">Ville</code></label>-->
<!--                                           <input type="text" class="form-control form-control-border" name="ville" id="exampleInputBorder" placeholder="">-->
<!--                                       </div>-->
<!--                                   </div>-->
<!--                               </div>-->

<!--                               <div class="row">-->
<!--                                   <div class="col-md-6">-->
<!--                                       <div class="form-group">-->
<!--                                           <label for="exampleInputBorder">Numéro de téléphone</code></label>-->
<!--                                           <input type="text" class="form-control form-control-border" name="phone" id="exampleInputBorder" placeholder="">-->
<!--                                       </div>-->
<!--                                   </div>-->
<!--                                   <div class="col-md-6">-->
<!--                                       <div class="form-group">-->
<!--                                           <label for="exampleInputBorder">SIRET</code></label>-->
<!--                                           <input type="text" class="form-control form-control-border" name="siret" id="exampleInputBorder" placeholder="">-->
<!--                                       </div>-->
<!--                                   </div>-->
<!--                               </div>-->

                               <div class="form-group">
                                   <div class="row">
                                       <div class="col-md-6">
                                           <label for="exampleInputBorder">Heure d'ouverture</code></label>
                                           <div class="input-group">
                                               <div class="input-group-prepend">
                                                   <span class="input-group-text"><i class="far fa-clock"></i></span>
                                               </div>
                                               <input type="time" name="heure_debut" class="form-control float-right"
                                                     value="<?php echo isset($horairedebut) ? $horairedebut : "" ?>"
                                               >
                                           </div>
                                       </div>
                                       <div class="col-md-6">
                                           <label for="exampleInputBorder">Heure de fermeture</code></label>
                                           <div class="input-group">
                                               <div class="input-group-prepend">
                                                   <span class="input-group-text"><i class="far fa-clock"></i></span>
                                               </div>
                                               <input type="time" name="heure_fin" class="form-control float-right"
                                                       value="<?php echo isset($horairefin) ? $horairefin : "" ?>"
                                               >
                                           </div>
                                       </div>
                                   </div>
                               </div>


<!--                               <div class="form-group">-->
<!--                                   <label for="exampleInputFile">Ajouter un logo</label>-->
<!--                                   <div class="input-group">-->
<!--                                       <div class="custom-file">-->
<!--                                           <input type="file" class="custom-file-input" id="exampleInputFile">-->
<!--                                           <label class="custom-file-label" for="exampleInputFile">Choisir un fichier</label>-->
<!--                                       </div>-->
<!--                               </div>-->


                               <input type="submit" class="btn btn-primary" name="btnSaveProfil"  value="Enregistrer"/>


                           </form>
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
