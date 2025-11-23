<?php
header('Content-Type: application/json');
include("conexion.php");

$cedula = $_POST['cedula'] ?? '';

if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
    exit();
}

// Verificar si el cliente tiene préstamos activos
$sql_check = "SELECT COUNT(*) as total FROM prestamos WHERE cliente_id = (SELECT id FROM clientes WHERE cedula = ?) AND estado = 'activo'";
$stmt_check = mysqli_prepare($conexion, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $cedula);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$row = mysqli_fetch_assoc($result_check);

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar. El cliente tiene préstamos activos']);
    exit();
}

// Eliminar cliente
$sql = "DELETE FROM clientes WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $cedula);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontró el cliente']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
