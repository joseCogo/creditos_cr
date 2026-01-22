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

// Permisos: solo admin y el empleado con acceso
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permisos insuficientes']);
    exit();
}

$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$correo = $_POST['correo'] ?? '';

// Validar campos obligatorios
if (empty($cedula) || empty($nombre) || empty($telefono) || empty($direccion)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit();
}

// Validar longitudes y formato
if (!Seguridad::validar_cedula($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula inválida (5-20 caracteres, solo números)']);
    exit();
}

if (!Seguridad::validar_texto($nombre, 2, 100, '/^[a-záéíóúñ\s]+$/i')) {
    echo json_encode(['success' => false, 'message' => 'Nombre inválido']);
    exit();
}

if (!Seguridad::validar_telefono($telefono)) {
    echo json_encode(['success' => false, 'message' => 'Teléfono inválido']);
    exit();
}

if (strlen($direccion) < 5 || strlen($direccion) > 255) {
    echo json_encode(['success' => false, 'message' => 'Dirección debe tener entre 5 y 255 caracteres']);
    exit();
}

// Escapar inputs
$nombre = Seguridad::escapar_html($nombre);
$telefono = Seguridad::escapar_html($telefono);
$direccion = Seguridad::escapar_html($direccion);
$correo = Seguridad::escapar_html($correo);

// Actualizar cliente
$sql = "UPDATE clientes SET nombre = ?, telefono = ?, direccion = ?, correo = ? WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en preparación de consulta']);
    exit();
}

mysqli_stmt_bind_param($stmt, "sssss", $nombre, $telefono, $direccion, $correo, $cedula);

if (mysqli_stmt_execute($stmt)) {
    $affected = mysqli_stmt_affected_rows($stmt);
    if ($affected > 0) {
        // Registrar en auditoría
        $cliente_id_sql = "SELECT id FROM clientes WHERE cedula = ?";
        $client_stmt = mysqli_prepare($conexion, $cliente_id_sql);
        mysqli_stmt_bind_param($client_stmt, "s", $cedula);
        mysqli_stmt_execute($client_stmt);
        $client_result = mysqli_stmt_get_result($client_stmt);
        $client_row = mysqli_fetch_assoc($client_result);
        $cliente_id = $client_row['id'] ?? 0;
        
        Seguridad::registrar_auditoria(
            $conexion,
            $_SESSION['usuario_id'],
            'EDITAR',
            'clientes',
            $cliente_id,
            json_encode(['nombre' => $nombre, 'telefono' => $telefono, 'direccion' => $direccion])
        );
        
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el cliente o no hubo cambios']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
