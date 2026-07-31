<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include '../DBConfig.php';



if(isset($_POST['btnCloture'])){
    $id_caisse = 99;
    
    if($_POST['sous-total-espece'] == "" OR $_POST['sous-total-espece'] == 0 ){
        $especeNew = 0;
    }
    else{
        $especeNew = $_POST['sous-total-espece'];
    }
    $cb_New = $_POST['cb-ca-2'];
    $cheque_new = $_POST['cheque-euro'];
    // $total_euro = $especeNew+$cb_New+$cheque_new;
    $dateNew = date('Y-m-d'); 
    $sql = "INSERT INTO `table_client_valeurcaisse`(`id_caisse`, `qte_articles`, `p_especes_euro`, `p_cheque_euro`, `p_cb`, `retourarticle`, `total_euro`, `total_euro_du`, `date`, `sendserveur`) VALUES ('$id_caisse','1','$especeNew','$cheque_new','$cb_New','0','0','1','$dateNew','0')";
    $query = $conn->query($sql);
    if($query){
        header('Location: https://caisse.mobisoft.me/restaurant/admin/statistiques.php');
    }
}


if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $date = str_replace('/', '-', $date);
    $date = date('Y-m-d',strtotime($date));
    $sql = "SELECT p_espece_euro, p_cb, p_cheque_euro FROM table_client_ticket WHERE date like '%$date%' ";
    
    $tickets = $conn->query($sql);

    $cb_count = 0;
    $espece_count = 0;
    $cheques_count = 0;

    $total_espece = 0;
    $total_cb = 0;
    $total_cheques = 0;
    while($ticket = $tickets->fetch_assoc()){
        $total_espece+=$ticket['p_espece_euro'];
        $total_cb+=$ticket['p_cb'];
        $total_cheques+=$ticket['p_cheque_euro'];

        if($ticket['p_cb'] > 0){
            $cb_count += 1;
        }
        if($ticket['p_espece_euro'] > 0){
            $espece_count += 1;
        }
        if($ticket['p_cheque_euro'] > 0){
            $cheques_count += 1;
        }
    }
}
$title = 'Cloture de caisse';
$page = 'Cloture de caisse';
$accueil = '../index.php';
include('../template/header.php');
include('../DBConfig.php');
?>

<div class="content-wrapper" id="cloture">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <form action="cloture-caisse.php" method="POST">
                    <h2 style="text-align:center;padding:50px 0;">Calcul de la somme restant en espèce le <?php echo date('d/m/Y',strtotime($date)) ?></h2>

               <!--  <div class="card card-danger" id="stats">
                    <div class="card-header">
                        <h3 style="width:100%;text-align: center;">Encaissé hors de la webcaisse</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row mb-3 ">
                            <div class="col-4 text-right text-danger"><label for="cb-ca" style="font-size:17px;">Par carte bancaire : </label></div>
                            <div class="col-6"><input type="number" name="cb-bancaire" value="<?php echo isset($total_cb) ?  - $total_cb : 0 ?>" /><span class="euro">€</span>  </div>
                        </div>
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="col-4 text-right text-danger"><label for="cb-ca" style="font-size:17px;" >Par chèques : </label></div>
                            <div class="col-6"><input type="number" name="cheque" value="<?php echo isset($total_cheques) ? - $total_cheques : 0 ?>" /><span class="euro">€</span>  </div>
                        </div>

                        <div class="form-row">
                            <div class="col-4 text-right"><label for="sous-total" style="font-size:22px;font-weight: 800" >SOUS TOTAL : </label></div>
                            <div class="col-6"><input type="number" name="sous-total"/><span class="euro">€</span></div>
                        </div>
                    </div>
                </div> -->

                <div class="card card-navy" id="stats">

                    <div class="card-header">
                        <h3 style="width:100%;text-align: center;">Dans le systeme webcaisse</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row mb-3 ">
                            <div class="col-4 text-right text-navy"><label for="cb-ca" style="font-size:17px;">Espèces euro : </label></div>
                            <div class="col-6"><input type="number" step="any" id="espece-euro" name="espece" value="<?php echo isset($total_espece) ? $total_espece : 0 ?>" /><span class="euro">€</span>  <span>(<?php echo isset($espece_count) ? $espece_count : 0 ?>)</span></div>
                        </div>
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="col-4 text-right text-danger"><label for="cb-ca" style="font-size:19px;font-weight: 800" >Sous-total espèces : </label></div>
                            <div class="col-6"><input type="number" step="any" id="sous-total" value="0"  name="sous-total-espece"/><span class="euro">€</span></div>
                        </div>

                        <div class="form-row">
                            <div class="col-4 text-right text-navy"><label for="cheque-euro"  >Chèque euro : </label></div>
                            <div class="col-6"><input type="number" step="any" onclick="this.select()" name="cheque-euro" value="<?php echo isset($total_cheques) ? $total_cheques : 0 ?>"  /><span class="euro">€</span>  <span>(<?php echo isset($cheques_count) ? $cheques_count : 0 ?>)</span></div>
                        </div>
                        <div class="form-row">
                            <div class="col-4 text-right text-navy"><label for="cb-ca"  >Carte bancaire CB: </label></div>
                            <div class="col-6"><input type="number" step="any"  onclick="this.select()" name="cb-ca-2" value="<?php echo isset($total_cb) ? $total_cb : 0 ?>" /><span class="euro">€</span>  <span>(<?php echo isset($cb_count) ? $cb_count : 0 ?>)</span></div>
                        </div>


                    </div>
                </div>

                <div class="card card-secondary" id="stats">
                    <div class="card-header">
                        <h3 style="width:100%;text-align: center;">Résultats</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row mb-3 ">
                            <div class="col-4 text-right text-danger"><label for="espece" style="font-size:22px;font-weight: 800">Espèces à trouver : </label></div>
                            <div class="col-6"><input type="number"step="any" name="espece" id="especeToFind" value="<?php echo isset($total_espece) ? $total_espece : 0 ?>" /><span class="euro">€</span></div>
                        </div>
                    </div>
                </div>

                <div class="form-row" style="padding:20px;0">
                    <div class="col-6"><button type="button" class="btn btn-primary btn-block" id="btnPrint" onclick="printDiv('cloture')"><i class="fa fa-print"></i> Imprimer la page</button></div>
                    <div class="col-6"><input type="submit" class="btn btn-success btn-block"  name="btnCloture" value="Cloturer la caisse" /> </div>
                </div>
            </form>

        </div>
    </div>
</div>
</div>

<?php include('../template/footer.php') ?>



<?php include('../template/script.php') ?>

<script type="text/javascript">
    function printDiv(divID) {
    //Get the HTML of div
    var divElements = document.getElementById(divID).innerHTML;
    //Get the HTML of whole page
    var oldPage = document.body.innerHTML;

    //Reset the page's HTML with div's HTML only
    document.body.innerHTML = 
    "<html><head><title></title></head><body>" + 
    divElements + "</body>";

    $('#btnPrint').hide()
    $('#btnCloture').hide()
    //Print Page
    window.print();

    //Restore orignal HTML
    document.body.innerHTML = oldPage;

}
var espece = $('#espece-euro').val()
var especeToFind = $('#especeToFind').val()
var cbtotal = '<?php echo $total_cb ?>'
var totalcheque = '<?php echo $total_cheques ?>'
$("input[type=number][name=cheque-euro]").on("input", function (e) {
    var val = $(this).val()
    if(espece<especeToFind){
        $('#sous-total').val(parseFloat(espece)+parseFloat(val))
    }else if (espece>especeToFind){
        $('#sous-total').val((parseFloat(espece)-parseFloat(totalcheque))+parseFloat(val))
    }

})
$("input[type=number][name=cb-ca-2]").on("input", function (e) {
    var val = $(this).val()

    if(espece<especeToFind){
        $('#sous-total').val(parseFloat(espece)+parseFloat(val))
    }else if (espece>especeToFind){
        $('#sous-total').val((parseFloat(espece)-parseFloat(cbtotal))+parseFloat(val))
    }
})

</script>
</body>

</html>
