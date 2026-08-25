<?php
require __DIR__ . '/database.php';

$today = date('Y-m-d');

$stmt = $db->prepare(
    'SELECT * FROM lunches WHERE lunch_date = ? ORDER BY id DESC'
);
$stmt->execute([$today]);
$lunches = $stmt->fetchAll();
$totalToday = count($lunches);

$schedule = [
    ['08:00–09:00 AM', 'Breakfast', 'Idli / dosa / chapati + egg'],
    ['10:30–11:00 AM', 'Morning Snack', 'Banana or another fruit'],
    ['12:00–01:00 PM', 'Lunch', 'Rice + vegetables + fish/chicken/egg/dal'],
    ['04:30–05:00 PM', 'Evening Snack', 'Milk + guava + peanuts/almonds'],
    ['08:00–09:00 PM', 'During Duty', '2 boiled eggs + banana'],
    ['10:15–10:45 PM', 'Dinner', 'Rice + fish/egg/chicken + vegetables']
];

function displayDate(string $iso): string {
    $time = strtotime($iso);
    return $time ? date('d-m-Y', $time) : $iso;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Log & Timetable</title>
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f3f4f6;color:#111827}.container{width:100%;max-width:760px;margin:auto;padding:16px}.header{background:#111827;color:#fff;padding:22px;border-radius:16px;margin-bottom:16px}.header h1{margin:0 0 6px;font-size:25px}.header p{margin:0;opacity:.8}.card{background:#fff;border-radius:16px;padding:18px;margin-bottom:16px;box-shadow:0 3px 12px rgba(0,0,0,.07)}.card h2{margin-top:0;font-size:19px}label{display:block;font-weight:bold;margin-top:12px}input,textarea,select,button{width:100%;padding:13px;margin-top:7px;border-radius:10px;font-size:16px}input,textarea,select{border:1px solid #d1d5db}textarea{min-height:80px;resize:vertical}button{border:0;background:#111827;color:#fff;font-weight:bold;margin-top:16px}.date-help{color:#6b7280;font-size:13px;margin-top:5px}.schedule{display:grid;gap:8px}.meal-row{display:grid;grid-template-columns:125px 1fr;gap:10px;padding:11px;background:#f9fafb;border-radius:10px}.meal-time{font-weight:bold}.meal-food{color:#4b5563;font-size:14px}.item{padding:12px 0;border-bottom:1px solid #e5e7eb}.item:last-child{border-bottom:0}.food{font-weight:bold;font-size:17px}.meta{color:#374151;margin-top:4px}.quantity{color:#6b7280;margin-top:4px}.notes{color:#4b5563;margin-top:5px}.nav{display:grid;grid-template-columns:1fr 1fr;gap:10px}.nav a{text-decoration:none;text-align:center;background:#eef2ff;color:#111827;padding:13px;border-radius:10px;font-weight:bold}.empty{color:#6b7280;text-align:center;padding:15px}.alert-banner{position:fixed;left:16px;right:16px;bottom:16px;max-width:728px;margin:auto;background:#111827;color:white;padding:16px;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,.25);transform:translateY(150%);transition:.3s;z-index:20}.alert-banner.show{transform:translateY(0)}.notify-btn{background:#374151;margin-top:10px}@media(max-width:500px){.meal-row{grid-template-columns:1fr}.nav{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="container">
<div class="header"><h1>🍽️ Food Log & Timetable</h1><p>Track your meals and get time-based reminders.</p></div>

<div class="card">
<h2>⏰ Daily Food Timetable</h2>
<div class="schedule">
<?php foreach ($schedule as $meal): ?>
<div class="meal-row"><div class="meal-time"><?= htmlspecialchars($meal[0]) ?><br><strong><?= htmlspecialchars($meal[1]) ?></strong></div><div class="meal-food"><?= htmlspecialchars($meal[2]) ?></div></div>
<?php endforeach; ?>
</div>
<button type="button" class="notify-btn" onclick="requestMealNotifications(); alert('Notification permission requested. Keep this page open for time reminders.');">🔔 Enable Meal Notifications</button>
</div>

<div class="card">
<h2>Add Food Entry</h2>
<form method="post" action="save.php" id="foodForm">
<label>Date</label>
<input type="text" id="date_display" value="<?= htmlspecialchars(displayDate($today)) ?>" placeholder="DD-MM-YYYY" inputmode="numeric" maxlength="10" autocomplete="off" required>
<input type="hidden" name="lunch_date" id="lunch_date" value="<?= htmlspecialchars($today) ?>">
<div class="date-help">Enter date as DD-MM-YYYY, for example: 26-08-2026</div>
<label>Meal Time</label>
<select name="meal_type" required>
<option value="Breakfast">08:00–09:00 AM — Breakfast</option>
<option value="Morning Snack">10:30–11:00 AM — Morning Snack</option>
<option value="Lunch" selected>12:00–01:00 PM — Lunch</option>
<option value="Evening Snack">04:30–05:00 PM — Evening Snack</option>
<option value="During Duty">08:00–09:00 PM — During Duty</option>
<option value="Dinner">10:15–10:45 PM — Dinner</option>
</select>
<label>Food Item</label><input type="text" name="food_item" placeholder="Example: Rice + Fish Curry" required>
<label>Quantity</label><input type="text" name="quantity" placeholder="Example: 1 plate">
<label>Notes</label><textarea name="notes" placeholder="Optional notes"></textarea>
<button type="submit">Save Food Entry</button>
</form>
</div>

<div class="card"><h2>Today's Entries (<?= $totalToday ?>)</h2>
<?php if (!$lunches): ?><div class="empty">No food recorded for <?= htmlspecialchars(displayDate($today)) ?>.</div>
<?php else: foreach ($lunches as $item): ?>
<div class="item"><div class="food"><?= htmlspecialchars($item['food_item']) ?></div><div class="meta">📅 <?= htmlspecialchars(displayDate($item['lunch_date'])) ?> · 🕐 <?= htmlspecialchars($item['meal_type'] ?? 'Lunch') ?> · <?= htmlspecialchars($item['meal_time'] ?? '') ?></div><?php if ($item['quantity']): ?><div class="quantity">Quantity: <?= htmlspecialchars($item['quantity']) ?></div><?php endif; ?><?php if ($item['notes']): ?><div class="notes"><?= htmlspecialchars($item['notes']) ?></div><?php endif; ?></div>
<?php endforeach; endif; ?></div>

<div class="card"><h2>Reports & History</h2><div class="nav"><a href="reports.php">📊 Reports</a><a href="export.php">⬇️ Export</a></div></div>
</div>
<div id="mealAlertBanner" class="alert-banner"></div>
<script src="assets/meal-alert.js"></script>
<script>
function isoFromDisplay(value) {
    const m = value.trim().match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (!m) return null;
    const d = Number(m[1]), mo = Number(m[2]), y = Number(m[3]);
    const date = new Date(Date.UTC(y, mo - 1, d));
    if (date.getUTCFullYear() !== y || date.getUTCMonth() !== mo - 1 || date.getUTCDate() !== d) return null;
    return `${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
}

document.getElementById('foodForm').addEventListener('submit', function(e) {
    const iso = isoFromDisplay(document.getElementById('date_display').value);
    if (!iso) {
        e.preventDefault();
        alert('Please enter a valid date in DD-MM-YYYY format.');
        return;
    }
    document.getElementById('lunch_date').value = iso;
});
</script>
</body></html>
