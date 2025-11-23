<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");

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

$sql = "INSERT INTO pagos (prestamo_id, fecha_pago, monto_pagado, metodo_pago, observacion, usuario_id) 
        VALUES (?, NOW(), ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "idssi", $prestamo_id, $monto_pagado, $metodo_pago, $observacion, $usuario_id);

if (mysqli_stmt_execute($stmt)) {
    $nuevo_saldo = $prestamo['saldo_pendiente'] - $monto_pagado;
    $nuevo_estado = ($nuevo_saldo <= 0) ? 'cancelado' : 'activo';
    
    $sql_update = "UPDATE prestamos SET saldo_pendiente = ?, estado = ? WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "dsi", $nuevo_saldo, $nuevo_estado, $prestamo_id);
    mysqli_stmt_execute($stmt_update);
    
    $tipo_pago = ($monto_pagado >= $prestamo['cuota_diaria']) ? 'completo' : 'parcial';
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pago registrado exitosamente',
        'tipo_pago' => $tipo_pago,
        'nuevo_saldo' => $nuevo_saldo
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al : ' . mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>
