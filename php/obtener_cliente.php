  <?php
  header('Content-Type: application/json');
  include("conexion.php");

  if (isset($_GET['cedula'])) {
      // Si se pasa cédula, devolver solo ese cliente
      $cedula = $_GET['cedula'];
      $sql = "SELECT * FROM clientes WHERE cedula = ?";
      $stmt = mysqli_prepare($conexion, $sql);
      mysqli_stmt_bind_param($stmt, "s", $cedula);
      mysqli_stmt_execute($stmt);
      $resultado = mysqli_stmt_get_result($stmt);
      $cliente = mysqli_fetch_assoc($resultado);
      
      if ($cliente) {
          echo json_encode($cliente);
      } else {
          echo json_encode(['error' => 'Cliente no encontrado']);
      }
  } else {
      // Si no hay cédula, devolver lista completa (para cargarClientes)
      $sql = "SELECT * FROM clientes ORDER BY id DESC";
      $resultado = mysqli_query($conexion, $sql);
      $clientes = [];
      while ($row = mysqli_fetch_assoc($resultado)) {
          $clientes[] = $row;
      }
      echo json_encode($clientes);
  }

  mysqli_close($conexion);
?>
  