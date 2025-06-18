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
require_once '../conexion_login.php';


$cedula = $_SESSION['ci'];

$sql = "SELECT a.nombre_aula 
        FROM aulas a
        INNER JOIN usuarios u ON a.ci=u.ci
        WHERE u.ci = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();
if (isset($_POST['registrar_practica'])) {
  $estudiantes = $_POST['numero_estudiantes'] ?? '';
  $semestre = $_POST['semestre'] ?? '';
  $paralelo = $_POST['paralelo'] ?? '';
  $numero_practica = $_POST['numero_practica'] ?? '';
  $laboratorio = $_POST['laboratorio_asignado'] ?? '';
  $fecha = $_POST['fecha_practica'] ?? '';
  $unidad = $_POST['unidad'] ?? '';
  $tema = $_POST['tema'] ?? '';
  $resultados = $_POST['resultados_aprendizaje'] ?? '';
  $objetivo = $_POST['objetivo_practica'] ?? '';
  $actividades = $_POST['actividades_practica'] ?? '';
  $materiales = $_POST['materiales_practica'] ?? '';
  $referencias = $_POST['referencias_practica'] ?? '';
  $cedula = $_SESSION['ci'];

  $sql = "INSERT INTO practicas (
    numero_estudiantes, semestre_practica, paralelo_practica, numero_practica,
    laboratorio_asignado, fecha_practica, unidad, tema, resultado_aprendizaje,
    objetivos_practica, actividades_practica, materiales_practica,
    referencias_practica, ci
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);

  if ($stmt) {
    $stmt->bind_param(
      "iisisissssssss",
      $estudiantes,
      $semestre,
      $paralelo,
      $numero_practica,
      $laboratorio,
      $fecha,
      $unidad,
      $tema,
      $resultados,
      $objetivo,
      $actividades,
      $materiales,
      $referencias,
      $cedula
    );

    if ($stmt->execute()) {
      $mensaje = "Práctica registrada correctamente.";
    } else {
      $mensaje = "Error al registrar: " . $stmt->error;
    }

    $stmt->close();
  } else {
    $mensaje = "Error al preparar la consulta.";
  }
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Prácticas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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



  <div class="container mt-5">
    <?php if (isset($mensaje)): ?>
      <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
    <?php endif; ?>



    <h2 class="mb-6">Registrar Nueva Práctica</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

      <h4 class="mb-4 text-primary">Registro de Práctica</h4>
      <div class="col-md-4">
        <label for="asignatura" class="form-label">Asignatura</label>
        <select name="asignatura" id="asignatura" class="form-select" required>
          <option value="">Seleccione una asignatura</option>
          <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($row['nombre_aula']); ?>">
              <?php echo htmlspecialchars($row['nombre_aula']); ?>
            </option>
          <?php endwhile; ?>
        </select>

      </div>

      <div class="col-md-4">
        <label for="docente" class="form-label">Docente</label>
        <input type="text" name="docente" id="docente" class="form-control"
          value="<?php echo isset($_SESSION['nombres']) && isset($_SESSION['apellidos']) ? $_SESSION['nombres'] . ' ' . $_SESSION['apellidos'] : ''; ?>"
          readonly>
      </div>


      <div class="col-md-4">
        <label for="estudiantes" class="form-label">Número de estudiantes</label>
        <input type="number" name="estudiantes" id="estudiantes" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label for="estudiantes" class="form-label">Número de estudiantes registrados</label>
        <input type="number" name="estudiantes" id="estudiantes" class="form-control" required readonly>
      </div>

      <div class="col-md-4">
        <label for="semestre" class="form-label">Semestre</label>
        <select class="form-select" id="semestre" name="semestre" required>
          <option value="" disabled selected>Seleccione una opción</option>
          <?php for ($i = 1; $i <= 9; $i++): ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
          <?php endfor; ?>
        </select>

      </div>
      <div class="col-md-4">
        <label for="paralelo" class="form-label">Paralelo</label>
        <select class="form-select" id="paralelo" name="paralelo" required>
          <option value="" disabled selected>Seleccione una opción</option>
          <?php foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $p): ?>
            <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label for="numero_practica" class="form-label">Número de Práctica</label>
        <input type="number" name="numero_practica" id="numero_practica" class="form-control" required>
      </div>
      <?php

      $query = "SELECT id_laboratorio, nombre_laboratorio FROM laboratorios";
      $result_labs = $conn->query($query);
      ?>

      <div class="col-md-4">
        <label for="laboratorio" class="form-label">Laboratorio</label>
        <select name="laboratorio" id="laboratorio" class="form-select" required>
          <option value="" disabled selected>Seleccione un laboratorio...</option>
          <?php while ($lab = $result_labs->fetch_assoc()): ?>
            <option value="<?php echo $lab['id_laboratorio']; ?>"><?php echo $lab['nombre_laboratorio']; ?></option>
          <?php endwhile; ?>
        </select>
      </div>


      <div class="col-md-4">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" name="fecha" id="fecha" class="form-control" required>
      </div>


      <div class="col-md-6">
        <label for="unidad" class="form-label">Unidad</label>
        <input type="number" name="unidad" id="unidad" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label for="tema" class="form-label">Tema</label>
        <input type="text" name="tema" id="tema" class="form-control" required>
      </div>

      <div class="col-12">
        <label for="resultados" class="form-label">Resultados de aprendizaje</label>
        <textarea name="resultados" id="resultados" class="form-control" rows="3" required></textarea>
      </div>

      <div class="col-12">
        <label for="objetivo" class="form-label">Objetivo de la Práctica</label>
        <textarea name="objetivo" id="objetivo" class="form-control" rows="3" required></textarea>
      </div>

      <div class="col-12">
        <label for="actividades" class="form-label">Actividades de la Práctica</label>
        <textarea name="actividades" id="actividades" class="form-control" rows="3" required></textarea>
      </div>

      <div class="col-md-12">
        <label for="materiales" class="form-label">Materiales</label>
        <textarea name="materiales" id="materiales" class="form-control" rows="3" required></textarea>
      </div>

      <div class="col-md-12">

        <label for="referencias" class="form-label">Referencias</label>

        <textarea name="referencias" id="referencias" class="form-control" rows="3" required></textarea>
      </div>

      <div class="mt-4 text-end">

        <button type="submit" name="registrar_practica" class="btn btn-primary">Registrar Práctica</button>



        </button>

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