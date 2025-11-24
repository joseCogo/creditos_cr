<?php
// Configuración de base de datos usando variables de entorno
$servidor = getenv('DB_HOST') ?: 'bcoyhnvaiydt6al37nzh-mysql.services.clever-cloud.com';
$usuario = getenv('DB_USER') ?: 'unez0xrkrwy1djsp';
$password = getenv('DB_PASSWORD') ?: 'PmKpV5Jhiia00h4VMnav';
$basedatos = getenv('DB_NAME') ?: 'bcoyhnvaiydt6al37nzh';

// Intentar conexión
$conexion = mysqli_connect($servidor, $usuario, $password, $basedatos);

// Verificar conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer charset UTF-8
mysqli_set_charset($conexion, "utf8");
?>