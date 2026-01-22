<?php
session_start();
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');

// Forzar HTTPS en producción
if (APP_ENV === 'production' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

$correo = trim($_POST['correo'] ?? '');
$clave = trim($_POST['clave'] ?? '');

// Validar que los campos no estén vacíos
if (empty($correo) || empty($clave)) {
    Seguridad::log_seguro("Intento de login sin credenciales desde {$_SERVER['REMOTE_ADDR']}", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Correo y contraseña son requeridos']);
    exit;
}

// Validar formato de email
if (!Seguridad::validar_email($correo)) {
    Seguridad::log_seguro("Intento de login con email inválido desde {$_SERVER['REMOTE_ADDR']}", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

// Verificar rate limiting (máximo 5 intentos en 5 minutos)
if (!Seguridad::verificar_rate_limit($conexion, $correo, 'login')) {
    Seguridad::log_seguro("Rate limit excedido para: $correo desde {$_SERVER['REMOTE_ADDR']}", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Demasiados intentos fallidos. Intenta más tarde.']);
    exit;
}

// Usar Prepared Statement (protección contra SQL Injection)
$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // Verificar contraseña con password_verify
    if (password_verify($clave, $fila['clave'])) {
        // Login exitoso
        $_SESSION['usuario'] = Seguridad::escapar_html($fila['correo']);
        $_SESSION['rol'] = Seguridad::escapar_html($fila['rol']);
        $_SESSION['nombre'] = Seguridad::escapar_html($fila['nombre']);
        $_SESSION['usuario_id'] = (int)$fila['id'];
        $_SESSION['login_time'] = time();
        
        // Regenerar session ID para prevenir session fixation
        session_regenerate_id(true);
        
        Seguridad::registrar_auditoria($conexion, $fila['id'], 'LOGIN_EXITOSO', 'usuarios', $fila['id'], ['correo' => $correo]);
        
        if ($fila['rol'] === 'admin') {
            echo json_encode(['success' => true, 'message' => '¡Bienvenido!', 'redirect' => '../home/admin.php']);
        } else {
            echo json_encode(['success' => true, 'message' => '¡Bienvenido!', 'redirect' => '../home/empleado.php']);
        }
        exit();
    } else {
        // Contraseña incorrecta
        Seguridad::registrar_auditoria($conexion, -1, 'LOGIN_FALLIDO', 'usuarios', -1, ['correo' => $correo, 'razon' => 'contraseña_incorrecta']);
        Seguridad::log_seguro("Intento de login con contraseña incorrecta para: $correo", 'WARNING');
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta. Verifica tus datos e intenta nuevamente.']);
        exit;
    }
} else {
    // Usuario no encontrado
    Seguridad::registrar_auditoria($conexion, -1, 'LOGIN_FALLIDO', 'usuarios', -1, ['correo' => $correo, 'razon' => 'usuario_no_existe']);
    Seguridad::log_seguro("Intento de login con usuario inexistente: $correo desde {$_SERVER['REMOTE_ADDR']}", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado. ¿Estás registrado?']);
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
