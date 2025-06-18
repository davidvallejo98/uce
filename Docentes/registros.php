<?php

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
include('conexion.php'); // Tu archivo de conexión a la BD

$sql = "SELECT l.nombre_laboratorio, c.id_computadora, c.nombre_equipo, c.fecha_registro
        FROM computadoras c
        INNER JOIN laboratorios l ON c.id_laboratorio = l.id_laboratorio
        ORDER BY l.nombre_laboratorio, c.fecha_registro DESC";

$result = $conn->query($sql);

$agrupados = [];
while ($row = $result->fetch_assoc()) {
    $agrupados[$row['nombre_laboratorio']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registros por Laboratorio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">Listado de Registros por Laboratorio</h2>

  <?php foreach ($agrupados as $laboratorio => $registros): ?>
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><?= htmlspecialchars($laboratorio) ?></h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Equipo</th>
              <th>Fecha de Registro</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($registros as $registro): ?>
              <tr>
                <td><?= $registro['id_computadora'] ?></td>
                <td><?= htmlspecialchars($registro['nombre_equipo']) ?></td>
                <td><?= $registro['fecha_registro'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>

</div>
</body>
</html>
