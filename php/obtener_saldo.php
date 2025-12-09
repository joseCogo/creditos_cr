<?php
header('Content-Type: application/json');
include("conexion.php");

// Obtener saldo actual de la caja
$sql = "SELECT saldo_actual FROM caja LIMIT 1";
$result = mysqli_query($conexion, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'saldo' => $row['saldo_actual']
    ]);
} else {
    // Si no existe, crear registro inicial
    mysqli_query($conexion, "INSERT INTO caja (saldo_actual) VALUES (0)");
    echo json_encode([
        'success' => true,
        'saldo' => 0
    ]);
}

mysqli_close($conexion);
?>