<?php
require_once '../conexion_login.php';
require('fpdf186/fpdf.php');

class ReportGenerator
{
    private $conn;
    private $pdf;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->pdf = new FPDF('P', 'mm', 'A4'); // 'P' = vertical
        $this->pdf->SetMargins(10, 10, 10);
        $this->pdf->SetAutoPageBreak(true, 20);
    }

    public function generateReport($tipo)
    {
        $this->pdf->AddPage();
        $this->addHeader($tipo);

        $query = $this->getQuery($tipo);
        if (!$query) {
            die("❌ Tipo de reporte no válido.");
        }

        $result = $this->conn->query($query);
        if (!$result) {
            die("❌ Error en la consulta: " . $this->conn->error);
        }

        if ($result->num_rows == 0) {
            $this->pdf->Cell(0, 10, 'No hay datos disponibles', 1, 1, 'C');
        } else {
            $columns = array_keys($result->fetch_assoc());
            $result->data_seek(0);
            $this->addTableData($result, $columns);
        }

        // Firma/nombre del autor en la parte inferior
        $this->pdf->SetY(-30); // 30mm desde la parte inferior
        $this->pdf->SetFont('Arial', 'I', 10);
        $this->pdf->Cell(0, 10, utf8_decode('Carrera de informática - Derechos Reservados ©'), 0, 1, 'C');

        // Imágenes en la parte inferior izquierda y derecha
        $this->pdf->Output();
    }

    private function addHeader($tipo)
    {
        date_default_timezone_set('America/Guayaquil');
        $fecha_actual = date('d/m/Y H:i:s');

        // Imágenes en la parte superior izquierda y derecha
        $this->pdf->Image('../imagenes/Escudo.png', 10, 10, 30); // Izquierda
        $this->pdf->Image('../imagenes/Escudo-Fil.png', 170, 10, 30); // Derecha

        // Espacio para las imágenes
        $this->pdf->Ln(25);

        // Título
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->Cell(190, 10, 'Reporte de ' . ucfirst($tipo), 0, 1, 'C');

        // Fecha
        $this->pdf->SetFont('Arial', 'I', 12);
        $this->pdf->Cell(190, 8, 'Fecha: ' . $fecha_actual, 0, 1, 'C');

        $this->pdf->Ln(5); // Espacio antes de los datos
    }

    private function getQuery($tipo)
    {
        $queries = [
            'reservas' => "SELECT usuarios.ci AS 'Cédula de Identidad',usuarios.nombres AS 'Nombres',usuarios.apellidos AS 'Apellidos',usuarios.correo_institucional AS 'Correo Institucional',usuarios.numero_telefono AS 'Número de Teléfono',
            recursos.equipo as'Equipo reservado',reservas.fecha_inicio AS 'Fecha de Inicio',reservas.hora_inicio AS 'Hora de Inicio',
            reservas.fecha_fin AS 'Fecha de fin', reservas.hora_fin AS 'Hora Fin' FROM `reservas` INNER JOIN usuarios ON reservas.ci=usuarios.ci INNER JOIN recursos ON reservas.codigo=recursos.codigo ;    
            ",

            'laboratorios' => "SELECT 
                nombre_laboratorio AS 'Laboratorio',
                estado AS 'Estado',
                total_computadoras AS 'Total Computadoras',
                nombre_docente AS 'Docente Responsable',
                num_mesas AS 'Número de Mesas',
                num_sillas_negras AS 'Sillas Negras',
                num_sillas_azules AS 'Sillas Azules',
                num_escritorio AS 'Escritorios',
                num_pizarra AS 'Pizarras'
                FROM laboratorios",

            'inventario' => "SELECT l.nombre_laboratorio AS 'Laboratorio' ,c.equipo_id AS 'Serial PC', c.procesador AS 'Procesador',c.ram AS 'Memoria RAM',c.disco_duro AS 'Disco Duro',c.sistema_operativo AS 'Sistema Operativo', c.teclado AS 'Teclado', c.mouse AS 'Mouse'
FROM computadoras c
INNER JOIN laboratorios l ON c.id_laboratorio = l.id_laboratorio
INNER JOIN (
    SELECT equipo_id, MAX(hora_registro) AS max_hora
    FROM computadoras
    GROUP BY equipo_id
) AS sub ON c.equipo_id = sub.equipo_id AND c.hora_registro = sub.max_hora
WHERE l.id_laboratorio ",

            'usuarios' => "SELECT 
                u.nombres AS 'Nombres',
                u.apellidos AS 'Apellidos',
                u.correo_institucional AS 'Correo Institucional',
                p.tipo_usuario AS 'Tipo de Usuario' 
                FROM usuarios u 
                INNER JOIN permisos p ON u.id_permisos = p.id_permisos",


            'recursos' => "SELECT codigo,equipo,tipo,marca,descripcion,fecha_registro AS 'Fecha de Registro',
  CASE disponibilidad
    WHEN 0 THEN 'Ocupado'
    WHEN 1 THEN 'Disponible'
    WHEN 2 THEN 'Dado de baja'
    ELSE 'Desconocido'
  END AS Disponibilidad
FROM recursos;"



        ];




        return $queries[$tipo] ?? null;
    }

    private function addTableData($result, $columns)
    {
        $this->pdf->SetFont('Arial', '', 10);
        $counter = 1;

        while ($row = $result->fetch_assoc()) {
            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->Cell(0, 8, "Registro #$counter", 0, 1, 'L');
            $this->pdf->SetFont('Arial', '', 10);

            foreach ($columns as $col) {
                $this->pdf->Cell(60, 8, utf8_decode($col) . ':', 0, 0, 'L');
                $this->pdf->Cell(0, 8, utf8_decode($row[$col]), 0, 1, 'L');
            }

            $this->pdf->Ln(5); // Espacio entre registros
            $counter++;
        }
    }
}
