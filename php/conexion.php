<?php
// Configuración de base de datos usando variables de entorno
$servidor = getenv('DB_HOST') ?: 'bcoyhnvaiydt6al37nzh-mysql.services.clever-cloud.com';
$usuario = getenv('DB_USER') ?: 'unez0xrkrwy1djsp';
$password = getenv('DB_PASSWORD') ?: 'PmKpV5Jhiia00h4VMnav';
$basedatos = getenv('DB_NAME') ?: 'bcoyhnvaiydt6al37nzh';
$puerto = getenv('DB_PORT') ?: 3306;

// Mostrar información de depuración (SOLO para diagnóstico - eliminar después)
error_log("Intentando conectar a: $servidor:$puerto");
error_log("Usuario: $usuario");
error_log("Base de datos: $basedatos");

// Intentar conexión con puerto específico
$conexion = mysqli_connect($servidor, $usuario, $password, $basedatos, $puerto);

// Verificar conexión con mensaje visible
if (!$conexion) {
    // Mostrar error detallado
    $error = mysqli_connect_error();
    $errno = mysqli_connect_errno();
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error de Conexión</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
            h1 { color: #e53e3e; }
            .detail { background: #fee; padding: 15px; border-radius: 5px; margin: 15px 0; font-family: monospace; }
            .info { background: #e6f7ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>❌ Error de Conexión a la Base de Datos</h1>
            <p>No se pudo conectar a la base de datos de Clever Cloud.</p>
            
            <div class='detail'>
                <strong>Error:</strong> [{$errno}] {$error}
            </div>
            
            <div class='info'>
                <strong>Información de conexión:</strong><br>
                • Host: {$servidor}<br>
                • Puerto: {$puerto}<br>
                • Usuario: {$usuario}<br>
                • Base de datos: {$basedatos}
            </div>
            
            <h3>Posibles causas:</h3>
            <ul>
                <li>Clever Cloud no permite conexiones desde la IP de Render</li>
                <li>Credenciales incorrectas</li>
                <li>Firewall bloqueando el puerto 3306</li>
                <li>Base de datos no disponible</li>
            </ul>
            
            <h3>Soluciones:</h3>
            <ol>
                <li>Verifica en Clever Cloud que las conexiones remotas estén habilitadas</li>
                <li>Configura las variables de entorno en Render</li>
                <li>Considera usar la base de datos de Render en lugar de Clever Cloud</li>
            </ol>
        </div>
    </body>
    </html>";
    exit();
}

// Establecer charset UTF-8
if (!mysqli_set_charset($conexion, "utf8mb4")) {
    error_log("Error setting charset: " . mysqli_error($conexion));
}

// Log de conexión exitosa
error_log("✅ Conexión exitosa a la base de datos");
?>