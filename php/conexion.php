<?php
$conexion = mysqli_connect("localhost", "root", "", "creditos_cr");
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>