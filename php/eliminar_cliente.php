<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("conexion.php");

$cedula = trim($_POST['cedula'] ?? '');

if (!$cedula) {
  echo json_encode([
    "success" => false,
    "message" => "Cédula no proporcionada."
  ]);
  exit;
}

$sql = "DELETE FROM clientes WHERE cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
  echo json_encode([
    "success" => false,
    "message" => "Error al preparar la consulta.",
    "debug" => mysqli_error($conexion)
  ]);
  exit;
}

mysqli_stmt_bind_param($stmt, "s", $cedula);
mysqli_stmt_execute($stmt);

$filas_afectadas = mysqli_stmt_affected_rows($stmt);

if ($filas_afectadas > 0) {
  echo json_encode([
    "success" => true,
    "message" => "Cliente eliminado correctamente."
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "No se encontró ningún cliente con esa cédula.",
    "debug" => [
      "cedula" => $cedula,
      "filas_afectadas" => $filas_afectadas
    ]
  ]);
}