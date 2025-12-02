<?php
// controllers/ReportesController.php
if (!defined('INDEX_KEY')) die('Acceso denegado');

// Clase ReportesController: Maneja la generación y exportación de reportes
class ReportesController {
    
    // Acción index: Muestra la vista de filtros para generar reportes
    public function index() {
        // Renderizar vista de selección de reportes
        // require_once 'views/reportes/index.php';
    }

    // Acción generar: Procesa la solicitud de reporte y devuelve JSON o CSV
    public function generar() {
        global $db_connection;
        $modelo = new Reporte($db_connection);
        
        // Parámetros GET: tipo de reporte, formato (json/csv), fechas
        $tipo = $_GET['tipo'] ?? 'inventario';
        $formato = $_GET['formato'] ?? 'json'; // json o csv
        
        // Parámetros GET: fechas que vienen de un formulario para saber qué rango de fechas se va a generar el reporte
        $inicio = $_GET['f_ini'] ?? date('Y-m-d');
        $fin = $_GET['f_fin'] ?? date('Y-m-d');
        
        $data = [];
        $filename = "reporte";

        // Switch para determinar qué método del modelo llamar según el tipo
        switch ($tipo) {
            case 'inventario':
                $q = $_GET['q'] ?? '';
                $activos = isset($_GET['activos']) ? true : false;
                $data = $modelo->inventarioActual($q, $activos);
                $filename = "reporte_inventario_" . date('Ymd_Hi');
                break;
            case 'ventas':
                $data = $modelo->ventasPorRango($inicio, $fin);
                $filename = "reporte_ventas_" . date('Ymd_Hi');
                break;
            case 'ventas_detalle':
                $data = $modelo->detalleVentas($inicio, $fin);
                $filename = "reporte_ventas_detalle_" . date('Ymd_Hi');
                break;
            case 'compras':
                $data = $modelo->comprasPorRango($inicio, $fin);
                $filename = "reporte_compras_" . date('Ymd_Hi');
                break;
            case 'devoluciones':
                $data = $modelo->devolucionesPorRango($inicio, $fin);
                $filename = "reporte_devoluciones_" . date('Ymd_Hi');
                break;
        }

        // Si el formato solicitado es CSV, llamamos a la función de exportación
        if ($formato === 'csv') {
            $this->exportarCSV($data, $filename);
        } else {
            // Si no, devolvemos JSON (para vistas HTML dinámicas o API)
            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }

    // Método privado para generar y descargar un archivo CSV
    private function exportarCSV($data, $filename) {
        if (empty($data)) {
            echo "No hay datos para exportar";
            return;
        }

        // Headers para forzar la descarga del archivo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');

        // Abrimos el flujo de salida php://output
        $output = fopen('php://output', 'w');
        
        // Escribimos el BOM (Byte Order Mark) para que Excel reconozca caracteres UTF-8 correctamente
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Escribimos la primera fila con los nombres de las columnas (keys del array)
        fputcsv($output, array_keys($data[0]));

        // Escribimos cada fila de datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }
}
