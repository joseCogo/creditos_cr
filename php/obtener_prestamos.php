<?php
header('Content-Type: application/json');
include("conexion.php");

$estado = $_GET['estado'] ?? '';

if (!empty($estado)) {
    $sql = "SELECT p.*, c.nombre as cliente_nombre, c.cedula as cliente_cedula 
            FROM prestamos p 
            INNER JOIN clientes c ON p.cliente_id = c.id 
            WHERE p.estado = ?
            ORDER BY p.id DESC";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $estado);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT p.*, c.nombre as cliente_nombre, c.cedula as cliente_cedula 
            FROM prestamos p 
            INNER JOIN clientes c ON p.cliente_id = c.id 
            ORDER BY p.id DESC";
    $resultado = mysqli_query($conexion, $sql);
}

$prestamos = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $prestamos[] = $row;
}

echo json_encode($prestamos);
mysqli_close($conexion);
?>