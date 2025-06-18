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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // 1. ELIMINAR
  if (isset($_POST['eliminar_id_aula'])) {
    $stmt = $conn->prepare("DELETE FROM aulas WHERE id_aula = ?");
    $stmt->bind_param("i", $_POST['eliminar_id_aula']);
    if ($stmt->execute()) {
      header("Location: vista_cursos.php");
      exit;
    } else {
      echo "Error al eliminar el curso: " . $stmt->error;
    }
    $stmt->close();
  }

  // 2. EDITAR
  elseif (isset($_POST['id_aula'], $_POST['nombre_aula'], $_POST['periodo'], $_POST['fecha_creacion'], $_POST['estado'])) {
    $stmt = $conn->prepare("UPDATE aulas SET nombre_aula = ?, periodo = ?, fecha_creacion = ?, estado = ? WHERE id_aula = ?");
    $stmt->bind_param("sssii", $_POST['nombre_aula'], $_POST['periodo'], $_POST['fecha_creacion'], $_POST['estado'], $_POST['id_aula']);
    if ($stmt->execute()) {
      header("Location: vista_cursos.php");
    } else {
      echo "Error al actualizar aula: " . $stmt->error;
    }
    $stmt->close();
  }

  // 3. INSERTAR
  elseif (isset($_POST['nombre_aula'], $_POST['periodo'], $_POST['fecha_creacion'], $_POST['estado'])) {
    $ci = $_SESSION['ci'];
    $nombre = trim($_POST['nombre_aula']);
    $periodo = trim($_POST['periodo']);
    $fecha = $_POST['fecha_creacion'];
    $estado = intval($_POST['estado']); // Asegurarse de que sea entero

    $stmt = $conn->prepare("INSERT INTO aulas (nombre_aula, periodo, fecha_creacion, estado, ci) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nombre, $periodo, $fecha, $estado, $ci);
    if ($stmt->execute()) {
      header("Location: vista_cursos.php");
    } else {
      echo "Error al registrar el curso: " . $stmt->error;
    }
    $stmt->close();
  }
}


// Consulta de cursos del usuario
$ci_usuario = $_SESSION['ci'];
$nombres = $_SESSION['nombres'];
$apellidos = $_SESSION['apellidos'];

$query = $conn->prepare("SELECT * FROM aulas INNER JOIN usuarios ON aulas.ci = usuarios.ci WHERE usuarios.ci = ? AND usuarios.nombres = ? AND usuarios.apellidos = ?");
$query->bind_param("sss", $ci_usuario, $nombres, $apellidos);
$query->execute();
$resultado = $query->get_result();


?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cursos Registrados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Sin esto no funcionan los modales -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>


</head>

<body class="bg-light">


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
            <a class="nav-link" href="modificar_docente.php">Mi Perfil</a>
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


  <!-- Mostrar los cursos  -->
  <div class="container py-5">
    <h2 class="mb-4">Mis Cursos Registrados</h2>

    <?php if ($resultado->num_rows >= 0): ?>
      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarCurso">
              <i class="fas fa-plus"></i> Agregar Curso
            </button>
          </div>
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre del Curso</th>
              <th>Docente a cargo</th>
              <th>Periodo</th>
              <th>Fecha de Registro</th>
              <th>Estado</th>
              <th>Contraseña</th>
              <th>Acciones</th>

            </tr>
          </thead>
          <tbody>

            <?php while ($curso = $resultado->fetch_assoc()): ?>
              <tr>
                <td><?php echo $curso['id_aula']; ?></td>
                <td><?php echo htmlspecialchars($curso['nombre_aula']); ?></td>
                <td><?php echo htmlspecialchars($curso['nombres'] . " " . $curso['apellidos']); ?></td>
                <td><?php echo htmlspecialchars($curso['periodo']); ?></td>
                <td><?php echo $curso['fecha_creacion']; ?></td>

                <td>
                  <?php
                  if ($curso['estado'] === '1' || $curso['estado'] === 1) {
                    echo "Activado";
                  } elseif ($curso['estado'] === '0' || $curso['estado'] === 0) {
                    echo "Desactivado";
                  } else {
                    echo "Desconocido";
                  }
                  ?>
                </td>
                <td><?php echo $curso['contrasenia_curso']; ?></td>
                <td>

                  <button class="btn btn-warning btn-sm btnEditar"
                    data-bs-toggle="modal"
                    data-bs-target="#editarModal"
                    data-id="<?php echo $curso['id_aula']; ?>"
                    data-nombre="<?php echo htmlspecialchars($curso['nombre_aula']); ?>"
                    data-periodo="<?php echo htmlspecialchars($curso['periodo']); ?>" ,
                    data-fecha_creacion="<?php echo htmlspecialchars($curso['fecha_creacion']); ?>">
                    <i class="fas fa-edit"></i> Editar
                    <button class="btn btn-danger btn-sm btnEliminar"
                      data-bs-toggle="modal"
                      data-bs-target="#eliminarModal"
                      data-id="<?php echo $curso['id_aula']; ?>"
                      data-nombre="<?php echo htmlspecialchars($curso['nombre_aula']); ?>">
                      <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>


        <!-- Modal para Agregar Aula -->
        <div class="modal fade" id="modalAgregarCurso" tabindex="-1" aria-labelledby="modalAgregarCursoLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAgregarCursoLabel">Registrar Nueva Aula</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">

                <div class="mb-3">
                  <label for="nombre_aula" class="form-label">Nombre del Aula</label>
                  <input type="text" class="form-control" id="nombre_aula" name="nombre_aula" required>
                </div>

                <div class="mb-3">
                  <label for="ci" class="form-label">Cédula de identidad</label>
                  <input type="text" id="ci" name="ci" class="form-control" value="<?php echo isset($_SESSION['ci']) ? htmlspecialchars($_SESSION['ci']) : ''; ?>" readonly>
                </div>

                <div class="mb-3">
                  <label for="periodo" class="form-label">Período</label>
                  <input type="text" class="form-control" id="periodo" name="periodo" required>
                </div>

                <div class="mb-3">
                  <label for="fecha_creacion" class="form-label">Fecha de Registro</label>
                  <input type="date" class="form-control" id="fecha_creacion" name="fecha_creacion" required>
                </div>

                <div class="mb-3">
                  <label for="estado" class="form-label">Estado</label>
                  <select class="form-select" id="estado" name="estado" id="modal-estado" required>
                    <option value="#">Seleccione...</option>
                    <option value="0">Desactivado</option>
                    <option value="1">Activado</option>
                  </select>
                </div>


                <div class="mb-3">
                  <label for="contrasenia_curso" class="form-label">Contraseña del Curso</label>
                  <input type="password" class="form-control" id="contrasenia_curso" name="contrasenia_curso" required>
                </div>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Guardar Aula</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </form>
          </div>
        </div>


        <!-- Modal para editar curso -->
        <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="vista_cursos.php">
              <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title" id="editarModalLabel">Editar Curso</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id_aula" id="modal-id-aula">
                  <div class="mb-3">
                    <label for="modal-nombre-aula" class="form-label">Nombre del Curso</label>
                    <input type="text" class="form-control" name="nombre_aula" id="modal-nombre-aula" required>
                  </div>
                  <div class="mb-3">
                    <label for="modal-periodo" class="form-label">Periodo</label>
                    <input type="text" class="form-control" name="periodo" id="modal-periodo" required>
                  </div>

                  <div class="mb-3">
                    <label for="modal-fecha_creacion" class="form-label">Fecha de registro</label>
                    <input type="date" class="form-control" name="fecha_creacion" id="modal-fecha_creacion" required>
                  </div>

                  <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado" id="modal-estado" required>
                      <option value="#">Seleccione...</option>
                      <option value="0">Desactivado</option>
                      <option value="1">Activado</option>
                    </select>
                  </div>



                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-success">Guardar Cambios</button>
                </div>
              </div>
            </form>
          </div>
        </div>


        <!-- Modal de Confirmación para Eliminar Curso -->
        <div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="vista_cursos.php">
              <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title" id="eliminarModalLabel">Eliminar Curso</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="eliminar_id_aula" id="eliminar-id-aula">
                  <p>¿Estás seguro de que deseas eliminar el curso <strong id="nombre-curso-eliminar"></strong>?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </div>
              </div>
            </form>
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

          const editarModal = document.getElementById('editarModal');
          editarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const periodo = button.getAttribute('data-periodo');
            const fecha_creacion = button.getAttribute('data-fecha_creacion');
            const estado = button.getAttribute('data-estado');

            document.getElementById('modal-id-aula').value = id;
            document.getElementById('modal-nombre-aula').value = nombre;
            document.getElementById('modal-periodo').value = periodo;
            document.getElementById('modal-fecha_creacion').value = fecha_creacion;
            document.getElementById('modal-estado').value = estado;
          });


          const eliminarModal = document.getElementById('eliminarModal');
          eliminarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            document.getElementById('eliminar-id-aula').value = id;
            document.getElementById('nombre-curso-eliminar').textContent = nombre;
          });
        </script>


      </div>
    <?php else: ?>
      <div class="alert alert-info">No tienes cursos registrados.</div>
    <?php endif; ?>
  </div>
</body>

</html>