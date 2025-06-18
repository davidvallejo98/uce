# Obtener recursos del sistema
$serial = (Get-CimInstance Win32_BIOS).SerialNumber
$nombreEquipo = $env:COMPUTERNAME
$sistema = (Get-CimInstance Win32_OperatingSystem).Caption
$procesador = (Get-CimInstance Win32_Processor).Name
$ramGB = [math]::Round((Get-CimInstance Win32_ComputerSystem).TotalPhysicalMemory / 1GB, 2)
$discoGB = [math]::Round((Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='C:'").Size / 1GB, 2)


# Crear objeto con los datos
$data = @{
    
    serial_equipo     = $serial
    nombre_equipo     = $nombreEquipo
    sistema_operativo = $sistema
    procesador        = $procesador
    memoria_ram_gb    = $ramGB
    disco_total_gb    = $discoGB
   
}

# Convertir a JSON
$json = $data | ConvertTo-Json

# URL del servidor (ajusta si es necesario)
 $url = "https://pruebasistemauce.wuaze.com/Estudiantes/api_recibir_datos.php"



# Enviar al servidor
Invoke-RestMethod -Uri $url -Method POST -Body $json -ContentType "application/json"
