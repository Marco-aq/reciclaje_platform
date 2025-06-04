<?php
// conexion.php
$conexion = new mysqli('localhost', 'root', '', 'reciclaje_platform');
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
