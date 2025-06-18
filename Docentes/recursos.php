<?php
session_start();
require_once '../verificar_sesion.php';
verificarPermiso(['Docente']);
require_once '../conexion_login.php';
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

if (!isset($_SESSION['correo_institucional']) || !isset($_SESSION['tipo_usuario'])) {
    echo '...'; // Tu mensaje de sesión cerrada está bien.
    exit();
}

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];

        $codigo = $_POST['codigo'] ?? '';
        $equipo = $_POST['equipo'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $marca = $_POST['marca'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $disponibilidad = $_POST['disponibilidad'] ?? '';
        $fecha = $_POST['fecha_registro'] ?? '';

        if ($accion === 'agregar') {
            // Verificar si el código ya existe
            $sql_check = "SELECT codigo FROM recursos WHERE codigo = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $codigo);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                // El código ya existe
                header("Location: recursos.php?mensaje=duplicado");
                exit;
            }

            // Insertar si el código no existe
            $sql = "INSERT INTO recursos (codigo, equipo, tipo, marca, descripcion, disponibilidad, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $codigo, $equipo, $tipo, $marca, $descripcion, $disponibilidad, $fecha);

            if ($stmt->execute()) {
                header("Location: recursos.php?mensaje=agregado");
                exit;
            } else {
                header("Location: recursos.php?mensaje=error");
                exit;
            }
        }

        if ($accion === 'editar' && $codigo) {
            $sql = "UPDATE recursos SET codigo=?, equipo=?, tipo=?, marca=?, descripcion=?, disponibilidad=?, fecha_registro=? WHERE codigo=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $codigo, $equipo, $tipo, $marca, $descripcion, $disponibilidad, $fecha, $codigo);

            if ($stmt->execute()) {
                header("Location: recursos.php?mensaje=editado");
                exit;
            } else {
                header("Location: recursos.php?mensaje=error");
                exit;
            }
        }

        if ($accion === 'eliminar' && $codigo) {
            $sql = "DELETE FROM recursos WHERE codigo=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $codigo);
            if ($stmt->execute()) {
                header("Location: recursos.php?mensaje=eliminado");
                exit;
            } else {
                header("Location: recursos.php?mensaje=error");
                exit;
            }
        }
    }
}

// Obtener recursos para listar
$sql = "SELECT  codigo, equipo, tipo, marca, descripcion, disponibilidad, fecha_registro FROM recursos";
$result = $conn->query($sql);

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="\TESIS UCE\imagenes\Escudo-Fil.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .card-custom {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }

        .card-custom:hover {
            transform: scale(1.05);
        }

        .icon-container {
            font-size: 2rem;
            color: #007bff;
        }

        #reloj {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
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


    <!-- Fin Nav -->
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Listado de Recursos</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    </head>

    <body class="bg-light">
        <div class="container py-5">
            <h2 class="mb-4 text-center">Listado de Recursos</h2>
            <!-- 
              <div class="d-flex justify-content-between mb-3">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus-circle"></i> Agregar Recurso
                </button>
            </div> 
    
    -->



            <?php if (isset($_GET['mensaje'])): ?>
                <?php if ($_GET['mensaje'] === 'agregado'): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <strong>¡Éxito!</strong> El recurso fue agregado correctamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif ($_GET['mensaje'] === 'error'): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <strong>Error:</strong> No se pudo completar la operación. Intenta nuevamente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php elseif ($_GET['mensaje'] === 'duplicado'): ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <strong>Advertencia:</strong> Ya existe un recurso con ese código. Usa uno diferente.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['mensaje'])): ?>
                <div class="container mt-3">
                    <?php if ($_GET['mensaje'] == 'editado'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ✅ Recurso <strong>editado correctamente</strong>.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['mensaje'] == 'eliminado'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            🗑️ Recurso <strong>eliminado correctamente</strong>.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Marca</th>
                                <th>Descripción</th>
                                <th>Disponibilidad</th>
                                <th>Fecha de Registro</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['codigo']) ?></td>
                                        <td><?= htmlspecialchars($row['equipo']) ?></td>
                                        <td><?= htmlspecialchars($row['tipo']) ?></td>
                                        <td><?= htmlspecialchars($row['marca']) ?></td>
                                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                                        <td>
                                            <?php
                                            switch ($row['disponibilidad']) {
                                                case 1:
                                                    echo '<span class="text-success fw-bold">Disponible</span>';
                                                    break;
                                                case 0:
                                                    echo '<span class="text-warning fw-bold">Ocupado</span>';
                                                    break;
                                                case 2:
                                                    echo '<span class="text-danger fw-bold">Dado de baja</span>';
                                                    break;
                                                default:
                                                    echo '<span class="text-secondary">Desconocido</span>';
                                            }
                                            ?>
                                        </td>


                                        <td><?= htmlspecialchars($row['fecha_registro']) ?></td>
                                        <!--   <td>
                                           Editar 
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $row['codigo'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
-->
                                        <!-- Eliminar
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $row['codigo'] ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                     -->

                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="modalEditar<?= $row['codigo'] ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $row['codigo'] ?>" aria-hidden="true" data-bs-backdrop="false">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form action="recursos.php" method="POST">
                                                        <input type="hidden" name="accion" value="editar">
                                                        <input type="hidden" name="codigo" value="<?= $row['codigo'] ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Recurso</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Código</label>
                                                                    <input type="text" class="form-control" name="codigo" value="<?= htmlspecialchars($row['codigo']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Nombre</label>
                                                                    <input type="text" class="form-control" name="equipo" value="<?= htmlspecialchars($row['equipo']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Tipo</label>
                                                                    <input type="text" class="form-control" name="tipo" value="<?= htmlspecialchars($row['tipo']) ?>" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Marca</label>
                                                                    <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($row['marca']) ?>" required>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Descripción</label>
                                                                    <textarea class="form-control" name="descripcion" required><?= htmlspecialchars($row['descripcion']) ?></textarea>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Disponibilidad</label>
                                                                    <select class="form-select" name="disponibilidad" required>
                                                                        <option value="#">Seleccione...</option>
                                                                        <option value="1" <?= $row['disponibilidad'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                                                                        <option value="0" <?= $row['disponibilidad'] == 'Ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                                                        <option value="2" <?= $row['disponibilidad'] == 'Dado de baja' ? 'selected' : '' ?>>Dado de baja</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Fecha de Registro</label>
                                                                    <input type="date" class="form-control" name="fecha_registro" value="<?= $row['fecha_registro'] ?>" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Eliminar -->
                                        <div class="modal fade" id="modalEliminar<?= $row['codigo'] ?>" tabindex="-1" aria-labelledby="modalEliminarLabel<?= $row['codigo'] ?>" aria-hidden="true" data-bs-backdrop="false">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="recursos.php" method="POST">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="codigo" value="<?= $row['codigo'] ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Eliminar Recurso</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            ¿Estás seguro de que deseas eliminar el recurso <strong><?= htmlspecialchars($row['equipo']) ?></strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No se encontraron recursos.</td>
                                    </tr>
                                <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalAgregar" data-bs-backdrop="false" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">


            <!-- Modal Agregar 
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">-->

            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="recursos.php" method="POST">
                        <input type="hidden" name="accion" value="agregar">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Nuevo Recurso</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Código</label>
                                    <input type="text" class="form-control" name="codigo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Equipo</label>
                                    <input type="text" class="form-control" name="equipo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo</label>
                                    <input type="text" class="form-control" name="tipo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Marca</label>
                                    <input type="text" class="form-control" name="marca" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="2" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Disponibilidad</label>
                                    <select class="form-select" name="Disponibilidad" required>
                                        <option value="#">Seleccione...</option>
                                        <option value="1">Disponible</option>
                                        <option value="0">Ocupado</option>
                                        <option value="2">Dado de baja</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Registro</label>
                                    <input type="date" class="form-control" name="fecha_registro" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar Recurso</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        </script>




    </body>

    </html>