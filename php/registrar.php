<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    include("conexion.php");

    $correo = $_POST['correo'];
    $clave = $_POST['clave'];
    $nombre = $_POST['nombre'];
    $rol = "empleado";

    // Validar si el correo ya existe
    $sql_check = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $correo);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        mysqli_stmt_close($stmt_check);
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Correo duplicado',
            text: 'Este correo ya está registrado.'
        }).then(() => {
            window.location.href = '/home/';
        });
    </script>";
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // Encriptar la contraseña
    $clave_segura = password_hash($clave, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (correo, clave, rol, nombre) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $correo, $clave_segura, $rol, $nombre);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: 'Ahora puedes iniciar sesión.'
        }).then(() => {
            window.location.href = '/home/';
        });
    </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error al registrar',
            text: 'Intenta nuevamente más tarde.'
        }).then(() => {
            window.location.href = '/home/';
        });
    </script>";
    }
    ?>
</body>

</html>