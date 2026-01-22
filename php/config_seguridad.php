<?php
/**
 * Configuración centralizada de seguridad
 * Carga variables de entorno desde .env SI EXISTE
 */

// Cargar archivo .env (SOLO PARA LOCAL)
function cargar_env($archivo = __DIR__ . '/../.env') {
    // 🔥 CORRECCIÓN: Verificamos si existe. Si NO existe, simplemente seguimos 
    // sin hacer nada (asumiendo que estamos en Producción/Render).
    if (file_exists($archivo)) {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            if (strpos($linea, '=') === false || strpos($linea, '#') === 0) {
                continue;
            }
            list($clave, $valor) = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);
            if (!empty($clave)) {
                putenv("$clave=$valor");
                $_ENV[$clave] = $valor; // Asegurar compatibilidad
            }
        }
    }
}

// Cargar variables de entorno
cargar_env();

// Constantes de Seguridad
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT') ?: 3600));
define('RATE_LIMIT_ATTEMPTS', (int)(getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5));
define('RATE_LIMIT_WINDOW', (int)(getenv('RATE_LIMIT_LOGIN_WINDOW') ?: 300));

// Configurar manejo de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // EN PRODUCCIÓN: Cero errores visibles al navegador para no romper el JSON
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Crear directorio de logs si no existe
if (!is_dir(__DIR__ . '/../logs')) {
    @mkdir(__DIR__ . '/../logs', 0755, true);
}

// Funciones de Utilidad de Seguridad
class Seguridad {
    
    /**
     * Escapar HTML para prevenir XSS
     */
    public static function escapar_html($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar número positivo y con decimales correctos
     */
    public static function validar_monto($monto, $minimo = 0.01, $maximo = 999999999.99) {
        $monto = floatval($monto);
        
        if ($monto < $minimo || $monto > $maximo) {
            return false;
        }
        
        // Validar máximo 2 decimales
        if (round($monto, 2) != $monto) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Redondear correctamente para operaciones financieras
     */
    public static function redondear_dinero($cantidad, $decimales = 2) {
        return round(floatval($cantidad), $decimales);
    }
    
    /**
     * Validar entrada de texto con límite de longitud
     */
    public static function validar_texto($texto, $minimo = 1, $maximo = 255, $patron = null) {
        $texto = trim($texto);
        
        if (strlen($texto) < $minimo || strlen($texto) > $maximo) {
            return false;
        }
        
        if ($patron && !preg_match($patron, $texto)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validar teléfono
     */
    public static function validar_telefono($telefono) {
        return preg_match('/^\+?[0-9\s\-()]{7,20}$/', trim($telefono));
    }
    
    /**
     * Validar email
     */
    public static function validar_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar cédula (formato básico)
     */
    public static function validar_cedula($cedula) {
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        return strlen($cedula) >= 5 && strlen($cedula) <= 15 && is_numeric($cedula);
    }
    
    /**
     * Generar token CSRF
     */
    public static function generar_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar token CSRF
     */
    public static function validar_csrf_token($token) {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Registrar en auditoría
     */
    public static function registrar_auditoria($conexion, $usuario_id, $accion, $tabla, $id_registro, $datos) {
        // Crear tabla si no existe
        $sql_crear = "CREATE TABLE IF NOT EXISTS auditoria (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            accion VARCHAR(100) NOT NULL,
            tabla VARCHAR(100),
            id_registro INT,
            datos_json JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario (usuario_id),
            INDEX idx_timestamp (timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        @mysqli_query($conexion, $sql_crear);
        
        // Registrar el evento
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        $datos_json = json_encode($datos, JSON_UNESCAPED_UNICODE);
        
        $sql = "INSERT INTO auditoria (usuario_id, accion, tabla, id_registro, datos_json, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexion, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ississs", $usuario_id, $accion, $tabla, $id_registro, $datos_json, $ip, $user_agent);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Verificar rate limiting (para login)
     */
    public static function verificar_rate_limit($conexion, $identificador, $tipo = 'login') {
        $sql = "CREATE TABLE IF NOT EXISTS rate_limit (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(50),
            identificador VARCHAR(255),
            intentos INT DEFAULT 1,
            primer_intento DATETIME DEFAULT CURRENT_TIMESTAMP,
            ultimo_intento DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            bloqueado_hasta DATETIME,
            INDEX idx_identificador (identificador, tipo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        @mysqli_query($conexion, $sql);
        
        $ahora = date('Y-m-d H:i:s');
        $ventana_tiempo = date('Y-m-d H:i:s', strtotime('-' . RATE_LIMIT_WINDOW . ' seconds'));
        
        // Verificar si está bloqueado
        $sql_check = "SELECT * FROM rate_limit WHERE tipo = ? AND identificador = ? AND bloqueado_hasta > ?";
        $stmt = mysqli_prepare($conexion, $sql_check);
        mysqli_stmt_bind_param($stmt, "sss", $tipo, $identificador, $ahora);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            return false; // Bloqueado
        }
        
        // Contar intentos recientes
        $sql_count = "SELECT SUM(intentos) as total FROM rate_limit WHERE tipo = ? AND identificador = ? AND primer_intento > ?";
        $stmt_count = mysqli_prepare($conexion, $sql_count);
        mysqli_stmt_bind_param($stmt_count, "sss", $tipo, $identificador, $ventana_tiempo);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        $total_intentos = $row_count['total'] ?? 0;
        
        if ($total_intentos >= RATE_LIMIT_ATTEMPTS) {
            // Bloquear por 15 minutos
            $bloqueado_hasta = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $sql_bloquear = "INSERT INTO rate_limit (tipo, identificador, intentos, bloqueado_hasta) 
                             VALUES (?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE bloqueado_hasta = ?";
            $stmt_bloquear = mysqli_prepare($conexion, $sql_bloquear);
            mysqli_stmt_bind_param($stmt_bloquear, "ssiis", $tipo, $identificador, $total_intentos, $bloqueado_hasta, $bloqueado_hasta);
            mysqli_stmt_execute($stmt_bloquear);
            return false;
        }
        
        // Registrar intento
        $sql_insert = "INSERT INTO rate_limit (tipo, identificador, intentos) VALUES (?, ?, 1) 
                       ON DUPLICATE KEY UPDATE intentos = intentos + 1";
        $stmt_insert = mysqli_prepare($conexion, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ss", $tipo, $identificador);
        mysqli_stmt_execute($stmt_insert);
        
        return true; // Permitido
    }
    
    /**
     * Registrar en log sin exponer credenciales
     */
    public static function log_seguro($mensaje, $nivel = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$nivel] $mensaje\n";
        error_log($log_message);
    }
}
?>