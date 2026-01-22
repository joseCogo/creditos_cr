<?php
// archivo: php/registrar_prestamo.php
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');
session_start();
include("conexion.php");

$cliente_id = $_POST['cliente_id'] ?? '';
$monto = floatval($_POST['monto'] ?? 0); // Capital entregado
$interes_porcentaje = floatval($_POST['interes'] ?? 20); // % Ganancia
$usuario_id = $_SESSION['usuario_id'] ?? 0;

if (empty($cliente_id) || $monto <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

// 1. Validar Caja
$sql_saldo = "SELECT saldo_actual FROM caja WHERE id = 1";
$res = mysqli_query($conexion, $sql_saldo);
$saldo_caja = ($res && $fila = mysqli_fetch_assoc($res)) ? floatval($fila['saldo_actual']) : 0;

if ($monto > $saldo_caja) {
    echo json_encode(['success' => false, 'message' => 'Saldo insuficiente en caja.']);
    exit();
}

// 2. Cálculos Simples
$ganancia = $monto * ($interes_porcentaje / 100);
$monto_total = $monto + $ganancia; // Deuda inicial
$fecha_hoy = date('Y-m-d');

mysqli_begin_transaction($conexion);

try {
    // Insertar Préstamo (Sin cuotas, sin periodicidad)
    // Usamos fecha_fin para indicar "Última Actualización" o "Último Pago". Al inicio es hoy.
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, monto_total, saldo_pendiente, fecha_inicio, fecha_fin, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    // Tipos: i (int), d (double), d, d, d, s (string), s, i
    mysqli_stmt_bind_param(
        $stmt,
        "iddddssi",
        $cliente_id,
        $monto,
        $interes_porcentaje,
        $monto_total,
        $monto_total,
        $fecha_hoy,
        $fecha_hoy,
        $usuario_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al crear préstamo');
    }
    $prestamo_id = mysqli_insert_id($conexion);

    // Descontar de Caja
    $sql_caja = "UPDATE caja SET saldo_actual = saldo_actual - ? WHERE id = 1";
    $stmt_caja = mysqli_prepare($conexion, $sql_caja);
    mysqli_stmt_bind_param($stmt_caja, "d", $monto);
    mysqli_stmt_execute($stmt_caja);

    // Movimiento Caja
    $concepto = "Préstamo Simple #$prestamo_id";
    $sql_mov = "INSERT INTO movimientos_caja (tipo, monto, concepto, usuario_id, referencia) VALUES ('egreso', ?, ?, ?, ?)";
    $ref = "PRESTAMO-$prestamo_id";
    $stmt_mov = mysqli_prepare($conexion, $sql_mov);
    mysqli_stmt_bind_param($stmt_mov, "dsis", $monto, $concepto, $usuario_id, $ref);
    mysqli_stmt_execute($stmt_mov);

    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Préstamo creado. Deuda: $' . number_format($monto_total)
    ]);
} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>