<?php
// Agregar esta función en un archivo común o en registrar_prestamo.php

function calcularValorBoleta($monto_prestamo) {
    // Tabla de valores según el monto
    $tabla_boletas = [
        100000 => 10000,
        200000 => 12000,
        300000 => 20000,
        400000 => 25000,
        500000 => 30000,
        600000 => 35000,
        700000 => 35000,
        800000 => 40000,
        900000 => 40000,
        1000000 => 50000
    ];
    
    // Si el monto está exactamente en la tabla, retornar ese valor
    if (isset($tabla_boletas[$monto_prestamo])) {
        return $tabla_boletas[$monto_prestamo];
    }
    
    // Si el monto es mayor a 1,000,000, calcular proporcionalmente (5%)
    if ($monto_prestamo > 1000000) {
        return round($monto_prestamo * 0.05);
    }
    
    // Para montos intermedios, interpolar linealmente entre dos valores conocidos
    $montos_ordenados = array_keys($tabla_boletas);
    sort($montos_ordenados);
    
    for ($i = 0; $i < count($montos_ordenados) - 1; $i++) {
        $monto_inferior = $montos_ordenados[$i];
        $monto_superior = $montos_ordenados[$i + 1];
        
        if ($monto_prestamo > $monto_inferior && $monto_prestamo < $monto_superior) {
            $valor_inferior = $tabla_boletas[$monto_inferior];
            $valor_superior = $tabla_boletas[$monto_superior];
            
            // Interpolación lineal
            $porcentaje = ($monto_prestamo - $monto_inferior) / ($monto_superior - $monto_inferior);
            $valor_boleta = $valor_inferior + (($valor_superior - $valor_inferior) * $porcentaje);
            
            return round($valor_boleta);
        }
    }
    
    // Si es menor al mínimo, calcular 10%
    return round($monto_prestamo * 0.10);
}
?>