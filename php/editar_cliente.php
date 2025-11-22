<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("conexion.php");

$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$correo = $_POST['correo'] ?? '';

if ($cedula && $nombre && $telefono && $direccion) {
  $sql = "UPDATE clientes SET nombre=?, telefono=?, direccion=?, correo=? WHERE cedula=?";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "sssss", $nombre, $telefono, $direccion, $correo, $cedula);

  if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
      "success" => true,
      "message" => "Cliente actualizado correctamente."
    ]);
  } else {
    echo json_encode([
      "success" => false,
      "message" => "No se pudo actualizar el cliente."
    ]);
  }
} else {
  echo json_encode([
    "success" => false,
    "message" => "Faltan campos obligatorios."
  ]);
}
?>


