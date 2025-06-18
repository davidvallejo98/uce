<?php
//session_start();
session_unset();
session_destroy(); // Destruir la sesión activa
header("Location:../index.html"); // Redirigir al formulario de inicio de sesión
exit();
?>
