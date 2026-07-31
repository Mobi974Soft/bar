<?php   
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
// include('../DBConfig.php');


// $fh   = fopen('data.txt',"w");//php path

// $tickets  = $conn->query("SELECT * FROM `table_client_ticket` where p_espece_euro > 0 and date LIKE '%2022-09-%'");


// $outPut = "id\tcheque\tespece\tcb\ttotal\ttotal_euro_du\n";

// //retrive records from database and write to file
// while($row = $tickets->fetch_assoc())
// {
//  $outPut .= $row['id']."\t".$row['p_cheque_euro']."\t".  $row['p_espece_euro']."\t".$row['p_cb']."\t". $row['total_euro']."\t".$row['total_euro_du']."\n";
// } 


// fwrite($fh,$outPut);
// fclose($fh);
 ?>
 <!DOCTYPE html>
<html>
<head>
    <title>Système de pagination</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            chargerDonnees(1); // Charger les données de la première page au chargement de la page

            // Gérer le clic sur les numéros de page
            $(document).on('click', '.pagination li a', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                chargerDonnees(page);
            });

            // Fonction pour charger les données via AJAX
            function chargerDonnees(page) {
                $.ajax({
                    url: 'pagination.php',
                    type: 'GET',
                    data: { page: page },
                    dataType: 'json',
                    success: function(response) {
                        var donnees = response.donnees;
                        var totalPages = response.totalPages;

                        console.log(donnees,totalPages)

                        // Mettre à jour le tableau de données
                        var tableHTML = '<table>';
                        tableHTML += '<thead><tr><th>ID</th><th>Nom</th><th>Description</th></tr></thead>';
                        tableHTML += '<tbody>';
                        for (var i = 0; i < donnees.length; i++) {
                            tableHTML += '<tr><td>' + donnees[i].ref + '</td><td>' + donnees[i].titre + '</td><td>' + donnees[i].prixttc_euro + '</td></tr>';
                        }
                        tableHTML += '</tbody></table>';

                        $('#table-donnees').html(tableHTML);

                        // Mettre à jour la pagination
                        var paginationHTML = '<ul class="pagination">';
                        for (var j = 1; j <= totalPages; j++) {
                            paginationHTML += '<li><a href="#" data-page="' + j + '">' + j + '</a></li>';
                        }
                        paginationHTML += '</ul>';

                        $('.pagination-container').html(paginationHTML);
                    }
                });
            }
        });
    </script>
</head>
<body>
    <div id="table-donnees"></div>
    <div class="pagination-container"></div>
</body>
</html>