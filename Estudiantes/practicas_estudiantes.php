<?php
session_start();
require_once '../verificar_sesion.php';
verificarPermiso(['Estudiante']);

if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
  echo '<script>alert("Sesión expirada. Redirigiendo..."); window.location.href="../index.html";</script>';
  exit();
}
require_once '../conexion_login.php';

// Consulta para obtener prácticas registradas por el docente
$cedula = $_SESSION['ci'];

// Tu consulta para obtener las prácticas
$sql = "SELECT p.*, u.nombres,u.apellidos
FROM practicas p
INNER JOIN aulas a ON p.id_aula = a.id_aula
INNER JOIN usuarios u ON a.ci = u.ci;";


$stmt = $conn->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();


// Obtener aulas del docente
$sql1 = "SELECT a.id_aula, a.nombre_aula FROM aulas a INNER JOIN usuarios u ON a.ci = u.ci WHERE u.ci = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $cedula);
$stmt1->execute();
$result = $stmt1->get_result();

$aulas = [];
while ($row = $result->fetch_assoc()) {
  $aulas[] = $row;
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
    <!-- Fin nav -->


  


  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Prácticas Registradas</h2>
      <!-- Botón que abre el modal para nueva práctica -->
   
    </div>

    <div class="table-responsive">
      <table id="tablaPracticas" class="table table-striped table-bordered">
        <thead class="table-primary text-center">
          <tr>
            <th>Nombres Docente</th>
            <th>Númedo de Estudiantes</th>
            <th>Semestre</th>
            <th>Paralelo</th>
            <th>Númedo de Práctica</th>
            <th>Laboratorio</th>
            <th>Fecha</th>
            <th>Unidad</th>
            <th>Tema</th>
            <th>Más detalles</th>
          
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