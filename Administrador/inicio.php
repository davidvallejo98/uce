<?php
session_start();

require_once '../verificar_sesion.php';
verificarPermiso(['Administrador']);

if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
    // Mostrar mensaje antes de redirigir
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="refresh" content="5;url=../index.html">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Sesión cerrada</title>
      <style>
        body {
          font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
          background-color: #f8f9fa;
          display: flex;
          justify-content: center;
          align-items: center;
          height: 100vh;
          margin: 0;
        }
        .alert {
          background-color: #fff;
          border: 1px solid #dee2e6;
          padding: 30px 40px;
          border-radius: 10px;
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
          text-align: center;
        }
        .alert h2 {
          color: #dc3545;
          margin-bottom: 15px;
        }
        .alert p {
          color: #343a40;
        }
        .countdown {
          margin-top: 20px;
          color: #6c757d;
          font-weight: bold;
        }
      </style>
    </head>
    <body>
      <div class="alert">
        <h2>¡Sesión cerrada!</h2>
        <p>Tu sesión ha finalizado por seguridad. Serás redirigido al inicio de sesión.</p>
        <div class="countdown">
          Redirigiendo en <span id="seconds">5</span> segundos...
        </div>
      </div>
      <script>
        let seconds = 5;
        const countdown = document.getElementById("seconds");
        const timer = setInterval(() => {
          seconds--;
          countdown.textContent = seconds;
          if (seconds === 0) {
            clearInterval(timer);
          }
        }, 1000);
      </script>
    </body>
    </html>';
    exit(); // Termina la ejecución después de mostrar el mensaje
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
    <style>
        .card-custom {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }

        .card-custom:hover {
            transform: scale(1.05);
        }

        .icon-container {
            font-size: 2rem;
            color: #007bff;
        }

        #reloj {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="inicio.php">
                <img src="\TESIS UCE\imagenes\Escudo.png" width="70" height="60" class="me-2">
                Centro de Cómputo
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="inicio.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservas.php">Reservas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="inventario.php">Laboratorios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios_admin.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">Reportes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recursos.php">Recursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="practicas_registradas_docente.php">Prácticas</a>
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

    <!-- Fin Nav -->

    <div class="container mt-4">
        <header class="text-center mb-4">
            <h1>Bienvenido al Sistema de Reservas</h1>
            <div id="reloj"></div>
            <p class="lead">Gestiona tus reservas, equipos y más desde aquí.</p>

        </header>
        <main>
            <div class="row g-4">
                <!-- Reservas -->
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h5 class="card-title">Reservas</h5>
                            <p class="card-text">Consulta y gestiona las reservas de equipos y laboratorios.</p>
                            <a href="reservas.php" class="btn btn-primary">Ver Reservas</a>
                        </div>
                    </div>
                </div>
                <!-- Inventario -->
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <h5 class="card-title">Laboratorios</h5>
                            <p class="card-text">Consulta el estado de los Laboratorios.</p>
                            <a href="inventario.php" class="btn btn-secondary">Ver Inventario</a>
                        </div>
                    </div>
                </div>
                <!-- Usuarios -->
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title">Usuarios</h5>
                            <p class="card-text">Visualiza y gestiona los usuarios registrados en el sistema.</p>
                            <a href="usuarios_admin.php" class="btn btn-info">Ver Usuarios</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <!-- Reportes -->
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fa-solid fa-file"></i>
                            </div>
                            <h5 class="card-title">Reportes</h5>
                            <p class="card-text">Generación de reportes.</p>
                            <a href="reportes.php" class="btn btn-warning">Ver Reportes</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <h5 class="card-title">Gestión de prácticas</h5>
                            <p class="card-text">Gestión de prácticas docentes.</p>
                            <a href="practicas_registradas_docente.php" class="btn btn-success">Ver Prácticas</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                                <i class="fa-solid fa-keyboard"></i>
                            </div>
                            <h5 class="card-title">Gestión de recursos</h5>
                            <p class="card-text">Gestión de recursos.</p>
                            <a href="recursos.php" class="btn btn-info">Ver Recursos</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom">
                        <div class="card-body text-center">
                            <div class="icon-container mb-3">
                            <i class="fa-solid fa-book-open-reader"></i>
                            </div>
                            <h5 class="card-title">Gestión de Horarios</h5>
                            <p class="card-text">Gestión de Horarios de los Laboratorios.</p>
                            <a href="horario_clases.php" class="btn btn-dark">Ver Horarios</a>
                        </div>
                    </div>
                </div>


        </main>
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