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
$monto_pagado = floatval($_POST['monto_pagado'] ?? 0);
$metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
$observacion = $_POST['observacion'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

if (empty($prestamo_id) || $monto_pagado <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o monto inválido']);
    exit;
}

// 1. Obtener información del préstamo (Solo saldo pendiente)
// YA NO buscamos cuota_diaria
$sql_prestamo = "SELECT saldo_pendiente FROM prestamos WHERE id = ?";
$stmt_prestamo = mysqli_prepare($conexion, $sql_prestamo);
mysqli_stmt_bind_param($stmt_prestamo, "i", $prestamo_id);
mysqli_stmt_execute($stmt_prestamo);
$result_prestamo = mysqli_stmt_get_result($stmt_prestamo);
$prestamo = mysqli_fetch_assoc($result_prestamo);

if (!$prestamo) {
    echo json_encode(['success' => false, 'message' => 'Préstamo no encontrado']);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Fecha exacta
    $fecha_actual = date('Y-m-d');

    // 2. Registrar pago en tabla pagos
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

    // 3. Calcular nuevos estados
    $nuevo_saldo = floatval($prestamo['saldo_pendiente']) - $monto_pagado;
    if ($nuevo_saldo < 0) $nuevo_saldo = 0; // Evitar negativos
    
    $nuevo_estado = ($nuevo_saldo <= 0) ? 'cancelado' : 'activo';
    
    // fecha_fin ahora representa la "Fecha de Último Pago"
    $fecha_ultimo_pago = date('Y-m-d'); 

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