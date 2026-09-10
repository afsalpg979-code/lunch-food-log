<?php
require __DIR__ . '/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid entry ID.');
}

$stmt = $db->prepare('SELECT * FROM lunches WHERE id = ?');
$stmt->execute([$id]);
$entry = $stmt->fetch();
if (!$entry) {
    http_response_code(404);
    exit('Food entry not found.');
}

$allowedMeals = [
    'Breakfast' => '08:00-09:00',
    'Morning Snack' => '10:30-11:00',
    'Lunch' => '12:00-13:00',
    'Evening Snack' => '16:30-17:00',
    'During Duty' => '20:00-21:00',
    'Dinner' => '22:15-22:45'
];

function displayDate(string $iso): string {
    $d = DateTime::createFromFormat('Y-m-d', $iso);
    return $d ? $d->format('d-m-Y') : $iso;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#172554"><title>Edit Food Entry</title>
<style>
:root{--bg:#f5f7fb;--card:#fff;--text:#172033;--muted:#687386;--line:#e5e9f0;--primary:#172554;--blue:#2563eb;--shadow:0 12px 32px rgba(15,23,42,.08)}*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,"Segoe UI",Arial,sans-serif}.wrap{width:min(100% - 24px,760px);margin:auto;padding:18px 0 40px}.top{display:flex;justify-content:space-between;margin-bottom:14px}.back{color:var(--primary);font-weight:800;text-decoration:none}.hero{background:linear-gradient(135deg,#172554,#2563eb);color:#fff;border-radius:22px;padding:24px;margin-bottom:16px;box-shadow:var(--shadow)}.hero h1{margin:0 0 5px;font-size:27px}.hero p{margin:0;color:#dbeafe}.card{background:var(--card);border:1px solid var(--line);border-radius:20px;padding:22px;box-shadow:var(--shadow)}label{display:block;font-size:13px;font-weight:800;margin:14px 0 7px}input,select,textarea,button{width:100%;font:inherit;border-radius:13px;padding:12px 13px}input,select,textarea{border:1px solid #d7dce5;background:#fff;color:var(--text)}textarea{min-height:100px;resize:vertical}.grid{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}.full{grid-column:1/-1}.actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:18px}button{border:0;background:var(--primary);color:#fff;font-weight:800;cursor:pointer}.cancel{text-align:center;text-decoration:none;background:#eef2ff;color:#1e3a8a;border-radius:13px;padding:12px;font-weight:800}@media(max-width:600px){.grid{grid-template-columns:1fr}.actions{grid-template-columns:1fr}}
</style></head>
<body><main class="wrap"><div class="top"><a class="back" href="index.php">← Dashboard</a><span style="color:var(--muted);font-size:12px">Edit entry</span></div>
<section class="hero"><h1>✏️ Edit Food Entry</h1><p>Update the date, meal, food, quantity or notes.</p></section>
<section class="card"><form method="post" action="update.php"><input type="hidden" name="id" value="<?=htmlspecialchars((string)$entry['id'])?>">
<div class="grid"><div><label>Date</label><input type="text" name="lunch_date" value="<?=htmlspecialchars(displayDate($entry['lunch_date']))?>" placeholder="DD-MM-YYYY" inputmode="numeric" maxlength="10" required></div>
<div><label>Meal</label><select name="meal_type"><?php foreach($allowedMeals as $name=>$time):?><option value="<?=htmlspecialchars($name)?>" <?=$entry['meal_type']===$name?'selected':''?>><?=htmlspecialchars($name)?> · <?=htmlspecialchars($time)?></option><?php endforeach;?></select></div>
<div class="full"><label>Food Item</label><input type="text" name="food_item" value="<?=htmlspecialchars($entry['food_item'])?>" maxlength="200" required></div>
<div><label>Quantity</label><input type="text" name="quantity" value="<?=htmlspecialchars($entry['quantity']??'')?>" maxlength="100"></div>
<div><label>Notes</label><textarea name="notes" maxlength="1000"><?=htmlspecialchars($entry['notes']??'')?></textarea></div></div>
<div class="actions"><a class="cancel" href="index.php">Cancel</a><button type="submit">Save Changes</button></div></form></section></main></body></html>
