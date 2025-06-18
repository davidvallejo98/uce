<?php
require_once '../conexion_login.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT COUNT(*) AS total FROM computadoras WHERE id_laboratorio = $id";
    $resultado = $conn->query($sql);

    if ($resultado && $row = $resultado->fetch_assoc()) {
        echo "Total de computadoras registradas: <strong>" . $row['total'] . "</strong>";
    } else {
        echo "Error al obtener el total de computadoras.";
    }
}
?>
