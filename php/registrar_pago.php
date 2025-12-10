<?php
session_start();
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json');
include("conexion.php");

// Logging inicial
error_log("Iniciando registro de pago. Usuario ID: " . ($_SESSION['usuario_id'] ?? 'No definido'));

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$prestamo_id = $_POST['prestamo_id'] ?? '';
$monto_pagado = $_POST['monto_pagado'] ?? 0;
$metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
$observacion = $_POST['observacion'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

if (empty($prestamo_id) || $monto_pagado <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Obtener información del préstamo
$sql_prestamo = "SELECT saldo_pendiente, cuota_diaria FROM prestamos WHERE id = ?";
$stmt_prestamo = mysqli_prepare($conexion, $sql_prestamo);
mysqli_stmt_bind_param($stmt_prestamo, "i", $prestamo_id);
mysqli_stmt_execute($stmt_prestamo);
$result_prestamo = mysqli_stmt_get_result($stmt_prestamo);
$prestamo = mysqli_fetch_assoc($result_prestamo);

if (!$prestamo) {
    echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado']);
    exit;
}

// Obtener información de la boleta
$sql_boleta = "SELECT valor_boleta, boleta_descontada FROM boletas_prestamos WHERE prestamo_id = ?";
$stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
mysqli_stmt_bind_param($stmt_boleta, "i", $prestamo_id);
mysqli_stmt_execute($stmt_boleta);
$result_boleta = mysqli_stmt_get_result($stmt_boleta);
$boleta = mysqli_fetch_assoc($result_boleta);

$valor_boleta = $boleta ? floatval($boleta['valor_boleta']) : 0;
$boleta_descontada = $boleta ? (bool)$boleta['boleta_descontada'] : false;

// Verificar si es primer pago
$sql_count = "SELECT COUNT(*) as total FROM pagos WHERE prestamo_id = ?";
$stmt_count = mysqli_prepare($conexion, $sql_count);
mysqli_stmt_bind_param($stmt_count, "i", $prestamo_id);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$count_data = mysqli_fetch_assoc($result_count);
$es_primer_pago = ($count_data['total'] == 0);

// Determinar tipo de pago
$cuota_diaria = floatval($prestamo['cuota_diaria']);
$tipo_pago = 'parcial';

if ($es_primer_pago && !$boleta_descontada) {
    $cuota_esperada = $cuota_diaria - $valor_boleta;
    if ($monto_pagado >= $cuota_esperada) {
        $tipo_pago = 'completo';
    }
} else {
    if ($monto_pagado >= $cuota_diaria) {
        $tipo_pago = 'completo';
    }
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Fecha exacta (sin hora)
    $fecha_actual = date('Y-m-d');
    error_log("Fecha calculada: " . $fecha_actual);

    // Registrar pago
    $sql = "INSERT INTO pagos (prestamo_id, fecha_pago, monto_pagado, metodo_pago, observacion, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "isdsii",
        $prestamo_id, $fecha_actual, $monto_pagado,
        $metodo_pago, $observacion, $usuario_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al registrar pago: ' . mysqli_error($conexion));
    }

    // Actualizar saldo del préstamo
    $nuevo_saldo = $prestamo['saldo_pendiente'] - $monto_pagado;
    if ($nuevo_saldo < 0) $nuevo_saldo = 0;

    $nuevo_estado = ($nuevo_saldo <= 0) ? 'cancelado' : 'activo';

    $sql_update = "UPDATE prestamos SET saldo_pendiente = ?, estado = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "dsi", $nuevo_saldo, $nuevo_estado, $prestamo_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar préstamo');
    }

    // Marcar boleta descontada si corresponde
    if ($es_primer_pago && !$boleta_descontada && $boleta) {
        $sql_update_boleta = "UPDATE boletas_prestamos 
                              SET boleta_descontada = TRUE, fecha_descuento = CURDATE() 
                              WHERE prestamo_id = ?";
        $stmt_boleta_update = mysqli_prepare($conexion, $sql_update_boleta);
        mysqli_stmt_bind_param($stmt_boleta_update, "i", $prestamo_id);
        mysqli_stmt_execute($stmt_boleta_update);
    }

    /* ============================================================
       🆕 INTEGRACIÓN NUEVA:
       SUMAR EL PAGO A LA CAJA (INGRESO)
       ============================================================ */

    // // 1️⃣ Sumar dinero a la caja
    // $sql_sumar = "UPDATE caja SET saldo_actual = saldo_actual + ? WHERE id = 1";
    // $stmt_sumar = mysqli_prepare($conexion, $sql_sumar);
    // mysqli_stmt_bind_param($stmt_sumar, "d", $monto_pagado);

    // if (!mysqli_stmt_execute($stmt_sumar)) {
    //     throw new Exception('Error al actualizar saldo de caja');
    // }

   // 2️⃣ Registrar movimiento de ingreso
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

    // Confirmar todo
    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente',
        'tipo_pago' => $tipo_pago,
        'nuevo_saldo' => $nuevo_saldo,
        'es_primer_pago' => $es_primer_pago,
        'valor_boleta' => $es_primer_pago ? $valor_boleta : 0
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("Error en registrar_pago.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>
