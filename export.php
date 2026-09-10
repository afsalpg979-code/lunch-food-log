<?php
require __DIR__ . '/database.php';

$format = $_GET['format'] ?? 'csv';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

function displayDate(string $iso): string {
    $time = strtotime($iso);
    return $time ? date('d-m-Y', $time) : $iso;
}

function cleanCell($value): string {
    return str_replace(["\t", "\n", "\r"], ' ', trim((string)$value));
}

function validIsoDate(string $value): bool {
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date) return false;
    $errors = DateTime::getLastErrors();
    return (!$errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
        && $date->format('Y-m-d') === $value;
}

if (!validIsoDate($start) || !validIsoDate($end) || $start > $end) {
    http_response_code(400);
    exit('Invalid date range.');
}

$stmt = $db->prepare(
    'SELECT lunch_date, meal_type, meal_time, recorded_time, food_item, quantity, notes
     FROM lunches WHERE lunch_date BETWEEN ? AND ?
     ORDER BY lunch_date ASC, meal_time ASC, id ASC'
);
$stmt->execute([$start, $end]);
$rows = $stmt->fetchAll();

$displayStart = displayDate($start);
$displayEnd = displayDate($end);
$filenameBase = 'food_report_' . $displayStart . '_to_' . $displayEnd;

if ($format === 'xlsx') {
    // This is Excel-compatible tab-separated output, intentionally saved as .xls.
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filenameBase . '.xls"');
    echo "\xEF\xBB\xBF";
    echo "Food Log Report\n";
    echo "Period\t" . cleanCell($displayStart) . " to " . cleanCell($displayEnd) . "\n\n";
    echo "Date\tMeal\tMeal Time\tRecorded Time\tFood Item\tQuantity\tNotes\n";
    foreach ($rows as $row) {
        echo implode("\t", array_map('cleanCell', [
            displayDate($row['lunch_date']), $row['meal_type'], $row['meal_time'],
            $row['recorded_time'], $row['food_item'], $row['quantity'], $row['notes']
        ])) . "\n";
    }
    exit;
}

if ($format !== 'csv') {
    http_response_code(400);
    exit('Unsupported export format.');
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filenameBase . '.csv"');
$output = fopen('php://output', 'w');
fprintf($output, "\xEF\xBB\xBF");
fputcsv($output, ['Date','Meal','Meal Time','Recorded Time','Food Item','Quantity','Notes']);
foreach ($rows as $row) {
    fputcsv($output, [displayDate($row['lunch_date']), $row['meal_type'], $row['meal_time'], $row['recorded_time'], $row['food_item'], $row['quantity'], $row['notes']]);
}
fclose($output);
exit;
