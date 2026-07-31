<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditional Reload Div</title>
    <script>
        // Variable pour stocker le contenu précédent
        let previousContent = '';

        // Fonction pour vérifier les nouvelles données et mettre à jour la div si nécessaire
        function checkForNewData() {
            fetch('pagecommandes.php')
                .then(response => response.text())
                .then(data => {
                    // Comparer les nouvelles données avec le contenu précédent
                    if (data !== previousContent) {
                        // Mettre à jour le contenu de la div
                        document.getElementById('myDiv').innerHTML = data;
                        // Mettre à jour le contenu précédent
                        previousContent = data;
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Appeler la fonction toutes les secondes (1000 ms)
        setInterval(checkForNewData, 5000);
    </script>
</head>
<body>
    <div id="myDiv">
        <!-- Le contenu de cette div sera mis à jour uniquement lorsqu'il y a de nouvelles données -->
    </div>
</body>
</html>
