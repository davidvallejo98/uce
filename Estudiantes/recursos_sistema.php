<?php
// Leer los datos enviados desde PowerShell
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validar que sean válidos
if (!$data) {
    http_response_code(400);
    echo json_encode(["estado" => "error", "mensaje" => "Datos JSON inválidos"]);
    exit;
}

// Guardar en archivo temporal
file_put_contents("datos_temp.json", json_encode($data, JSON_PRETTY_PRINT));

// Opcional: responder al cliente
echo json_encode(["estado" => "ok", "mensaje" => "Datos guardados correctamente"]);
?>
