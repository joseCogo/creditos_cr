<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");
require_once(__DIR__ . '/config_seguridad.php');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !Seguridad::validar_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Verificar que sea administrador
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
    exit;
}

$monto = $_POST['monto'] ?? 0;
$concepto = $_POST['concepto'] ?? 'Ingreso de capital';
$usuario_id = $_SESSION['usuario_id'];

// Validar monto con función de seguridad
if (!Seguridad::validar_monto($monto, 0.01, 999999999.99)) {
    echo json_encode(['success' => false, 'message' => 'Monto inválido']);
    exit;
}

// Redondear correctamente
$monto = Seguridad::redondear_dinero($monto);

// Validar concepto
if (strlen($concepto) < 3 || strlen($concepto) > 200) {
    echo json_encode(['success' => false, 'message' => 'Concepto debe tener entre 3 y 200 caracteres']);
    exit;
}
$concepto = Seguridad::escapar_html($concepto);

/* ========================================================
   1️⃣ Verificar si existe la caja (id = 1)
   Si no existe, crearla automáticamente
======================================================== */
$sql_check = "SELECT id FROM caja WHERE id = 1";
$result_check = mysqli_query($conexion, $sql_check);

if (!$result_check || mysqli_num_rows($result_check) == 0) {
    // Crear caja inicial
    $crear = mysqli_query($conexion, "INSERT INTO caja (id, saldo_actual) VALUES (1, 0)");
    if (!$crear) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al crear caja inicial']);
        exit;
    }
}

mysqli_begin_transaction($conexion);

try {
    // Actualizar saldo en caja
    $sql_update = "UPDATE caja SET saldo_actual = saldo_actual + ? WHERE id = 1";
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    if (!$stmt_update) {
        throw new Exception('Error en preparación de consulta');
    }
    mysqli_stmt_bind_param($stmt_update, "d", $monto);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception('Error al actualizar saldo');
    }
    
    // Registrar movimiento
    $sql_movimiento = "INSERT INTO movimientos_caja (tipo, monto, concepto, usuario_id) VALUES ('ingreso', ?, ?, ?)";
    $stmt_movimiento = mysqli_prepare($conexion, $sql_movimiento);
    if (!$stmt_movimiento) {
        throw new Exception('Error en preparación de movimiento');
    }
    mysqli_stmt_bind_param($stmt_movimiento, "dsi", $monto, $concepto, $usuario_id);
    
    if (!mysqli_stmt_execute($stmt_movimiento)) {
        throw new Exception('Error al registrar movimiento');
    }
    
    // Registrar en auditoría
    Seguridad::registrar_auditoria(
        $conexion,
        $usuario_id,
        'AGREGAR_SALDO',
        'caja',
        1,
        json_encode(['monto' => $monto, 'concepto' => $concepto])
    );
    
    // Obtener nuevo saldo
    $result = mysqli_query($conexion, "SELECT saldo_actual FROM caja WHERE id = 1");
    $nuevo_saldo = mysqli_fetch_assoc($result)['saldo_actual'];
    
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Saldo agregado exitosamente',
        'nuevo_saldo' => $nuevo_saldo
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conexion);
    http_response_code(500);
    Seguridad::log_seguro('Error en agregar_saldo: ' . $e->getMessage(), 'ERROR');
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar operación'
    ]);
}
?>