<?php
// Configuración de base de datos usando variables de entorno
$servidor = getenv('DB_HOST') ?: 'bcoyhnvaiydt6al37nzh-mysql.services.clever-cloud.com';
$usuario = getenv('DB_USER') ?: 'unez0xrkrwy1djsp';
$password = getenv('DB_PASSWORD') ?: 'PmKpV5Jhiia00h4VMnav';
$basedatos = getenv('DB_NAME') ?: 'bcoyhnvaiydt6al37nzh';

// Intentar conexión
// Intentar conexión
$conexion = mysqli_connect($servidor, $usuario, $password, $basedatos);

// Verificar conexión
if (!$conexion) {
    // 🛑 CAMBIAR EL 'die' SILENCIOSO POR UN MENSAJE VISIBLE
    // Esto se ejecutará si Render no puede comunicarse con Clever Cloud
    echo "<h1>Error de Conexión a la Base de Datos.</h1>";
    echo "<p>Causa: " . mysqli_connect_error() . "</p>";
    echo "<p>Por favor, revisa tus credenciales de Clever Cloud y la configuración de acceso remoto.</p>";
    exit(); // Detenemos todo para ver este mensaje
}
// Verificar conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecer charset UTF-8
mysqli_set_charset($conexion, "utf8");
?>