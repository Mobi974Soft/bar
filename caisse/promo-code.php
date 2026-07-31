<?php

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../DBConfig.php';
require_once __DIR__ . '/promo/PromoCode.php';

function promoResponse($success, $message, array $extra = array())
{
    http_response_code($success ? 200 : 422);
    echo json_encode(array_merge(array(
        'response' => $success ? 1 : 0,
        'message' => $message,
    ), $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    promoResponse(false, 'Méthode non autorisée.');
}

$request = json_decode(file_get_contents('php://input'));
if (!$request || !isset($request->action, $request->id_caisse, $request->id_table)) {
    promoResponse(false, 'Requête incomplète.');
}

$idCaisse = (int) $request->id_caisse;
$idTable = (int) $request->id_table;
if ($idCaisse <= 0 || $idTable < 0) {
    promoResponse(false, 'Panier invalide.');
}
if (!isset($_SESSION['loggedin'], $_SESSION['id_caisse']) || (int) $_SESSION['id_caisse'] !== $idCaisse) {
    promoResponse(false, 'Session de caisse invalide.');
}

try {
    if ($request->action === 'apply') {
        $state = PromoCode::apply($conn, $idCaisse, $idTable);
        promoResponse(true, 'Code promo appliqué à ' . $state['product_count_at_apply'] . ' produit(s).', array(
            'active' => true,
            'product_count' => $state['product_count_at_apply'],
            'discount_total' => $state['discount_at_apply'],
        ));
    }
    if ($request->action === 'undo') {
        PromoCode::undo($conn, $idCaisse, $idTable);
        promoResponse(true, 'Le code promo a été retiré du panier.', array('active' => false));
    }
    promoResponse(false, 'Action inconnue.');
} catch (Exception $e) {
    promoResponse(false, $e->getMessage());
}
