<?php
require_once '../conexion_login.php';
require_once 'reporte.php';


if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $reportGenerator = new ReportGenerator($conn);
    $reportGenerator->generateReport($tipo);
} else {
    die("No se especificó el tipo de reporte.");
}
