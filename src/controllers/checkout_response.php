<?php
// src/controllers/checkout_response.php
// Controlador para recibir y validar la respuesta de ePayco

// Sesión iniciada centralmente en index.php

// Quitamos la validación de autenticación estricta aquí porque los navegadores 
// de forma predeterminada pueden soltar la cookie de sesión al regresar de ePayco (Cross-Site).
// Restauraremos la sesión usando los datos seguros que vengan de la API de ePayco.

$ref_payco = $_GET['ref_payco'] ?? null;
$transaccion = null;
$error = null;

if ($ref_payco) {
    try {
        // 2. Consultar la API de validación de ePayco
        $url = 'https://secure.epayco.co/validation/v1/reference/' . $ref_payco;
        
        $options = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['success']) && $data['success']) {
                $transaccion = $data['data'];
                
                // RESTAURAR SESIÓN MÁGICAMENTE DESDE LA FACTURA
                // La referencia la armamos en checkout.php como: 'VIVA-' . time() . '-' . $id_user
                $partes_factura = explode('-', $transaccion['x_id_invoice']);
                $id_user_recuperado = isset($partes_factura[2]) ? $partes_factura[2] : null;
                
                require_once __DIR__ . '/../functions/auth_helper.php';

                if ($id_user_recuperado && !AuthHelper::verifyToken()) {
                    $token = AuthHelper::generateToken(['id_user' => $id_user_recuperado]);
                    AuthHelper::setAuthCookie($token);
                }

                // 3. Si la transacción fue Aceptada, procesar el pedido
                if ($transaccion['x_cod_response'] == 1) {
                    require_once __DIR__ . '/../functions/database.php';
                    $db = Database::getInstance();

                    $userData = AuthHelper::verifyToken();
                    $id_user_pago = $userData ? $userData->id_user : $id_user_recuperado;

                    // 3.1 Actualizar tab_clientes con datos de ePayco
                    try {
                        $tipoDocMap = ['CC' => 1, 'NIT' => 2, 'CE' => 3, 'PP' => 4];
                        $db->ejecutar('actualizarClienteEpayco', [
                            ':id_user'     => $id_user_pago,
                            ':id_client'   => $transaccion['x_customer_document'] ?? (string)$id_user_pago,
                            ':id_tipo_doc' => $tipoDocMap[$transaccion['x_customer_doctype'] ?? ''] ?? null,
                            ':tel'         => $transaccion['x_customer_phone']  ?? null,
                            ':ref'         => $transaccion['x_ref_payco']       ?? null,
                            ':txn'         => $transaccion['x_transaction_id']  ?? null,
                            ':banco'       => $transaccion['x_bank_name']       ?? null,
                            ':cod_resp'    => $transaccion['x_cod_response']    ?? null,
                        ]);
                    } catch (Exception $e) {
                        error_log('[CheckoutResponse] Error actualizando tab_clientes: ' . $e->getMessage());
                    }

                    // 3.2 Obtener dirección de envío del cliente
                    $dir_envio_fact = null;
                    $id_dpto_fact   = 11;
                    $id_ciudad_fact = 11001;
                    try {
                        $rowDir = $db->ejecutar('obtenerDireccionCliente', [':id_user' => $id_user_pago])->fetch(PDO::FETCH_ASSOC);
                        if ($rowDir) {
                            $id_dpto_fact   = $rowDir['id_departamento'];
                            $id_ciudad_fact = $rowDir['id_ciudad'];
                            $dir_envio_fact = trim(($rowDir['dir_envio'] ?? '') . ' ' . ($rowDir['barrio_envio'] ?? ''));
                        }
                    } catch (Exception $e) {
                        error_log('[CheckoutResponse] Error leyendo dirección: ' . $e->getMessage());
                    }

                    // 3.3 Obtener artículos del carrito
                    $stmt_obt    = $db->ejecutar('gestionarCarrito', [
                        ':id_user'     => $id_user_pago,
                        ':accion'      => 'obtener',
                        ':id_producto' => null,
                        ':cantidad'    => null,
                    ]);
                    $fila_obt    = $stmt_obt->fetch(PDO::FETCH_ASSOC);
                    $carrito_res = json_decode($fila_obt['fun_carrito'] ?? $fila_obt[array_key_first($fila_obt)], true);

                    // LOG: estado del carrito
                    error_log('[Factura-DEBUG] id_user_pago=' . $id_user_pago);
                    error_log('[Factura-DEBUG] carrito_exito=' . ($carrito_res['exito'] ? 'true' : 'false'));
                    error_log('[Factura-DEBUG] carrito_items=' . count($carrito_res['carrito'] ?? []));

                    if ($carrito_res['exito'] && !empty($carrito_res['carrito'])) {
                        $ids_prod = $cantidades = [];
                        foreach ($carrito_res['carrito'] as $item) {
                            $ids_prod[]  = (int) $item['id_producto'];
                            $cantidades[] = (int) $item['cantidad'];
                        }

                        error_log('[Factura-DEBUG] id_user=' . $id_user_pago);
                        error_log('[Factura-DEBUG] ids_prod=' . implode(',', $ids_prod));
                        error_log('[Factura-DEBUG] dpto=' . $id_dpto_fact . ' ciudad=' . $id_ciudad_fact);

                        try {
                            $stmtFac = $db->ejecutar('facturar', [
                                ':id_user'      => (int) $id_user_pago,
                                ':id_pago'      => 'EPAYCO',
                                ':dpto'         => $id_dpto_fact,
                                ':ciudad'       => $id_ciudad_fact,
                                ':dir'          => $dir_envio_fact,
                                ':epayco_ref'   => $transaccion['x_ref_payco']      ?? null,
                                ':epayco_txn'   => $transaccion['x_transaction_id'] ?? null,
                                ':epayco_estado'=> $transaccion['x_response']       ?? null,
                                ':ids_producto' => '{' . implode(',', $ids_prod) . '}',
                                ':cantidades'   => '{' . implode(',', $cantidades) . '}',
                            ]);

                            $id_factura = $stmtFac->fetchColumn();
                            error_log('[Factura-DEBUG] fun_facturar resultado=' . var_export($id_factura, true));

                            if ($id_factura) {
                                $db->ejecutar('gestionarCarrito', [
                                    ':id_user'     => $id_user_pago,
                                    ':accion'      => 'limpiar',
                                    ':id_producto' => null,
                                    ':cantidad'    => null,
                                ]);
                            } else {
                                error_log('[CheckoutResponse] fun_facturar devolvio NULL');
                            }
                        } catch (PDOException $e) {
                            error_log('[Factura-ERROR] SQLSTATE=' . $e->getCode() . ' MSG=' . $e->getMessage());
                        } catch (Exception $e) {
                            error_log('[Factura-ERROR] ' . $e->getMessage());
                        }
                    } else {
                        error_log('[Factura-DEBUG] Carrito vacío o exito=false, no se factura');
                    }
                }
            } else {
                $error = "No se pudo validar la referencia con ePayco.";
            }
        } else {
            $error = "Fallo de conexión con el servidor de pagos.";
        }
    } catch (Exception $e) {
        $error = "Ocurrió un error al procesar la respuesta: " . $e->getMessage();
    }
} else {
    $error = "No se recibió ninguna referencia de pago.";
}



// Cargar la vista de resultados
require_once ROOT_PATH . 'src/views/checkout_response.view.php';

?>
