<?php
session_start();
session_unset();
session_destroy(); // Destruir la sesión activa
header("Location:C:/xampp/htdocs/Tesis UCE/index.html"); // Redirigir al formulario de inicio de sesión
exit();
?>
