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
$valor_boleta = $_POST['valor_boleta'] ?? 0; // NUEVO: Recibir valor de boleta
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Validar campos
if (empty($cliente_id) || $monto <= 0 || empty($fecha_inicio)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
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

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // Insertar préstamo
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, cuotas, cuota_diaria, fecha_inicio, fecha_fin, monto_total, saldo_pendiente, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "iddiissddi", $cliente_id, $monto, $interes, $cuotas, $cuota_diaria, $fecha_inicio, $fecha_fin, $monto_total, $monto_total, $usuario_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al registrar préstamo: ' . mysqli_error($conexion));
    }

    $prestamo_id = mysqli_insert_id($conexion);
    
    // Insertar boleta en tabla separada
    $sql_boleta = "INSERT INTO boletas_prestamos (prestamo_id, valor_boleta, boleta_descontada, gano_rifa) 
                   VALUES (?, ?, FALSE, FALSE)";
    $stmt_boleta = mysqli_prepare($conexion, $sql_boleta);
    mysqli_stmt_bind_param($stmt_boleta, "id", $prestamo_id, $valor_boleta);
    
    if (!mysqli_stmt_execute($stmt_boleta)) {
        throw new Exception('Error al registrar boleta: ' . mysqli_error($conexion));
    }

    // Confirmar transacción
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Préstamo y boleta registrados exitosamente',
        'prestamo_id' => $prestamo_id,
        'valor_boleta' => $valor_boleta
    ]);

} catch (Exception $e) {
    // Revertir cambios si hay error
    mysqli_rollback($conexion);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_stmt_close($stmt);
if (isset($stmt_boleta)) mysqli_stmt_close($stmt_boleta);
mysqli_close($conexion);
?>