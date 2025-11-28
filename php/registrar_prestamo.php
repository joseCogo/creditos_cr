<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

error_log("Iniciando registrar_prestamo.php");
error_log("POST data: " . print_r($_POST, true));

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
$numero_boleta = $_POST['numero_boleta'] ?? ''; // NUEVO: Número de boleta
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

// NUEVA LÓGICA: Calcular valor de primera cuota (boleta + cuota diaria)
$primer_pago = $valor_boleta + $cuota_diaria;

// El saldo inicial es: monto_total - primer_pago
$saldo_inicial = $monto_total - $primer_pago;

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // 1. Insertar préstamo con saldo ya descontado
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, cuotas, cuota_diaria, fecha_inicio, fecha_fin, monto_total, saldo_pendiente, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iddiissddi", 
        $cliente_id, $monto, $interes, $cuotas, $cuota_diaria, 
        $fecha_inicio, $fecha_fin, $monto_total, $saldo_inicial, $usuario_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al registrar préstamo: ' . mysqli_error($conexion));
    }

    $prestamo_id = mysqli_insert_id($conexion);
    
    // 2. Insertar boleta en tabla separada (ya descontada desde el inicio)
    $sql_boleta = "INSERT INTO boletas_prestamos (prestamo_id, valor_boleta, numero_boleta, boleta_descontada, fecha_descuento, gano_rifa) 
                   VALUES (?, ?, ?, TRUE, CURDATE(), FALSE)";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "ids", $prestamo_id, $valor_boleta, $numero_boleta);
    
    if (!mysqli_stmt_execute($stmt_boleta)) {
        throw new Exception('Error al registrar boleta: ' . mysqli_error($conexion));
    }

    // 3. NUEVO: Registrar el primer pago automáticamente
    $fecha_actual = date('Y-m-d');
    $observacion_primer_pago = "Primera cuota automática (Boleta #$numero_boleta + Cuota diaria)";
    
    $sql_primer_pago = "INSERT INTO pagos (prestamo_id, fecha_pago, monto_pagado, metodo_pago, observacion, usuario_id) 
                        VALUES (?, ?, ?, 'efectivo', ?, ?)";
    $stmt_primer_pago = mysqli_prepare($conexion, $sql_primer_pago);
    mysqli_stmt_bind_param($stmt_primer_pago, "isdsi", 
        $prestamo_id, $fecha_actual, $primer_pago, $observacion_primer_pago, $usuario_id
    );
    
    if (!mysqli_stmt_execute($stmt_primer_pago)) {
        throw new Exception('Error al registrar primer pago: ' . mysqli_error($conexion));
    }

    // Confirmar transacción
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Préstamo registrado exitosamente',
        'prestamo_id' => $prestamo_id,
        'valor_boleta' => $valor_boleta,
        'numero_boleta' => $numero_boleta,
        'primer_pago' => $primer_pago,
        'monto_entregado' => $monto - $primer_pago, // Lo que realmente se entrega al cliente
        'saldo_inicial' => $saldo_inicial
    ]);

} catch (Exception $e) {
    // Revertir cambios si hay error
    mysqli_rollback($conexion);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($stmt)) mysqli_stmt_close($stmt);
if (isset($stmt_boleta)) mysqli_stmt_close($stmt_boleta);
if (isset($stmt_primer_pago)) mysqli_stmt_close($stmt_primer_pago);
mysqli_close($conexion);
?>