<?php
// Recibe JSON crudo
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validar datos
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

// Guardar en archivo temporal
file_put_contents("datos_temp.json", json_encode($data));

// Respuesta
echo json_encode(['mensaje' => 'Datos guardados temporalmente']);
?>
