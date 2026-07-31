<?php



function updateData($sql,$conn){
    $query = $conn->multi_query($sql);

}
function connClient(){
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'DjlQMvzTdYZwdmXP';
    $DATABASE_PASS = 'lAOy0feUOCvl0jFo';
    $DATABASE_NAME = 'mobipos_clients';
    $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

    return $con;
}
function regenerePanier($conn,$sql,$jsonfile){

    $result = $conn->query($sql." ORDER BY date DESC");
    if ($result->num_rows > 0) {

        while ($row[] = $result->fetch_assoc()) {

            $tem = $row;

            $json = $tem;
        }
        $fp = fopen($jsonfile, 'w');
        fwrite($fp, json_encode($json));
        fclose($fp);

        return $json;
    }else{
        $fp = fopen($jsonfile, 'w');
        fwrite($fp, json_encode([]));
        fclose($fp);
    }

}

function ean_check($ean) {
    $ean = strrev($ean);
        // Split number into checksum and number
    $checksum = substr($ean, 0, 1);
    $number = substr($ean, 1);
    $total = 0;
    for ($i = 0, $max = strlen($number); $i < $max; $i++) {
        if (($i % 2) == 0) {
            $total += ($number[$i] * 3);
        } else {
            $total += $number[$i];
        }
    }
    $mod = ($total % 10);
    $calculated_checksum = (10 - $mod);
    if ($calculated_checksum == $checksum) {
        return true;
    } else {
        return false;
    }
}



function calculTotal($conn,$session,$id_caisse){
    $sql = "SELECT * FROM table_client_panier WHERE idtable = $session AND id_caisse = $id_caisse ORDER BY num DESC";

    $result = $conn->query($sql);
    $total = 0;
    $qteTotal = 0;
    $cumul_tva = 0;
    if ($result->num_rows > 0) {
        while ($row= $result->fetch_assoc()) {
            $ref = $row['ref'];
            if ($ref!="totalpanier") {
                $remise = json_decode($row['remise_unique']);
                $remise = $remise != NULL ? $remise[0] : 0;
                $qte = $row['qte'];
                $remise_euro = $row['remise_euro'];
                $pu_euro = $row['pu_euro'];
                $promo = $row['promo'];
                $retour = $row['retour'];
                $tauxtva = $row['taux_tva'];
                if($ref!="remise"){
                    $qteTotal+=$qte;
                }
                if($ref == "remise"){
                    $total += $pu_euro * -$qte;
                }else{
                     if($promo>0){
                        if($retour == 1){
                            $total += $promo *  - $qte;
                            $cumul_tva += ($promo - ($promo / (1+ $tauxtva / 100 ))) *  - $qte;
                        }else{
                            $total +=  $promo * $qte - ($promo * $qte * ($remise / 100)) - ($remise_euro * $qte);
                            $cumul_tva += ($promo - ($promo / (1+ $tauxtva / 100 ))) * $qte; // A REVOIR
                        }
                        

                    }else{
                        if($retour == 1){
                            $total += $pu_euro *  - $qte;
                            $cumul_tva += ($pu_euro - ($pu_euro / (1+ $tauxtva / 100 ))) *  - $qte;
                        }
                        else{
                            $total +=  $pu_euro * $qte - ($pu_euro * $qte * ($remise / 100)) - ($remise_euro * $qte) ;
                            $cumul_tva += ($pu_euro - ($pu_euro / (1+ $tauxtva / 100 ))) *  $qte;
                        }
                    } 
            }
        }
    }

}
return array($total,$qteTotal,$cumul_tva);
}

function numberToWords($number) {
    $words = array(
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine'
    );

    if ($number < 10) {
        return $words[$number];
    } elseif ($number < 20) {
        $teens = array(
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        );
        return $teens[$number];
    } elseif ($number < 100) {
        $tens = array(
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        );
        $tens_digit = (int)($number / 10);
        $remainder = $number % 10;
        return $tens[$tens_digit] . ($remainder > 0 ? ' ' . $words[$remainder] : '');
    } elseif ($number < 1000) {
        $hundreds_digit = (int)($number / 100);
        $remainder = $number % 100;
        return $words[$hundreds_digit] . ' Hundred' . ($remainder > 0 ? ' and ' . numberToWords($remainder) : '');
    } else {
        return 'Number is too large to convert';
    }
}
function formatNumber($value){
    return number_format((float)$value, 2, '.', '');
}


function response($json,$res){
    return array('json'=>$json,'result'=>$res);
}

function errorResponse($message,$response){
    echo json_encode(array('message'=>$message,'response'=>$response));
    exit();
}

function successResponse($message,$response,$data=null){
    echo json_encode(array('message'=>$message,'response'=>$response,'data' => $data));
}



function ticketFormatString($str,$limit){
    $row1 = $lastrow = str_repeat("*",$limit);

    if(strlen($str)>$limit){
        $splited = explode(' ',$str);

//        $first = count($splited) % 2 == 0 ? array_slice($splited,0,count($splited)/2) : array_slice($splited,0,((count($splited) + 1) / 2 ));
        $first = array_slice($splited,0,((count($splited) / 2) +1 ));
        $firstRow = implode(" ",$first);
        $secondRow = array_slice($splited,count($first));
        $secondRow = implode(" ", $secondRow);


        $diff1 = strlen($firstRow) < $limit ? $limit  - strlen($firstRow) : strlen($firstRow);
        $diff2 = strlen($secondRow) < $limit ? $limit  - strlen($secondRow) : strlen($secondRow);
        $firstRow = $diff1%2 == 0 ? str_repeat("*",$diff1/2) . $firstRow . str_repeat("*",$diff1/2) : str_repeat("*",($diff1+1)/2) . $firstRow . str_repeat("*",($diff1-1)/2);
        $secondRow = $diff2%2 == 0 ? str_repeat("*",$diff2/2) . $secondRow . str_repeat("*",$diff2/2) : str_repeat("*",($diff2+1)/2) . $secondRow . str_repeat("*",($diff2-1)/2);
        return $row1 ."\n". $firstRow ."\n". $secondRow ."\n". $lastrow ;
    }
    else{
        $diff = strlen($str) < $limit ? $limit  - strlen($str) : strlen($str);
//        $diff = mb_strtoupper($str, 'utf-8') == $str ? $diff / 1.5 : $diff;
        $str = $diff%2 == 0 ? str_repeat("*",$diff/2) . $str . str_repeat("*",$diff/2) : str_repeat("*",($diff+1)/2) . $str . str_repeat("*",($diff-1)/2);
        return $row1 ."\n". $str ."\n". $lastrow ;

    }
}

function setStringLen($str,$limit,$eur = false){
    if($eur){
        return strlen($str) <= $limit ? $str. str_repeat(" ", $limit - strlen($str)) : mb_substr($str,0,$limit-1) ;
    }
    return strlen($str) <= $limit ? $str . str_repeat(" ", $limit - strlen($str)) : mb_substr($str,0,$limit-1) . str_repeat(" ",1);
}

function validate_EAN13Barcode($barcode)
{
    // check to see if barcode is 13 digits long
    if (!preg_match("/^[0-9]{13}$/", $barcode)) {
        return false;
    }

    $digits = $barcode;

    // 1. Add the values of the digits in the 
    // even-numbered positions: 2, 4, 6, etc.
    $even_sum = $digits[1] + $digits[3] + $digits[5] +
    $digits[7] + $digits[9] + $digits[11];

    // 2. Multiply this result by 3.
    $even_sum_three = $even_sum * 3;

    // 3. Add the values of the digits in the 
    // odd-numbered positions: 1, 3, 5, etc.
    $odd_sum = $digits[0] + $digits[2] + $digits[4] +
    $digits[6] + $digits[8] + $digits[10];

    // 4. Sum the results of steps 2 and 3.
    $total_sum = $even_sum_three + $odd_sum;

    // 5. The check character is the smallest number which,
    // when added to the result in step 4, produces a multiple of 10.
    $next_ten = (ceil($total_sum / 10)) * 10;
    $check_digit = $next_ten - $total_sum;

    // if the check digit and the last digit of the 
    // barcode are OK return true;
    if ($check_digit == $digits[12]) {
        return true;
    }

    return false;
}

function random_strings($length_of_string)
{

    // String of all alphanumeric character
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    // Shuffle the $str_result and returns substring
    // of specified length
    return substr(str_shuffle($str_result),
     0, $length_of_string);
}

function checkIfBarcodeExist($barcode,$conn,$action)
{
    $existGencode = 'false';
    if($action == 'creer'){
        do {
            $sql = "SELECT ref FROM table_client_catalogue WHERE ref = '$barcode' ";
            $result = $conn->query($sql);
            $nbligne = $result->num_rows;
            $existGencode = $nbligne > 0 ? 'true' : 'false';
        } while ($existGencode == 'true');
        return $existGencode;
    }
    else{
        $sql = "SELECT ref FROM table_client_catalogue WHERE ref = '$barcode' ";
        $result = $conn->query($sql);
        $nbligne = $result->num_rows;
        if($nbligne>0){
            return "true";
        } 
        elseif(validate_EAN13Barcode($barcode) OR ean_check($barcode)){
            return "false";
        }elseif($nbligne==0 AND !validate_EAN13Barcode($barcode)){
            return 0;
        }
        elseif($nbligne==0 AND !ean_check($barcode)){
            return 0;
        }
    }



}

function connexionDb($HostName, $HostUser, $HostPass, $DatabaseName)
{

    return new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
}


function formatLabel($titre,$prix,$codebarre,$arrayPos,$package,$promo,$promoFin = null){
    $prixSplitted =  explode(".",$prix);
    $prixEntier = $prixSplitted[0];
    $prixDecimal = $prixSplitted[1];

    $fontTitre = 40;
    $fontPackage = 20;
    if(strlen($titre)>=20){
        $fontTitre = 30;
    }
    $today = date('Y-m-d');

    if($promo>0 && $promoFin > $today ){
        $promoSplitted =  explode(".",$promo);
        $promoEntier = $promoSplitted[0];
        $promoDecimal = $promoSplitted[1];

        // $arrayPos['prixEntier'][0] = 255;
        // $arrayPos['prixEntier'][1] = 65;

        // $arrayPos['prixDecimal'][0] = 340;
        // $arrayPos['prixDecimal'][1] = 110;


        $arrayPos['euro'][0] = 550;
        $arrayPos['euro'][1] = 72;

        $promoFinDate = date('d/m/Y',strtotime($promoFin));

        if(strlen($promoEntier) == 2){
            $arrayPos['nouveauPrixEntier'][0] -= 35;
        }
        elseif (strlen($promoEntier) == 3) {
            $arrayPos['nouveauPrixEntier'][0] -= 60;
        }

        if(strlen($prixEntier) == 1){
            $arrayPos['promoAncienPrix'][0] += 20;
        }
        elseif (strlen($prixEntier) == 3) {
            $arrayPos['promoAncienPrix'][0] -= 15;
        }

        // if (strlen($promoDecimal) == 2) {
        //     $arrayPos['nouveauPrixDecimal'][1] -= 180;
        // }

        $zpl = "^FO".$arrayPos['titre'][0].",".$arrayPos['titre'][1];
        $zpl .= "^A0,".$fontTitre."^FH^FD".trim($titre)."^FS";
        $zpl .= "^FO".$arrayPos['package'][0].",".$arrayPos['package'][1];
        $zpl .= "^A0,".$fontPackage."^FH^FD".trim($package)."^FS";
        $zpl .= "^FO".$arrayPos['promoFin'][0].",".$arrayPos['promoFin'][1]."^A0,20^FH^FDJUSQU AU ".trim($promoFinDate)."^FS";
        $zpl .= "^FO".$arrayPos['codebarre'][0].",".$arrayPos['codebarre'][1];
        $zpl .= "^BY2";
        $zpl .= "^BE,50,Y,N^FH^FD".$codebarre."^FS";

        $zpl .= "^FO".$arrayPos['nouveauPrixEntier'][0].",".$arrayPos['nouveauPrixEntier'][1];
        $zpl .= "^A0,100,60^";
        $zpl .= "^FH^FD".trim($promoEntier).".^FS";
        $zpl .= "^FO".$arrayPos['nouveauPrixDecimal'][0].",".$arrayPos['nouveauPrixDecimal'][1];
        $zpl .= "^^A0,40^^FH^FD".trim($promoDecimal)."^FS";
        $zpl .= "^FO".$arrayPos['nouveauPrixEuro'][0].",".$arrayPos["nouveauPrixEuro"][1]."^^A0,40^^FH^FD _80^FS";

        $zpl .= "^FO".$arrayPos['barre_promo'][0].", ".$arrayPos['barre_promo'][1]."^FH^GD 115, 50,5^FS";
        $zpl .= "^FO".$arrayPos['promoAncienPrix'][0].",".$arrayPos['promoAncienPrix'][1]."^A0,35,35^^FH^FD".trim($prixEntier).".^FS";
        $zpl .= "^FO".$arrayPos['promoAncienDecimal'][0].",".$arrayPos['promoAncienDecimal'][1]."^^A0,30^^FH^FD".trim($prixDecimal)."^FS";
        $zpl .= "^FO".$arrayPos['euroPromo'][0].",".$arrayPos['euroPromo'][1]."^^A0,20^^FH^FD _80^FS";


        $zpl .= "^XZ";


    }
    else{
        if(strlen($prixEntier) == 1){
            $arrayPos['prixEntier'][0] += 35;
        }
        elseif (strlen($prixEntier) == 3) {
            $arrayPos['prixEntier'][0] -= 30;
        }
        
        // $titre = preg_replace('#[^\w()/.%\-&]#',"",$titre);
        $zpl .= "^FO".$arrayPos['titre'][0].",".$arrayPos['titre'][1];
        $zpl .= "^A0,".$fontTitre."^FH^FD".trim($titre)."^FS";
        $zpl .= "^FO".$arrayPos['package'][0].",".$arrayPos['package'][1];
        $zpl .= "^A0,".$fontPackage."^FH^FD".trim($package)."^FS";
        $zpl .= "^FO".$arrayPos['codebarre'][0].",".$arrayPos['codebarre'][1];
        $zpl .= "^BY2";
        $zpl .= "^BE,50,Y,N^FH^FD".$codebarre."^FS";
        $zpl .= "^FO".$arrayPos['prixEntier'][0].",".$arrayPos['prixEntier'][1];
        $zpl .= "^A0,125,65^^FH^FD".trim($prixEntier).".^FS";
        $zpl .= "^FO".$arrayPos['prixDecimal'][0].",".$arrayPos['prixDecimal'][1];
        $zpl .= "^^A0,40^^FH^FD".trim($prixDecimal)."^FS";
        $zpl .= "^FO".$arrayPos['euro'][0].",".$arrayPos["euro"][1]."^^A0,50^^FH^FD _80^FS";
        $zpl .= "^XZ";
    }
    return $zpl;

}

function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

$string = strtr($string, $chars);

return $string;
}
