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
$datos = [];

if (file_exists("datos_temp.json")) {
    $contenido = file_get_contents("datos_temp.json");
    $datos = json_decode($contenido, true);
}


$horaRegistro = date("Y-m-d H:i:s");

$nombre = $_SESSION['nombres'];
$apellido = $_SESSION['apellidos'];
$correo = $_SESSION['correo_institucional'];
$id_usuario = $_SESSION['ci'];






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
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Formulario de Recursos del Sistema</h4>
        </div>
        <div class="card-body">
            <form method="post" action="guardar_final.php">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Serial del Equipo</label>
                        <input type="text" class="form-control" name="serial_equipo" value="<?= htmlspecialchars($datos['serial_equipo'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nombre del Equipo</label>
                        <input type="text" class="form-control" name="nombre_equipo" value="<?= htmlspecialchars($datos['nombre_equipo'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sistema Operativo</label>
                        <input type="text" class="form-control" name="sistema_operativo" value="<?= htmlspecialchars($datos['sistema_operativo'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Procesador</label>
                        <input type="text" class="form-control" name="procesador" value="<?= htmlspecialchars($datos['procesador'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Memoria RAM total (GB)</label>
                        <input type="number" step="0.01" class="form-control" name="memoria_ram_gb" value="<?= htmlspecialchars($datos['memoria_ram_gb'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Disco Total C: (GB)</label>
                        <input type="number" step="0.01" class="form-control" name="disco_total_gb" value="<?= htmlspecialchars($datos['disco_total_gb'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Docente Asignado</label>
                        <select class="form-select" name="docente" required>
                            <option value="">-- Selecciona un docente...</option>
                            <?php
                            // Asegúrate de tener la conexión a $conn
                            $sql = "SELECT ci, nombres, apellidos FROM usuarios WHERE id_permisos='2'";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()):
                            ?>
                                <option value="<?= $row['ci'] ?>"><?= htmlspecialchars($row['nombres']) . " - " . htmlspecialchars($row['apellidos']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teclado</label>
                        <select class="form-select" name="teclado" required>
                            <option value="">-- Selecciona una opción...</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mouse</label>
                        <select class="form-select" name="mouse" required>
                            <option value="">-- Selecciona una opción...</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observacion" rows="3" placeholder="SN si no hay observaciones."></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Laboratorio Asignado</label>
                        <select class="form-select" name="laboratorio" required>
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
                        <label class="form-label">Hora de Registro</label>
                        <input type="text" class="form-control" name="hora" value="<?= $horaRegistro ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado" required>
                            <option value="">-- Selecciona un estado...</option>
                            <option value="2">Operativo</option>
                            <option value="3">En mantenimiento</option>
                            <option value="4">No operativo</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombre" value="<?= $nombre ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellido" value="<?= $apellido ?>" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" value="<?= $correo ?>" readonly>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Guardar en Base de Datos</button>
                </div>
            </form>
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
        </script>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
