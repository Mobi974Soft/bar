<?php
$title = $page = 'Graphiques Statistiques';
$accueil = 'index.php';

include('../template/header.php');
include('../DBConfig.php');

$current_year = date('Y');
$sql = "SELECT sum(total_euro) as CA FROM table_client_ticket WHERE date >= '$current_year-01-01' ";
$current_year_CA = $conn->query($sql);


?>

<div class="content-wrapper" style="min-height: 823px;">
    <?php include('../template/info-page.php') ?>
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-3">

                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Chiffres d'affaires</h3>
                                <a href="javascript:void(0);">View Report</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg"><?php echo $current_year_CA->fetch_row()[0]; ?> € </span>
                                    <span>CA par mois</span>
                                </p>
<!--                               <p class="ml-auto d-flex flex-column text-right">-->
<!--                                    <span class="text-success">-->
<!--                                    <i class="fas fa-arrow-up"></i> 33.1%-->
<!--                                    </span>-->
<!--                                    <span class="text-muted">Since last month</span>-->
<!--                                </p>-->
                            </div>

                            <div class="position-relative mb-4">
                                <div class="chartjs-size-monitor">
                                    <div class="chartjs-size-monitor-expand">
                                        <div class=""></div>
                                    </div>
                                    <div class="chartjs-size-monitor-shrink">
                                        <div class=""></div>
                                    </div>
                                </div>
                                <canvas id="sales-chart" height="200"
                                        style="display: block; width: 501px; height: 200px;" width="501"
                                        class="chartjs-render-monitor"></canvas>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <span class="mr-2">
                                <i class="fas fa-square text-primary"></i> Cette année
                                </span>
                                                                <span>
                                <i class="fas fa-square text-gray"></i> Année 2021
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../template/footer.php') ?>
<?php include('../template/script.php') ?>

<script src="../lib/dist/plugins/chart.js/Chart.min.js"></script>
<script type="text/javascript">
    //-------------
    //- BAR CHART -
    //-------------
    var ticksStyle = {
        fontColor: '#495057',
        fontStyle: 'bold'
    }
    var mode = 'index'
    var intersect = true
    var $salesChart = $('#sales-chart')
    // eslint-disable-next-line no-unused-vars
    var salesChart = new Chart($salesChart, {
        type: 'bar',
        data: {
            labels: ['JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            datasets: [
                {
                    backgroundColor: '#007bff',
                    borderColor: '#007bff',
                    data: [1000, 2000, 3000, 2500, 2700, 2500, 3000]
                },
                {
                    backgroundColor: '#ced4da',
                    borderColor: '#ced4da',
                    data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                mode: mode,
                intersect: intersect
            },
            hover: {
                mode: mode,
                intersect: intersect
            },
            legend: {
                display: false
            },
            scales: {
                yAxes: [{
                    // display: false,
                    gridLines: {
                        display: true,
                        lineWidth: '4px',
                        color: 'rgba(0, 0, 0, .2)',
                        zeroLineColor: 'transparent'
                    },
                    ticks: $.extend({
                        beginAtZero: true,

                        // Include a dollar sign in the ticks
                        callback: function (value) {
                            if (value >= 1000) {
                                value /= 1000
                                value += 'k'
                            }

                            return '$' + value
                        }
                    }, ticksStyle)
                }],
                xAxes: [{
                    display: true,
                    gridLines: {
                        display: false
                    },
                    ticks: ticksStyle
                }]
            }
        }
    })

</script>

</body>

</html>
