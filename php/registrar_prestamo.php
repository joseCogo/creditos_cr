<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');

session_start();
include("conexion.php");

$cliente_id = $_POST['cliente_id'] ?? '';
$monto = $_POST['monto'] ?? 0;
$interes = $_POST['interes'] ?? 0;
$cuotas = $_POST['cuotas'] ?? 0;
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$cuota_diaria = $_POST['cuota_diaria'] ?? 0;
$fecha_fin = $_POST['fecha_fin'] ?? '';
$valor_boleta = $_POST['valor_boleta'] ?? 0;
$numero_boleta = $_POST['numero_boleta'] ?? '';
$periodicidad = $_POST['periodicidad'] ?? 'diario'; // NUEVO: diario, semanal, quincenal
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Validar campos
if (empty($cliente_id) || $monto <= 0 || empty($fecha_inicio) || empty($numero_boleta)) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos. El número de boleta es obligatorio.'
    ]);
    exit();
}

$cliente_id = intval($cliente_id);
$monto = floatval($monto);
$interes = floatval($interes);
$cuotas = intval($cuotas);
$cuota_diaria = floatval($cuota_diaria);
$valor_boleta = floatval($valor_boleta);
$usuario_id = intval($usuario_id);

// Calcular monto total con interés
$monto_total = $monto + ($monto * ($interes / 100));

// NUEVO: Calcular cuota según periodicidad
$cuota_periodica = 0;
switch ($periodicidad) {
    case 'diario':
        $cuota_periodica = $cuota_diaria;
        break;
    case 'semanal':
        $cuota_periodica = $cuota_diaria * 7; // 7 días
        break;
    case 'quincenal':
        $cuota_periodica = $cuota_diaria * 15; // 15 días
        break;
}

// La primera cuota total es la suma de la cuota diaria + el valor de la boleta.
$primer_descuento = $cuota_diaria + $valor_boleta;

// El saldo inicial es: monto_total - primer_descuento
$saldo_inicial = $monto_total - $primer_descuento;


// ❗ NUEVO: OBTENER SALDO DISPONIBLE Y VALIDAR
$sql_saldo = "SELECT saldo_actual FROM caja LIMIT 1";
$result_saldo = mysqli_query($conexion, $sql_saldo);
$saldo_disponible = 0;

if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
    $saldo_disponible = mysqli_fetch_assoc($result_saldo)['saldo_actual'];
}

// ❗ VALIDACIÓN CRÍTICA DE SALDO
if ($monto > $saldo_disponible) {
    echo json_encode([
        'success' => false,
        'message' => 'Saldo insuficiente en caja. El monto a prestar (' . number_format($monto) . ') excede el saldo disponible (' . number_format($saldo_disponible) . ').'
    ]);
    exit();
}
// ❗ FIN VALIDACIÓN

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // 1. Insertar préstamo con saldo ya descontado
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, cuotas, cuota_diaria, periodicidad, fecha_inicio, fecha_fin, monto_total, saldo_pendiente, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "iddidsssddi",
        $cliente_id,
        $monto,
        $interes,
        $cuotas,
        $cuota_diaria,
        $periodicidad,
        $fecha_inicio,
        $fecha_fin,
        $monto_total,
        $saldo_inicial,
        $usuario_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al registrar préstamo: ' . mysqli_error($conexion));
    }

    $prestamo_id = mysqli_insert_id($conexion);

    // 2. Insertar boleta (ya descontada desde el inicio)
    $sql_boleta = "INSERT INTO boletas_prestamos (prestamo_id, valor_boleta, numero_boleta, boleta_descontada, fecha_descuento, gano_rifa) 
                   VALUES (?, ?, ?, TRUE, CURDATE(), FALSE)";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "ids", $prestamo_id, $valor_boleta, $numero_boleta);

    if (!mysqli_stmt_execute($stmt_boleta)) {
        throw new Exception('Error al registrar boleta: ' . mysqli_error($conexion));
    }

    // 3. Registrar el primer pago automáticamente (SOLO la cuota diaria)
    // NOTA: Se registra solo la cuota diaria en el pago, pero la boleta ya se considera en el cálculo de saldo inicial (montoEntregado)
    $fecha_actual = date('Y-m-d');
    $observacion_primer_pago = "Primera cuota automática - Préstamo #$prestamo_id";

    $sql_primer_pago = "INSERT INTO pagos (prestamo_id, fecha_pago, monto_pagado, metodo_pago, observacion, usuario_id) 
                        VALUES (?, ?, ?, 'efectivo', ?, ?)";
    $stmt_primer_pago = mysqli_prepare($conexion, $sql_primer_pago);
    // IMPORTANTE: Aquí solo se registra la CUOTA DIARIA como pago (el valor_boleta ya se descontó del capital entregado).
    mysqli_stmt_bind_param(
        $stmt_primer_pago,
        "isdsi",
        $prestamo_id,
        $fecha_actual,
        $cuota_diaria,
        $observacion_primer_pago,
        $usuario_id
    );

    if (!mysqli_stmt_execute($stmt_primer_pago)) {
        throw new Exception('Error al registrar primer pago: ' . mysqli_error($conexion));
    }
    
    // ❗ NUEVO: 4. REGISTRAR EGRESO Y ACTUALIZAR SALDO EN CAJA
    $concepto_egreso = "Préstamo otorgado #$prestamo_id ($monto) al cliente $cliente_id";
    
    // a) Actualizar saldo (restar el monto del préstamo)
    $sql_update_saldo = "UPDATE caja SET saldo_actual = saldo_actual - ? WHERE id = 1";
    $stmt_update_saldo = mysqli_prepare($conexion, $sql_update_saldo);
    mysqli_stmt_bind_param($stmt_update_saldo, "d", $monto);
    
    if (!mysqli_stmt_execute($stmt_update_saldo)) {
        throw new Exception('Error al actualizar saldo en caja (egreso)');
    }
    
    // b) Registrar movimiento de egreso
    $sql_movimiento = "INSERT INTO movimientos_caja (tipo, monto, concepto, usuario_id, referencia) 
                       VALUES ('egreso', ?, ?, ?, ?)";
    $referencia = "Préstamo #$prestamo_id";
    $stmt_movimiento = mysqli_prepare($conexion, $sql_movimiento);
    mysqli_stmt_bind_param($stmt_movimiento, "dsis", $monto, $concepto_egreso, $usuario_id, $referencia);
    
    if (!mysqli_stmt_execute($stmt_movimiento)) {
        throw new Exception('Error al registrar movimiento de egreso');
    }
    // ❗ FIN NUEVO: 4.

    // Confirmar transacción
    mysqli_commit($conexion);

    // ... (restar el monto del préstamo del capital a entregar para mostrar en el mensaje de éxito)
    $monto_entregado = $monto - $primer_descuento;
    
    echo json_encode([
        'success' => true,
        'message' => 'Préstamo registrado exitosamente',
        'prestamo_id' => $prestamo_id,
        'valor_boleta' => $valor_boleta,
        'numero_boleta' => $numero_boleta,
        'periodicidad' => $periodicidad,
        'cuota_periodica' => $cuota_periodica,
        'primer_descuento' => $primer_descuento,
        'monto_entregado' => $monto_entregado,
        'saldo_inicial' => $saldo_inicial
    ]);
} catch (Exception $e) {
    mysqli_rollback($conexion);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($stmt_boleta)) mysqli_stmt_close($stmt_boleta);
if (isset($stmt_primer_pago)) mysqli_stmt_close($stmt_primer_pago);
if (isset($stmt_update_saldo)) mysqli_stmt_close($stmt_update_saldo); // ❗ NUEVO
if (isset($stmt_movimiento)) mysqli_stmt_close($stmt_movimiento);     // ❗ NUEVO
mysqli_close($conexion);
?>