<?php


ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
function redirect(){
  header( "refresh:5; url=../profil.php" ); 
  die();
}
// Check if image file is a actual image or fake image
if(isset($_POST["submit"],$_POST['nom_magasin'],$_POST['slide']) && $_POST['nom_magasin'] != "") {
  $nom_magasin = $_POST['nom_magasin'];
  $slide = $_POST['slide'];
  $target_dir = "uploads/$nom_magasin/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  
  if($check !== false) {
    // echo "Le fichier est une image -" . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    // echo "Le fichier n'est pas une image.";
    $uploadOk = 0;
  }
}





// Check file size
if ($_FILES["fileToUpload"]["size"] > 1500000) {
  echo "Désolé, votre fichier est trop volumineux.";
  $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" && $imageFileType != "webp") {
  echo "Désolé, seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
$uploadOk = 0;
}

$cheminsImages = glob($target_dir."/slide-".$slide.'.*');

if ($cheminsImages !== false) {
    foreach ($cheminsImages as $cheminImage) {
        unlink($cheminImage);
    }
} 
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Désolé, votre fichier n'a pas été téléchargé.";
  redirect();
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "Le fichier ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " a bien été télécharger. Vous allez être redirigé";

    include('../../DBConfig.php');
    $filename = $_FILES["fileToUpload"]["name"];
    $url = "https://caisse.mobisoft.fr/restaurant/admin/pub/uploads/$nom_magasin/$filename";
    $sql = "SELECT slide FROM pub_images WHERE slide = $slide";

    $check = $conn->query($sql);
    
    
    if (rename($target_file,$target_dir."/slide-".$slide.".".$imageFileType)) {
      $newUrl = "slide-".$slide.".".$imageFileType;
      if ($check->num_rows>0) {
        $sql = "UPDATE `pub_images` SET url = '$newUrl' WHERE slide = $slide";
        }else{
          $sql = "INSERT INTO `pub_images`( `url`,`slide`) VALUES ('$newUrl','$slide')";
        }
      $query = $conn->query($sql);
      if($query){
        redirect();
      }
    }


    
  } else {
    echo "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
    redirect();
  }
}
?>