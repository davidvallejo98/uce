<?php
session_start();

require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);

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
    <title>Inicio - Docente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   

    <style>
        /* Estilos generales */
        body,
        html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        /* Navbar fija */
        .navbar {
            background: linear-gradient(90deg, #007bff, #0056b3);
        }

        .navbar-brand {
            font-weight: bold;
        }

        .navbar .nav-link {
            color: white !important;
        }

        /* Contenedor principal que ocupa toda la pantalla */
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Distribución en cuadrícula */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }

        /* Estilo de las tarjetas */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Footer fijo */
        footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 15px 0;
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
                        <a class="nav-link" href="modificar_docente.php  ">Mi perfil</a>
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


    <!-- Contenedor principal -->
    <div class="container main-container mt-5 pt-5">
        <h1 class="text-center text-primary">Bienvenido, <?php echo htmlspecialchars($_SESSION['tipo_usuario'] . " " . $_SESSION['nombres'] . " " . $_SESSION['apellidos']); ?></h1>
        <div id="reloj"></div>
        <p class="text-center text-muted">Desde aquí puede gestionar sus Prácticas, estudiantes y reservas.</p>

        <!-- Sección de Tarjetas Mejorada -->
        <div class="container mt-4">
            <div class="row g-4">

                <!-- Mis Prácticas -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                            <i class="fas fa-chalkboard-teacher fa-3x text-primary"></i>
                            <h5 class="card-title mt-3 fw-bold">Mis Prácticas</h5>
                            <p class="card-text text-muted">Consulte y gestione sus prácticas académicas.</p>
                            <a href="clases_docente_inicio.php" class="btn btn-outline-primary rounded-pill">Ir a Mis Clases</a>
                        </div>
                    </div>
                </div>

                <!-- Ver mis Cursos -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                            <i class="fas fa-book-open fa-3x text-secondary"></i>
                            <h5 class="card-title mt-3 fw-bold">Ver mis Cursos</h5>
                            <p class="card-text text-muted">Consulte y gestione sus cursos registrados.</p>
                            <a href="vista_cursos.php" class="btn btn-outline-secondary rounded-pill">Ver Cursos</a>
                        </div>
                    </div>
                </div>
                <!-- Gestión de Reservas -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                            <i class="fas fa-calendar-check fa-3x text-warning"></i>
                            <h5 class="card-title mt-3 fw-bold">Gestión de Reservas</h5>
                            <p class="card-text text-muted">Administre y visualice las reservas actuales.</p>
                            <a href="reservas_docentes.php" class="btn btn-warning text-white rounded-pill">Ver Reservas</a>
                        </div>
                    </div>
                </div>
                <!-- Gestión de Estudiantes -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                            <i class="fas fa-user-graduate fa-3x text-success"></i>
                            <h5 class="card-title mt-3 fw-bold">Modificar mi perfil</h5>
                            <p class="card-text text-muted">Administrar mi perfil.</p>
                            <a href="modificar_docente.php" class="btn btn-outline-success rounded-pill">Modificar Perfil</a>
                        </div>
                    </div>
                </div>

                <!-- Gestión de Estudiantes -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                         
                            <h5 class="card-title mt-3 fw-bold">Mi Horario de Clases</h5>
                            <p class="card-text text-muted">Administrar Mi Horario de Clases.</p>
                            <a href="horario_clases.php" class="btn btn-outline-success rounded-pill">Modificar Perfil</a>
                        </div>
                    </div>
                </div>

                 <!-- Gestión de Estudiantes -->
                <div class="col-md-6">
                    <div class="card text-center shadow rounded-4 h-100 border-0">
                        <div class="card-body">
                           
                            <h5 class="card-title mt-3 fw-bold">Recursos Disponibles</h5>
                            <p class="card-text text-muted">Ver Recursos Disponibles.</p>
                            <a href="recursos.php" class="btn btn-outline-success rounded-pill">Modificar Perfil</a>
                        </div>
                    </div>
                </div>


            </div>
        </div>




        <!-- Scripts -->
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