<?php
session_start();

// Verifica si la sesión está iniciada correctamente
if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
  echo '<!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=../index.html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión cerrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4 text-center">
      <h2 class="text-danger">¡Sesión cerrada!</h2>
      <p class="text-dark">Tu sesión ha finalizado por seguridad. Serás redirigido al inicio de sesión.</p>
      <div class="text-muted fw-bold mt-3">
        Redirigiendo en <span id="seconds">5</span> segundos...
      </div>
    </div>
    <script>
      let seconds = 5;
      const countdown = document.getElementById("seconds");
      const timer = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;
        if (seconds === 0) clearInterval(timer);
      }, 1000);
    </script>
  </body>
  </html>';
  exit();
}

// Verifica el tipo de usuario
require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);

require_once '../conexion_login.php';

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_practica'])) {
  $id_aula = $_POST['id_aula'] ?? null;
  $estudiantes = trim($_POST['numero_estudiantes'] ?? '');
  $paralelo_practica = trim($_POST['paralelo_practica'] ?? '');
  $semestre = trim($_POST['semestre'] ?? '');
  $numero_practica = trim($_POST['numero_practica'] ?? '');
  $laboratorio_asignado = trim($_POST['laboratorio_asignado'] ?? '');
  $fecha = $_POST['fecha_practica'] ?? '';
  $unidad = trim($_POST['unidad'] ?? '');
  $tema = trim($_POST['tema'] ?? '');
  $resultados = trim($_POST['resultados_aprendizaje'] ?? '');
  $objetivo = trim($_POST['objetivo_practica'] ?? '');
  $actividades = trim($_POST['actividades_practica'] ?? '');
  $materiales = trim($_POST['materiales_practica'] ?? '');
  $referencias = trim($_POST['referencias_practica'] ?? '');

  if (empty($id_aula) || empty($numero_practica) || empty($fecha)) {
    $mensaje = "<div class='alert alert-danger'>Faltan campos obligatorios.</div>";
  } else {
    $sql = "INSERT INTO practicas (
      id_aula, numero_estudiantes, paralelo_practica, semestre_practica, numero_practica,
      laboratorio_asignado, fecha_practica, unidad, tema, resultado_aprendizaje,
      objetivos_practica, actividades_practica, materiales_practica, referencias_practica
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
      $stmt->bind_param(
        "iisissssssssss",
        $id_aula,
        $estudiantes,
        $paralelo_practica,
        $semestre,
        $numero_practica,
        $laboratorio_asignado,
        $fecha,
        $unidad,
        $tema,
        $resultados,
        $objetivo,
        $actividades,
        $materiales,
        $referencias
      );

      if ($stmt->execute()) {
        $mensaje = "<div class='alert alert-success'>Práctica registrada correctamente.</div>";
      } else {
        $mensaje = "<div class='alert alert-danger'>Error al registrar: " . $stmt->error . "</div>";
      }
      $stmt->close();
    } else {
      $mensaje = "<div class='alert alert-danger'>Error al preparar la consulta: " . $conn->error . "</div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Práctica</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Formulario de Registro de Práctica</h4>
      </div>
      <div class="card-body">
        <?= $mensaje ?>
        <form method="POST">
          <input type="hidden" name="registrar_practica" value="1">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Aula asignada:</label>
              <select name="id_aula" class="form-select" required>
                <option value="">Seleccione un aula</option>
                <?php foreach ($aulas as $aula): ?>
                  <option value="<?= $aula['id_aula'] ?>"> <?= $aula['nombre_aula'] ?> </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Número de estudiantes:</label>
              <input type="number" name="numero_estudiantes" class="form-control" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Paralelo:</label>
              <input type="text" name="paralelo_practica" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Semestre:</label>
              <input type="number" name="semestre" class="form-control" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Número de práctica:</label>
              <input type="number" name="numero_practica" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
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
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de práctica:</label>
              <input type="date" name="fecha_practica" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Unidad:</label>
              <input type="text" name="unidad" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Tema:</label>
            <input type="text" name="tema" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Resultados de aprendizaje:</label>
            <textarea name="resultados_aprendizaje" class="form-control"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Objetivo de la práctica:</label>
            <textarea name="objetivo_practica" class="form-control"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Actividades:</label>
            <textarea name="actividades_practica" class="form-control"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Materiales:</label>
            <textarea name="materiales_practica" class="form-control"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Referencias:</label>
            <textarea name="referencias_practica" class="form-control"></textarea>
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-success">Registrar Práctica</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
