<?php
include('../DBConfig.php');
$nom_magasin = $conn->query('SELECT nom_magasin FROM table_client_info');
$nom_magasin = $nom_magasin->fetch_assoc()['nom_magasin'];
include('csv_stat_cat/csv_stat_categorie.php');


?>
<!DOCTYPE HTML>
<html>
<head>

	<script>
		window.onload = function () {
			
			var dataPoints = [];
			
			var chart = new CanvasJS.Chart("chartContainer", {
				animationEnabled: true,
				exportEnabled: true,
				title:{
					text: "Statistiques par catégorie",
					fontSize:30,
				},
				axisY: {
					title: "CA (en Euro)",
					fontSize:15,
					includeZero: false,
					prefix: "",
					suffix:  "€",
					labelFontSize:13,
				},
				axisX:{
					interval: 1,
					labelFontSize:13,
				},
	// data: [{
	// 	type: "column",
	// 	toolTipContent: "{y} €",
	// 	dataPoints: dataPoints
	// }]
				data: [{
					type: "bar",
					yValueFormatString: "##0€",
					toolTipContent: "{y} ",
					// indexLabel: "{y}",
					// indexLabelFontSize: 15,
					// indexLabelPlacement: "inside",
					// indexLabelFontWeight: "bolder",
					// indexLabelFontColor: "white",
					dataPoints: dataPoints
				}]
			});

			var nom_magasin = '<?php echo $nom_magasin ?>';
			var base_url = document.location.origin;
			$.get(base_url+"/restaurant/admin/csv_stat_cat/"+nom_magasin+".csv", getDataPointsFromCSV);
			
//CSV Format
//Year,Volume
			function getDataPointsFromCSV(csv) {
				var csvLines = points = [];
				csvLines = csv.split(/[\r?\n|\r|\n]+/);
				for (var i = 1; i < csvLines.length; i++) {
					if (csvLines[i].length > 0) {
						points = csvLines[i].split(",");
						if (points[2]!=0) {
							dataPoints.push({
								label: points[1].replace(/["']/g, ""),
								y: parseFloat(points[2])
							});
						}
						
					}
				}
				dataPoints = dataPoints.reverse();
				chart.render();
			}
			
		}
	</script>

</head>
<body>
	<div id="chartContainer" style="height: 1200px; width: 100%;overflow-y: hidden; 
  overflow-x: hidden; "></div>
	<script type="text/javascript" src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>        