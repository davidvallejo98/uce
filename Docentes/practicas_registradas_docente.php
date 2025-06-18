<?php
session_start();
require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);
require_once '../conexion_login.php';

if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
  echo '<script>alert("Sesión expirada. Redirigiendo..."); window.location.href="../index.html";</script>';
  exit();
}


// Consulta para obtener prácticas registradas por el docente
$cedula = $_SESSION['ci'];

$sql = "SELECT p.id_practica, p.numero_estudiantes, p.paralelo_practica, p.semestre_practica, p.numero_practica, p.fecha_practica, p.unidad, p.tema, p.laboratorio_asignado,
p.resultado_aprendizaje, p.objetivos_practica, p.actividades_practica, p.materiales_practica, p.referencias_practica, u.nombres, u.apellidos
FROM practicas p 
INNER JOIN aulas a ON a.id_aula = p.id_aula
INNER JOIN usuarios u ON a.ci = u.ci
WHERE u.ci = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$resultado = $stmt->get_result();

// Obtener aulas del docente
$sql1 = "SELECT a.id_aula, a.nombre_aula, u.nombres, u.apellidos FROM aulas a INNER JOIN usuarios u ON a.ci = u.ci WHERE u.ci = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $cedula);
$stmt1->execute();
$result = $stmt1->get_result();
$aulas = [];
while ($row = $result->fetch_assoc()) {
  $aulas[] = $row;
}
// REGISTRAR NUEVA PRÁCTICA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
  $id_aula = $_POST['id_aula'];
  $laboratorio = $_POST['laboratorio_asignado'];
  $num_estudiantes = $_POST['numero_estudiantes'];
  $semestre = $_POST['semestre_practica'];
  $paralelo = $_POST['paralelo_practica'];
  $num_practica = $_POST['numero_practica'];
  $fecha = $_POST['fecha_practica'];
  $unidad = $_POST['unidad'];
  $tema = $_POST['tema'];
  $resultado = $_POST['resultado_aprendizaje'];
  $objetivos = $_POST['objetivos_practica'];
  $actividades = $_POST['actividades_practica'];
  $materiales = $_POST['materiales_practica'];
  $referencias = $_POST['referencias_practica'];

  $sql = $conn->prepare("INSERT INTO practicas (id_aula, laboratorio_asignado, numero_estudiantes, semestre_practica, paralelo_practica, numero_practica, fecha_practica, unidad, tema, resultado_aprendizaje, objetivos_practica, actividades_practica, materiales_practica, referencias_practica) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $sql->bind_param("iiississssssss", $id_aula, $laboratorio, $num_estudiantes, $semestre, $paralelo, $num_practica, $fecha, $unidad, $tema, $resultado, $objetivos, $actividades, $materiales, $referencias);
  $sql->execute();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

// EDITAR PRÁCTICA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
  $id = $_POST["id_practica"];
  $num_estudiantes = $_POST["numero_estudiantes"];
  $paralelo = $_POST["paralelo_practica"];
  $semestre = $_POST["semestre_practica"];
  $num_practica = $_POST["numero_practica"];
  $laboratorio = $_POST["laboratorio"];
  $fecha = $_POST["fecha_practica"];
  $unidad = $_POST["unidad"];
  $tema = $_POST["tema"];
  $resultado = $_POST["resultado_aprendizaje"];
  $objetivos = $_POST["objetivos"];
  $actividades = $_POST["actividades"];
  $materiales = $_POST["materiales"];
  $referencias = $_POST["referencias"];

  $update = $conn->prepare("UPDATE practicas SET 
    numero_estudiantes = ?, 
    paralelo_practica = ?, 
    semestre_practica = ?, 
    numero_practica = ?, 
    laboratorio_asignado = ?, 
    fecha_practica = ?, 
    unidad = ?, 
    tema = ?, 
    resultado_aprendizaje = ?, 
    objetivos_practica = ?, 
    actividades_practica = ?, 
    materiales_practica = ?, 
    referencias_practica = ?
    WHERE id_practica = ?");

  $update->bind_param(
    "isssissssssssi",
    $num_estudiantes,
    $paralelo,
    $semestre,
    $num_practica,
    $laboratorio,
    $fecha,
    $unidad,
    $tema,
    $resultado,
    $objetivos,
    $actividades,
    $materiales,
    $referencias,
    $id
  );

  $update->execute();
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

// ELIMINAR PRÁCTICA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_practica'])) {
  $id = $_POST['id_practica'];
  $conn->query("DELETE FROM practicas WHERE id_practica = $id");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}



?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Prácticas Registradas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
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
    <!-- Fin nav -->


  <!-- Modal para registrar nueva práctica -->
  <div class="modal fade" id="modalNuevaPractica" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="#" method="POST">
          <input type="hidden" name="accion" value="registrar">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Registrar Nueva Práctica</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">

            <div class="row mb-3">
              <label class="form-label">Aula asignada:</label>
              <select name="id_aula" class="form-select" required>
                <option value="">Seleccione un aula</option>
                <?php foreach ($aulas as $aula): ?>
                  <option value="<?= $aula['id_aula'] ?>"> <?= $aula['nombre_aula'] ?> </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="row mb-3">
              <label for="laboratorio_asignado" class="form-label">Laboratorio</label>
              <select name="laboratorio_asignado" id="laboratorio_asignado" class="form-select" required>
                <option value="" disabled selected>Seleccione un laboratorio...</option>
                <?php
                $query = "SELECT id_laboratorio, nombre_laboratorio FROM laboratorios";
                $result_labs = $conn->query($query);
                while ($lab = $result_labs->fetch_assoc()): ?>
                  <option value="<?= $lab['id_laboratorio'] ?>"><?= $lab['nombre_laboratorio'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label>Número de Estudiantes</label>
                <input type="number" class="form-control" name="numero_estudiantes" required>
              </div>
              <div class="col">
                <label>Semestre</label>
                <input type="text" class="form-control" name="semestre_practica" required>
              </div>
              <div class="col">
                <label>Paralelo</label>
                <input type="text" class="form-control" name="paralelo_practica" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label>Número de Práctica</label>
                <input type="number" class="form-control" name="numero_practica" required>
              </div>
              <div class="col">
                <label>Fecha</label>
                <input type="date" class="form-control" name="fecha_practica" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label>Unidad</label>
                <input type="text" class="form-control" name="unidad" required>
              </div>
              <div class="col">
                <label>Tema</label>
                <input type="text" class="form-control" name="tema" required>
              </div>
            </div>
            <div class="mb-3">
              <label>Resultado de Aprendizaje</label>
              <textarea class="form-control" name="resultado_aprendizaje" required></textarea>
            </div>
            <div class="mb-3">
              <label>Objetivos</label>
              <textarea class="form-control" name="objetivos_practica" required></textarea>
            </div>
            <div class="mb-3">
              <label>Actividades</label>
              <textarea class="form-control" name="actividades_practica" required></textarea>
            </div>
            <div class="mb-3">
              <label>Materiales</label>
              <textarea class="form-control" name="materiales_practica" required></textarea>
            </div>
            <div class="mb-3">
              <label>Referencias</label>
              <textarea class="form-control" name="referencias_practica" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Registrar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>


  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Prácticas Registradas</h2>

      <!-- Botón que abre el modal para nueva práctica -->
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaPractica">Registrar Nueva Práctica</button>
    </div>

    <div class="table-responsive">
      <table id="tablaPracticas" class="table table-striped table-bordered">
        <thead class="table-primary text-center">
          <tr>
            <th>Nombre Docente</th>
            <th>Número de Estudiantes</th>
            <th>Semestre</th>
            <th>Paralelo</th>
            <th>Número de Práctica</th>
            <th>Laboratorio</th>
            <th>Fecha</th>
            <th>Unidad</th>
            <th>Tema</th>
            <th>Más detalles</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
              <td><?= $row["nombres"].$row["apellidos"] ?></td>
              <td><?= $row["numero_estudiantes"] ?></td>
              <td><?= $row["semestre_practica"] ?></td>
              <td><?= $row["paralelo_practica"] ?></td>
              <td><?= $row["numero_practica"] ?></td>
              <td><?= $row["laboratorio_asignado"] ?></td>
              <td><?= $row["fecha_practica"] ?></td>
              <td><?= $row["unidad"] ?></td>
              <td><?= $row["tema"] ?></td>
              <td class="text-center">
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#verModal<?= $row['id_practica'] ?>">Ver más</button>
                <!-- Modal Ver Más -->
                <div class="modal fade" id="verModal<?= $row['id_practica'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detalles de la Práctica</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <p><strong>Resultado de Aprendizaje:</strong> <?= nl2br($row["resultado_aprendizaje"]) ?></p>
                        <p><strong>Objetivos:</strong> <?= nl2br($row["objetivos_practica"]) ?></p>
                        <p><strong>Actividades:</strong> <?= nl2br($row["actividades_practica"]) ?></p>
                        <p><strong>Materiales:</strong> <?= nl2br($row["materiales_practica"]) ?></p>
                        <p><strong>Referencias:</strong> <?= nl2br($row["referencias_practica"]) ?></p>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
              <td class="text-center">


                <!-- Botón Editar -->
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal<?= $row['id_practica'] ?>">Editar</button>

                <!-- Modal Editar -->
                <div class="modal fade" id="editarModal<?= $row['id_practica'] ?>" tabindex="-1">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <form action="#" method="POST">
                        <input type="hidden" name="accion" value="editar">
                        <div class="modal-header bg-warning">
                          <h5 class="modal-title">Editar Práctica</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id_practica" value="<?= $row['id_practica'] ?>">

                          <div class="row mb-3">
                            <div class="col">
                              <label>Número estudiantes</label>
                              <input type="number" class="form-control" name="numero_estudiantes" value="<?= $row['numero_estudiantes'] ?>" required>
                            </div>
                            <div class="col">
                              <label>Paralelo práctica</label>
                              <input type="text" class="form-control" name="paralelo_practica" value="<?= $row['paralelo_practica'] ?>" required>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col">
                              <label>Semestre práctica</label>
                              <input type="text" class="form-control" name="semestre_practica" value="<?= $row['semestre_practica'] ?>" required>
                            </div>
                            <div class="col">
                              <label>Número de práctica</label>
                              <input type="number" class="form-control" name="numero_practica" value="<?= $row['numero_practica'] ?>" required>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <?php
                            $id_lab_actual = $row['laboratorio_asignado'];

                            // Obtener todos los laboratorios
                            $query = $conn->query("SELECT id_laboratorio, nombre_laboratorio FROM laboratorios");
                            ?>
                            <div class="col">
                              <label for="laboratorio" class="form-label">Laboratorio</label>
                              <select class="form-select" name="laboratorio" id="laboratorio" required>
                                <option value="">Seleccione un laboratorio</option>
                                <?php while ($lab = $query->fetch_assoc()): ?>
                                  <option value="<?= $lab['id_laboratorio'] ?>"
                                    <?= $lab['id_laboratorio'] == $id_lab_actual ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($lab['nombre_laboratorio']) ?>
                                  </option>
                                <?php endwhile; ?>
                              </select>
                            </div>
                            <div class="col">
                              <label>fecha</label>
                              <input type="date" class="form-control" name="fecha_practica" value="<?= $row['fecha_practica'] ?>" required>
                            </div>
                          </div>

                          <div class="row mb-3">
                            <div class="col">
                              <label>Unidad</label>
                              <input type="text" class="form-control" name="unidad" value="<?= $row['unidad'] ?>" required>
                            </div>
                            <div class="col">
                              <label>Tema</label>
                              <input type="text" class="form-control" name="tema" value="<?= $row['tema'] ?>" required>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <label>Resultados Aprendizaje</label>
                            <textarea class="form-control" name="resultado_aprendizaje"><?= $row['resultado_aprendizaje'] ?></textarea>
                          </div>
                          <div class="mb-3">
                            <label>Objetivos</label>
                            <textarea class="form-control" name="objetivos"><?= $row['objetivos_practica'] ?></textarea>
                          </div>
                          <div class="mb-3">
                            <label>Actividades</label>
                            <textarea class="form-control" name="actividades"><?= $row['actividades_practica'] ?></textarea>
                          </div>
                          <div class="mb-3">
                            <label>Materiales</label>
                            <textarea class="form-control" name="materiales"><?= $row['materiales_practica'] ?></textarea>
                          </div>
                          <div class="mb-3">
                            <label>Referencias</label>
                            <textarea class="form-control" name="referencias"><?= $row['referencias_practica'] ?></textarea>
                          </div>

                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Eliminar -->
                <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta práctica?');" style="display:inline;">
                  <input type="hidden" name="id_practica" value="<?= $row['id_practica']; ?>">
                  <button type="submit" name="eliminar_practica" class="btn btn-danger btn-sm">
                    Eliminar
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    $(document).ready(function() {
      $('#tablaPracticas').DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
      });
    });
  </script>
</body>

</html>