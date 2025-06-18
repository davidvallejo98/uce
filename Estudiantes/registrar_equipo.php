<?php
require_once '../conexion_login.php';

// Establecer zona horaria
date_default_timezone_set('America/Guayaquil');

// Leer datos enviados desde PowerShell
$equipo_id   = $_POST['equipo_id'] ?? '';
$procesador  = $_POST['procesador'] ?? '';
$ram_gb      = $_POST['ram'] ?? '';
$disco_gb    = $_POST['disco'] ?? '';
$sistema     = $_POST['sistema'] ?? '';

// Datos fijos o de formulario (puedes adaptarlos si vienen también por POST desde PowerShell o el frontend)
$teclado      = $_POST['teclado'] ?? 'Sí';
$mouse        = $_POST['mouse'] ?? 'Sí';
$observaciones = $_POST['observacion'] ?? '';
$laboratorio   = $_POST['laboratorio'] ?? 'Laboratorio 1';
$estado        = $_POST['estado'] ?? 'Operativo';
$nombre        = $_POST['nombre'] ?? 'Nombre';
$apellido      = $_POST['apellido'] ?? 'Apellido';
$correo        = $_POST['correo'] ?? 'correo@ejemplo.com';
$docente       = $_POST['docente'] ?? 'Docente';
$id_laboratorio = $_POST['id_laboratorio'] ?? 1;

$horaRegistro = date("Y-m-d H:i:s");

// Verificar campos esenciales
if (empty($equipo_id) || empty($procesador) || empty($ram_gb) || empty($disco_gb) || empty($sistema)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Datos incompletos',
        'received' => $_POST
    ]);
    exit();
}

$sql = "INSERT INTO computadoras (
    equipo_id, procesador, ram, disco_duro, sistema_operativo,
    docente_asignado, teclado, mouse, observacion,
    laboratorio_asignado, hora_registro, estado_equipo,
    nombre_estudiante, apellido_estudiante, correo_estudiante, id_laboratorio
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al preparar la consulta: ' . $conn->error
    ]);
    exit();
}

// ⚠️ Hay 16 parámetros, por eso usamos 16 tipos en bind_param (15 "s" y 1 "i" al final)
$stmt->bind_param(
    "ssssssssssssssssi",
    $equipo_id,
    $procesador,
    $ram_gb,
    $disco_gb,
    $sistema,
    $docente,
    $teclado,
    $mouse,
    $observaciones,
    $laboratorio,
    $horaRegistro,
    $estado,
    $nombre,
    $apellido,
    $correo,
    $id_laboratorio
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Datos insertados correctamente'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al insertar: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
