<?php
if (!isset($_SESSION['tipo_usuario'])) {
    session_start();
    // Si no hay sesión, redirige al inicio
    header("Location: ../index.html");
    exit();
}

// Define los permisos según el rol
function verificarPermiso($permitido = []) {
    if (!in_array($_SESSION['tipo_usuario'], $permitido)) {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Acceso Denegado</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <script>
                setTimeout(function() {
                    window.location.href = '../index.html';
                }, 1500);
            </script>
        </head>
        <body class='d-flex justify-content-center align-items-center vh-100 bg-light'>
            <div class='text-center'>
                <h2 class='text-danger'>Acceso Denegado</h2>
                <p class='text-muted'>No tienes permisos para acceder a esta página.</p>
                <p>Serás redirigido al inicio...</p>
                <a href='../index.html' class='btn btn-danger mt-3'>Volver al inicio</a>
            </div>
        </body>
        </html>";
        exit();
    }
}

?>
