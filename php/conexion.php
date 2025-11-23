<?php
$conexion = mysqli_connect("localhost:3307", "root", "", "creditos_cr");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>