<?php

if (!isset($_POST['password'])) {
    $error = 'Veuillez entrez le mot de passe !';
} else {
    include ('dbconfig.php');
    if ($stmt = $con->prepare('SELECT id, password,client_id,role FROM user WHERE password = ?')) {
        $password_entry = md5($_POST['password']);
        $stmt->bind_param('s', $password_entry);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password,$client_id,$role);
            $stmt->fetch();
            if ($password_entry == $password) {
                session_regenerate_id();
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['id'] = $id;
                $_SESSION['client_id'] = $client_id;
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;
//                $_SESSION['expiration'] =  date('Y-m-d H:i:s', strtotime('+11 hours 30 minutes'));
                $_SESSION['expiration'] =  date('Y-m-d H:i:s', strtotime('+11 hours +30 minutes'));
//                echo 'Welcome ' . $_SESSION['name'] . '!';
                echo json_encode(array('response' => 1, 'message' => 'Connexion réussi !', 'role' => $role));
            } else {
                echo json_encode(array('response' => 0, 'message' => 'Code d\'accès incorrect'));
            }
        } else {
            echo json_encode(array('response' => 0, 'message' => 'Code d\'accès incorrect'));
        }
        $stmt->close();
    }
}


?>
