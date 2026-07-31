<?php 

include('../DBConfig.php');
include('../functions.php');


$accueil = 'index.php';
include('../template/header.php');
?>

<div class="content-wrapper" style="min-height: 823px;">
	<?php include('../template/info-page.php') ?>
	<div class="content">
		<div class="container">
			<div class="row">
				<div class="col-6">
					<div class="card card-primary">
					<div class="card-header">
						Liste des produits qui s'affiche sur la caisse
					</div>
					<div class="card-body">
						<ul>
							<?php
							$sql = "SELECT titre,ref,accueil FROM table_client_catalogue WHERE accueil = 1";
							$query = $conn->query($sql);

							while ($row = $query->fetch_assoc()) {
								?>
								<li>
									<div class="form-check">
										<input class="form-check-input"  onchange="changeStatusAccueil($(this))" type="checkbox" <?php echo $row['accueil'] == 1 ? "checked" : "" ?>>
										<input type="hidden" value="<?php echo $row['ref'] ?>" id="refAccueil">
										<label class="form-check-label"><?php echo $row['titre'] ?></label>
									</div>
								</li>
								<?php
							}
							?> 
						</ul>
					</div>
					
				</div>
				</div>
			</div>
		</div>
	</div>
</div>


<?php include('../template/footer.php') ?>
<?php include('../template/script.php') ?>
<script type="text/javascript">
	function changeStatusAccueil(elm){
		var isChecked = elm.prop('checked'); // Vérifier si la case est cochée ou non
        var valueToUpdate = isChecked ? 1 : 0; // Convertir en 1 si cochée, sinon 0
        var ref = $('#refAccueil').val()
        // Effectuer une requête AJAX pour mettre à jour la valeur sur le serveur
        $.ajax({
          url: 'request.php', // Remplacez par l'URL de votre script de mise à jour
          type: "POST",
          contentType: "application/json",
          data: JSON.stringify({
          	accueil: valueToUpdate,
          	ref:ref
          }),
          success: function(response) {
            // La mise à jour a été effectuée avec succès
          	var result = JSON.parse(response)
          	if(result.response == 1){
          		Toast.fire({
          			icon: 'success',
          			title: result.message
          		})
          		if(valueToUpdate==1){
          			elm.prop('checked', true);
          		}else{
          			elm.prop('checked', false);
          		}

          	}else{
          		Toast.fire({
                        icon: 'error',
                        title: "Une erreur c'est produite."
                    })
          	}
          },
          error: function(xhr, status, error) {
            // Une erreur s'est produite lors de la mise à jour
          	console.error('Erreur de mise à jour : ' + error);
          }
      });
	}
	
</script>

	</body>
	
	</html>
