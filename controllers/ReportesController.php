<?php
// controllers/ReportesController.php
if (!defined('INDEX_KEY'))
    die('Acceso denegado');

require_once 'models/Reporte.php';
// Clase ReportesController: Maneja la generación y exportación de reportes
class ReportesController
{

    // Acción index: Muestra la vista de filtros para generar reportes
    public function index()
    {
        // Renderizar vista de selección de reportes
        require_once 'views/reportes/index.php';
    }

    // Acción generar: Procesa la solicitud de reporte y devuelve JSON o CSV
    public function generar()
    {
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
        $tituloReporte = "Reporte";
        $columnas = [];
        $totalesReporte = [];
        $rangoFiltros = "Del $inicio al $fin";

        switch ($tipo) {
            case 'inventario':
                $tituloReporte = "INVENTARIO DE EXISTENCIAS";
                $q = $_GET['q'] ?? '';
                $activos = isset($_GET['activos']) ? true : false;
                $rangoFiltros = "Filtro: " . ($q ? "'$q' " : "Todos ") . ($activos ? "(Solo Activos)" : "");

                $data = $modelo->inventarioActual($q, $activos);
                $filename = "reporte_inventario_" . date('Ymd_Hi');

                $columnas = ['Código', 'Nombre', 'Precio', 'Existencia', 'Estado'];
                // Calcular Totales
                $sumaExistencias = 0;
                foreach ($data as $row)
                    $sumaExistencias += $row['existencia'];
                $totalesReporte = [
                    'Total Productos Listados' => count($data),
                    'Suma de Existencias' => $sumaExistencias
                ];
                break;

            case 'ventas':
                $tituloReporte = "REPORTE DE VENTAS (ENCABEZADOS)";
                $data = $modelo->ventasPorRango($inicio, $fin);
                $filename = "reporte_ventas_" . date('Ymd_Hi');

                $columnas = ['Folio', 'Fecha', 'Cajero', 'Total', 'Subtotal', 'IVA'];
                // Reordenar columnas para que coincidan con la vista si es necesario, 
                // o ajustar la vista para usar keys. En view usamos foreach($row).
                // Ajustamos el orden en el array $data o confiamos en el orden del SQL/Modelo.
                // SQL devuelve: folio, fecha, cajero, total. Modelo agrega: subtotal, iva.

                // Calcular Totales
                $totalFacturado = 0;
                foreach ($data as $row)
                    $totalFacturado += $row['total'];
                $numTickets = count($data);
                $promedio = $numTickets > 0 ? $totalFacturado / $numTickets : 0;

                $totalesReporte = [
                    'Importe Total Facturado' => $totalFacturado,
                    'Número de Tickets' => $numTickets,
                    'Ticket Promedio' => $promedio
                ];
                break;

            case 'ventas_detalle':
                $tituloReporte = "DETALLE DE VENTAS (LÍNEAS)";
                $data = $modelo->detalleVentas($inicio, $fin);
                $filename = "reporte_ventas_detalle_" . date('Ymd_Hi');

                $columnas = ['Fecha', 'Folio', 'Código', 'Nombre', 'Cantidad', 'Precio Unit.', 'Importe'];

                $unidadesVendidas = 0;
                $importeTotal = 0;
                foreach ($data as $row) {
                    $unidadesVendidas += $row['cantidad'];
                    $importeTotal += $row['importe'];
                }

                $totalesReporte = [
                    'Unidades Vendidas' => $unidadesVendidas,
                    'Importe Total' => $importeTotal
                ];
                break;

            case 'compras':
                $tituloReporte = "REPORTE DE COMPRAS";
                $data = $modelo->comprasPorRango($inicio, $fin);
                $filename = "reporte_compras_" . date('Ymd_Hi');

                $columnas = ['Folio', 'Fecha', 'Proveedor', 'Total'];

                $importeComprado = 0;
                foreach ($data as $row)
                    $importeComprado += $row['total'];

                $totalesReporte = [
                    'Importe Total Comprado' => $importeComprado
                ];
                break;

            case 'devoluciones':
                $tituloReporte = "REPORTE DE DEVOLUCIONES";
                $data = $modelo->devolucionesPorRango($inicio, $fin);
                $filename = "reporte_devoluciones_" . date('Ymd_Hi');

                $columnas = ['Fecha', 'Folio Venta', 'Código', 'Nombre', 'Cant. Dev.', 'Motivo', 'Importe Ajust.'];

                $unidadesDev = 0;
                $importeDev = 0;
                foreach ($data as $row) {
                    $unidadesDev += $row['cantidad'];
                    $importeDev += $row['importe_ajustado'];
                }

                $totalesReporte = [
                    'Unidades Devueltas' => $unidadesDev,
                    'Importe Total Ajustado' => $importeDev
                ];
                break;

            case 'corte':
                $tituloReporte = "CIERRE DE CAJA (CORTE DEL DÍA)";
                // Para corte usamos solo fecha inicio como fecha de referencia
                $resultado = $modelo->corteCaja($inicio);
                $data = $resultado['detalles_cajero']; // Main table data
                $filename = "corte_caja_" . str_replace('-', '', $inicio);

                $columnas = ['Cajero', 'Tickets Emitidos', 'Total Vendido'];

                // Mapear claves a nombres legibles para la vista genérica
                $dataFormatted = [];
                foreach ($data as $row) {
                    $dataFormatted[] = [
                        'cajero' => $row['cajero'],
                        'num_ventas' => $row['num_ventas'],
                        'total_vendido' => $row['total_vendido']
                    ];
                }
                $data = $dataFormatted;

                $totalesReporte = $resultado['resumen'];
                $rangoFiltros = "Fecha de Corte: $inicio";
                break;
        }

        // Si el formato solicitado es CSV, llamamos a la función de exportación
        if ($formato === 'csv') {
            $this->exportarCSV($data, $filename, $tituloReporte, $rangoFiltros, $totalesReporte);
        } elseif ($formato === 'json') {
            // Si no, devolvemos JSON (para vistas HTML dinámicas o API)
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            // Formato 'html' o 'print' -> Renderizar Vista
            $datosReporte = $data; // Variable para la vista
            require_once 'views/reportes/imprimir.php';
        }
    }

    // Método privado para generar y descargar un archivo CSV
    private function exportarCSV($data, $filename, $titulo = "Reporte", $filtros = "", $totales = [])
    {
        if (empty($data)) {
            echo json_encode(['status' => 'error', 'message' => 'No hay datos para exportar']);
            return;
        }

        // Headers para forzar la descarga del archivo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');

        // Abrimos el flujo de salida php://output
        $output = fopen('php://output', 'w');

        // Escribimos el BOM (Byte Order Mark) para que Excel reconozca caracteres UTF-8 correctamente
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // 1. Encabezados del Negocio y del Reporte
        fputcsv($output, ["LAZER DISC"]);
        fputcsv($output, ["Calle Ficticia 123, Col. Centro, Mazatlán, Sinaloa. CP 82000"]);
        fputcsv($output, ["RFC: XAXX010101000"]);
        fputcsv($output, []); // Línea en blanco
        fputcsv($output, [$titulo]);
        fputcsv($output, [$filtros]);
        fputcsv($output, ["Generado: " . date('Y-m-d H:i')]);
        fputcsv($output, ["Generado por: " . (isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Sistema')]);
        fputcsv($output, []); // Línea en blanco

        // 2. Tabla de Datos
        // Escribimos la primera fila con los nombres de las columnas (keys del array del primer elemento)
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));

            // Escribimos cada fila de datos
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        // 3. Totales
        if (!empty($totales)) {
            fputcsv($output, []); // Línea en blanco separadora
            fputcsv($output, ["RESUMEN Y TOTALES"]);
            foreach ($totales as $key => $value) {
                // Formatear valor si es numérico y parece moneda (opcional, pero ayuda a la lectura)
                // En CSV es mejor dejar números limpios, pero si el usuario quiere "lo mismo que el reporte impreso"...
                // Dejaremos el número 'raw' pero podríamos formatearlo si se quejan.
                // Por ahora, raw es más útil para cálculos posteriores en Excel.
                fputcsv($output, [$key, $value]);
            }
        }

        fclose($output);
    }
}
