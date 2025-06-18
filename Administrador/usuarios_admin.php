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
// Conexión a la base de datos
require_once '../conexion_login.php';

$query = $conn->prepare("SELECT * FROM usuarios");
$query->execute();
$result = $query->get_result();
// Manejo de acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_POST['crear'])) {
    // Crear usuario
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombres'];
    $apellido = $_POST['apellidos'];
    $correo = $_POST['correo_institucional'];
    $contrasena = $_POST['contrasenia'];
    //$contrasena = password_hash($_POST['contrasenia'], PASSWORD_BCRYPT);
    $id_permisos = $_POST['id_permisos'];

    // Verificar si ya existe un usuario con la misma cédula o correo
    $verificar = $conn->prepare("SELECT * FROM usuarios WHERE ci = ? OR correo_institucional = ?");
    $verificar->bind_param("ss", $cedula, $correo);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
      // Ya existe un usuario con la misma cédula o correo
      echo "<script>alert('Error: Ya existe un usuario con esa cédula o correo institucional.'); window.history.back();</script>";
      exit();
    }

    // Insertar usuario si no existe duplicado
    $query = $conn->prepare("INSERT INTO usuarios (ci, nombres, apellidos, correo_institucional, contrasenia, id_permisos) VALUES (?, ?, ?, ?, ?, ?)");
    $query->bind_param("sssssi", $cedula, $nombre, $apellido, $correo, $contrasena, $id_permisos);
    $query->execute();

    header("Location: usuarios_admin.php");
    exit();
  }
}


// Editar usuario --------------------- VALIDAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Editar usuario
  if (isset($_POST['editar'])) {
    $id = $_POST['id']; // CAMPO CORREGIDO: era 'ci' antes
    $nombre = $_POST['nombres'];
    $apellido = $_POST['apellidos'];
    $correo = $_POST['correo_institucional'];
    $id_permisos = $_POST['id_permisos'];

    if (!is_numeric($id_permisos)) {
      die("Error: id_permisos no válido.");
    }

    $query = $conn->prepare("UPDATE usuarios SET nombres=?, apellidos=?, correo_institucional=?, id_permisos=? WHERE ci=?");
    $query->bind_param("ssssi", $nombre, $apellido, $correo, $id_permisos, $id);
    $query->execute();

    header("Location: usuarios_admin.php");
    exit();
  }
}
// Eliminar usuario
if (isset($_POST['eliminar'])) {
  $id = $_POST['id'];
  $query = $conn->prepare("DELETE FROM usuarios WHERE ci= ?");
  $query->bind_param("i", $id);
  $query->execute();
  header("Location: usuarios_admin.php");
  exit();
}


// Obtener usuarios
//$result = $conn->query("SELECT u.id_usuario, u.nombre, u.apellido, u.correo, p.tipo_usuario FROM usuarios u INNER JOIN permisos p on u.id_permisos=p.id_permisos");
$result = $conn->query("SELECT u.ci, u.nombres, u.apellidos, u.correo_institucional, p.tipo_usuario FROM usuarios u INNER JOIN permisos p on u.id_permisos=p.id_permisos ");


?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios Registrados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <style>
    .btn-custom {
      background-color: #28a745;
      color: white;
    }

    .btn-custom:hover {
      background-color: #218838;
    }

    .btn-danger-custom {
      background-color: #dc3545;
      color: white;
    }

    .btn-danger-custom:hover {
      background-color: #c82333;
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

      <h1>Usuarios Registrados</h1>

      <p class="lead">Listado de usuarios registrados en el sistema.</p>
      <div id="reloj"></div>
    </header>
    <!-- Botón Crear Usuario -->
    <button class="btn btn-custom mb-4" data-bs-toggle="modal" data-bs-target="#crearModal">
      <i class="fas fa-user-plus"></i> Crear Usuario
    </button>
    <main>
      <table id="usuariosTable" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>CI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['ci']; ?></td>
              <td><?php echo $row['nombres']; ?></td>
              <td><?php echo $row['apellidos']; ?></td>
              <td><?php echo $row['correo_institucional']; ?></td>
              <td><?php echo $row['tipo_usuario']; ?></td>
              <td>

                <!-- Botón para abrir modal de edición -->
                <button class="btn btn-warning btn-sm btnEditar"
                  data-bs-toggle="modal"
                  data-bs-target="#editarModal"
                  data-id="<?php echo $row['ci']; ?>"
                  data-ci="<?php echo $row['ci']; ?>"
                  data-nombre="<?php echo $row['nombres']; ?>"
                  data-apellido="<?php echo $row['apellidos']; ?>"
                  data-correo="<?php echo $row['correo_institucional']; ?>"
                  data-tipo_usuario="<?php echo $row['tipo_usuario']; ?>">

                  <i class="fas fa-edit"></i> Editar
                </button>

                <!-- Botón para abrir modal de eliminación -->
                <button class="btn btn-danger btn-sm btnEliminar"
                  data-bs-toggle="modal"
                  data-bs-target="#eliminarModal"
                  data-id="<?php echo $row['ci']; ?>">
                  <i class="fas fa-trash-alt"></i> Eliminar
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
  </div>

  <!-- Modal para Crear Usuario -->
  <div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="crearModalLabel">Crear Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <div class="mb-3">
              <label for="cedula" class="form-label">Cédula</label>
              <input type="text" class="form-control" id="cedula" name="cedula" required
                pattern="\d{10}" maxlength="10" minlength="10"
                title="La cédula debe tener exactamente 10 dígitos numéricos">
            </div>

            <div class="mb-3">
              <label for="nombres" class="form-label">Nombres</label>
              <input type="text" class="form-control" id="nombres" name="nombres" required>
            </div>

            <div class="mb-3">
              <label for="apellidos" class="form-label">Apellidos</label>
              <input type="text" class="form-control" id="apellidos" name="apellidos" required>
            </div>

            <div class="mb-3">
              <label for="correo_institucional" class="form-label">Correo</label>
              <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" required>
            </div>

            <div class="mb-3">
              <label for="id_permisos" class="form-label">Rol</label>
              <select class="form-select" id="id_permisos" name="id_permisos" required>
                <option value="">-- Selecciona un rol...</option>
                <option value="1">Administrador</option>
                <option value="2">Docente</option>
                <option value="3">Estudiante</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="contrasenia" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="contrasenia" name="contrasenia" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="crear" class="btn btn-primary">Crear Usuario</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <!-- Modal para Editar Usuario -->
  <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Editar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <!-- ID oculto -->
            <input type="hidden" id="edit_id" name="id">

            <!-- Cédula editable -->
            <label for="edit_ci">Cédula de identidad</label>
            <input type="text" id="edit_ci" name="ci" class="form-control mb-2" required>

            <label for="edit_nombre">Nombre</label>
            <input type="text" id="edit_nombre" name="nombres" class="form-control mb-2" required>

            <label for="edit_apellido">Apellido</label>
            <input type="text" id="edit_apellido" name="apellidos" class="form-control mb-2" required>

            <label for="edit_correo">Correo</label>
            <input type="email" id="edit_correo" name="correo_institucional" class="form-control mb-2" required>

            <label for="edit_id_permisos">Rol</label>
            <select id="edit_id_permisos" name="id_permisos" class="form-control mb-2" required>
              <option value="">-- Selecciona un rol...</option>
              <option value="1">Administrador</option>
              <option value="2">Docente</option>
              <option value="3">Estudiante</option>
            </select>
            
          </div>
          <div class="modal-footer">
            <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>





  <!-- Modal para Eliminar Usuario -->
  <div class="modal fade" id="eliminarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Eliminar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar este usuario?</p>
            <input type="hidden" id="delete_id" name="id">
          </div>
          <div class="modal-footer">
            <button type="submit" name="eliminar" class="btn btn-danger">Eliminar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $('.btnEditar').click(function() {
      
      $('#edit_id').val($(this).data('id'));
      var ci = $(this).data('ci');
      $('#edit_ci').val(ci);
      $('#edit_nombre').val($(this).data('nombre'));
      $('#edit_apellido').val($(this).data('apellido'));
      $('#edit_correo').val($(this).data('correo'));
      $('#edit_id_permisos').val($(this).data('rol'));
    });


    $('.btnEliminar').click(function() {
      $('#delete_id').val($(this).data('id'));
    });


    // Inicializar la tabla con DataTables
    $(document).ready(function() {
      $('#usuariosTable').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
      });
    });

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