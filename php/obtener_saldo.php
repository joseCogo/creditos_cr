<?php
header('Content-Type: application/json');
include("conexion.php");

// CORRECCIÓN: Forzamos a leer el ID 1. No usamos LIMIT 1.
$sql = "SELECT saldo_actual FROM caja WHERE id = 1";
$result = mysqli_query($conexion, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    // Devolvemos el saldo exacto de la base de datos
    echo json_encode([
        'success' => true,
        'saldo' => floatval($row['saldo_actual'])
    ]);
} else {
    // Si por alguna razón no existe el ID 1, devolvemos 0 (pero no creamos nada para no ensuciar)
    echo json_encode([
        'success' => true,
        'saldo' => 0
    ]);
}

mysqli_close($conexion);
?>