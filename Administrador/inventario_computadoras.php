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

// Parámetros de paginación
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Capturamos el ID del laboratorio desde la URL
$id_laboratorio = isset($_GET['id']) ? (int)$_GET['id'] : 1;

/*$query = "SELECT c.*, l.*
FROM computadoras c
INNER JOIN laboratorios l ON c.id_laboratorio = l.id_laboratorio
WHERE l.id_laboratorio = ?
LIMIT ? OFFSET ?";
*/

$query = "SELECT c.*, l.*
FROM computadoras c
INNER JOIN laboratorios l ON c.id_laboratorio = l.id_laboratorio
WHERE l.id_laboratorio = ?
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $id_laboratorio, $items_per_page, $offset);

$stmt->execute();
$result = $stmt->get_result();

// Total de registros
$total_query = "SELECT COUNT(*) as total
FROM computadoras
INNER JOIN laboratorios ON computadoras.id_laboratorio = laboratorios.id_laboratorio
WHERE laboratorios.id_laboratorio = ?";

$total_stmt = $conn->prepare($total_query);
$total_stmt->bind_param('i', $id_laboratorio);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Eliminar registro
if (isset($_POST['delete'])) {
  $id = $_POST['id'];
  $deleteQuery = "DELETE FROM computadoras WHERE equipo_id=?";
  $stmt = $conn->prepare($deleteQuery);
  $stmt->bind_param('i', $id);
  $stmt->execute();

  header("Location: inventario_computadoras.php");
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inventario de Computadoras</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="../imagenes/Escudo-Fil.png">
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

  <div class="container mt-4">
    <h1 class="text-center">Inventario de Computadoras</h1>

    <!-- Barra de búsqueda -->
    <div class="row mb-3">
      <div class="col-md-6 mx-auto">
        <form class="d-flex" method="get" action="inventario_computadoras.php">
          <input class="form-control me-2" type="search" placeholder="Buscar por ID" name="search" value="<?php echo $search; ?>" aria-label="Buscar">
          <button class="btn btn-primary" type="submit">Buscar</button>
        </form>
      </div>
    </div>

    <!-- Tabla de computadoras -->
    <?php if ($result->num_rows > 0): ?>
      <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Equipo ID: <?php echo $row['id_computadora']; ?></h5>
                <p><strong>Serial Computador: </strong> <?php echo $row['equipo_id']; ?></p>
                <p><strong>Procesador:</strong> <?php echo $row['procesador']; ?></p>
                <p><strong>RAM:</strong> <?php echo $row['ram']; ?> GB</p>
                <p><strong>Disco Duro:</strong> <?php echo $row['disco_duro']; ?> GB</p>
                <p><strong>Sistema Operativo:</strong> <?php echo $row['sistema_operativo']; ?></p>
                <p><strong>Teclado:</strong> <?php echo $row['teclado']; ?></p>
                <p><strong>Mouse:</strong> <?php echo $row['mouse']; ?></p>
                <p><strong>Observaciones:</strong> <?php echo $row['observacion']; ?></p>
                <p><strong>Laboratorio Asignado:</strong> <?php echo $row['nombre_laboratorio']; ?></p>
                <p><strong>Hora y Fecha de registro:</strong> <?php echo $row['hora_registro']; ?></p>
                <span class="badge <?php echo $row['estado'] === 'Operativo' ? 'bg-success' : ($row['estado'] === 'Mantenimiento' ? 'bg-warning' : 'bg-danger'); ?>">
                  <?php echo $row['estado']; ?>
                </span>

                <div class="mt-3">
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id_computadora']; ?>" disabled>Editar</button>
                  <form method="post" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $row['equipo_id']; ?>">
                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Eliminar</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal para editar registro -->
          <div class="modal fade" id="editModal<?php echo $row['id_computadora']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="post">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar Equipo ID: <?php echo $row['id_computadora']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_computadora" value="<?php echo $row['id_computadora']; ?>">
                    <div class="mb-3">
                      <label>Procesador</label>
                      <input type="text" class="form-control" name="procesador" value="<?php echo $row['procesador']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>RAM (GB)</label>
                      <input type="number" class="form-control" name="ram" value="<?php echo $row['ram']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Disco Duro (GB)</label>
                      <input type="number" class="form-control" name="disco_duro" value="<?php echo $row['disco_duro']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Sistema Operativo</label>
                      <input type="text" class="form-control" name="sistema_operativo" value="<?php echo $row['sistema_operativo']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Teclado</label>
                      <input type="text" class="form-control" name="teclado" value="<?php echo $row['teclado']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Mouse</label>
                      <input type="text" class="form-control" name="mouse" value="<?php echo $row['mouse']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Observaciones</label>
                      <textarea class="form-control" name="observacion"><?php echo $row['observacion']; ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label>Laboratorio Asignado</label>
                      <input type="text" class="form-control" name="id_laboratorio" value="<?php echo $row['id_laboratorio']; ?>">
                    </div>
                    <div class="mb-3">
                      <label>Estado</label>
                      <select class="form-control" name="estado">
                        <option value="Operativa" <?php echo $row['estado'] === 'Operativo' ? 'selected' : ''; ?>>Operativo</option>
                        <option value="Mantenimiento" <?php echo $row['estado'] === 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        <option value="Fuera de servicio" <?php echo $row['estado'] === 'Fuera de servicio' ? 'selected' : ''; ?>>Fuera de servicio</option>
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="update" class="btn btn-primary">Guardar cambios</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">No se encontraron registros.</div>
    <?php endif; ?>

    <!-- Paginación -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>