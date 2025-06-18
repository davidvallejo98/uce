<?php
session_start();
require_once '../verificar_sesion.php';
verificarPermiso(['Estudiante']);
if (!isset($_SESSION['correo_institucional']) && !$_SESSION(['tipo_usuario'])) {
  header("Location: index.html");
  exit();
}

require_once '../conexion_login.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {


  $stmt->execute();

  $result = $stmt->get_result(); // ← Luego obtienes el resultado

  if ($result->num_rows > 0) {
    session_start();
    $user = $result->fetch_assoc();
    $_SESSION['ci'] = $user['ci'];
    $_SESSION['nombres'] = $user['nombres'];
    $_SESSION['apellidos'] = $user['apellidos'];
    $_SESSION['correo_institucional'] = $user['correo_institucional'];
    $_SESSION['id_permisos'] = $user['id_permisos'];
    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
  }
}

$ci_usuario = isset($_SESSION['ci']) ? $_SESSION['ci'] : '';

$busqueda = isset($_GET['buscar']) ? $conn->real_escape_string($_GET['buscar']) : '';
$busqueda_like = "%$busqueda%";
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

$query = "SELECT reservas.*, 
                 recursos.equipo AS nombre_equipo,
                 recursos.disponibilidad AS disponibilidad_recurso,
                 CONCAT(usuarios.nombres, ' ', usuarios.apellidos) AS nombre_reservador, 
                 CONCAT(usuarios2.nombres, ' ', usuarios2.apellidos) AS nombre_docente 
          FROM reservas 
          INNER JOIN usuarios ON reservas.ci = usuarios.ci 
          INNER JOIN usuarios AS usuarios2 ON reservas.docente_asignado = usuarios2.ci 
          INNER JOIN recursos ON reservas.codigo = recursos.codigo 
          WHERE (reservas.ci = '$ci_usuario' OR reservas.docente_asignado = '$ci_usuario')";

if (!empty($busqueda)) {
  $query .= " AND (
        usuarios.ci LIKE '$busqueda_like' 
        OR usuarios2.ci LIKE '$busqueda_like' 
        OR CONCAT(usuarios2.nombres, ' ', usuarios2.apellidos) LIKE '$busqueda_like'
        OR recursos.equipo LIKE '$busqueda_like'
        OR reservas.curso LIKE '$busqueda_like'
    )";
}

if (!empty($fecha_inicio)) {
  $query .= " AND reservas.fecha_inicio >= '$fecha_inicio'";
}

if (!empty($fecha_fin)) {
  $query .= " AND reservas.fecha_fin <= '$fecha_fin'";
}


$result = $conn->query($query);

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['accion']) || isset($_POST['accion-editar']))) {

  $cedula = $_SESSION['ci'];
  $nombres = $_POST['nombres'];
  $codigo = $_POST['codigo'];
  $fecha_inicio = $_POST['fecha_inicio'];
  $hora_inicio = $_POST['hora_inicio'];
  $fecha_fin = $_POST['fecha_fin'];
  $hora_fin = $_POST['hora_fin'];
  $docente = $_POST['docente_asignado'];
  $curso = $_POST['curso'];

  // Restricción de horarios
  $hora_min = "07:00:00";
  $hora_max = "20:00:00";

  if ($hora_inicio < $hora_min || $hora_inicio > $hora_max || $hora_fin < $hora_min || $hora_fin > $hora_max) {
    echo "<script>
      alert('Las reservas solo pueden realizarse entre las 07:00 AM y las 20:00 PM horas.');
      window.location.href = 'reservas_docentes.php';
    </script>";
    exit();
  }
  // Antes de ejecutar la inserción o actualización, valida fechas y horas
  $fecha_inicio = $_POST['fecha_inicio'];
  $fecha_fin = $_POST['fecha_fin'];
  $hora_inicio = $_POST['hora_inicio'];
  $hora_fin = $_POST['hora_fin'];

  // Validar que la fecha fin no sea menor que la fecha inicio
  if ($fecha_fin < $fecha_inicio) {
    echo "<script>
        alert('La fecha de fin debe ser mayor o igual a la fecha de inicio.');
         window.location.href = 'reservas_docentes.php';
    </script>";
    exit();
  }

  // Si las fechas son iguales, validar que la hora fin sea mayor que la hora inicio
  if ($fecha_fin == $fecha_inicio && $hora_fin <= $hora_inicio) {
    echo "<script>
        alert('La hora de fin debe ser mayor a la hora de inicio cuando las fechas son iguales.');
       window.location.href = 'reservas_docentes.php';
    </script>";
    exit();
  }



  // =========================
  // CREAR RESERVA
  // =========================
  if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {

    $stmt = $conn->prepare("INSERT INTO reservas (ci, fecha_inicio, hora_inicio, fecha_fin, hora_fin, docente_asignado, curso, codigo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $cedula, $fecha_inicio, $hora_inicio, $fecha_fin, $hora_fin, $docente, $curso, $codigo);

    if ($stmt->execute()) {
      // Actualizar disponibilidad del equipo a ocupado (0)
      $updateStmt = $conn->prepare("UPDATE recursos SET disponibilidad = 0 WHERE codigo = ?");
      $updateStmt->bind_param("s", $codigo);
      $updateStmt->execute();
      $updateStmt->close();

      header("Location: reservas_docentes.php?mensaje=Reserva creada");
      exit;
    } else {
      echo "Error al crear reserva: " . $stmt->error;
    }
    $stmt->close();
  }

  // =========================
  // EDITAR RESERVA
  // =========================
  if (isset($_POST['accion-editar']) && $_POST['accion-editar'] == 'editar') {

    $id_reserva = $_POST['id_reserva'];

    // Obtener código anterior para liberar disponibilidad
    $stmt_old = $conn->prepare("SELECT codigo FROM reservas WHERE id_reserva = ?");
    $stmt_old->bind_param("i", $id_reserva);
    $stmt_old->execute();
    $stmt_old->bind_result($codigo_anterior);
    $stmt_old->fetch();
    $stmt_old->close();

    $stmt = $conn->prepare("UPDATE reservas 
                            SET codigo = ?, fecha_inicio = ?, hora_inicio = ?, fecha_fin = ?, hora_fin = ?, docente_asignado = ?, curso = ? 
                            WHERE id_reserva = ?");
    $stmt->bind_param("sssssssi", $codigo, $fecha_inicio, $hora_inicio, $fecha_fin, $hora_fin, $docente, $curso, $id_reserva);

    if ($stmt->execute()) {

      // Si el código cambió, liberar recurso anterior
      if ($codigo_anterior !== $codigo) {
        $stmtLiberar = $conn->prepare("UPDATE recursos SET disponibilidad = 1 WHERE codigo = ?");
        $stmtLiberar->bind_param("s", $codigo_anterior);
        $stmtLiberar->execute();
        $stmtLiberar->close();
      }

      // Marcar nuevo equipo como ocupado
      $stmtOcupar = $conn->prepare("UPDATE recursos SET disponibilidad = 0 WHERE codigo = ?");
      $stmtOcupar->bind_param("s", $codigo);
      $stmtOcupar->execute();
      $stmtOcupar->close();

      header("Location: reservas_docentes.php?mensaje=Reserva actualizada");
      exit;
    } else {
      echo "Error al actualizar reserva: " . $stmt->error;
    }

    $stmt->close();
  }
}

if (isset($_GET['id_reserva'])) {

  $id_reserva = $_GET['id_reserva'];

  // Obtener el código del recurso asociado a la reserva
  $stmt_codigo = $conn->prepare("SELECT codigo FROM reservas WHERE id_reserva = ?");
  $stmt_codigo->bind_param("i", $id_reserva);
  $stmt_codigo->execute();
  $stmt_codigo->bind_result($codigo_recurso);
  $stmt_codigo->fetch();
  $stmt_codigo->close();

  // Eliminar la reserva
  $stmt_delete = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
  $stmt_delete->bind_param("i", $id_reserva);

  if ($stmt_delete->execute()) {
    // Restaurar disponibilidad del recurso
    $stmt_update = $conn->prepare("UPDATE recursos SET disponibilidad = 1 WHERE codigo = ?");
    $stmt_update->bind_param("s", $codigo_recurso);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: reservas_docentes.php?mensaje=Reserva eliminada con éxito");
    exit;
  } else {
    echo "<script>alert('Error al eliminar la reserva.'); window.location.href='reservas_docentes.php';</script>";
  }

  $stmt_delete->close();
}





if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_reserva'])) {
  $id_reserva = intval($_POST['id_reserva']);

  // Paso 1: Obtener el código del recurso desde la reserva
  $stmt = $conn->prepare("SELECT codigo FROM reservas WHERE id_reserva = ?");
  $stmt->bind_param("i", $id_reserva);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $codigo = $row['codigo'];
    $stmt3 = $conn->prepare("UPDATE reservas SET entregado = 1 WHERE id_reserva = ?");
    $stmt3->bind_param("i", $id_reserva);
    $stmt3->execute();


    // Paso 2: Actualizar la disponibilidad en la tabla recursos
    $stmt2 = $conn->prepare("UPDATE recursos SET disponibilidad = 1 WHERE codigo = ?");
    $stmt2->bind_param("s", $codigo); // Usa "s" porque el código suele ser texto (como "PC001")

    if ($stmt2->execute()) {
      header("Location: reservas_docentes.php?mensaje=equipo_entregado");
      exit();
    } else {
      echo "Error al actualizar disponibilidad: " . $stmt2->error;
    }
  } else {
    echo "No se encontró la reserva.";
  }
}

$sql1 = "SELECT codigo, equipo FROM recursos WHERE disponibilidad = 1";
$result1 = $conn->query($sql1);


$ci = isset($_SESSION['ci']) ? $_SESSION['ci'] : '';

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Reservas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
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

  
<div class="container my-5">
  <h1 class="text-center mb-4">Gestión de Reservas</h1>
  <div id="reloj"></div>

  <!-- Formulario de búsqueda -->
  <form method="GET" class="mb-4">
    <div class="row">
      <div class="col-md-3">
        <input type="text" class="form-control" name="buscar" value="<?php echo $ci; ?>" readonly>
      </div>
      <div class="col-md-3">
        <input type="date" class="form-control" name="fecha_inicio" value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">
      </div>
      <div class="col-md-3">
        <input type="date" class="form-control" name="fecha_fin" value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">
      </div>
      <div class="col-md-3">
        <button class="btn btn-primary w-100" type="submit">Buscar</button>
      </div>
    </div>
  </form>


  <!-- Tabla de reservas -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">Reservas Actuales</div>
    <div class="card-body">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Cédula Reservante</th>
            <th>Nombres y Apellidos Reservante</th>
            <th>Equipo</th>
            <th>Fecha/Hora Inicio</th>
            <th>Fecha/Hora Fin</th>
            <th>Cédula Docente</th>
            <th>Nombres del docente solicitante</th>
            <th>Curso</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>

          <?php while ($row = $result->fetch_assoc()) {

            $codigo = $row['codigo'];

            // Consulta para obtener el nombre del equipo desde la tabla recursos
            $stmtEquipo = $conn->prepare("SELECT equipo FROM recursos WHERE codigo = ?");
            $stmtEquipo->bind_param("s", $codigo);
            $stmtEquipo->execute();
            $resultEquipo = $stmtEquipo->get_result();
            $nombreEquipo = "Desconocido"; // valor por defecto si no existe

            if ($resultEquipo && $resultEquipo->num_rows > 0) {
              $equipoRow = $resultEquipo->fetch_assoc();
              $nombreEquipo = $equipoRow['equipo'];
            }
          ?>
            <tr>
              <td><?= $row['id_reserva'] ?></td>
              <td><?= $row['ci'] ?></td>
              <td><?= $row['nombre_reservador'] ?></td>
              <td><?= htmlspecialchars($equipoRow['equipo']) ?></td>
              <td><?= $row['fecha_inicio'] . " " . $row['hora_inicio'] ?></td>
              <td><?= $row['fecha_fin'] . " " . $row['hora_fin'] ?></td>
              <td><?= $row['docente_asignado'] ?></td>
              <td><?= $row['nombre_docente'] ?></td>
              <td><?= $row['curso'] ?></td>
              <td class="
  <?php
            if ($row['entregado'] == '1') {
              echo 'text-success'; // Disponible
            } else {
              echo 'text-danger'; // Ocupado
            }
  ?>
">
                <?php
                if ($row['entregado'] == '1') {
                  echo 'Entregado';
                } else {
                  echo 'No Entregado';
                }
                ?>
              </td>

              <td>


                <!-- Botón de editar (desactivado por defecto) -->
                <button
                  class="btn btn-warning btn-sm editar-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#editarModal<?= $row['id_reserva'] ?>"
                  disabled>
                  Editar
                </button>
                <br>

                <!-- Botón de eliminar (desactivado por defecto) -->
                <button
                  class="btn btn-danger btn-sm eliminar-btn"
                  data-bs-toggle="modal"
                  data-bs-target="#eliminarModal<?= $row['id_reserva'] ?>"
                  disabled>
                  Eliminar
                </button>
                <br>
              </td>

              <!-- Modal de edición de reserva -->
              <div class="modal fade" id="editarModal<?= $row['id_reserva'] ?>" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editarModalLabel">Editar Reserva</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Formulario de edición -->
                      <form action="reservas_docentes.php" method="POST">
                        <input type="hidden" name="accion-editar" value="editar">
                        <input type="hidden" name="id_reserva" value="<?= $row['id_reserva'] ?>">

                        <div class="mb-3">
                          <label for="ci<?= $row['id_reserva'] ?>" class="form-label">Cédula</label>
                          <input type="text" id="ci<?= $row['id_reserva'] ?>" name="ci" class="form-control" value="<?= $row['ci'] ?>" required readonly>
                        </div>
                        <div class="mb-3">
                          <label for="nombres<?= $row['id_reserva'] ?>" class="form-label">Nombres y Apellidos</label>
                          <input type="text" id="nombres<?= $row['id_reserva'] ?>" name="nombres" class="form-control" value="<?= $row['nombre_reservador'] ?>" required readonly>
                        </div>

                        <!-- Equipo -->
                        <div class="mb-3">
                          <label for="codigo<?= $row['id_reserva'] ?>" class="form-label">Equipo</label>
                          <select class="form-select" id="codigo<?= $row['id_reserva'] ?>" name="codigo" required>
                            <option value="" disabled>Seleccione un equipo</option>
                            <?php
                            $sql1 = "SELECT codigo, equipo FROM recursos";
                            $equipos = $conn->query($sql1);
                            while ($equipo = $equipos->fetch_assoc()):
                              $selected = ($row['codigo'] == $equipo['codigo']) ? 'selected' : '';
                            ?>
                              <option value="<?= $equipo['codigo'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($equipo['equipo']) ?>
                              </option>
                            <?php endwhile; ?>
                          </select>
                        </div>

                        <!-- Fechas y horas -->
                        <div class="mb-3">
                          <label for="fecha_inicio<?= $row['id_reserva'] ?>" class="form-label">Fecha Inicio</label>
                          <input type="date" id="fecha_inicio<?= $row['id_reserva'] ?>" name="fecha_inicio" class="form-control" value="<?= $row['fecha_inicio'] ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="hora_inicio<?= $row['id_reserva'] ?>" class="form-label">Hora Inicio</label>
                          <input type="time" id="hora_inicio<?= $row['id_reserva'] ?>" name="hora_inicio" class="form-control" value="<?= $row['hora_inicio'] ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="fecha_fin<?= $row['id_reserva'] ?>" class="form-label">Fecha Fin</label>
                          <input type="date" id="fecha_fin<?= $row['id_reserva'] ?>" name="fecha_fin" class="form-control" value="<?= $row['fecha_fin'] ?>" required>
                        </div>
                        <div class="mb-3">
                          <label for="hora_fin<?= $row['id_reserva'] ?>" class="form-label">Hora Fin</label>
                          <input type="time" id="hora_fin<?= $row['id_reserva'] ?>" name="hora_fin" class="form-control" value="<?= $row['hora_fin'] ?>" required>
                        </div>

                        <!-- Docente -->
                        <?php
                        $sqlDocentes = "SELECT ci, nombres, apellidos FROM usuarios";
                        $docentes = $conn->query($sqlDocentes);
                        ?>
                        <div class="mb-3">
                          <label for="docente_asignado<?= $row['id_reserva'] ?>" class="form-label">Docente Asignado</label>
                          <select id="docente_asignado<?= $row['id_reserva'] ?>" name="docente_asignado" class="form-select" required>
                            <option value="">-- Seleccione un docente --</option>
                            <?php while ($docente = $docentes->fetch_assoc()) {
                              $nombreCompleto = $docente['nombres'] . ' ' . $docente['apellidos'];
                              $selected = ($row['docente_asignado'] == $docente['ci']) ? 'selected' : '';
                              echo "<option value=\"{$docente['ci']}\" $selected>$nombreCompleto</option>";
                            } ?>
                          </select>
                        </div>

                        <!-- Curso -->
                        <div class="mb-3">
                          <label for="curso<?= $row['id_reserva'] ?>" class="form-label">Curso</label>
                          <input type="text" id="curso<?= $row['id_reserva'] ?>" name="curso" class="form-control" value="<?= $row['curso'] ?>" required>
                        </div>
                        <!-- Curso -->
                        <div class="mb-3">
                          <label for="curso<?= $row['id_reserva'] ?>" class="form-label">Curso</label>
                          <input type="text" id="curso<?= $row['id_reserva'] ?>" name="curso" class="form-control" value="<?= $row['curso'] ?>" required>
                        </div>

                        <!-- Disponibilidad -->
                        <div class="mb-3">
                          <label for="disponibilidad<?= $row['id_reserva'] ?>" class="form-label">Disponibilidad</label>
                          <select class="form-select" id="disponibilidad<?= $row['id_reserva'] ?>" name="disponibilidad" required>
                            <option value="#">Selecccione...</option>
                            <option value="0" <?= $row['disponibilidad_recurso'] == '0' ? 'selected' : '' ?>>Ocupado</option>
                          </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal de eliminación -->
              <div class="modal fade" id="eliminarModal<?= $row['id_reserva'] ?>" tabindex="-1" aria-labelledby="eliminarModalLabel<?= $row['id_reserva'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="eliminarModalLabel<?= $row['id_reserva'] ?>">Confirmar Eliminación</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                      ¿Estás seguro de que deseas eliminar esta reserva?
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <a href="?id_reserva=<?= $row['id_reserva'] ?>" class="btn btn-danger">Eliminar</a>
                    </div>
                  </div>
                </div>
              </div>


            <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Boton para realizar una nueva reserva -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Realizar una Reserva</h5>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearModal">Crear Reserva</button>
    </div>
  </div>
</div>




<!-- Modal para crear reserva -->
<div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="crearModalLabel">Crear Reserva</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="formCrearReserva" action="reservas_docentes.php" method="POST">
          <input type="hidden" name="accion" value="crear">

          <div class="mb-3">
            <label for="ci" class="form-label">Cédula</label>
            <input type="text" class="form-control" id="ci" name="cedula"
              value="<?= htmlspecialchars($_SESSION['ci']) ?>" readonly>
          </div>

          <div class="mb-3">
            <label for="nombres" class="form-label">Nombres y Apellidos</label>
            <input type="text" class="form-control" id="nombres" name="nombres"
              value="<?= htmlspecialchars($_SESSION['nombres'] . " " . $_SESSION['apellidos']) ?>" readonly>
          </div>

          <div class="mb-3">
            <label for="codigo" class="form-label">Seleccionar Equipo</label>
            <select class="form-select" id="codigo" name="codigo" required>
              <option value="" disabled selected>Seleccione un equipo</option>
              <?php while ($row = $result1->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['codigo']) ?>">
                  <?= htmlspecialchars($row['equipo']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
          </div>

          <div class="mb-3">
            <label for="hora_inicio" class="form-label">Hora Inicio</label>
            <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" min="07:00" max="20:00" required>
          </div>

          <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
          </div>

          <div class="mb-3">
            <label for="hora_fin" class="form-label">Hora Fin</label>
            <input type="time" class="form-control" id="hora_fin" name="hora_fin" min="07:00" max="20:00" required>
          </div>

          <div class="mb-3">
            <label for="docente_asignado" class="form-label">Docente Asignado</label>
            <select class="form-select" id="docente_asignado" name="docente_asignado" required>
              <option value="">-- Selecciona un docente...</option>
              <?php
              $sql = "SELECT ci, nombres, apellidos FROM usuarios WHERE id_permisos='2'";
              $result = $conn->query($sql);
              while ($row = $result->fetch_assoc()):
              ?>
                <option value="<?= htmlspecialchars($row['ci']) ?>">
                  <?= htmlspecialchars($row['nombres']) . " " . htmlspecialchars($row['apellidos']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="curso" class="form-label">Curso</label>
            <input type="text" class="form-control" id="curso" name="curso" required>
          </div>

          <div class="mb-3">
            <label for="disponibilidad" class="form-label">Disponibilidad</label>
            <select class="form-select" id="disponibilidad" name="disponibilidad" required>
              <option value="">Seleccione...</option>
              <option value="0">Ocupado</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Reservar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Validaciones JavaScript -->
<script>
  // Validación horas dentro del rango permitido
  function validarHora(input) {
    const min = "07:00";
    const max = "20:00";
    if (input.value < min || input.value > max) {
      alert("La hora debe estar entre 07:00 y 20:00.");
      input.value = "";
    }
  }

  document.getElementById('hora_inicio').addEventListener('input', function() {
    validarHora(this);
  });

  document.getElementById('hora_fin').addEventListener('input', function() {
    validarHora(this);
  });

  // Validación antes de enviar el formulario
  document.getElementById('formCrearReserva').addEventListener('submit', function(event) {
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const horaInicio = document.getElementById('hora_inicio').value;
    const horaFin = document.getElementById('hora_fin').value;

    if (fechaFin < fechaInicio) {
      alert('La fecha de fin debe ser mayor o igual a la fecha de inicio.');
      event.preventDefault();
      return;
    }

    if (fechaFin === fechaInicio && horaFin <= horaInicio) {
      alert('La hora de fin debe ser mayor a la hora de inicio cuando las fechas son iguales.');
      event.preventDefault();
      return;
    }
  });
</script>


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



  function cambiarEstadoBotones(boton, habilitar) {
    const td = boton.closest("td");

    const editarBtn = td.querySelector(".editar-btn");
    const eliminarBtn = td.querySelector(".eliminar-btn");

    if (editarBtn) editarBtn.disabled = !habilitar;
    if (eliminarBtn) eliminarBtn.disabled = !habilitar;
  }
</script>

</body>

</html>