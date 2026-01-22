<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");
require_once(__DIR__ . '/config_seguridad.php');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !Seguridad::validar_csrf_token($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit();
}

// Solo admin puede eliminar
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permisos insuficientes']);
    exit();
}

$cedula = $_POST['cedula'] ?? '';

if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
    exit();
}

// Validar cedula
if (!Seguridad::validar_cedula($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula inválida']);
    exit();
}

// Verificar si el cliente tiene préstamos activos
$sql_check = "SELECT COUNT(*) as total FROM prestamos WHERE cliente_id = (SELECT id FROM clientes WHERE cedula = ?) AND estado = 'activo'";
$stmt_check = mysqli_prepare($conexion, $sql_check);
if (!$stmt_check) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en consulta']);
    exit();
}
mysqli_stmt_bind_param($stmt_check, "s", $cedula);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($result_check);

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar. El cliente tiene préstamos activos']);
    exit();
}

// Obtener ID del cliente antes de eliminar (para auditoría)
$sql_get_id = "SELECT id FROM clientes WHERE cedula = ?";
$stmt_get_id = mysqli_prepare($conexion, $sql_get_id);
mysqli_stmt_bind_param($stmt_get_id, "s", $cedula);
mysqli_stmt_execute($stmt_get_id);
$result_id = mysqli_stmt_get_result($stmt_get_id);
$client_row = mysqli_fetch_assoc($result_id);
$cliente_id = $client_row['id'] ?? 0;
mysqli_stmt_close($stmt_get_id);

// Eliminar cliente
$sql = "DELETE FROM clientes WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en preparación']);
    exit();
}
mysqli_stmt_bind_param($stmt, "s", $cedula);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Registrar eliminación en auditoría
        Seguridad::registrar_auditoria(
            $conexion,
            $_SESSION['usuario_id'],
            'ELIMINAR',
            'clientes',
            $cliente_id,
            json_encode(['cedula' => $cedula])
        );
        
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el cliente']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar cliente']);
}

mysqli_stmt_close($stmt);
?>