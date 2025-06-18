<?php

session_start();

require_once '../verificar_sesion.php';
verificarPermiso(['Estudiante']);
date_default_timezone_set('America/Guayaquil');

require_once '../conexion_login.php';
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

function limpiar($texto)
{
  $lineas = explode("\n", trim($texto));
  return isset($lineas[1]) ? trim($lineas[1]) : trim($lineas[0]);
}



$horaRegistro = date("Y-m-d H:i:s");

$nombre = $_SESSION['nombres'];
$apellido = $_SESSION['apellidos'];
$correo = $_SESSION['correo_institucional'];
$id_usuario = $_SESSION['ci'];


if (isset($_POST['crear'])) {

  $teclado = $_POST['teclado'];
  $mouse = $_POST['mouse'];
  $observaciones = $_POST['observacion'];
  $laboratorio = $_POST['laboratorio'];
  $estado = $_POST['estado'];
  $nombre = $_POST['nombre'];
  $apellido = $_POST['apellido'];
  $correo = $_POST['correo'];
  $docente = $_POST['docente']; // ID del docente asignado
  $id_laboratorio = $_POST['laboratorio'];

  $sql = "INSERT INTO computadoras (
              equipo_id, procesador, ram, disco_duro, sistema_operativo,
              docente_asignado, teclado, mouse, observacion,
              laboratorio_asignado, hora_registro, estado_equipo,
              nombre_estudiante, apellido_estudiante, correo_estudiante, id_laboratorio
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);

  if (!$stmt) {
    // En caso de error en la preparación, redirigir con mensaje de error
    $error = urlencode($conn->error);
    header("Location: registro_equipos.php?error=$error");
    exit();
  }

  $stmt->bind_param(
    "sssssssssssssssi",
    $equipo_id,
    $procesador,
    $ram_gb,
    $disco_gb,
    $sistema,
    $docente,
    $teclado,
    $mouse,
    $observaciones,
    $laboratorio,
    $horaRegistro,
    $estado,
    $nombre,
    $apellido,
    $correo,
    $id_laboratorio
  );

  if ($stmt->execute()) {
    // Redirigir con mensaje de éxito
    header("Location: registro_equipos.php?success=1");
    exit();
  } else {
    // Redirigir con mensaje de error
    $error = urlencode($stmt->error);
    header("Location: registro_equipos.php?error=$error");
    exit();
  }
}


  // $stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registro de Equipo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>

<body class="bg-light">

  <!-- Navbar -->
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

  
  <!-- formulario -->
  <div class="container mt-5">
    <h2 class="text-center mb-4">Registro de Equipo</h2>
    <div id="reloj"></div>
    <form method="POST" class="border p-4 bg-white rounded">
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="equipo_id" class="form-label">Equipo ID (Mainboard)</label>
          <input type="text" class="form-control" name="equipo_id" readonly value="<?= $equipo_id ?>">
        </div>
        <div class="col-md-6">
          <label for="procesador" class="form-label">Procesador</label>
          <input type="text" class="form-control" name="procesador" readonly value="<?= $procesador ?>">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="ram" class="form-label">RAM</label>
          <input type="text" class="form-control" name="ram" readonly value="<?= $ram_gb ?>">
        </div>
        <div class="col-md-4">
          <label for="disco" class="form-label">Disco Duro</label>
          <input type="text" class="form-control" name="disco" readonly value="<?= $disco_gb ?>">
        </div>
        <div class="col-md-4">
          <label for="sistema" class="form-label">Sistema Operativo</label>
          <input type="text" class="form-control" name="sistema" readonly value="<?= $sistema ?>">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="docente" class="form-label">Docente Asignado</label>
          <select class="form-select" id="docente" name="docente" required>
            <option value="">-- Selecciona un docente...</option>
            <?php
            $sql = "SELECT ci, nombres, apellidos FROM usuarios WHERE id_permisos='2'";

            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()):
            ?>
              <option value="<?= $row['ci'] ?>"><?= htmlspecialchars($row['nombres']) . " - " . htmlspecialchars($row['apellidos']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>


        <div class="col-md-4">
          <label for="teclado" class="form-label">Teclado</label>
          <select class="form-select" id="teclado" name="teclado" required placeholder="Si/No">
            <option value="">-- Selecciona una opción...</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="mouse" class="form-label">Mouse</label>
          <select class="form-select" id="mouse" name="mouse" required placeholder="Si/No">
            <option value="">-- Selecciona una opción...</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label for="observacion" class="form-label" required>Observaciones</label>
        <textarea class="form-control" name="observacion" placeholder="Llene este campo en el caso de que exista alguna novedad, de lo contrario escribir SN " rows="3"></textarea>
      </div>

      <div class="row mb-3">

        <div class="col-md-4">

          <label for="laboratorio" class="form-label">Laboratorio Asignado</label>
          <select class="form-select" id="laboratorio" name="laboratorio" required>
            <option value="">-- Selecciona un laboratorio...</option>
            <?php
            $sql = "SELECT id_laboratorio, nombre_laboratorio FROM laboratorios";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()):
            ?>
              <option value="<?= $row['id_laboratorio'] ?>"><?= htmlspecialchars($row['nombre_laboratorio']) ?></option>
            <?php endwhile; ?>
          </select>


        </div>
        <div class="col-md-4">
          <label for="hora" class="form-label">Hora de Registro</label>
          <input type="text" class="form-control" name="hora" readonly value="<?= $horaRegistro ?>">
        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado" required>
              <option value="">-- Selecciona un estado...</option>
              <option value="2">Operativo</option>
              <option value="3">En mantenimiento</option>
              <option value="3">No operativo</option>
            </select>
          </div>

        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label for="nombre" class="form-label">Nombres </label>
          <input type="text" class="form-control" name="nombre" readonly value="<?= $nombre ?>">
        </div>
        <div class="col-md-4">
          <label for="apellido" class="form-label">Apellidos </label>
          <input type="text" class="form-control" name="apellido" readonly value="<?= $apellido ?>">
        </div>

        <div class="col-md-4">
          <label for="correo" class="form-label">Correo Electrónico</label>
          <input type="text" class="form-control" name="correo" readonly value="<?= $correo ?>">

        </div>
      </div>
      <?php if (isset($_GET['success'])): ?>
        <script>
          alert('Registro guardado correctamente.');
        </script>
      <?php elseif (isset($_GET['error'])): ?>
        <script>
          alert('Error al guardar el registro: <?= htmlspecialchars($_GET['error']) ?>');
        </script>
      <?php endif; ?>

      <div class="text-end">
        <button type="submit" class="btn btn-primary" name="crear">Guardar Registro</button>
      </div>
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
</body>

</html>