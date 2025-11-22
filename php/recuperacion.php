<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
    <title>Document</title>
</head>
<body>
    <?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include("conexion.php");

$correo = $_POST['correo'];

// Verificar si el correo existe
$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) === 0) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Correo no encontrado',
            text: 'Verifica tu dirección e intenta nuevamente.'
        }).then(() => {
            window.history.back();
        });
    </script>";
    exit;
}

// Generar token único
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Guardar token en la base de datos
$sql_token = "UPDATE usuarios SET token_recuperacion = ?, token_expira = ? WHERE correo = ?";
$stmt_token = mysqli_prepare($conexion, $sql_token);
mysqli_stmt_bind_param($stmt_token, "sss", $token, $expira, $correo);
mysqli_stmt_execute($stmt_token);

// Enviar correo
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jcogollonoriega@gmail.com';
    $mail->Password = 'igrx xhqs maow ggpu|';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('jcogollonoriega@gmail.com', 'Soporte Créditos');
    $mail->addAddress($correo);
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de contraseña';
    $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña:<br><br>
    <a href='http://localhost/creditos_cr/php/recuperacion.php?token=$token'>Restablecer contraseña</a><br><br>
    Este enlace expirará en 1 hora.";

    $mail->send();

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Correo enviado',
            text: 'Revisa tu bandeja de entrada para continuar.'
        }).then(() => {
            window.location.href = '../index.php';
        });
    </script>";
} catch (Exception $e) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error al enviar',
            text: 'No se pudo enviar el correo. Intenta más tarde.'
        }).then(() => {
            window.history.back();
        });
    </script>";
}
?>
</body>
</html>
