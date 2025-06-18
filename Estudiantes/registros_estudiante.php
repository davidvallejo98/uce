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

require_once '../conexion_login.php';
// Obtener el criterio de orden desde GET
$order = (isset($_GET['orden']) && $_GET['orden'] == 'desc') ? 'DESC' : 'ASC';

// Consulta con orden dinámico por fecha
//$sql = "SELECT * FROM computadoras INNER JOIN laboratorios ON laboratorios.id_laboratorio=computadoras.id_laboratorio ORDER BY hora_registro $order";
$userCorreo = $conn->real_escape_string($_SESSION['correo_institucional']);
$sql = "SELECT computadoras.*, laboratorios.nombre_laboratorio, CONCAT(usuarios.nombres, ' ', usuarios.apellidos) AS nombre_docente
FROM computadoras
INNER JOIN laboratorios ON laboratorios.id_laboratorio = computadoras.id_laboratorio
INNER JOIN usuarios ON usuarios.ci = computadoras.docente_asignado
WHERE correo_estudiante = '$userCorreo'
ORDER BY hora_registro $order;
";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registros del Estudiante</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>



<body>
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
  <div class="container mt-5">
    <h3 class="text-center mb-4">Registros Realizados</h3>

    <div class="mb-3 d-flex justify-content-end">
      <form method="GET">
        <select name="orden" class="form-select w-auto" onchange="this.form.submit()">
          <option value="asc" <?= ($order == 'ASC') ? 'selected' : '' ?>>Ordenar por Fecha (Ascendente)</option>
          <option value="desc" <?= ($order == 'DESC') ? 'selected' : '' ?>>Ordenar por Fecha (Descendente)</option>
        </select>
      </form>
    </div>

    <table class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Datos del estudiante</th>
          <th>Caracteristicas del equipo</th>
          <th>Laboratorio</th>
          <th>Serial del Equipo</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php $i = 1;
          while ($fila = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?=
                  "Nombre del Estudiante: " . htmlspecialchars($fila['nombre_estudiante'])
                    . "<br>" .
                    "Apellido del Estudiante: " . htmlspecialchars($fila['apellido_estudiante']) . "<br>" .
                    "Correo electronico: " . htmlspecialchars($fila['correo_estudiante']) . "<br>" .
                    "Fecha y Hora de Registro: " . htmlspecialchars($fila['hora_registro'])

                  ?></td>

              <td>
                <?= "RAM: " . htmlspecialchars($fila['ram']) . "<br>" .
                  "Procesador: " . htmlspecialchars($fila['procesador']) . "<br>" .
                  "Disco Duro: " . htmlspecialchars($fila['disco_duro'])
                  . "<br>" .
                  "Sistema Operativo: " . htmlspecialchars($fila['sistema_operativo'])
                  . "<br>" .
                  "Docente Asignado: " . htmlspecialchars( $fila['nombre_docente'])
                

                  . "<br>" .
                  "Teclado: " . htmlspecialchars($fila['teclado'])
                  . "<br>" .
                  "Mouse: " . htmlspecialchars($fila['mouse'])
                  . "<br>" .
                  "Observacion: " . htmlspecialchars($fila['observacion'])
                  . "<br>"
                ?>
              </td>


              <td><?= htmlspecialchars($fila['nombre_laboratorio']) ?></td>

              <td class="text-center"><?= $fila['equipo_id'] ?></td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">No hay registros.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
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