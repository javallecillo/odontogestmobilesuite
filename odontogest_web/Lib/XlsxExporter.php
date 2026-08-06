<?php
/**
 * XlsxExporter — Generador de archivos .xlsx sin dependencias externas.
 * Usa ZipArchive (extensión estándar de PHP) + XML puro.
 *
 * Estilos incluidos:
 *  0 — celda normal con borde fino
 *  1 — encabezado: negrita, fondo verde petróleo (#2B7A78), texto blanco, centrado
 *  2 — totales:    negrita, fondo gris claro (#E8EDF5)
 *  3 — (reservado para uso futuro)
 *  4 — título del reporte: negrita grande, texto azul
 *  5 — subtítulo:  itálica pequeña, texto gris
 */
class XlsxExporter {

    /** @var array<string,int>  cadena → índice en tabla de strings compartidos */
    private static array $strings   = [];
    private static int   $stringIdx = 0;

    /**
     * Genera y envía como descarga un .xlsx profesional.
     *
     * @param string  $titulo      Primera fila (nombre del reporte), mergeada
     * @param string  $subtitulo   Segunda fila (rango de fechas u observación)
     * @param array   $encabezados Cabeceras de columna
     * @param array   $filas       Filas de datos (arrays indexados o asociativos)
     * @param array   $totales     Fila de totales opcional (array indexado)
     * @param string  $nombre      Nombre del archivo sin extensión
     */
    public static function descargar(
        string $titulo,
        string $subtitulo,
        array  $encabezados,
        array  $filas,
        array  $totales = [],
        string $nombre  = 'reporte'
    ): void {
        // Reset estado para que sea reutilizable
        self::$strings   = [];
        self::$stringIdx = 0;

        // Algunos servidores PHP no incluyen la extensión ZipArchive. En ese
        // caso se entrega un libro Excel 2003 XML (.xls), que Excel abre de
        // forma nativa y evita que la exportación falle por el entorno.
        if (!class_exists('ZipArchive')) {
            self::descargarExcelXml(
                $titulo, $subtitulo, $encabezados, $filas, $totales, $nombre
            );
        }

        // La hoja debe construirse ANTES de sharedStrings para poblar la tabla
        $sheetXml = self::buildSheet($titulo, $subtitulo, $encabezados, $filas, $totales);

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            exit('No se pudo crear el archivo temporal.');
        }

        $zip->addFromString('[Content_Types].xml',            self::buildContentTypes());
        $zip->addFromString('_rels/.rels',                    self::buildRels());
        $zip->addFromString('xl/workbook.xml',                self::buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels',     self::buildWorkbookRels());
        $zip->addFromString('xl/styles.xml',                  self::buildStyles());
        $zip->addFromString('xl/sharedStrings.xml',           self::buildSharedStrings());
        $zip->addFromString('xl/worksheets/sheet1.xml',       $sheetXml);
        $zip->close();

        $safeName = preg_replace('/[^\w\-. áéíóúñÁÉÍÓÚÑ]/u', '_', $nombre);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($safeName) . '.xlsx"');
        header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    /** Genera un libro Excel compatible sin requerir extensiones de PHP. */
    private static function descargarExcelXml(
        string $titulo,
        string $subtitulo,
        array $encabezados,
        array $filas,
        array $totales,
        string $nombre
    ): void {
        $safeName = preg_replace('/[^\w\-. áéíóúñÁÉÍÓÚÑ]/u', '_', $nombre);
        $columnas = max(1, count($encabezados));

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($safeName) . '.xls"');
        header('Cache-Control: max-age=0, must-revalidate, no-cache, no-store');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<?mso-application progid="Excel.Sheet"?>';
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        echo '<Styles>'
            . '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="14" ss:Color="#2B7A78"/></Style>'
            . '<Style ss:ID="Subtitle"><Font ss:Italic="1" ss:Color="#52616B"/></Style>'
            . '<Style ss:ID="Header"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#2B7A78" ss:Pattern="Solid"/></Style>'
            . '<Style ss:ID="Total"><Font ss:Bold="1"/><Interior ss:Color="#CFF3EA" ss:Pattern="Solid"/></Style>'
            . '</Styles><Worksheet ss:Name="Reporte"><Table>';

        self::xmlRow([$titulo], 'Title', $columnas);
        self::xmlRow([$subtitulo], 'Subtitle', $columnas);
        self::xmlRow([], null, $columnas);
        self::xmlRow($encabezados, 'Header');
        foreach ($filas as $fila) self::xmlRow(array_values($fila));
        if ($totales) self::xmlRow($totales, 'Total');

        echo '</Table></Worksheet></Workbook>';
        exit;
    }

    private static function xmlRow(array $values, ?string $style = null, int $merge = 0): void {
        echo '<Row>';
        if ($merge > 1) {
            self::xmlCell($values[0] ?? '', $style, $merge - 1);
        } elseif (!$values) {
            echo '<Cell/>';
        } else {
            foreach ($values as $value) self::xmlCell($value, $style);
        }
        echo '</Row>';
    }

    private static function xmlCell($value, ?string $style = null, int $mergeAcross = 0): void {
        $attrs = $style ? ' ss:StyleID="' . $style . '"' : '';
        if ($mergeAcross > 0) $attrs .= ' ss:MergeAcross="' . $mergeAcross . '"';
        $type = is_numeric($value) ? 'Number' : 'String';
        $text = htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        echo '<Cell' . $attrs . '><Data ss:Type="' . $type . '">' . $text . '</Data></Cell>';
    }

    // ── String table ─────────────────────────────────────────────
    private static function str(string $s): int {
        if (!isset(self::$strings[$s])) {
            self::$strings[$s] = self::$stringIdx++;
        }
        return self::$strings[$s];
    }

    // ── Referencia de columna (1→A, 26→Z, 27→AA …) ──────────────
    private static function col(int $n): string {
        $s = '';
        while ($n > 0) {
            $n--;
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26);
        }
        return $s;
    }

    // ── Construye un elemento <c> ─────────────────────────────────
    private static function cell(string $colLetter, int $row, $value, int $style = 0): string {
        $ref = $colLetter . $row;
        if ($value === null || $value === '') {
            return "<c r=\"{$ref}\" s=\"{$style}\"/>";
        }
        // Número: almacenar como valor numérico (no shared string)
        if (is_numeric($value) && !preg_match('/^0\d/', (string)$value)) {
            $v = htmlspecialchars((string)$value, ENT_XML1);
            return "<c r=\"{$ref}\" s=\"{$style}\"><v>{$v}</v></c>";
        }
        // Cadena: shared string
        $idx = self::str((string)$value);
        return "<c r=\"{$ref}\" t=\"s\" s=\"{$style}\"><v>{$idx}</v></c>";
    }

    // ── Hoja principal ────────────────────────────────────────────
    private static function buildSheet(
        string $titulo, string $subtitulo,
        array $encabezados, array $filas, array $totales
    ): string {
        $numCols  = count($encabezados);
        $lastCol  = self::col($numCols);

        // Calcular anchos de columna basados en el contenido máximo
        $widths = array_map('mb_strlen', $encabezados);
        foreach ($filas as $fila) {
            $fila = array_values($fila);
            for ($c = 0; $c < $numCols; $c++) {
                $widths[$c] = max($widths[$c] ?? 8, mb_strlen((string)($fila[$c] ?? '')));
            }
        }
        foreach (array_values($totales) as $c => $v) {
            $widths[$c] = max($widths[$c] ?? 8, mb_strlen((string)$v));
        }

        $colsXml = '<cols>';
        for ($c = 1; $c <= $numCols; $c++) {
            $w = min(max(($widths[$c - 1] * 1.35), 10), 60);
            $colsXml .= sprintf('<col min="%d" max="%d" width="%.1f" bestFit="1" customWidth="1"/>', $c, $c, $w);
        }
        $colsXml .= '</cols>';

        $sheetData  = '<sheetData>';
        $curRow     = 1;

        // Fila 1: Título (mergeada, estilo 4)
        $sheetData .= "<row r=\"{$curRow}\" ht=\"22\" customHeight=\"1\">";
        $sheetData .= self::cell('A', $curRow, $titulo, 4);
        $sheetData .= '</row>';
        $curRow++;

        // Fila 2: Subtítulo (mergeada, estilo 5)
        $sheetData .= "<row r=\"{$curRow}\" ht=\"16\" customHeight=\"1\">";
        $sheetData .= self::cell('A', $curRow, $subtitulo, 5);
        $sheetData .= '</row>';
        $curRow++;

        // Fila 3: Vacía (separador visual)
        $sheetData .= "<row r=\"{$curRow}\"></row>";
        $curRow++;

        // Fila 4: Encabezados (estilo 1 = azul+blanco+negrita)
        $headerRow  = $curRow;
        $sheetData .= "<row r=\"{$curRow}\" ht=\"18\" customHeight=\"1\">";
        foreach ($encabezados as $ci => $h) {
            $sheetData .= self::cell(self::col($ci + 1), $curRow, $h, 1);
        }
        $sheetData .= '</row>';
        $curRow++;

        // Filas de datos (estilo 0 = normal con borde)
        foreach ($filas as $fila) {
            $fila = array_values($fila);
            $sheetData .= "<row r=\"{$curRow}\">";
            for ($c = 0; $c < $numCols; $c++) {
                $sheetData .= self::cell(self::col($c + 1), $curRow, $fila[$c] ?? '', 0);
            }
            $sheetData .= '</row>';
            $curRow++;
        }

        // Fila de totales (estilo 2 = negrita+gris)
        if (!empty($totales)) {
            $tRow = array_values($totales);
            $sheetData .= "<row r=\"{$curRow}\" ht=\"16\" customHeight=\"1\">";
            for ($c = 0; $c < $numCols; $c++) {
                $sheetData .= self::cell(self::col($c + 1), $curRow, $tRow[$c] ?? '', 2);
            }
            $sheetData .= '</row>';
        }

        $sheetData .= '</sheetData>';

        // Merge de título y subtítulo
        $merges = '<mergeCells count="2">'
            . "<mergeCell ref=\"A1:{$lastCol}1\"/>"
            . "<mergeCell ref=\"A2:{$lastCol}2\"/>"
            . '</mergeCells>';

        // AutoFiltro en la fila de encabezados
        $autoFilter = "<autoFilter ref=\"A{$headerRow}:{$lastCol}{$headerRow}\"/>";

        // Panel congelado bajo los encabezados (filas 1-4 fijas)
        $freezeRow  = $headerRow + 1;
        $freezePane = "<pane ySplit=\"{$headerRow}\" topLeftCell=\"A{$freezeRow}\" "
            . "activePane=\"bottomLeft\" state=\"frozen\"/>"
            . "<selection pane=\"bottomLeft\" activeCell=\"A{$freezeRow}\" sqref=\"A{$freezeRow}\"/>";

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews>'
            . '<sheetView tabSelected="1" workbookViewId="0">'
            . $freezePane
            . '</sheetView>'
            . '</sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . $colsXml
            . $sheetData
            . $merges
            . $autoFilter
            . '</worksheet>';
    }

    // ── Tabla de strings compartidos ──────────────────────────────
    private static function buildSharedStrings(): string {
        $count  = count(self::$strings);
        $sorted = array_flip(self::$strings); // índice → cadena
        ksort($sorted);
        $si = '';
        foreach ($sorted as $s) {
            $s   = htmlspecialchars((string)$s, ENT_XML1);
            $si .= "<si><t xml:space=\"preserve\">{$s}</t></si>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . " count=\"{$count}\" uniqueCount=\"{$count}\">{$si}</sst>";
    }

    // ── Estilos ───────────────────────────────────────────────────
    private static function buildStyles(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            // ── Formatos de número (solo built-in) ──
            . '<numFmts count="0"/>'
            // ── Fuentes ──
            . '<fonts count="5">'
            // 0: normal
            . '<font><sz val="11"/><name val="Calibri"/><family val="2"/></font>'
            // 1: encabezado — negrita, blanco
            . '<font><b/><sz val="11"/><name val="Calibri"/><family val="2"/><color rgb="FFFFFFFF"/></font>'
            // 2: totales — negrita, azul oscuro
            . '<font><b/><sz val="11"/><name val="Calibri"/><family val="2"/><color rgb="FF0C1F46"/></font>'
            // 3: título — negrita grande, azul
            . '<font><b/><sz val="14"/><name val="Calibri"/><family val="2"/><color rgb="FF2B7A78"/></font>'
            // 4: subtítulo — itálica, gris
            . '<font><i/><sz val="10"/><name val="Calibri"/><family val="2"/><color rgb="FF6B7280"/></font>'
            . '</fonts>'
            // ── Rellenos (los 2 primeros son obligatorios por la spec) ──
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            // 2: azul principal (encabezado)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF2B7A78"/><bgColor indexed="64"/></patternFill></fill>'
            // 3: gris muy claro (totales)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8EDF5"/><bgColor indexed="64"/></patternFill></fill>'
            // 4: blanco (sin relleno explícito)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>'
            . '</fills>'
            // ── Bordes ──
            . '<borders count="2">'
            // 0: sin borde
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            // 1: borde fino gris
            . '<border>'
            . '<left style="thin"><color rgb="FFB9E5D9"/></left>'
            . '<right style="thin"><color rgb="FFB9E5D9"/></right>'
            . '<top style="thin"><color rgb="FFB9E5D9"/></top>'
            . '<bottom style="thin"><color rgb="FFB9E5D9"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            // ── cellStyleXfs (requerido por spec) ──
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            // ── cellXfs — cada índice corresponde al parámetro $style en cell() ──
            . '<cellXfs>'
            // 0: normal con borde
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyBorder="1"/>'
            // 1: encabezado (azul + blanco + centrado)
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"'
            . ' applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="center" vertical="center" wrapText="1"/>'
            . '</xf>'
            // 2: totales (gris + negrita)
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0"'
            . ' applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="center"/>'
            . '</xf>'
            // 3: reservado (igual que 0)
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyBorder="1"/>'
            // 4: título (grande, azul, sin borde)
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0"'
            . ' applyFont="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="center"/>'
            . '</xf>'
            // 5: subtítulo (itálica gris, sin borde)
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0"'
            . ' applyFont="1" applyAlignment="1">'
            . '<alignment horizontal="left" vertical="center"/>'
            . '</xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    // ── Archivos estructurales del paquete OOXML ──────────────────
    private static function buildContentTypes(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml"  ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function buildRels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            . ' Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function buildWorkbook(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="480" yWindow="60" windowWidth="18140" windowHeight="8226"/></bookViews>'
            . '<sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function buildWorkbookRels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
            . ' Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            . ' Target="sharedStrings.xml"/>'
            . '<Relationship Id="rId3"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>'
            . '</Relationships>';
    }
}
