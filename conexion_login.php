<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "centro_computo";

try {
    $conn = new mysqli($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['correo_institucional'] ?? '';
        $password = $_POST['contrasenia'] ?? '';

        $sql = "SELECT u.*, p.tipo_usuario FROM usuarios u 
                    INNER JOIN permisos p ON u.id_permisos = p.id_permisos  
                    WHERE u.correo_institucional = ? AND u.contrasenia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            session_start();
            $user = $result->fetch_assoc();

            $_SESSION['ci'] = $user['ci'];
            $_SESSION['nombres'] = $user['nombres'];
            $_SESSION['apellidos'] = $user['apellidos'];
            $_SESSION['correo_institucional'] = $user['correo_institucional'];
            $_SESSION['id_permisos'] = $user['id_permisos'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

            if ($user['tipo_usuario'] === 'Estudiante') {
                header("Refresh: 3; URL=Estudiantes/inicio_estudiantes.php");
                echo renderRedirect("Redirigiendo al panel del Estudiante...");
            } elseif ($user['tipo_usuario'] === 'Docente') {
                header("Refresh: 3; URL=Docentes/inicio_docente.php");
                echo renderRedirect("Redirigiendo al panel del Docente...");
            } elseif ($user['tipo_usuario'] === 'Administrador') {
                header("Refresh: 3; URL=Administrador/inicio.php");
                echo renderRedirect("Redirigiendo al panel del Administrador...");
            }
            exit();
        } else {
            showErrorModal("Credenciales incorrectas. Verifica tu correo y contraseña.");
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error en la conexión a la base de datos: " . $e->getMessage());
    showErrorModal("No se pudo establecer conexión con la base de datos. Inténtelo más tarde.");
    exit();
}

function renderRedirect($message)
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Cargando...</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                background-color: #f8f9fa;
            }
        </style>
    </head>
    <body>

    
        <div class="text-center">
            <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 fs-5">$message</p>
        </div>
    </body>
    </html>
    HTML;
}

function showErrorModal($message)
{
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
    <div class="modal fade show" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="errorModalLabel">Error de autenticación</h5>
            </div>
            <div class="modal-body">
            $message
            </div>
            <div class="modal-footer">
            <a href="index.html" class="btn btn-danger">Volver al inicio</a>
            </div>
        </div>
        </div>
    </div>

    

    <script>
        setTimeout(function () {
        window.location.href = "index.html";
        }, 3500);

        
    </script>



    </body>
    </html>
    HTML;
}
