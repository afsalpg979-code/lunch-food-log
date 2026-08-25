<?php
require __DIR__ . '/database.php';

$report = $_GET['report'] ?? '';
$year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
$start = $end = '';

if ($report === 'monthly') {
    $start = sprintf('%04d-%02d-01', $year, $month);
    $end = date('Y-m-t', strtotime($start));
} elseif ($report === 'six_months') {
    $end = date('Y-m-d');
    $start = date('Y-m-d', strtotime('-5 months', strtotime(date('Y-m-01'))));
} elseif ($report === 'yearly') {
    $start = "$year-01-01";
    $end = "$year-12-31";
} elseif ($report === 'custom') {
    $start = $_GET['start'] ?? '';
    $end = $_GET['end'] ?? '';
}

$rows = [];
if ($start && $end) {
    $stmt = $db->prepare(
        'SELECT lunch_date, meal_type, meal_time, recorded_time, food_item, quantity, notes
         FROM lunches WHERE lunch_date BETWEEN ? AND ?
         ORDER BY lunch_date ASC, meal_time ASC, id ASC'
    );
    $stmt->execute([$start, $end]);
    $rows = $stmt->fetchAll();
}
$total = count($rows);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Food Reports</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827}.container{max-width:900px;margin:auto;padding:16px}.card{background:#fff;padding:18px;margin-bottom:16px;border-radius:16px;box-shadow:0 3px 12px rgba(0,0,0,.07)}h1,h2{margin-top:0}label{display:block;font-weight:bold;margin-top:12px}input,select,button{width:100%;padding:12px;margin-top:7px;border-radius:9px;border:1px solid #d1d5db;font-size:16px}button{background:#111827;color:#fff;border:0;font-weight:bold}.download{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:15px}.download a{text-align:center;text-decoration:none;background:#111827;color:#fff;padding:13px;border-radius:9px;font-weight:bold}table{width:100%;border-collapse:collapse;margin-top:15px;font-size:14px}th,td{padding:9px;border-bottom:1px solid #e5e7eb;text-align:left}th{background:#f9fafb}.back{display:inline-block;margin-bottom:15px;text-decoration:none;color:#111827;font-weight:bold}@media(max-width:600px){table{font-size:12px}.download{grid-template-columns:1fr}}
</style></head><body><div class="container">
<a class="back" href="index.php">← Dashboard</a>
<div class="card"><h1>📊 Food Reports</h1><form method="get"><label>Report Type</label><select name="report" onchange="toggleFields(this.value)" required><option value="">Select Report</option><option value="monthly" <?= $report==='monthly'?'selected':'' ?>>Monthly Report</option><option value="six_months" <?= $report==='six_months'?'selected':'' ?>>Last 6 Months</option><option value="yearly" <?= $report==='yearly'?'selected':'' ?>>Yearly Report</option><option value="custom" <?= $report==='custom'?'selected':'' ?>>Custom Date Range</option></select>
<div id="monthYear"><label>Month</label><select name="month"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month===$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select><label>Year</label><input type="number" name="year" value="<?= $year ?>" min="2000" max="2100"></div>
<div id="customDates"><label>Start Date</label><input type="date" name="start" value="<?= htmlspecialchars($_GET['start']??'') ?>"><label>End Date</label><input type="date" name="end" value="<?= htmlspecialchars($_GET['end']??'') ?>"></div><button type="submit">Generate Report</button></form></div>
<?php if($report && $start && $end): ?><div class="card"><h2>Report Summary</h2><p><strong>Period:</strong> <?= htmlspecialchars($start) ?> to <?= htmlspecialchars($end) ?></p><p><strong>Total Entries:</strong> <?= $total ?></p><div class="download"><a href="export.php?format=csv&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">⬇️ CSV</a><a href="export.php?format=xlsx&start=<?= urlencode($start) ?>&end=<?= urlencode($end) ?>">⬇️ Excel</a></div></div>
<div class="card"><h2>Report Preview</h2><?php if(!$rows): ?><p>No food entries found.</p><?php else: ?><div style="overflow-x:auto"><table><thead><tr><th>Date</th><th>Meal Time</th><th>Food</th><th>Quantity</th><th>Recorded</th><th>Notes</th></tr></thead><tbody><?php foreach($rows as $row): ?><tr><td><?= htmlspecialchars($row['lunch_date']) ?></td><td><?= htmlspecialchars(($row['meal_type']??'Lunch').' '.($row['meal_time']??'')) ?></td><td><?= htmlspecialchars($row['food_item']) ?></td><td><?= htmlspecialchars($row['quantity']) ?></td><td><?= htmlspecialchars($row['recorded_time']??'') ?></td><td><?= htmlspecialchars($row['notes']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div><?php endif; ?></div>
<script>function toggleFields(v){document.getElementById('monthYear').style.display=(v==='monthly'||v==='yearly')?'block':'none';document.getElementById('customDates').style.display=v==='custom'?'block':'none';}toggleFields(<?= json_encode($report) ?>);</script></body></html>
