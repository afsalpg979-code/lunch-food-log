<?php
require __DIR__ . '/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid entry ID.'); }

$stmt = $db->prepare('SELECT * FROM lunches WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { http_response_code(404); exit('Food entry not found.'); }

function displayDate(string $iso): string { $t = strtotime($iso); return $t ? date('d-m-Y', $t) : $iso; }

$meals = [
    'Breakfast' => '08:00-09:00',
    'Morning Snack' => '10:30-11:00',
    'Lunch' => '12:00-13:00',
    'Evening Snack' => '16:30-17:00',
    'During Duty' => '20:00-21:00',
    'Dinner' => '22:15-22:45'
];
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f172a"><title>Edit Food Entry</title><style>
*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#0f172a;font-family:Inter,system-ui,-apple-system,"Segoe UI",Arial,sans-serif}.wrap{width:min(100% - 24px,650px);margin:auto;padding:28px 0}.card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:24px;box-shadow:0 12px 32px rgba(15,23,42,.07)}h1{margin:0 0 5px;font-size:26px}.sub{color:#64748b;font-size:13px;margin-bottom:20px}label{display:block;font-size:12px;font-weight:800;margin:13px 0 7px}input,select,textarea,button{width:100%;font:inherit;border-radius:12px;padding:12px 13px}input,select,textarea{border:1px solid #d6dde8;background:#fff}textarea{min-height:90px;resize:vertical}button{border:0;background:#0f172a;color:#fff;font-weight:800;cursor:pointer;margin-top:16px}.back{display:inline-block;margin-bottom:15px;color:#1d4ed8;text-decoration:none;font-weight:800;font-size:13px}.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:600px){.row{grid-template-columns:1fr}.card{padding:18px}}
</style></head><body><main class="wrap"><a class="back" href="index.php">← Back to Dashboard</a><section class="card"><h1>✏️ Edit Food Entry</h1><div class="sub">Update your meal details. Dates use DD-MM-YYYY.</div><form method="post" action="update.php"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars(csrfToken())?>"><input type="hidden" name="id" value="<?=htmlspecialchars((string)$item['id'])?>"><div class="row"><div><label for="date_display">Date</label><input id="date_display" value="<?=htmlspecialchars(displayDate($item['lunch_date']))?>" maxlength="10" inputmode="numeric" required><input type="hidden" name="lunch_date" id="lunch_date" value="<?=htmlspecialchars($item['lunch_date'])?>"></div><div><label for="meal_type">Meal</label><select name="meal_type" id="meal_type"><?php foreach($meals as $name=>$time):?><option value="<?=htmlspecialchars($name)?>" <?=$item['meal_type']===$name?'selected':''?>><?=htmlspecialchars($name)?></option><?php endforeach;?></select></div></div><label for="food_item">Food Item</label><input id="food_item" name="food_item" maxlength="150" value="<?=htmlspecialchars($item['food_item'])?>" required><div class="row"><div><label for="quantity">Quantity</label><input id="quantity" name="quantity" maxlength="80" value="<?=htmlspecialchars($item['quantity']??'')?>"></div><div><label for="notes">Notes</label><input id="notes" name="notes" maxlength="250" value="<?=htmlspecialchars($item['notes']??'')?>"></div></div><button type="submit">Save Changes</button></form></section></main><script>function iso(v){const m=v.trim().match(/^(\d{2})-(\d{2})-(\d{4})$/);if(!m)return null;const d=+m[1],mo=+m[2],y=+m[3],x=new Date(Date.UTC(y,mo-1,d));return x.getUTCFullYear()===y&&x.getUTCMonth()===mo-1&&x.getUTCDate()===d?`${y}-${String(mo).padStart(2,'0')}-${String(d).padStart(2,'0')}`:null;}document.querySelector('form').addEventListener('submit',e=>{const v=iso(document.getElementById('date_display').value);if(!v){e.preventDefault();alert('Please enter a valid date in DD-MM-YYYY format.');return}document.getElementById('lunch_date').value=v;});</script></body></html>
