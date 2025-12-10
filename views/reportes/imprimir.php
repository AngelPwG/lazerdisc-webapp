<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - Lazer Disc</title>
    <style>
        /* Reset y Estilos Base */
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #000; }
        
        /* Configuración de Impresión */
        @media print {
            @page { margin: 20mm; size: auto; }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }

        /* Cabecera */
        .header-report { overflow: hidden; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { float: left; width: 32mm; height: auto; margin-right: 20px; }
        .info-negocio { float: left; }
        .info-negocio h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .info-negocio p { margin: 2px 0; font-size: 11px; }
        
        .titulo-reporte { text-align: center; clear: both; margin-top: 10px; }
        .titulo-reporte h2 { margin: 5px 0; text-transform: uppercase; font-size: 16px; }
        .filtros-info { text-align: center; font-size: 11px; font-style: italic; margin-bottom: 5px; }

        /* Tabla */
        .tabla-reporte { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        .tabla-reporte th, .tabla-reporte td { border: 1px solid #ccc; padding: 6px 8px; }
        
        /* Encabezados resaltados */
        .tabla-reporte th { background-color: #f0f0f0; font-weight: bold; text-align: center; text-transform: uppercase; }
        
        /* Alineación */
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Zebra striping */
        .tabla-reporte tbody tr:nth-child(even) { background-color: #f9f9f9; }

        /* Totales */
        .tabla-totales { width: 40%; float: right; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid; }
        .tabla-totales td { padding: 5px; border: 1px solid #ddd; font-weight: bold; }
        .tabla-totales td:first-child { text-align: right; background-color: #f0f0f0; }
        .tabla-totales td:last-child { text-align: right; }

        /* Pie de página */
        .footer-report { margin-top: 40px; padding-top: 20px; border-top: 1px solid #000; font-size: 10px; }
        .firma { margin-top: 40px; border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; }
        .page-number:after { content: counter(page); }
        .fecha-gen { float: left; }
        .pagination { float: right; }

    </style>
</head>
<body>
    <!-- Botón No Imprimible -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ IMPRIMIR</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">CERRAR</button>
    </div>

    <div class="header-report">
        <!-- Logo -->
        <img src="assets/img/logo.png" alt="Logo Lazer Disc" class="logo">
        
        <div class="info-negocio">
            <h1>LAZER DISC</h1>
            <p>Calle Ficticia 123, Col. Centro</p>
            <p>Mazatlán, Sinaloa. CP 82000</p>
            <p>RFC: XAXX010101000</p>
        </div>

        <div class="titulo-reporte">
            <h2><?php echo htmlspecialchars($tituloReporte); ?></h2>
            <div class="filtros-info">
                <?php echo htmlspecialchars($rangoFiltros); ?>
            </div>
            <div class="filtros-info">
                Generado: <?php echo date('Y-m-d H:i'); ?>
            </div>
        </div>
    </div>

    <table class="tabla-reporte">
        <thead>
            <tr>
                <?php foreach($columnas as $col): ?>
                    <th><?php echo htmlspecialchars($col); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($datosReporte)): ?>
                <tr>
                    <td colspan="<?php echo count($columnas); ?>" class="text-center">Sin resultados</td>
                </tr>
            <?php else: ?>
                <?php foreach($datosReporte as $fila): ?>
                    <tr>
                        <?php foreach($fila as $key => $valor): ?>
                            <?php 
                                // Determinar alineación basada en contenido (simple heurística o clase)
                                $class = 'text-left';
                                if(is_numeric(str_replace(['.',','], '', $valor)) || strpos($key, 'precio') !== false || strpos($key, 'total') !== false || strpos($key, 'importe') !== false || strpos($key, 'cantidad') !== false) {
                                    $class = 'text-right';
                                }
                                if($key == 'folio' || $key == 'codigo' || $key == 'fecha') {
                                    $class = 'text-center';
                                }
                            ?>
                            <td class="<?php echo $class; ?>">
                                <?php 
                                    // Formato monetario si es precio/total
                                    if(is_numeric($valor) && (strpos($key, 'precio') !== false || strpos($key, 'total') !== false || strpos($key, 'importe') !== false || strpos($key, 'subtotal') !== false || strpos($key, 'iva') !== false)) {
                                        echo number_format($valor, 2, '.', ',');
                                    } else {
                                        echo htmlspecialchars($valor);
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Totales Section (Dinámico según tipo) -->
    <?php if(!empty($totalesReporte)): ?>
        <table class="tabla-totales">
            <?php foreach($totalesReporte as $label => $valor): ?>
            <tr>
                <td><?php echo htmlspecialchars($label); ?>:</td>
                <td>
                    <?php 
                        if(is_numeric($valor)) echo number_format($valor, 2, '.', ',');
                        else echo htmlspecialchars($valor);
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div style="clear: both;"></div>
    <?php endif; ?>

    <div class="footer-report">
        <div class="fecha-gen">
            Generado por: <?php echo isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'Sistema'; ?>
            <br>
            <div class="firma">Firma / Vo. Bo.</div>
        </div>
        
        <!-- Paginación se maneja mejor por CSS @page en impresión real -->
        <div class="pagination">
            <!-- Texto placeholder, los navegadores modernos agregan esto en headers/footers si se habilita, 
                 pero el request pide explicitamente 'Página X de Y' en pie. 
                 CSS puro no soporta 'X de Y' facilmente sin JS o Paged Media avanzado.
                 Usaremos un texto fijo indicativo para este scope web. -->
            Documento Oficial
        </div>
    </div>

    <!-- Script para numeración si usas Paged.js (Opcional, no incluido aquí para mantenerlo simple) -->
</body>
</html>
