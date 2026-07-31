<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include('../DBConfig.php');
include('../functions.php');
if (!isset($_SESSION['loggedin'],$_SESSION['id_caisse'])) { //if login in session is not set
	header("Location: ../login/");
}else{
	$id_caisse = $_SESSION['id_caisse'];
	$session = $_SESSION['session'];

}
// LISTE DES CATEGORIES 
$sql = "SELECT * FROM table_client_categorie WHERE id_parent = 0";
$categories = $conn->query($sql);

$table_active = $conn->query('SELECT numero FROM restaurant_tables WHERE status = 1')->fetch_assoc()['numero'];
$clientActive = $conn->query("SELECT id FROM clients WHERE statut = 1 AND idtable = $table_active");
$clientActive = $clientActive->num_rows>0 ? $clientActive->fetch_assoc()['id'] : 0 ;


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Caisse</title>
	<link rel="stylesheet" href="../lib/dist/plugins/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="../lib/dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="../lib/dist/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css"/>
	<link rel="stylesheet" href="../lib/dist/plugins/chart.js/Chart.min.css"/>
	<link rel="stylesheet" href="../lib/dist/css/adminlte.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
	integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
	crossorigin="anonymous" referrerpolicy="no-referrer"/>
	<link rel="stylesheet" href="../template/style.css?random=<?php echo uniqid(); ?>"/>
	<link rel="stylesheet" href="restaurant.css?random=<?php echo uniqid(); ?>"/>
	<?php 
	if(isset($_GET['paiement'])): ?>
		<style>
			#produitsBlock{
				display: none;
			}
		</style>

	<?php endif; ?>
</head>

<body>
	<div class="container-fluid full-screen-div">
		<div class="row" style="height: 100%;">
			<div class="col-lg-1 bg-dark" style="padding:0">
				<p class="text-center text-white menu-item" >
					<?php echo isset($_SESSION['id_caisse']) ? "Caisse n° " . $_SESSION['id_caisse'] : "" ?>
				</p>
				<p id="clotureCaisse" class="text-center text-white" style="padding-top:30px;font-size: 16px;border-bottom: solid;
            border-color: #fff;
            border-width: 1px;
            padding-bottom: 50px;cursor:pointer;">
                <i class="fas fa-cash-register fa-2x"></i>
            </p>
				<?php if ($restaurant == 1): ?>
					<!-- <a href="#" onclick="window.location.href = 'restaurant.php?tables'">
						<p  class="text-center text-white menu-item"  >
							<i class="fa-solid fa-utensils"></i></br>
							Tables
						</p>
					</a> -->

					<!-- <a href="#" onclick="window.location.href = 'restaurant.php?clients'">
						<p  class="text-center text-white menu-item"  >
							<i class="fa-solid fa-users"></i></br>
							Clients
						</p>
					</a> -->

					<a href="#" onclick="totalCaisse('<?php echo $id_caisse ?>')">
						<p  class="text-center text-white menu-item"  >
							
							Total Caisse
						</p>
					</a>
				<?php endif ?>
				<!-- <p  class="text-center text-white menu-item" 
				data-toggle="modal"
				data-target="#modal-reimpression">

				<i class="fa-solid fa-print fa-2x"></i>
			</p> -->
			<p class="text-center text-white" style="padding-top: 30px;margin: 0;background: red;font-size: 16px;border-top: solid;border-bottom: solid;
border-color: #fff;
border-width: 1px;
padding-bottom: 30px;cursor:pointer;" onclick="printLastTicket()">
                <i class="fa-solid fa-receipt fa-2x" style="margin-right:15px"></i><i
                    class="fa-solid fa-circle-plus fa-2x"></i><br>
                <span>Dernier Ticket</span>

            </p>

			<p class="text-center logoutBTN" >
				<a  class="btn btn-block btn-danger"   href="../login/logout.php?id_caisse=<?php echo $_SESSION['id_caisse'] ?>&userid=<?php echo $_SESSION['id'] ?>"
					><i class="fa fa-sign-out" aria-hidden="true"></i></a>
				</p>


			</div>

			<?php 
			if (isset($_GET['paiement'])) {
				include 'page/pagepaiement.php';
			}
			if (isset($_GET['clients'])) {
				include('page/pageclient.php');
			}
			elseif (isset($_GET['tables'])) {
				include 'page/pagetables.php';
			}
			// elseif (isset($_GET['commandes'])) {
			// 	include('page/pagecommandes.php');
			// }
			else{
				include 'page/pageproduits.php';
				include 'page/panier.php';
			}
			?>

		</div>
	</div>

	
<?php

include 'modal/modal_remise_unique.php';
include 'modal/modal_impression_ticket.php';
include 'modal/modal_commande.php';
include 'modal/modal_remise.php';
include 'modal/modal-partage.php';
include 'modal/modal_options.php';
include 'modal/modal_paiement.php';
include 'modal/modal_reimpression.php';


?>

<!-- MODAL DES TABLES -->
<div class="modal fade show" id="modal-tables" style="padding-right: 17px; display: none;" aria-modal="true" role="dialog">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn btn-block bg-gradient-info btnTableStatus" onclick="changeActiveStatus(this)" style="margin-top: 7px;">Tous</button>
				<button type="button" class="btn btn-block btn-outline-info btnTableStatus" onclick="changeActiveStatus(this)">Occupé</button>
				<button type="button" class="btn btn-block btn-outline-info btnTableStatus" onclick="changeActiveStatus(this)">Libre</button>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row" style="padding: 20px;">
					<?php 
					$sql = "SELECT * FROM restaurant_tables";
					$query = $conn->query($sql);
					if ($query->num_rows>0) {
						while($table = $query->fetch_assoc()){
							?>
							<div class="<?php echo $table['status'] == 1 ? "current-table" : "tableStyle" ?> col-lg-2">
								<div>
									<h3 class="tableNumero">Table n° <?php echo $table['numero'] ?></h3>
									<p class="tablePlace">Places:  <?php echo $table['places'] ?> </p>
									<button type="button" class="btn btn-outline-primary btn-block selectTable" 
									onclick="selectTable(this,'<?php echo $table['id'] ?>','<?php echo $table['numero'] ?>','<?php echo $table['places'] ?>')">Séléctionner </button>
								</div>

							</div>
							<?php
						}
					}
					?>

				</div>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
				<!-- <button type="button" class="btn btn-primary"></button> -->
			</div>
		</div>

	</div>

</div>








</body>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
crossorigin="anonymous"></script>
<script src="../lib/dist/js/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
crossorigin="anonymous"></script>
<script src="../lib/dist/plugins/moment/moment.min.js"></script>
<script src="../lib/dist/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../lib/dist/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="../lib/dist/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="../lib/dist/js/adminlte.min.js?v=3.2.0"></script>
<script src="../plugin-ticket-js/Impresora.js"></script>
<script src="restaurant.js?random=<?php echo uniqid(); ?>"></script>
<script>
	$(document).ready(function(){
		function alignModal(){
			var modalDialog = $(this).find(".modal-dialog");

        // Applying the top margin on modal to align it vertically center
			modalDialog.css("margin-top", Math.max(0, ($(window).height() - modalDialog.height()) / 2));
		}
    // Align modal when it is displayed
		$(".modal").on("shown.bs.modal", alignModal);

    // Align modal when user resize the window
		$(window).on("resize", function(){
			$(".modal:visible").each(alignModal);
		});   
	});
</script>
<script type="text/javascript">
	function getTotal() {
                var session = '<?php echo $session ?>';
                var id_caisse = '<?php echo $id_caisse ?>';
                var total = 0;
                $.ajax({
                    url: "getTotal.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({session: session,  id_caisse: id_caisse}),
                    async:false,
                    success: function (data) {
                        console.log(data)
                        var res = JSON.parse(data)
                        total = parseFloat(res.total);
                    }
                })
                $('#total').text(total.toFixed(2)+"€")
                return total.toFixed(2);
            }
	function resetModal(){
		$('#poidsProduit').val('')
		$('#poidsContenant').val('')
		$('#poidsTotal').text('0.00 g')
		$('#prixTotal').text('0.00 €')
	}

	$(document).ready(function() {
		var totalPanier = $('#totalPanier').text()
		$("#totalDu").text(totalPanier);
				// $("#ResteAPayer").text(totalPanier);
	});


	var delay = (function() {
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();

	// UPDATE VALEUR POIDS
	$("#poidsContenant").keyup(function(){
		var poidsProduit = $('#poidsProduit').val();
		var poidsContenant = $(this).val()

		var poidsTotal = poidsProduit > poidsContenant ? poidsProduit - poidsContenant : poidsContenant - poidsProduit;
		var result = poidsTotal+" "+ $('#uniteTotal option:selected').text().slice(0,1)
		$('#poidsTotal').text(result)

		// CALCUL DU PRIX APRES PESAGE
		var priceProduct = $('#prixProduit').val()
		var uniteProduct = $('#modal_unite').val()
		var qteUniteProduct = $('#modal_qte_unite').val()

		var unite = $('#uniteTotal').val()
		console.log(unite)
		if ( unite == 2) {
			var total = parseFloat(priceProduct) * (parseFloat(poidsTotal)/1000)
			console.log(total)
			$('#prixTotal').text(total.toFixed(2)+" €")
		}


	})



			// function proceedToPayment(){
			// 	window.location.href='restaurant.php?paiement'; 
			// 	$("#totalDu").load(location.href + " #totalDu");
			// 	$("#ResteAPayer").load(location.href + " #ResteAPayer");
			// }
</script>
</html>