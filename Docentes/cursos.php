<?php
session_start();
require_once '../verificar_sesion.php';
require_once '../conexion_login.php';
verificarPermiso(['Docente']);




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_aula = $_POST['nombre_aula'];
    $ci = $_POST['ci'];
    $periodo = $_POST['periodo'];
    $fecha_creacion =  $_POST['fecha_creacion'];
    $estado = intval($_POST['estado']); // convertir a entero
    $contrasenia_curso = $_POST['contrasenia_curso'];

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "INSERT INTO aulas (nombre_aula, ci, periodo, fecha_creacion, estado, contrasenia_curso)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en preparación: " . $conn->error);
    }

    $stmt->bind_param("ssssis", $nombre_aula, $ci, $periodo, $fecha_creacion, $estado, $contrasenia_curso);

    if ($stmt->execute()) {
        $mensaje = "Curso registrado correctamente.";
    } else {
        $mensaje = "Error al registrar el curso: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Curso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
   
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="inicio_docente.php">
                <img src="\TESIS UCE\imagenes\Escudo.png" width="70" height="60" class="me-2">
                Centro de Cómputo
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="inicio_docente.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clases_docente_inicio.php">Mis Prácticas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios_docente.php  ">Gestión de Estudiantes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservas_docentes.php">Gestión de Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vista_cursos.php">Mis Cursos</a>
                    </li>
                </ul>
                <span>

                </span>
                <span class="navbar-text me-3 text-white">
                    Bienvenido, <?php echo htmlspecialchars($_SESSION['tipo_usuario'] . " " . $_SESSION['nombres'] . " " . $_SESSION['apellidos']); ?>
                </span>


                <!-- Botón que lanza el modal -->
                <button type="button" class="btn btn-danger" onclick="mostrarModal()">Cerrar Sesión</button>

                <!-- Modal de confirmación -->
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title" id="logoutModalLabel">¿Confirmar cierre de sesión?</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                ¿Estás seguro de que deseas cerrar sesión?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-danger" onclick="confirmLogout()">Sí, cerrar sesión</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal con spinner -->
                <div class="modal fade" id="spinnerModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content text-center p-4">
                            <div class="modal-body">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-3 fs-5">Saliendo del sistema...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario oculto para cerrar sesión -->
                <form id="logoutForm" action="logout.php" method="POST" style="display: none;"></form>



    </nav>
  <!-- Fin nav -->

    <!-- Modal para registrar un curso  -->

    <div class="container mt-5">
        <h2 class="mb-4">Registrar Curso</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-info"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="POST" class="shadow p-4 rounded bg-light">
            <div class="mb-3">
                <label for="nombre_aula" class="form-label">Nombre del Curso</label>
                <input type="text" class="form-control" id="nombre_aula" name="nombre_aula" required>
            </div>

            <div class="mb-3">
                <label for="nombres" class="form-label">Nombres</label>
                <input type="text" class="form-control" id="nombres" name="nombres"
                    value="<?= htmlspecialchars($_SESSION['nombres']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="apellidos" class="form-label">Apellidos </label>
                <input type="text" class="form-control" id="apellidos" name="apellidos"
                    value="<?= htmlspecialchars($_SESSION['apellidos']) ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="ci" class="form-label">Cédula de Identidad Docente</label>
                <input type="text" class="form-control" id="ci" name="ci"
                    value="<?= htmlspecialchars($_SESSION['ci']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="periodo" class="form-label">Periodo del Curso</label>
                <input type="text" class="form-control" id="periodo" name="periodo" required>
            </div>
            <div class="mb-3">
                <label for="fecha_creacion" class="form-label">Fecha de creación</label>
                <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" required>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="">Seleccione...</option>
                    <option value="0">Desactivado</option>
                    <option value="1">Activado</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="contrasenia_curso" class="form-label">Contraseña del curso</label>
                <input type="text" class="form-control" id="contrasenia_curso" name="contrasenia_curso" required>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Curso</button>
        </form>
    </div>



    <script>
        function mostrarModal() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        function confirmLogout() {
            // Oculta el modal de confirmación
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            confirmModal.hide();

            // Muestra el spinner
            const spinner = new bootstrap.Modal(document.getElementById('spinnerModal'));
            spinner.show();

            // Envía el formulario después de un breve retardo
            setTimeout(() => {
                document.getElementById("logoutForm").submit();
            }, 2000); // Espera 2 segundos antes de salir
        }

        function actualizarReloj() {
            const ahora = new Date();
            const opcionesFecha = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const opcionesHora = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const fecha = ahora.toLocaleDateString('es-ES', opcionesFecha);
            const hora = ahora.toLocaleTimeString('es-ES', opcionesHora);
            document.getElementById('reloj').innerHTML = `${fecha} - ${hora}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();
    </script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>