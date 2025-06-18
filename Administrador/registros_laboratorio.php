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

require_once '../conexion_login.php';
$id_laboratorio = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$limite = 20;
$offset = ($pagina - 1) * $limite;

// Total de registros
$sql_total = $conn->prepare("SELECT COUNT(*) as total FROM computadoras INNER JOIN laboratorios ON computadoras.id_laboratorio= laboratorios.id_laboratorio WHERE computadoras.id_laboratorio = ?");
$sql_total->bind_param("i", $id_laboratorio);
$sql_total->execute();
$resultado_total = $sql_total->get_result()->fetch_assoc();
$total_registros = $resultado_total['total'];
$total_paginas = ceil($total_registros / $limite);

// Registros actuales
$sql = $conn->prepare("SELECT * FROM computadoras INNER JOIN laboratorios ON computadoras.id_laboratorio= laboratorios.id_laboratorio  WHERE computadoras.id_laboratorio = ? LIMIT ?, ?");
$sql->bind_param("iii", $id_laboratorio, $offset, $limite);
$sql->execute();
$resultado = $sql->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Registros del Laboratorio</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
  <div class="container mt-5">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Registros del Laboratorio</h2>
      <p class="text-secondary">Laboratorio: <strong><?= htmlspecialchars($id_laboratorio) ?></strong></p>
    </div>

    <?php if ($resultado->num_rows > 0): ?>
      <div class="card shadow">
        <div class="card-body">
          <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark text-center">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Detalle</th>
                <th>Datos del estudiante</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                  <td class="text-center"><?= $fila['equipo_id'] ?></td>
                  <td><?= htmlspecialchars($fila['nombre_laboratorio']) ?></td>
                  <td>
                    <?= "RAM: " . htmlspecialchars($fila['ram']) . "<br>" .
                      "Procesador: " . htmlspecialchars($fila['procesador']) . "<br>" .
                      "Disco Duro: " . htmlspecialchars($fila['disco_duro'])
                      . "<br>" .
                      "Sistema Operativo: " . htmlspecialchars($fila['sistema_operativo'])
                      . "<br>" .
                      "Docente Asignado: " . htmlspecialchars($fila['docente_asignado'])
                      . "<br>" .
                      "Teclado: " . htmlspecialchars($fila['teclado'])
                      . "<br>" .
                      "Mouse: " . htmlspecialchars($fila['mouse'])
                      . "<br>" .
                      "Observacion: " . htmlspecialchars($fila['observacion'])
                      . "<br>"
                    ?>
                  </td>

                  <td><?=
                      "Nombre del Estudiante: " . htmlspecialchars($fila['nombre_estudiante'])
                        . "<br>" .
                        "Apellido del Estudiante: " . htmlspecialchars($fila['apellido_estudiante']) . "<br>" .
                        "Correo electronico: " . htmlspecialchars($fila['correo_estudiante']) . "<br>" .
                        "Hora de Registro: " . htmlspecialchars($fila['hora_registro'])

                      ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

          <!-- Paginación -->
          <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center">
              <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                  <a class="page-link" href="?id=<?= $id_laboratorio ?>&pagina=<?= $i ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        No se encontraron registros para este laboratorio.
      </div>
    <?php endif; ?>
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