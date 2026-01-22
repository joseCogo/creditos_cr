<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');
session_start();
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');

// Validar sesión y permisos (solo admin)
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    Seguridad::registrar_auditoria($conexion, $_SESSION['usuario_id'], 'ACCESO_DENEGADO', 'prestamos', 0, ['razon' => 'no_es_admin']);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para crear préstamos']);
    exit;
}

// Validar CSRF token
$csrf_token = $_POST['csrf_token'] ?? '';
if (!Seguridad::validar_csrf_token($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
    exit;
}

$cliente_id = (int)($_POST['cliente_id'] ?? 0);
$monto = floatval($_POST['monto'] ?? 0);
$interes_porcentaje = floatval($_POST['interes'] ?? 20);
$usuario_id = (int)$_SESSION['usuario_id'];

// VALIDACIÓN 1: Campos obligatorios
if (empty($cliente_id) || !Seguridad::validar_monto($monto, 1000, 999999999)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o monto inválido']);
    exit;
}

// VALIDACIÓN 2: Validar interés (entre 0% y 100%)
if ($interes_porcentaje < 0 || $interes_porcentaje > 100) {
    echo json_encode(['success' => false, 'message' => 'Porcentaje de interés debe estar entre 0 y 100']);
    exit;
}

// VALIDACIÓN 3: Verificar que el cliente existe
$sql_cliente = "SELECT id FROM clientes WHERE id = ?";
$stmt_cliente = mysqli_prepare($conexion, $sql_cliente);
mysqli_stmt_bind_param($stmt_cliente, "i", $cliente_id);
mysqli_stmt_execute($stmt_cliente);
$result_cliente = mysqli_stmt_get_result($stmt_cliente);

if (mysqli_num_rows($result_cliente) === 0) {
    echo json_encode(['success' => false, 'message' => 'Cliente no existe']);
    exit;
}

// 1. Validar Caja
$sql_saldo = "SELECT saldo_actual FROM caja WHERE id = 1";
$res = mysqli_query($conexion, $sql_saldo);
$saldo_caja = ($res && $fila = mysqli_fetch_assoc($res)) ? floatval($fila['saldo_actual']) : 0;

if ($monto > $saldo_caja) {
    Seguridad::registrar_auditoria($conexion, $usuario_id, 'PRESTAMO_RECHAZADO', 'prestamos', $cliente_id, 
        ['razon' => 'saldo_insuficiente', 'monto_solicitado' => $monto, 'saldo_disponible' => $saldo_caja]);
    echo json_encode(['success' => false, 'message' => 'Saldo insuficiente en caja']);
    exit;
}

// 2. Cálculos: Usar redondeo correcto para operaciones financieras
$ganancia = Seguridad::redondear_dinero($monto * ($interes_porcentaje / 100));
$monto_total = Seguridad::redondear_dinero($monto + $ganancia);
$fecha_hoy = date('Y-m-d');

mysqli_begin_transaction($conexion);

try {
    // Insertar Préstamo
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, monto_total, saldo_pendiente, fecha_inicio, fecha_fin, estado, usuario_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', ?)";

    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta');
    }
    
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
    if (!$stmt_caja) {
        throw new Exception('Error al actualizar caja');
    }
    
    mysqli_stmt_bind_param($stmt_caja, "d", $monto);
    if (!mysqli_stmt_execute($stmt_caja)) {
        throw new Exception('Error al descontar de caja');
    }

    // Movimiento Caja
    $concepto = "Préstamo Simple #$prestamo_id";
    $sql_mov = "INSERT INTO movimientos_caja (tipo, monto, concepto, usuario_id, referencia) VALUES ('egreso', ?, ?, ?, ?)";
    $ref = "PRESTAMO-$prestamo_id";
    $stmt_mov = mysqli_prepare($conexion, $sql_mov);
    if (!$stmt_mov) {
        throw new Exception('Error en movimiento de caja');
    }
    
    mysqli_stmt_bind_param($stmt_mov, "dsis", $monto, $concepto, $usuario_id, $ref);
    if (!mysqli_stmt_execute($stmt_mov)) {
        throw new Exception('Error al registrar movimiento');
    }

    // Registrar en auditoría
    Seguridad::registrar_auditoria($conexion, $usuario_id, 'PRESTAMO_CREADO', 'prestamos', $prestamo_id, [
        'cliente_id' => $cliente_id,
        'monto' => $monto,
        'interes_porcentaje' => $interes_porcentaje,
        'monto_total' => $monto_total,
        'ganancia' => $ganancia
    ]);

    mysqli_commit($conexion);

    echo json_encode([
        'success' => true,
        'message' => 'Préstamo creado. Deuda: $' . number_format($monto_total, 0, ',', '.'),
        'prestamo_id' => $prestamo_id
    ]);
} catch (Exception $e) {
    mysqli_rollback($conexion);
    Seguridad::log_seguro('Error al crear préstamo: ' . $e->getMessage(), 'ERROR');
    echo json_encode(['success' => false, 'message' => 'Error al procesar préstamo']);
}

mysqli_close($conexion);
?>