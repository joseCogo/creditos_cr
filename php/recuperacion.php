<?php
require_once(__DIR__ . '/config_seguridad.php');
require_once(__DIR__ . '/conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

$correo = trim($_POST['correo'] ?? '');

// Validar formato de email
if (!Seguridad::validar_email($correo)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Verificar si el correo existe
$sql = "SELECT id FROM usuarios WHERE correo = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) === 0) {
    Seguridad::registrar_auditoria($conexion, -1, 'RECUPERACION_FALLIDA', 'usuarios', -1, ['correo' => $correo, 'razon' => 'email_no_existe']);
    echo json_encode(['success' => false, 'message' => 'Correo no encontrado']);
    exit;
}

$usuario = mysqli_fetch_assoc($resultado);

// Generar token único
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token en la base de datos
$sql_token = "UPDATE usuarios SET token_recuperacion = ?, token_expira = ? WHERE correo = ?";
$stmt_token = mysqli_prepare($conexion, $sql_token);
mysqli_stmt_bind_param($stmt_token, "sss", $token, $expira, $correo);
if (!mysqli_stmt_execute($stmt_token)) {
    echo json_encode(['success' => false, 'message' => 'Error al generar token de recuperación']);
    exit;
}

// Enviar correo usando credenciales desde variables de entorno
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER');
    $mail->Password = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = 'tls';
    $mail->Port = (int)getenv('SMTP_PORT');

    $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
    $mail->addAddress($correo);
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de contraseña - Créditos CR';
    
    $recovery_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/creditos_cr/php/restablecer_contrasena.php?token=' . urlencode($token);
    
    $mail->Body = "
    <h2>Recuperación de Contraseña</h2>
    <p>Recibimos una solicitud para restablecer tu contraseña.</p>
    <p><strong>Haz clic en el siguiente enlace para restablecer tu contraseña:</strong></p>
    <p><a href='$recovery_link' style='display: inline-block; padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
    <p>Este enlace expirará en 1 hora.</p>
    <p><strong>Si no solicitaste esta recuperación, ignora este correo.</strong></p>
    ";

    $mail->send();

    Seguridad::registrar_auditoria($conexion, $usuario['id'], 'RECUPERACION_INICIADA', 'usuarios', $usuario['id'], ['correo' => $correo]);
    
    echo json_encode(['success' => true, 'message' => 'Se ha enviado un enlace de recuperación a tu correo']);
} catch (Exception $e) {
    Seguridad::log_seguro("Error al enviar correo de recuperación: " . $mail->ErrorInfo, 'ERROR');
    echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo. Intenta más tarde']);
}

mysqli_close($conexion);
?>
