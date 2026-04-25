<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../services/CheckoutService.php';

if (isset($_GET['ref_payco'])) {
    $respuestaPago = CheckoutService::procesarRespuestaPago($_GET['ref_payco'] ?? null);
    $transaccion = $respuestaPago['transaccion'];
    $error = $respuestaPago['error'];

    require_once __DIR__ . '/../views/checkout_response.view.php';
    exit;
}

$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

try {
    $checkoutData = CheckoutService::obtenerDatosCheckout((int) $id_user);

    if (isset($checkoutData['redirect'])) {
        header('Location: ' . $checkoutData['redirect']);
        exit;
    }

    extract($checkoutData, EXTR_SKIP);

    require_once __DIR__ . '/../views/checkout.view.php';

} catch (Exception $e) {
    throw $e;
}
