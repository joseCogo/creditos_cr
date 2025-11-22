
 <?php
header('Content-Type: application/json');
include("conexion.php");

$nombre = $_POST['nombre'] ?? '';
$cedula = $_POST['cedula'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$correo = $_POST['correo'] ?? '';

if ($nombre && $cedula && $telefono && $direccion) {
  // Verificar si la cédula ya existe
  $check = mysqli_prepare($conexion, "SELECT id FROM clientes WHERE cedula = ?");
  mysqli_stmt_bind_param($check, "s", $cedula);
  mysqli_stmt_execute($check);
  mysqli_stmt_store_result($check);

  if (mysqli_stmt_num_rows($check) > 0) {
    echo json_encode([
      "success" => false,
      "message" => "La cédula ya está registrada."
    ]);
    exit;
  }

  // Insertar cliente
  $sql = "INSERT INTO clientes (nombre, cedula, telefono, direccion, correo) VALUES (?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conexion, $sql);
  mysqli_stmt_bind_param($stmt, "sssss", $nombre, $cedula, $telefono, $direccion, $correo);

  if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
      "success" => true,
      "message" => "Cliente registrado correctamente."
    ]);
  } else {
    echo json_encode([
      "success" => false,
      "message" => "Error al guardar el cliente."
    ]);
  }
} else {
  echo json_encode([
    "success" => false,
    "message" => "Faltan campos obligatorios."
  ]);
}
?>

