<?php
session_start();
include("conexion.php");

$correo = $_POST['correo'];
$clave = $_POST['clave'];

$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $correo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    if (password_verify($clave, $fila['clave'])) {
        $_SESSION['usuario'] = $fila['correo'];
        $_SESSION['rol'] = $fila['rol'];
        $_SESSION['nombre'] = $fila['nombre'];
        $_SESSION['usuario_id'] = $fila['id'];

        // Redirect based on role
        if ($fila['rol'] === 'admin') {
            header("Location: ../home/admin.php");
        } else {
            header("Location: ../home/empleado.php");
        }
        exit();
    } else {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Contraseña incorrecta',
                text: 'Verifica tus datos e intenta nuevamente.'
            }).then(() => {
                window.history.back();
            });
        </script>
        </body>
        </html>";
    }
} else {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Usuario no encontrado',
            text: '¿Estás registrado?'
        }).then(() => {
            window.history.back();
        });
    </script>
    </body>
    </html>";
}
