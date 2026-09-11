<?php
declare(strict_types=1);

require __DIR__ . '/database.php';

$format = strtolower(trim((string)($_GET['format'] ?? 'csv')));
$start = trim((string)($_GET['start'] ?? ''));
$end = trim((string)($_GET['end'] ?? ''));

function displayDate(string $iso): string
{
    $time = strtotime($iso);
    return $time !== false ? date('d-m-Y', $time) : $iso;
}

function validIsoDate(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    if (!$date) {
        return false;
    }

    $errors = DateTime::getLastErrors();
    return (!$errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
        && $date->format('Y-m-d') === $value;
}

function cleanCell(mixed $value): string
{
    return trim(str_replace(["\t", "\n", "\r"], ' ', (string)$value));
}

function excelSafe(mixed $value): string
{
    $value = cleanCell($value);
    // Prevent spreadsheet formula injection while keeping the original text readable.
    if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
        return "'" . $value;
    }
    return $value;
}

if (!in_array($format, ['csv', 'xlsx'], true)) {
    http_response_code(400);
    exit('Unsupported export format.');
}

if (!validIsoDate($start) || !validIsoDate($end) || $start > $end) {
    http_response_code(400);
    exit('Invalid date range.');
}

$stmt = $db->prepare(
    'SELECT lunch_date, meal_type, meal_time, recorded_time, food_item, quantity, notes
     FROM lunches
     WHERE lunch_date BETWEEN ? AND ?
     ORDER BY lunch_date ASC, meal_time ASC, id ASC'
);
$stmt->execute([$start, $end]);
$rows = $stmt->fetchAll();

$displayStart = displayDate($start);
$displayEnd = displayDate($end);
$filenameBase = 'food_report_' . $displayStart . '_to_' . $displayEnd;

$headers = ['Date', 'Meal', 'Meal Time', 'Recorded Time', 'Food Item', 'Quantity', 'Notes'];
$data = [$headers];

foreach ($rows as $row) {
    $data[] = [
        displayDate((string)$row['lunch_date']),
        excelSafe($row['meal_type'] ?? ''),
        excelSafe($row['meal_time'] ?? ''),
        excelSafe($row['recorded_time'] ?? ''),
        excelSafe($row['food_item'] ?? ''),
        excelSafe($row['quantity'] ?? ''),
        excelSafe($row['notes'] ?? ''),
    ];
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');
    header('X-Content-Type-Options: nosniff');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        http_response_code(500);
        exit('Unable to create export.');
    }

    fwrite($output, "\xEF\xBB\xBF");
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

/**
 * Build a minimal valid XLSX package without Composer, PhpSpreadsheet,
 * or the PHP Zip extension. XLSX files are ZIP packages containing XML.
 * This makes the downloaded file a real .xlsx workbook instead of the
 * previous tab-separated .xls file that some mobile Office versions reject.
 */
function zipStore(array $files): string
{
    $local = '';
    $central = '';
    $offset = 0;

    $timestamp = getdate();
    $dosTime = (($timestamp['hours'] & 0x1f) << 11)
        | (($timestamp['minutes'] & 0x3f) << 5)
        | (int)floor($timestamp['seconds'] / 2);
    $dosDate = (($timestamp['year'] - 1980) << 9)
        | (($timestamp['mon'] & 0x0f) << 5)
        | ($timestamp['mday'] & 0x1f);

    foreach ($files as $name => $content) {
        $name = (string)$name;
        $content = (string)$content;
        $nameLength = strlen($name);
        $size = strlen($content);
        $crc = crc32($content);
        $crcUnsigned = $crc < 0 ? $crc + 4294967296 : $crc;

        $localHeader = pack(
            'VvvvvvVVVvv',
            0x04034b50,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crcUnsigned,
            $size,
            $size,
            $nameLength,
            0
        );

        $localRecord = $localHeader . $name . $content;
        $local .= $localRecord;

        $central .= pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50,
            20,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crcUnsigned,
            $size,
            $size,
            $nameLength,
            0,
            0,
            0,
            0,
            0,
            $offset
        ) . $name;

        $offset += strlen($localRecord);
    }

    $count = count($files);
    $centralSize = strlen($central);

    $end = pack(
        'VvvvvVVv',
        0x06054b50,
        0,
        0,
        $count,
        $count,
        $centralSize,
        strlen($local),
        0
    );

    return $local . $central . $end;
}

function xmlEscape(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function columnName(int $index): string
{
    $name = '';
    while ($index > 0) {
        $index--;
        $name = chr(65 + ($index % 26)) . $name;
        $index = intdiv($index, 26);
    }
    return $name;
}

function makeWorksheet(array $data, string $displayStart, string $displayEnd): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
        . '<sheetData>';

    $xml .= '<row r="1"><c r="A1" t="inlineStr"><is><t>Food Log Report</t></is></c></row>';
    $xml .= '<row r="2"><c r="A2" t="inlineStr"><is><t>Period: '
        . xmlEscape($displayStart . ' to ' . $displayEnd)
        . '</t></is></c></row>';

    $startRow = 4;
    foreach ($data as $rowIndex => $row) {
        $rowNumber = $startRow + $rowIndex;
        $xml .= '<row r="' . $rowNumber . '">';

        foreach ($row as $colIndex => $value) {
            $cellRef = columnName($colIndex + 1) . $rowNumber;
            $text = xmlEscape($value);
            $xml .= '<c r="' . $cellRef . '" t="inlineStr">'
                . '<is><t xml:space="preserve">' . $text . '</t></is></c>';
        }

        $xml .= '</row>';
    }

    $lastRow = $startRow + count($data) - 1;
    $xml .= '</sheetData>'
        . '<autoFilter ref="A4:G' . max($lastRow, 4) . '"/>'
        . '<mergeCells count="1"><mergeCell ref="A1:G1"/></mergeCells>'
        . '</worksheet>';

    return $xml;
}

$sheet = makeWorksheet($data, $displayStart, $displayEnd);

$files = [
    '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '</Types>',

    '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>',

    'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Food Log" sheetId="1" r:id="rId1"/></sheets>'
        . '</workbook>',

    'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '</Relationships>',

    'xl/worksheets/sheet1.xml' => $sheet,
];

$xlsx = zipStore($files);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filenameBase . '.xlsx"');
header('Content-Length: ' . strlen($xlsx));
header('X-Content-Type-Options: nosniff');
echo $xlsx;
exit;
