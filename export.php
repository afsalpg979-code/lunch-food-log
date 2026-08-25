<?php

require __DIR__ . '/database.php';

$format = $_GET['format'] ?? 'csv';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

if (!$start || !$end) {
    exit('Invalid date range.');
}

$stmt = $db->prepare(
    'SELECT lunch_date, food_item, quantity, notes
     FROM lunches
     WHERE lunch_date BETWEEN ? AND ?
     ORDER BY lunch_date ASC, id ASC'
);

$stmt->execute([$start, $end]);

$rows = $stmt->fetchAll();

if ($format === 'xlsx') {

    /*
     * Simple Excel-compatible Spreadsheet.
     * This creates an Excel-readable .xls file
     * without requiring Composer or external packages.
     */

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header(
        'Content-Disposition: attachment; filename="lunch_report_' .
        $start . '_to_' . $end . '.xls"'
    );

    echo "\xEF\xBB\xBF";

    echo "Lunch Date\tFood Item\tQuantity\tNotes\n";

    foreach ($rows as $row) {

        echo
            str_replace(["\t","\n","\r"], ' ', $row['lunch_date']) . "\t" .
            str_replace(["\t","\n","\r"], ' ', $row['food_item']) . "\t" .
            str_replace(["\t","\n","\r"], ' ', $row['quantity']) . "\t" .
            str_replace(["\t","\n","\r"], ' ', $row['notes']) . "\n";
    }

    exit;
}

header('Content-Type: text/csv; charset=UTF-8');

header(
    'Content-Disposition: attachment; filename="lunch_report_' .
    $start . '_to_' . $end . '.csv"'
);

$output = fopen('php://output', 'w');

fprintf($output, "\xEF\xBB\xBF");

fputcsv(
    $output,
    ['Lunch Date', 'Food Item', 'Quantity', 'Notes']
);

foreach ($rows as $row) {

    fputcsv(
        $output,
        [
            $row['lunch_date'],
            $row['food_item'],
            $row['quantity'],
            $row['notes']
        ]
    );
}

fclose($output);
exit;
