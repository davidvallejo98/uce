<?php
session_start();
require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);
require_once '../conexion_login.php';

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  $accion = $_POST['accion'];
  $id = $_POST['id'] ?? null;

  $dia = $_POST['dia_semana'] ?? '';
  $inicio = $_POST['hora_inicio'] ?? '';
  $fin = $_POST['hora_fin'] ?? '';
  $id_laboratorio = $_POST['id_laboratorio'] ?? null;
  $estado = $_POST['estado'] ?? '';
  $id_aula = $_POST['id_aula'] ?? '';
  if (in_array($accion, ['crear', 'editar'])) {
    $condicionTraslape = "SELECT COUNT(*) as total FROM reservas_laboratorios WHERE dia_semana = ? AND id_laboratorio = ? AND ((hora_inicio < ? AND hora_fin > ?) OR (hora_inicio < ? AND hora_fin > ?))";
    if ($accion === 'editar') {
      $condicionTraslape .= " AND id != ?";
    }
    $stmt = $conn->prepare($condicionTraslape);

    if ($accion === 'crear') {
      $stmt->bind_param("sissss", $dia, $id_laboratorio, $fin, $inicio, $inicio, $fin);
    } else {
      $stmt->bind_param("sissssi", $dia, $id_laboratorio, $fin, $inicio, $inicio, $fin, $id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data['total'] > 0) {
      $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'Error: Ya existe una reserva en ese horario.'
      ];
      header("Location: horario_clases.php?id_laboratorio=$id_laboratorio");
      exit();
    } else {
      if ($accion === 'editar') {
        $stmt = $conn->prepare("UPDATE reservas_laboratorios SET dia_semana=?, hora_inicio=?, hora_fin=?,estado=?, id_aula=? WHERE id=?");
        $stmt->bind_param("sssiii", $dia, $inicio, $fin, $estado, $id_aula, $id);


        $stmt->execute();
        $_SESSION['mensaje'] = [
          'tipo' => 'success',
          'texto' => 'Reserva actualizada.'
        ];
        header("Location: horario_clases.php?id_laboratorio=$id_laboratorio");
        exit();
      } elseif ($accion === 'crear') {
        $stmt = $conn->prepare("INSERT INTO reservas_laboratorios (id_laboratorio, dia_semana, hora_inicio, hora_fin, estado, id_aula) VALUES ( ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssii", $id_laboratorio, $dia, $inicio, $fin, $estado, $id_aula);
        $stmt->execute();
        $_SESSION['mensaje'] = [
          'tipo' => 'success',
          'texto' => 'Nueva reserva creada.'
        ];
        header("Location: horario_clases.php?id_laboratorio=$id_laboratorio");
        exit();
      }
    }
  } elseif ($accion === 'eliminar') {
    $stmt = $conn->prepare("DELETE FROM reservas_laboratorios WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['mensaje'] = [
      'tipo' => 'danger',
      'texto' => 'Reserva eliminada.'
    ];
    header("Location: horario_clases.php?id_laboratorio=$id_laboratorio");
    exit();
  }
}

$laboratorios = $conn->query("SELECT id_laboratorio, nombre_laboratorio FROM laboratorios");
$lab_id = $_GET['id_laboratorio'] ?? null;

$condiciones = "id_laboratorio = ?";
$params = [$lab_id];
$tipos = "i";

if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_fin = $_GET['fecha_fin'];
  $condiciones .= " AND DATE(hora_inicio) BETWEEN ? AND ?";
  $params[] = $fecha_inicio;
  $params[] = $fecha_fin;
  $tipos .= "ss";
} elseif (!empty($_GET['fecha_inicio'])) {
  $fecha_inicio = $_GET['fecha_inicio'];
  $condiciones .= " AND DATE(hora_inicio) >= ?";
  $params[] = $fecha_inicio;
  $tipos .= "s";
} elseif (!empty($_GET['fecha_fin'])) {
  $fecha_fin = $_GET['fecha_fin'];
  $condiciones .= " AND DATE(hora_inicio) <= ?";
  $params[] = $fecha_fin;
  $tipos .= "s";
}

$reservas = [];
if ($lab_id) {



  $sql = "
    SELECT r.*, 
          a.nombre_aula, 
          u.nombres, 
          u.apellidos
    FROM reservas_laboratorios r
    LEFT JOIN aulas a ON r.id_aula = a.id_aula
    LEFT JOIN usuarios u ON a.ci = u.ci
    WHERE $condiciones
  ";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($tipos, ...$params);
  $stmt->execute();
  $query = $stmt->get_result();
  while ($row = $query->fetch_assoc()) {
    $reservas[] = $row;
  }
}

$dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
$horas = [];
for ($h = 7; $h <= 20; $h++) {
  $horas[] = sprintf("%02d:00", $h);
}

$tabla = [];
foreach ($dias as $dia) {
  foreach ($horas as $hora) {
    $tabla[$dia][$hora] = [];
  }
}

foreach ($reservas as $reserva) {
  $inicio = substr($reserva['hora_inicio'], 0, 5);
  $fin = substr($reserva['hora_fin'], 0, 5);
  for ($h = strtotime($inicio); $h < strtotime($fin); $h += 3600) {
    $bloque = date("H:i", $h);
    $tabla[$reserva['dia_semana']][$bloque][] = $reserva;
  }
}

// Mostrar mensaje si existe
if (isset($_SESSION['mensaje'])) {
  $alerta = $_SESSION['mensaje'];
  echo '<div class="alert alert-' . $alerta['tipo'] . '">' . $alerta['texto'] . '</div>';
  unset($_SESSION['mensaje']);
}


// Obtener aulas/laboratorios desde la base de datos


$sql = "SELECT a.id_aula, a.nombre_aula, u.nombres, u.apellidos 
        FROM aulas a
        INNER JOIN usuarios u ON u.ci = a.ci";

$aulas_resultado = $conn->query($sql);

if (!$aulas_resultado) {
  die("Error al obtener aulas: " . $conn->error);
}



?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Horario de Laboratorio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script>
    function cargarDatos(reserva) {
      document.getElementById('editar_id').value = reserva.id;
      document.getElementById('editar_dia').value = reserva.dia_semana;
      document.getElementById('editar_hora_inicio').value = reserva.hora_inicio.substring(0, 5);
      document.getElementById('editar_hora_fin').value = reserva.hora_fin.substring(0, 5);
      document.getElementById('editar_estado').value = reserva.estado;
      document.getElementById('editar_id_aula').value = reserva.id_aula;

    }
  </script>
  <style>
    .table td,
    .table th {
      vertical-align: middle;
    }

    .badge {
      font-size: 0.85em;
      white-space: normal;
      word-wrap: break-word;
    }

    .btn-sm {
      padding: 0.2rem 0.4rem;
      font-size: 0.75rem;
    }

    .btn-icon {
      padding: 0.2rem 0.3rem;
      font-size: 0.9rem;
    }
  </style>
</head>

<body class="bg-light">
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
                        <a class="nav-link" href="modificar_docente.php  ">Mi perfil</a>
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
  
  <!-- Fin Nav -->

  <div class="container mt-4">
    <h3>Horario Semanal de Laboratorios</h3>

    <?= $mensaje ?>

    <form method="get" class="row mb-3">
      <div class="col-md-6">
        <select name="id_laboratorio" class="form-select" required onchange="this.form.submit()">
          <option value="">Seleccione un laboratorio</option>
          <?php foreach ($laboratorios as $lab): ?>
            <option value="<?= $lab['id_laboratorio'] ?>" <?= $lab_id == $lab['id_laboratorio'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($lab['nombre_laboratorio']) ?>
            </option>
          <?php endforeach; ?>
        </select>

      </div>
    </form>

    <!-- Tabla estilizada con tarjetas para cada reserva -->
    <div class="table-responsive mt-3">
      <!-- Botón para crear nueva reserva -->
      <div class="d-flex justify-content-between align-items-center mt-4">
        <h4 class="mb-0">Horario Semanal de Laboratorio</h4>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#crearModal">
          + Nueva Reserva
        </button>
      </div>

      <!-- Tabla estilizada -->
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle shadow-sm">
          <thead class="table-dark text-center">
            <tr>
              <th style="min-width: 120px;">Hora / Día</th>
              <?php foreach ($dias as $dia): ?>
                <th><?= $dia ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php foreach ($horas as $hora): ?>
              <tr>
                <td class="fw-bold bg-light"><?= $hora ?></td>
                <?php foreach ($dias as $dia): ?>
                  <td class="bg-white">
                    <?php if (!empty($tabla[$dia][$hora])): ?>
                      <?php foreach ($tabla[$dia][$hora] as $reserva): ?>
                        <?php
                        $esLibre = isset($reserva['estado']) && $reserva['estado'] == 1;
                        $badge = $esLibre ? '<span class="badge bg-success">Libre</span>' : '<span class="badge bg-danger">Ocupado</span>';
                        ?>
                        <div class="card mb-2 shadow-sm">
                          <div class="card-body p-2 text-start">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                <div><strong>📚 Materia:</strong> <?= htmlspecialchars($reserva['nombre_aula']) ?></div>
                                <div><strong>👩‍🏫 Docente:</strong> <?= htmlspecialchars($reserva['nombres'] . ' ' . $reserva['apellidos']) ?></div>
                                <div><strong>Estado:</strong> <?= $badge ?></div>
                              </div>

                              <div>
                                <button class="btn btn-sm btn-primary" onclick='cargarDatos(<?= json_encode($reserva) ?>)' data-bs-toggle="modal" data-bs-target="#editarModal">
                                  Editar
                                </button>
                                <form method="post" style="display:inline-block; margin-left: 5px;">
                                  <input type="hidden" name="accion" value="eliminar">
                                  <input type="hidden" name="id" value="<?= $reserva['id'] ?>">
                                  <!-- <button class="btn btn-sm btn-danger" type="submit">Eliminar</button> -->
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <script>
      // Mejora visual para tooltips (si deseas activar Bootstrap tooltips)
      document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl)
        })
      });
    </script>


    <!-- Modal para crear nueva reserva -->
    <div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="crearModalLabel">Nueva Reserva</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="accion" value="crear">
            <input type="hidden" name="id_laboratorio" value="<?= $lab_id ?>">

            <div class="mb-3">
              <label for="crear_dia" class="form-label">Día de la Semana</label>
              <select id="crear_dia" name="dia_semana" class="form-select" required>
                <option value="">Seleccione...</option>
                <option>Lunes</option>
                <option>Martes</option>
                <option>Miércoles</option>
                <option>Jueves</option>
                <option>Viernes</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="crear_hora_inicio" class="form-label">Hora Inicio</label>
              <input type="time" id="crear_hora_inicio" name="hora_inicio" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="crear_hora_fin" class="form-label">Hora Fin</label>
              <input type="time" id="crear_hora_fin" name="hora_fin" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="crear_estado" class="form-label">Seleccionar el estado</label>
              <select id="crear_estado" name="estado" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="0">Ocupado</option>
                <option value="1">Libre</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="aula" class="form-label">Seleccionar el Aula</label>
              <select id="aula" name="id_aula" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php while ($aula = $aulas_resultado->fetch_assoc()): ?>
                  <option value="<?= $aula['id_aula'] ?>">
                    <?= htmlspecialchars($aula['nombre_aula'] . " - " . $aula['nombres'] . " " . $aula['apellidos']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Crear Reserva</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal editar -->
    <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editarModalLabel">Editar Reserva</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" id="editar_id" name="id">

            <div class="mb-3">
              <label for="editar_dia" class="form-label">Día</label>
              <select id="editar_dia" name="dia_semana" class="form-select" required>
                <option>Lunes</option>
                <option>Martes</option>
                <option>Miércoles</option>
                <option>Jueves</option>
                <option>Viernes</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="editar_hora_inicio" class="form-label">Hora inicio</label>
              <input type="time" id="editar_hora_inicio" name="hora_inicio" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="editar_hora_fin" class="form-label">Hora fin</label>
              <input type="time" id="editar_hora_fin" name="hora_fin" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="editar_estado" class="form-label">Estado</label>
              <select id="editar_estado" name="estado" class="form-select" required>
                <option value="0">Ocupado</option>
                <option value="1">Libre</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="editar_id_aula" class="form-label">Aula</label>
              <select id="editar_id_aula" name="id_aula" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php
                // Asegúrate que $aulas_resultado se ejecute NUEVAMENTE o se guarde en array
                $resultado = $conn->query("SELECT id_aula, nombre_aula, nombres, apellidos FROM aulas INNER JOIN usuarios ON aulas.ci = usuarios.ci");

                while ($aula = $resultado->fetch_assoc()):
                ?>
                  <option value="<?= $aula['id_aula'] ?>">
                    <?= htmlspecialchars($aula['nombre_aula'] . " - " . $aula['nombres'] . " " . $aula['apellidos']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
    
        <!-- Scripts -->
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>