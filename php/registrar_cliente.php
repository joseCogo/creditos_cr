<?php
header('Content-Type: application/json');
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');

// Obtener y validar datos
$cedula = trim($_POST['cedula'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$correo = trim($_POST['correo'] ?? '');

// VALIDACIÓN 1: Campos obligatorios y formato
if (!Seguridad::validar_cedula($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula inválida']);
    exit;
}

if (!Seguridad::validar_texto($nombre, 2, 100)) {
    echo json_encode(['success' => false, 'message' => 'Nombre inválido (2-100 caracteres)']);
    exit;
}

if (!Seguridad::validar_telefono($telefono)) {
    echo json_encode(['success' => false, 'message' => 'Teléfono inválido']);
    exit;
}

if (!Seguridad::validar_texto($direccion, 5, 255)) {
    echo json_encode(['success' => false, 'message' => 'Dirección inválida (5-255 caracteres)']);
    exit;
}

if (!empty($correo) && !Seguridad::validar_email($correo)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// VALIDACIÓN 2: Cédula única
$sql_check = "SELECT cedula FROM clientes WHERE cedula = ?";
$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $cedula);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    mysqli_stmt_close($stmt_check);
    echo json_encode(['success' => false, 'message' => 'Ya existe un cliente con esta cédula']);
    exit;
}
mysqli_stmt_close($stmt_check);

$fecha_registro = date('Y-m-d');

// Preparar datos seguros
$nombre_seguro = Seguridad::escapar_html($nombre);
$telefono_seguro = Seguridad::escapar_html($telefono);
$direccion_seguro = Seguridad::escapar_html($direccion);
$correo_seguro = !empty($correo) ? Seguridad::escapar_html($correo) : '';

$sql = "INSERT INTO clientes (cedula, nombre, telefono, direccion, correo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $cedula, $nombre_seguro, $telefono_seguro, $direccion_seguro, $correo_seguro, $fecha_registro);

if (mysqli_stmt_execute($stmt)) {
    $cliente_id = mysqli_insert_id($conexion);
    Seguridad::registrar_auditoria($conexion, $_SESSION['usuario_id'] ?? 0, 'CLIENTE_REGISTRADO', 'clientes', $cliente_id, ['cedula' => $cedula, 'nombre' => $nombre]);
    echo json_encode(['success' => true, 'message' => 'Cliente registrado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al registrar cliente']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
