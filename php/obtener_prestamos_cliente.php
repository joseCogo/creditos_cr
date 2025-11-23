<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

include("conexion.php");

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula)) {
    echo json_encode(['success' => false, 'message' => 'Cédula requerida']);
    exit;
}

// Primero obtener el cliente
$sql_cliente = "SELECT id, nombre, telefono, direccion, correo FROM clientes WHERE cedula = ?";
$stmt_cliente = mysqli_prepare($conexion, $sql_cliente);
mysqli_stmt_bind_param($stmt_cliente, "s", $cedula);
mysqli_stmt_execute($stmt_cliente);
$result_cliente = mysqli_stmt_get_result($stmt_cliente);
$cliente = mysqli_fetch_assoc($result_cliente);

if (!$cliente) {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    exit;
}

// Obtener préstamos activos del cliente
$sql_prestamos = "SELECT id, monto, interes, cuotas, cuota_diaria, fecha_inicio, fecha_fin, 
                  monto_total, saldo_pendiente, estado 
                  FROM prestamos 
                  WHERE cliente_id = ? AND estado IN ('activo', 'mora')
                  ORDER BY fecha_inicio DESC";
$stmt_prestamos = mysqli_prepare($conexion, $sql_prestamos);
mysqli_stmt_bind_param($stmt_prestamos, "i", $cliente['id']);
mysqli_stmt_execute($stmt_prestamos);
$result_prestamos = mysqli_stmt_get_result($stmt_prestamos);

$prestamos = [];
while ($row = mysqli_fetch_assoc($result_prestamos)) {
    $prestamos[] = $row;
}

echo json_encode([
    'success' => true,
    'cliente' => $cliente,
    'prestamos' => $prestamos
]);

mysqli_close($conexion);
?>
