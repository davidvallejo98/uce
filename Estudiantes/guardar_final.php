<?php
session_start();
require_once '../conexion_login.php';

// Obtener datos del formulario y sesión
$docente_asignado     = $_POST['docente'];
$apellido_docente     = $_SESSION['apellidos'];
$correo_docente       = $_SESSION['correo_institucional'];
$id_usuario           = $_SESSION['ci'];
$serial               = $_POST['serial_equipo'];
$nombre_equipo        = $_POST['nombre_equipo'];
$sistema              = $_POST['sistema_operativo'];
$procesador           = $_POST['procesador'];
$ram                  = $_POST['memoria_ram_gb'];
$disco                = $_POST['disco_total_gb'];
$teclado              = $_POST['teclado'];
$mouse                = $_POST['mouse'];
$observacion          = $_POST['observacion'];
$laboratorio          = $_POST['laboratorio'];
$hora_registro        = $_POST['hora'];
$estado_equipo        = $_POST['estado'];
$nombre_estudiante    = $_POST['nombre'];
$apellido_estudiante  = $_POST['apellido'];
$correo_estudiante    = $_POST['correo'];

// Preparar SQL con 18 columnas
$sql = "INSERT INTO computadoras (
    equipo_id, nombre_equipo, procesador, ram, disco_duro,
    sistema_operativo, docente_asignado, teclado, mouse, estado,
    observacion, laboratorio_asignado, hora_registro, estado_equipo,
    nombre_estudiante, apellido_estudiante, correo_estudiante, id_laboratorio
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}

$stmt->bind_param(
    "ssssssssssssssssss",  // 18 letras s
    $serial,
    $nombre_equipo,
    $procesador,
    $ram,
    $disco,
    $sistema,
    $docente_asignado,
    $teclado,
    $mouse,
    $estado_equipo,
    $observacion,
    $laboratorio,
    $hora_registro,
    $estado_equipo,
    $nombre_estudiante,
    $apellido_estudiante,
    $correo_estudiante,
    $laboratorio // Este campo se supone que va como `id_laboratorio`, verifica si lo necesitas aquí.
);

// Ejecutar
if ($stmt->execute()) {
    echo "✅ Datos guardados exitosamente.";
    
} else {
    echo "❌ Error al guardar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
