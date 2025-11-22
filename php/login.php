<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title></title>
</head>
<body>
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

        // Redirigir con alerta
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: 'Inicio de sesión exitoso.',
                confirmButtonText: 'Ir al panel'
            }).then(() => {
                window.location.href = '../admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Contraseña incorrecta',
                text: 'Verifica tus datos e intenta nuevamente.'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        Swal.fire({
            icon: 'warning',
            title: 'Usuario no encontrado',
            text: '¿Estás registrado?'
        }).then(() => {
            window.history.back();
        });
    </script>";
}
?>
</body>
</html>
