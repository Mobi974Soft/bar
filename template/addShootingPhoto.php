<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
//http://stackoverflow.com/questions/18382740/cors-not-working-php
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin:{$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    //http://stackoverflow.com/questions/15485354/angular-http-post-to-php-and-undefined
    /*$postdata = file_get_contents("php://input");
    if (isset($postdata)) {*/

        /*$request = json_decode($postdata);
        $lavageId = $request->post_lavageId;
        $voitureId = $request->post_voitureId;
        $plaqueId = $request->post_plaqueId;*/
        $date = date("dmY");

        require_once ('../wp-config.php');
        //require_once(ABSPATH . 'wp-admin/includes/image.php');
        global $wpdb;

        // $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "mo_services/etatDesLieux/";
        // $uploadDir2 = $_SERVER['DOCUMENT_ROOT'] . "mo_services/etatDesLieux/";

        $uploadDir = "/home/sites/16b/8/8b6152d5d1/public_html/idocars/idocars/mo_services/etatDesLieux/";
        $uploadDir2 = "/mo_services/etatDesLieux/";
        if (isset($_FILES['file'])){
    
            $fichier = $_FILES['file']['name'];
            $size = $_FILES['file']['size'];
            $tmp = $_FILES['file']['tmp_name'];
            $type = $_FILES['file']['type'];
            $file = $uploadDir.$fichier;
            $path = $uploadDir2.$fichier;

            $tab = explode("_",$fichier);
            $lavageId = $tab[2];
            $voitureId = $tab[3];
            
            // $status = move_uploaded_file($tmp, $file);
            $current = file_get_contents($tmp);
            $status = file_put_contents($file, $current);
        }
        


        if ($status!=0) {
            
            $q = $wpdb->insert( 
                'ido_etat_des_lieux', 
                array(
                    'lavageId' => $lavageId,
                    'voitureId' => $voitureId,
                    'imgUrl' => $path,
                    'quand' => date("d-m-Y H:i:s"),
                ), 
                array(
                    '%d',
                    '%d',
                    '%s',
                    "%s",
                ) 
            );

            if ($q != false) {
                echo $wpdb->insert_id;
            }else{
                echo 0;
            }

        }else{
            echo $status;
        }

    /*}else{
        die();
    }*/
?>