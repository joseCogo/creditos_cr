 <?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json');
    ini_set('display_errors', 1);  // Temporal para ver errores
    error_reporting(E_ALL);
    // Log para depurar
    error_log("Iniciando registrar_prestamo.php");
    error_log("POST data: " . print_r($_POST, true));
    session_start();
    include("conexion.php");

    $cliente_id = $_POST['cliente_id'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $interes = $_POST['interes'] ?? 0;
    $cuotas = $_POST['cuotas'] ?? 0;
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $cuota_diaria = $_POST['cuota_diaria'] ?? 0;
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;

    // Validar campos
    if (empty($cliente_id) || $monto <= 0 || empty($fecha_inicio)) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit();
    }

    $cliente_id = intval($cliente_id);
    $monto = floatval($monto);
    $interes = floatval($interes);
    $cuotas = intval($cuotas);
    $cuota_diaria = floatval($cuota_diaria);
    $usuario_id = intval($usuario_id);

    // Calcular monto total con interés
    $monto_total = $monto + ($monto * ($interes / 100));

    // Insertar préstamo
    $sql = "INSERT INTO prestamos (cliente_id, monto, interes, cuotas, cuota_diaria, fecha_inicio, fecha_fin, monto_total, saldo_pendiente, estado, usuario_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = mysqli_prepare($conexion, $sql);

    // cliente_id(i), monto(d), interes(d), cuotas(i), cuota_diaria(d), fecha_inicio(s), fecha_fin(s), monto_total(d), saldo_pendiente(d), usuario_id(i)
    mysqli_stmt_bind_param($stmt, "iddiissddi", $cliente_id, $monto, $interes, $cuotas, $cuota_diaria, $fecha_inicio, $fecha_fin, $monto_total, $monto_total, $usuario_id);

    if (mysqli_stmt_execute($stmt)) {
        $prestamo_id = mysqli_insert_id($conexion);
        echo json_encode([
            'success' => true,
            'message' => 'Préstamo registrado exitosamente',
            'prestamo_id' => $prestamo_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar préstamo: ' . mysqli_error($conexion)
        ]);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    ?>
