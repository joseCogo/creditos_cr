<?php
include("conexion.php");

$cedula = $_GET['cedula'] ?? '';
$sql = "SELECT * FROM clientes WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $cedula);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

echo json_encode($cliente);
?>