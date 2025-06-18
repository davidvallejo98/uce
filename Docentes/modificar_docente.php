<?php
session_start();
include '../conexion_login.php';
require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);

if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
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
  exit();
}

$nombre = $_SESSION['nombres'];
$apellido = $_SESSION['apellidos'];
$correo = $_SESSION['correo_institucional'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = $_POST['nombres'];
  $apellido = $_POST['apellidos'];
  $correo = $_POST['correo_institucional'];
  $nueva_contrasenia = $_POST['nueva_contrasenia'] ?? '';
  $confirmar_contrasenia = $_POST['confirmar_contrasenia'] ?? '';

  if (!empty($nueva_contrasenia) && $nueva_contrasenia !== $confirmar_contrasenia) {
    header("Location: modificar_estudiante.php?mensaje=error");
    exit();
  }

if (!empty($nueva_contrasenia)) {
    // Guardar la contraseña tal cual (texto plano, no recomendado)
    $sql = "UPDATE usuarios SET nombres=?, apellidos=?, correo_institucional=?, contrasenia=? WHERE correo_institucional=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido, $correo, $nueva_contrasenia, $_SESSION['correo_institucional']);
} else {
    $sql = "UPDATE usuarios SET nombres=?, apellidos=?, correo_institucional=? WHERE correo_institucional=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $apellido, $correo, $_SESSION['correo_institucional']);
}


  if ($stmt->execute()) {
    $_SESSION['nombres'] = $nombre;
    $_SESSION['apellidos'] = $apellido;
    $_SESSION['correo_institucional'] = $correo;
    header("Location: modificar_estudiante.php?mensaje=exito");
  } else {
    header("Location: modificar_estudiante.php?mensaje=error");
  }

  $stmt->close();
  $conn->close();
  exit();
}
?>

<!-- HTML a continuación -->

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modificar Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Poppins', sans-serif;
    }
    .container {
      margin-top: 60px;
    }
    .card-custom {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      border-radius: 16px;
    }
    .btn-primary {
      border-radius: 30px;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
 
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

<div class="container">
  <h1 class="text-center mb-4">Modificar Perfil</h1>

  <?php if (isset($_GET['mensaje'])): ?>
    <?php if ($_GET['mensaje'] === 'error'): ?>
      <div class="alert alert-danger">Las contraseñas no coinciden. Intenta de nuevo.</div>
    <?php elseif ($_GET['mensaje'] === 'exito'): ?>
      <div class="alert alert-success">Perfil actualizado correctamente.</div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card card-custom">
        <div class="card-body">
          <form method="POST" action="modificar_estudiante.php">
            <div class="mb-3">
              <label for="nombres" class="form-label">Nombres</label>
              <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            <div class="mb-3">
              <label for="apellidos" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($apellido); ?>" required>
            </div>
            <div class="mb-3">
              <label for="correo_institucional" class="form-label">Correo Institucional</label>
              <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" value="<?php echo htmlspecialchars($correo); ?>" required>
            </div>
            <div class="mb-3">
              <label for="nueva_contrasenia" class="form-label">Nueva Contraseña</label>
              <input type="password" class="form-control" id="nueva_contrasenia" name="nueva_contrasenia">
            </div>
            <div class="mb-3">
              <label for="confirmar_contrasenia" class="form-label">Confirmar Contraseña</label>
              <input type="password" class="form-control" id="confirmar_contrasenia" name="confirmar_contrasenia">
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar Perfil</button>
          </form>
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

</script>



</body>
</html>
