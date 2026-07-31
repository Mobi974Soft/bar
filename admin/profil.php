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

               <input type="submit" class="btn btn-primary" name="btnSaveProfil"  value="Enregistrer"/>


             </form>
           </div>
         </div>


         <?php 
         $client_id = $_SESSION['client_id'];
         if ($client_id == 10  ) {
           # code...
           ?>
           <div class="card card-primary card-outline">
             <div class="card-header">
              <h5 class="m-0">Liste des comptes</h5>
            </div>
            <div class="card-body">
              <?php 

              $url = "https://caisse.mobisoft.fr/clients/manageUser.php?listeUtilisateur=$client_id";
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

              $headers = array();
              $headers[] = "Accept: application/json";
              curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

              $result = curl_exec($ch);
              if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
              }
              curl_close($ch);
              $result = json_decode($result);

              if ($result->response == 1) {
                $users = $result->data;
                echo "<dl class='row'>";
                foreach ($users as $user) {
                  ?>
                  <dt class="col-sm-8"><?php echo $user->username ?></dt>
                  <dd class="col-sm-4">
                    <button class="btn btn-block btn-danger" type="button" data-toggle="modal" onclick="$('#userID').val(this.id)" data-target="#modal-password" id="<?php echo $user->id; ?>" >
                      Changer mot de passe
                    </button>
                  </dd>
                  <?php
                }
                echo "</dl>";
              }
              ?>
            </div>
          </div>
           <?php } ?>
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="m-0">Images Publicité Caisse</h5>
            </div>
            <div class="card-body">
              <form action="pub/upload.php" class="m-3"  method="post" enctype="multipart/form-data">
                Publicité 1:
                <input   type="file" name="fileToUpload" >
                <input  type="hidden" name="nom_magasin" value="<?php echo isset($nom) ? $nom : "" ?>" >
                <input  type="hidden" name="slide" value="1" >
                <input  type="submit"  class="btn btn-dark btn-sm" value="Enregistrer" name="submit">
              </form>
              <hr>
               <form action="pub/upload.php"  class="m-3" method="post" enctype="multipart/form-data">
                Publicité 2:
                <input   type="file" name="fileToUpload" >
                <input  type="hidden" name="nom_magasin" value="<?php echo isset($nom) ? $nom : "" ?>" >
                <input  type="hidden" name="slide" value="2" >
                <input  type="submit" class="btn btn-dark btn-sm" value="Enregistrer" name="submit">
              </form>
              <hr>
              <form action="pub/upload.php" class="m-3"  method="post" enctype="multipart/form-data">
                Publicité 3:
                <input   type="file" name="fileToUpload" >
                <input  type="hidden" name="nom_magasin" value="<?php echo isset($nom) ? $nom : "" ?>" >
                <input  type="hidden" name="slide" value="3" >
                <input  type="submit" class="btn btn-dark btn-sm" value="Enregistrer" name="submit">
              </form>

            </div>
          </div>

       
      </div>
    </div>
  </div>
  <!-- MODAL CHANGEMENT DE MOT DE PASSE -->
  <div class="modal fade" id="modal-password" style="display: none;" aria-hidden="true" >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Changer le mot de passe</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Entrez le nouveau mot de passe</label>
            <input type="text" class="form-control" id="inputChangePassword" style="font-size:24px;" />
            <input type="hidden" id="userID"  />
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal" >Annuler</button>
          <button type="button" class="btn btn-primary" onClick="changePassword()">
            Confirmer
          </button>
        </div>
      </div>
    </div>
  </div>

</div>
</div>


<?php include('../template/footer.php') ?>
<?php include('../template/script.php') ?>

<script type="text/javascript">

  $('#modal-password').on('shown.bs.modal', function() {
    $('#inputChangePassword').select();
  })

  function changePassword(){
    var password = $('#inputChangePassword').val()
    var user_id = $('#userID').val();

    console.log(password,user_id)

    $.ajax({
      url: "../../clients/manageUser.php",
      type:"POST",
      // contentType: "application/json",
      data: {
        changePassword: password,
        user_id: user_id
      },
      success:function(data){
        console.log(data)
        if (data == 1) {
          alert('Le mot de passe a bien été modifié ! ');
          $('#modal-password').modal('hide')
        }
        

      }
    })
  }
</script>
</body>

</html>
