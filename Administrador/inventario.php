<?php
session_start();
require_once '../verificar_sesion.php';
require_once 'total_computadoras.php';
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

// Verificar si se ha enviado un formulario para agregar un nuevo laboratorio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nuevo_laboratorio'])) {
  $nombre_laboratorio = $_POST['nombre_laboratorio'];
  $estado = $_POST['estado'];
  $total_computadoras = $_POST['total_computadoras'];
  $nombre_docente = $_POST['nombre_docente'];
  $num_mesas = $_POST['num_mesas'];
  $num_sillas_negras = $_POST['num_sillas_negras'];
  $num_sillas_azules = $_POST['num_sillas_azules'];
  $num_escritorio = $_POST['num_escritorio'];
  $num_pizarra = $_POST['num_pizarra'];
  // Consulta para insertar el nuevo laboratorio
  $sql = "INSERT INTO laboratorios (nombre_laboratorio, estado, total_computadoras, nombre_docente,num_mesas,num_sillas_negras,
  num_sillas_azules,num_escritorio,num_pizarra) 
            VALUES (?, ?, ?, ?, ?,?,?,?,?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssisiiiii",
    $nombre_laboratorio,
    $estado,
    $total_computadoras,
    $nombre_docente,
    $num_mesas,
    $num_sillas_negras,
    $num_sillas_azules,
    $num_escritorio,
    $num_pizarra
  );
  $stmt->execute();
  $stmt->close();

  header('Location: inventario.php');
  exit();
}

// Verificar si se está editando un laboratorio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_laboratorio'])) {
  $id = $_POST['id'];
  $nombre_laboratorio = $_POST['nombre_laboratorio'];
  $estado = $_POST['estado'];
  $total_computadoras = $_POST['total_computadoras'];
  $nombre_docente = $_POST['nombre_docente'];
  $num_mesas = $_POST['num_mesas'];
  $num_sillas_negras = $_POST['num_sillas_negras'];
  $num_sillas_azules = $_POST['num_sillas_azules'];
  $num_escritorio = $_POST['num_escritorio'];
  $num_pizarra = $_POST['num_pizarra'];


  // Consulta para actualizar el laboratorio
  $sql = "UPDATE laboratorios SET nombre_laboratorio=?, estado=?, total_computadoras=?, nombre_docente=?,num_mesas=?,num_sillas_negras=?,num_sillas_azules=?
   ,num_escritorio=?,num_pizarra=? WHERE id_laboratorio=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssisiiiiii",
    $nombre_laboratorio,
    $estado,
    $total_computadoras,
    $nombre_docente,
    $num_mesas,
    $num_sillas_negras,
    $num_sillas_azules,
    $num_escritorio,
    $num_pizarra,
    $id
  );
  $stmt->execute();
  $stmt->close();

  header('Location: inventario.php');
  exit();
}

// Verificar si se está eliminando un laboratorio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_laboratorio'])) {
  $id = $_POST['id'];

  // Consulta para eliminar el laboratorio
  $sql = "DELETE FROM laboratorios WHERE id_laboratorio=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();

  header('Location: inventario.php');
  exit();
}

// Consultar todos los laboratorios para mostrarlos en la tabla
$sql = "SELECT * FROM laboratorios";
$result = $conn->query($sql);


// mostar todos los registros asociados al laboratorio

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $sql = "SELECT COUNT(*) AS total FROM computadoras WHERE id_laboratorio = $id";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  echo "Total de computadoras registradas: " . $row['total'];
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consulta de Equipos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
  <div class="container my-5">
    <h1 class="text-center mb-4">Consulta y Edición del Estado de los Laboratorios</h1>
    <div id="reloj"></div>

    <!-- Botón para agregar más laboratorios -->
    <div class="mb-3 text-end">
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus"></i> Agregar Más Laboratorios
      </button>
    </div>



    <!-- Tabla de laboratorios -->
    <div class="card">
      <div class="card-header bg-primary text-white">
        Estado Actual de los Laboratorios
      </div>
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>

              <th>Laboratorio</th>
              <th>Estado</th>
              <th>Detalles</th>
              <th>Opciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $row['nombre_laboratorio']; ?></td>
                <td><span class="badge <?php echo ($row['estado'] == 'Operativo') ? 'bg-success' : (($row['estado'] == 'Dado de Baja') ? 'bg-danger' : 'bg-warning text-dark'); ?>"><?php echo $row['estado']; ?></span></td>
                <td>
                  Total de computadoras: <?php echo $row['total_computadoras']; ?><br>
                  Docente a cargo del laboratorio: <?php echo $row['nombre_docente']; ?><br>
                  Mesas: <?php echo $row['num_mesas']; ?><br>
                  Sillas Negras: <?php echo $row['num_sillas_negras']; ?><br>
                  Sillas Azules: <?php echo $row['num_sillas_azules']; ?><br>
                  Escritorio: <?php echo $row['num_escritorio']; ?><br>
                  Pizarra: <?php echo $row['num_pizarra']; ?><br>
                </td>
                <td>
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                    onclick="editLaboratory(<?php echo $row['id_laboratorio']; ?>, '<?php echo addslashes($row['nombre_laboratorio']); ?>', '<?php echo addslashes($row['estado']); ?>', <?php echo $row['total_computadoras']; ?>, '<?php echo addslashes($row['nombre_docente']); ?>',
                    '<?php echo addslashes($row['num_mesas']); ?>',
                    '<?php echo addslashes($row['num_sillas_negras']); ?>',
                    '<?php echo addslashes($row['num_sillas_azules']); ?>',
                    '<?php echo addslashes($row['num_escritorio']); ?>',
                    '<?php echo addslashes($row['num_pizarra']); ?>')">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                  <!-- Botón que abre el modal -->
                  <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $row['id_laboratorio'] ?>">
                    Eliminar
                  </button>
                  <a href="registros_laboratorio.php?id=<?= $row['id_laboratorio'] ?>"

                    class="btn btn-success btn-sm">
                    Registros
                  </a>

                  <!-- Modal de confirmación de eliminación -->
                  <div class="modal fade" id="eliminarModal<?= $row['id_laboratorio'] ?>" tabindex="-1" aria-labelledby="eliminarModalLabel<?= $row['id_laboratorio'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <!-- Formulario que se envía solo si se confirma la eliminación -->
                        <form method="POST">
                          <input type="hidden" name="eliminar_laboratorio" value="true">
                          <input type="hidden" name="id" value="<?= $row['id_laboratorio'] ?>">

                          <div class="modal-header">
                            <h5 class="modal-title" id="eliminarModalLabel<?= $row['id_laboratorio'] ?>">Confirmar Eliminación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                          </div>

                          <div class="modal-body">
                            ¿Estás seguro de que deseas eliminar este laboratorio?
                          </div>

                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>


                  <button type="button" class="btn btn-info btn-sm"
                    onclick="window.location.href='inventario_computadoras.php?id=<?= $row['id_laboratorio'] ?>'">
                    <i class="fas fa-laptop"></i> Inventario Laboratorio
                  </button>
                  <button type="button" class="btn btn-secondary btn-sm" onclick="mostrarTotal(<?= $row['id_laboratorio'] ?>)">
                    <i class="fas fa-laptop"></i> Total Registros
                  </button>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

 <!-- Mostar el total de los laboratorio -->

<div class="modal fade" id="modalTotal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Total de Computadoras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenidoModal">
        Cargando...
      </div>
    </div>
  </div>
</div>
  <!-- Modal para agregar laboratorio -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addModalLabel">Agregar Laboratorio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="nuevo_laboratorio" value="true">

            <div class="mb-3">
              <label for="nombreLaboratorio" class="form-label">Nombre del Laboratorio</label>
              <input type="text" class="form-control" name="nombre_laboratorio" id="nombreLaboratorio" required>
            </div>

            <div class="mb-3">
              <label for="estado" class="form-label">Estado</label>
              <select class="form-select" name="estado" id="estado" required>
                <option value="En Mantenimiento">En Mantenimiento</option>
                <option value="Operativo">Operativo</option>
                <option value="Dado de Baja">Dado de Baja</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="totalComputadoras" class="form-label">Total de Computadoras</label>
              <input type="number" class="form-control" name="total_computadoras" id="totalComputadoras" value="<?= $total ?>">
            </div>
            <div class="mb-3">
              <label for="totalRegistros" class="form-label">Total de Registros</label>
              <input type="number" class="form-control" name="total_registros" id="totalRegistros" required readonly>
            </div>
            <div class="mb-3">
              <label for="nombreDocente" class="form-label">Nombre del Docente</label>
              <input type="text" class="form-control" name="nombre_docente" id="nombreDocente" required>
            </div>

            <div class="mb-3">
              <label for="num_mesas" class="form-label">Número de mesas</label>
              <input type="number" class="form-control" name="num_mesas" id="num_mesas" required>
            </div>
            <div class="mb-3">
              <label for="num_sillas_negras" class="form-label">Número de sillas negras</label>
              <input type="number" class="form-control" name="num_sillas_negras" id="num_sillas_negras" required>
            </div>
            <div class="mb-3">
              <label for="num_sillas_azules" class="form-label">Número de sillas azules</label>
              <input type="number" class="form-control" name="num_sillas_azules" id="num_sillas_azules" required>
            </div>
            <div class="mb-3">
              <label for="num_escritorio" class="form-label">Número de escritorios</label>
              <input type="number" class="form-control" name="num_escritorio" id="num_escritorio" required>
            </div>
            <div class="mb-3">
              <label for="num_pizarra" class="form-label">Número de pizarras</label>
              <input type="number" class="form-control" name="num_pizarra" id="num_pizarra" required>
            </div>

            <button type="submit" class="btn btn-primary">Agregar Laboratorio</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para editar laboratorio -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Editar Laboratorio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST">
            <input type="hidden" name="editar_laboratorio" value="true">
            <input type="hidden" name="id" id="editId">

            <div class="mb-3">
              <label for="editNombreLaboratorio" class="form-label">Nombre del Laboratorio</label>
              <input type="text" class="form-control" name="nombre_laboratorio" id="editNombreLaboratorio" required>
            </div>

            <div class="mb-3">
              <label for="editEstado" class="form-label">Estado</label>
              <select class="form-select" name="estado" id="editEstado" required>
                <option value="En Mantenimiento">En Mantenimiento</option>
                <option value="Operativo">Operativo</option>
                <option value="Dado de Baja">Dado de Baja</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="editTotalComputadoras" class="form-label">Total de Computadoras</label>
              <input type="number" class="form-control" name="total_computadoras" id="editTotalComputadoras" required>
            </div>
<div class="mb-3">
  <label for="editTotalRegistros" class="form-label">Total de Registros</label>
  <input type="number" class="form-control" name="total_registros" id="editTotalRegistros" required readonly>
</div>
            
            <div class="mb-3">
              <label for="editNombreDocente" class="form-label">Nombre del Docente</label>
              <input type="text" class="form-control" name="nombre_docente" id="editNombreDocente" required>
            </div>
            <div class="mb-3">
              <label for="editNumMesas" class="form-label">Número de Mesas</label>
              <input type="number" class="form-control" name="num_mesas" id="editNumMesas" required>
            </div>

            <div class="mb-3">
              <label for="editNumSillasNegras" class="form-label">Número de Sillas Negras</label>
              <input type="number" class="form-control" name="num_sillas_negras" id="editNumSillasNegras" required>
            </div>
            <div class="mb-3">
              <label for="editNumSillasAzules" class="form-label">Número de Sillas Azules</label>
              <input type="number" class="form-control" name="num_sillas_azules" id="editNumSillasAzules" required>
            </div>
            <div class="mb-3">
              <label for="editNumEscritorio" class="form-label">Total de Escritorios</label>
              <input type="number" class="form-control" name="num_escritorio" id="editNumEscritorio" required>
            </div>
            <div class="mb-3">
              <label for="editNumPizarra" class="form-label">Total de Pizarras</label>
              <input type="number" class="form-control" name="num_pizarra" id="editNumPizarra" required>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </form>
        </div>
      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function editLaboratory(id, nombre, estado, totalComputadoras, totalRegistros, nombreDocente, numMesas, numSillasNegras, numSillasAzules, numEscritorio, numPizarra) {
      document.getElementById('editId').value = id;
      document.getElementById('editNombreLaboratorio').value = nombre;
      document.getElementById('editEstado').value = estado;
      document.getElementById('editTotalComputadoras').value = totalComputadoras;
      document.getElementById('editTotalRegistros').value = totalRegistros;
      //document.getElementById('editNumeroHoras').value = numeroHoras;
      document.getElementById('editNombreDocente').value = nombreDocente;
      document.getElementById('editNumMesas').value = numMesas;
      document.getElementById('editNumSillasNegras').value = numSillasNegras;
      document.getElementById('editNumSillasAzules').value = numSillasAzules;
      document.getElementById('editNumEscritorio').value = numEscritorio;
      document.getElementById('editNumPizarra').value = numPizarra;
    }

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


function mostrarTotal(idLaboratorio) {
  fetch('total_computadoras.php?id=' + idLaboratorio)
    .then(response => response.text())
    .then(data => {
      document.getElementById('contenidoModal').innerHTML = data;
      var modal = new bootstrap.Modal(document.getElementById('modalTotal'));
      modal.show();
    });
}
</script>





</body>

</html>