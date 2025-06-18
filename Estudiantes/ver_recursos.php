<?php
$datos = [];

if (file_exists("datos_temp.json")) {
    $contenido = file_get_contents("datos_temp.json");
    $datos = json_decode($contenido, true);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Recursos del Sistema</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 500px; margin: auto; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; margin-top: 4px; }
    </style>
</head>
<body>
    <h2>Recursos del Sistema</h2>
    <form method="post" action="guardar_final.php">
        
    <label>Serial del equipo:</label>
        <input type="text" name="serial_equipo" value="<?= htmlspecialchars($datos['serial_equipo'] ?? '') ?>" required>

    
    <label>Nombre del equipo:</label>
        <input type="text" name="nombre_equipo" value="<?= htmlspecialchars($datos['nombre_equipo'] ?? '') ?>" required>

        <label>Sistema operativo:</label>
        <input type="text" name="sistema_operativo" value="<?= htmlspecialchars($datos['sistema_operativo'] ?? '') ?>" required>

        <label>Procesador:</label>
        <input type="text" name="procesador" value="<?= htmlspecialchars($datos['procesador'] ?? '') ?>" required>

        <label>Memoria RAM total (GB):</label>
        <input type="number" step="0.01" name="memoria_ram_gb" value="<?= htmlspecialchars($datos['memoria_ram_gb'] ?? '') ?>" required>

        <label>Disco Total C: (GB):</label>
        <input type="number" step="0.01" name="disco_total_gb" value="<?= htmlspecialchars($datos['disco_total_gb'] ?? '') ?>" required>

        <br><br>
        <button type="submit">Guardar en Base de Datos</button>
    </form>
</body>
</html>
