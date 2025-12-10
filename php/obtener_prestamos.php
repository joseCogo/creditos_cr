<?php
header('Content-Type: application/json');
include("conexion.php");

$estado = $_GET['estado'] ?? '';

$sql = "SELECT p.*, c.nombre as cliente_nombre, c.cedula as cliente_cedula 
        FROM prestamos p
        INNER JOIN clientes c ON p.cliente_id = c.id";

if ($estado) {
    $sql .= " WHERE p.estado = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $estado);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql .= " ORDER BY p.id DESC";
    $result = mysqli_query($conexion, $sql);
}

if (!$result) {
    echo json_encode(["error" => mysqli_error($conexion)]);
    exit;
}

$prestamos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $prestamos[] = $row;
}

echo json_encode($prestamos);
mysqli_close($conexion);
?>
