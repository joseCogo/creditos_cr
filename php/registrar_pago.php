<?php
session_start();
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json');
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Validar CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!Seguridad::validar_csrf_token($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

$prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
$monto_pagado = floatval($_POST['monto_pagado'] ?? 0);
$metodo_pago = trim($_POST['metodo_pago'] ?? 'efectivo');
$observacion = trim($_POST['observacion'] ?? '');
$usuario_id = (int)$_SESSION['usuario_id'];

// VALIDACIÓN 1: Campos obligatorios
if (empty($prestamo_id) || !Seguridad::validar_monto($monto_pagado, 0.01)) {
    echo json_encode(['success' => false, 'message' => 'Monto inválido o préstamo no especificado']);
    exit;
}

// VALIDACIÓN 2: Validar observación (max 500 caracteres)
if (strlen($observacion) > 500) {
    echo json_encode(['success' => false, 'message' => 'Observación demasiado larga']);
    exit;
}

// VALIDACIÓN 3: Validar método de pago
$metodos_validos = ['efectivo', 'nequi', 'daviplata', 'transferencia', 'cheque'];
if (!in_array($metodo_pago, $metodos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
    exit;
}

// VALIDACIÓN 4: Obtener información del préstamo con Prepared Statement
$sql_prestamo = "SELECT saldo_pendiente FROM prestamos WHERE id = ?";
$stmt_prestamo = mysqli_prepare($conexion, $sql_prestamo);
if (!$stmt_prestamo) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
    exit;
}
mysqli_stmt_bind_param($stmt_prestamo, "i", $prestamo_id);
mysqli_stmt_execute($stmt_prestamo);
$result_prestamo = mysqli_stmt_get_result($stmt_prestamo);
$prestamo = mysqli_fetch_assoc($result_prestamo);

if (!$prestamo) {
    Seguridad::registrar_auditoria($conexion, $usuario_id, 'PAGO_RECHAZADO', 'pagos', $prestamo_id, 
        ['razon' => 'prestamo_no_existe', 'monto' => $monto_pagado]);
    echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado']);
    exit;
}

// VALIDACIÓN 5: No permitir overpay
$saldo_pendiente = Seguridad::redondear_dinero($prestamo['saldo_pendiente']);
if ($monto_pagado > $saldo_pendiente) {
    Seguridad::registrar_auditoria($conexion, $usuario_id, 'PAGO_RECHAZADO', 'pagos', $prestamo_id, 
        ['razon' => 'monto_mayor_que_saldo', 'monto' => $monto_pagado, 'saldo' => $saldo_pendiente]);
    echo json_encode([
        'success' => false, 
        'message' => 'No puedes pagar más del saldo pendiente: $' . number_format($saldo_pendiente, 0, ',', '.')
    ]);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    $fecha_actual = date('Y-m-d');

    // Registrar pago con Prepared Statement
    $sql = "INSERT INTO pagos (prestamo_id, fecha_pago, monto_pagado, metodo_pago, observacion, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta');
    }
    
    mysqli_stmt_bind_param($stmt, "isdsii", $prestamo_id, $fecha_actual, $monto_pagado, $metodo_pago, $observacion, $usuario_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al registrar pago');
    }

    // Calcular nuevos estados con redondeo correcto
    $nuevo_saldo = Seguridad::redondear_dinero($saldo_pendiente - $monto_pagado);
    if ($nuevo_saldo < 0) {
        $nuevo_saldo = 0;
    }
    
    $nuevo_estado = ($nuevo_saldo <= 0) ? 'cancelado' : 'activo';
    $fecha_ultimo_pago = date('Y-m-d');

    // Actualizar préstamo
    $sql_update = "UPDATE prestamos 
                   SET saldo_pendiente = ?, 
                       estado = ?, 
                       fecha_fin = ? 
                   WHERE id = ?";
                   
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    if (!$stmt_update) {
        throw new Exception('Error en la actualización');
    }
    
    mysqli_stmt_bind_param($stmt_update, "dssi", $nuevo_saldo, $nuevo_estado, $fecha_ultimo_pago, $prestamo_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar saldo del préstamo');
    }

    // Registrar movimiento en CAJA (Ingreso)
    $concepto = "Pago préstamo #$prestamo_id";
    $referencia = "PAGO-$prestamo_id";

    $sql_movimiento = "INSERT INTO movimientos_caja (tipo, monto, concepto, referencia, usuario_id) 
                       VALUES ('ingreso', ?, ?, ?, ?)";

    $stmt_movimiento = mysqli_prepare($conexion, $sql_movimiento);
    if (!$stmt_movimiento) {
        throw new Exception('Error al registrar movimiento');
    }
    
    mysqli_stmt_bind_param($stmt_movimiento, "dssi", $monto_pagado, $concepto, $referencia, $usuario_id);

    if (!mysqli_stmt_execute($stmt_movimiento)) {
        throw new Exception('Error al registrar movimiento en caja');
    }

    // Registrar en auditoría
    Seguridad::registrar_auditoria($conexion, $usuario_id, 'PAGO_REGISTRADO', 'pagos', $prestamo_id, [
        'monto_pagado' => $monto_pagado,
        'nuevo_saldo' => $nuevo_saldo,
        'metodo_pago' => $metodo_pago,
        'fecha' => $fecha_actual
    ]);

    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente',
        'nuevo_saldo' => $nuevo_saldo,
        'estado' => $nuevo_estado
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    Seguridad::log_seguro('Error al registrar pago: ' . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Error al procesar el pago']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
 

    // 4. Actualizar préstamo
    $sql_update = "UPDATE prestamos 
                   SET saldo_pendiente = ?, 
                       estado = ?, 
                       fecha_fin = ? 
                   WHERE id = ?";
                   
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "dssi", $nuevo_saldo, $nuevo_estado, $fecha_ultimo_pago, $prestamo_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar saldo del préstamo');
    }

    // 5. Registrar movimiento en CAJA (Ingreso)
    $concepto = "Pago préstamo #$prestamo_id";
    $referencia = "PAGO-$prestamo_id";

    $sql_movimiento = "INSERT INTO movimientos_caja (tipo, monto, concepto, referencia, usuario_id) 
                       VALUES ('ingreso', ?, ?, ?, ?)";

    $stmt_movimiento = mysqli_prepare($conexion, $sql_movimiento);
    mysqli_stmt_bind_param($stmt_movimiento, "dssi",
        $monto_pagado, $concepto, $referencia, $usuario_id
    );

    if (!mysqli_stmt_execute($stmt_movimiento)) {
        throw new Exception('Error al registrar movimiento en caja');
    }
    
    // Sumar saldo a la tabla caja (Opcional, si usas la tabla caja acumulativa)
    $sql_caja = "UPDATE caja SET saldo_actual = saldo_actual + ? WHERE id = 1";
    $stmt_caja = mysqli_prepare($conexion, $sql_caja);
    mysqli_stmt_bind_param($stmt_caja, "d", $monto_pagado);
    mysqli_stmt_execute($stmt_caja);

    // Confirmar todo
    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente',
        'tipo_pago' => ($nuevo_saldo <= 0) ? 'CANCELACIÓN TOTAL' : 'ABONO',
        'nuevo_saldo' => $nuevo_saldo,
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("Error en registrar_pago.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>