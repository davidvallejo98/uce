<?php
session_start();

require_once '../verificar_sesion.php';
verificarPermiso(['Estudiante']);

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

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Estudiantes - Centro de Cómputo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Poppins', sans-serif;
      color: #333;
    }

    .navbar {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
      font-weight: 600;
    }

    .welcome-message {
      font-size: 1.2rem;
      color: #ffffff;
    }

    /* Ajuste para cartas más pequeñas */
    .card-custom {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      border: none;
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.3s ease-in-out;
      margin-bottom: 1rem;
    }

    .card-custom:hover {
      transform: translateY(-5px);
    }

    .card-body {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 20px;
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      font-size: 0.9rem;
      padding: 8px 16px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #0056b3;
      transform: scale(1.05);
    }

    .btn-custom {
      background-color: #28a745;
      border: none;
      font-size: 0.9rem;
      padding: 8px 16px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background-color: #218838;
      transform: scale(1.05);
    }

    .btn-info {
      background-color: #17a2b8;
      border: none;
      font-size: 0.9rem;
      padding: 8px 16px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn-info:hover {
      background-color: #138496;
      transform: scale(1.05);
    }

    .nav-link.active {
      font-weight: bold;
      color: #ffffff !important;
    }

    .card-title {
      font-weight: 600;
      font-size: 1.2rem;
    }

    .card-text {
      font-size: 0.9rem;
      color: #495057;
    }

    .card-custom img {
      max-width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 16px;
    }

    .container {
      margin-top: 60px;
    }

    h1 {
      font-weight: 600;
      font-size: 2rem;
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
      <a class="navbar-brand d-flex align-items-center" href="inicio_estudiantes.php">
        <img src="\TESIS UCE\imagenes\Escudo.png" width="70" height="60" class="me-2">
        Centro de Cómputo
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="reservas_estudiantes.php">Reservas </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="recursos.php">Estado Equipos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="modificar_estudiante.php  ">Mi Perfil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="formulario_recursos.php">Registro Equipo</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="registros_estudiante.php">Ver Mis Registros</a>
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


  <!-- Main Content -->
  <div class="container mt-5">
    <h1 class="text-center mb-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['tipo_usuario'] . " " . $_SESSION['nombres'] . " " . $_SESSION['apellidos']); ?></h1>
    <div id="reloj"></div>

    <div class="row">
      <!-- Reservas -->
      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/reserva.png" class="card-img-top" alt="Reservas">
          <div class="card-body text-center">
            <h5 class="card-title">Reservas</h5>
            <p class="card-text">Realiza reservas de equipos y laboratorios de manera sencilla.</p>
            <a href="reservas_estudiantes.php" class="btn btn-primary">
              <i class="fas fa-calendar-check"></i> Reservar Ahora
            </a>
          </div>
        </div>
      </div>
      <!-- Estado de Equipos -->
      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/estado.png" class="card-img-top" alt="Estado de Equipos">
          <div class="card-body text-center">
            <h5 class="card-title">Estado de Equipos</h5>
            <p class="card-text">Consulta el estado actual de los equipos disponibles.</p>
            <a href="recursos.php" class="btn btn-secondary">
              <i class="fa-solid fa-list"></i> Ver Estado

            </a>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/curriculum.png" class="card-img-top" alt="Soporte Técnico">
          <div class="card-body text-center">
            <h5 class="card-title">Mi Perfil</h5>
            <p class="card-text">Modifica tu perfil.</p>
            <a href="modificar_estudiante.php" class="btn btn-info">
              <i class="fa-solid fa-user"></i> Modificar mi perfil

            </a>
          </div>
        </div>
      </div>

      <!-- Registrar Equipo / Novedades -->
      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/novedad.png" class="card-img-top" alt="Registrar Equipo">
          <div class="card-body text-center">
            <h5 class="card-title">Registrar un equipo</h5>
            <p class="card-text">Registrar novedades en el equipo.</p>
            <a href="formulario_recursos.php" class="btn btn-success">
              <i class="fas fa-laptop"></i> Registrar
            </a>
          </div>
        </div>
      </div>

      <!-- Soporte Técnico -->

      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/registro.png" class="card-img-top" alt="Registrar Equipo">
          <div class="card-body text-center">
            <h5 class="card-title">Ver mis Registros</h5>
            <p class="card-text">Ver mis Registros de novedades de los equipos.</p>
            <a href="registros_estudiante.php" class="btn btn-warning">
              <i class="fa-solid fa-folder-open"></i> Registros
            </a>
          </div>
        </div>
      </div>
      <!-- Practicas Disponibles / Novedades -->
      <div class="col-md-4 mb-2">
        <div class="card card-custom">
          <img src="../imagenes/salon.png" class="card-img-top" alt="Registrar Equipo">
          <div class="card-body text-center">
            <h5 class="card-title">Practicas Disponibles</h5>
            <p class="card-text">Ver practicas disponibles en el sistema</p>
            <a href="practicas_estudiantes.php" class="btn btn-dark">
              <i class="fa-solid fa-book"></i> Aulas
              <i class=""></i>
            </a>
          </div>
        </div>
      </div>
    </div>


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
</body>

</html>