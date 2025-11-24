<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if (empty($correo) || empty($clave)) {
        header("Location: /home/?error=campos_vacios");
        exit();
    }

    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $correo);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        if (password_verify($clave, $usuario['clave'])) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según rol a /home/archivo.php
            if ($usuario['rol'] === 'admin') {
                header("Location: /home/admin.php");
            } else {
                header("Location: /home/empleado.php");
            }
            exit();
        } else {
            header("Location: /home/?error=credenciales_invalidas");
            exit();
        }
    } else {
        header("Location: /home/?error=usuario_no_encontrado");
        exit();
    }
}

mysqli_close($conexion);
?>