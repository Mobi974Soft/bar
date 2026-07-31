<?php 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$directoryPath = "./";

// Vérification de l'existence et du type du dossier
if (is_dir($directoryPath)) {
    // Utilisation de scandir pour obtenir la liste des fichiers et dossiers
    $items = scandir($directoryPath);

    $array_images = [];
    foreach ($items as $item) {
        // Vérifier si l'élément est un fichier
        if (is_file($directoryPath . $item)) {
            // Obtenir l'extension du fichier
            $extension = pathinfo($item, PATHINFO_EXTENSION);

            // Vérifier si l'extension est celle d'une image
            $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');
            if (in_array(strtolower($extension), $imageExtensions)) {
                $array_images[] = $item;
            }
        }
    }
    if(count($array_images)>0){
    	echo json_encode($array_images);
    }
} else {
    echo 0;
}
 ?>