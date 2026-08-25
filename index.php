<?php
require __DIR__ . '/database.php';

$today = date('Y-m-d');

$stmt = $db->prepare(
    'SELECT * FROM lunches WHERE lunch_date = ? ORDER BY id DESC'
);
$stmt->execute([$today]);
$lunches = $stmt->fetchAll();

$totalToday = count($lunches);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lunch Food Log</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f3f4f6;
    color: #111827;
}

.container {
    width: 100%;
    max-width: 700px;
    margin: auto;
    padding: 16px;
}

.header {
    background: #111827;
    color: white;
    padding: 22px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.header h1 {
    margin: 0 0 6px;
    font-size: 25px;
}

.header p {
    margin: 0;
    opacity: .8;
}

.card {
    background: white;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 16px;
    box-shadow: 0 3px 12px rgba(0,0,0,.07);
}

.card h2 {
    margin-top: 0;
    font-size: 19px;
}

label {
    display: block;
    font-weight: bold;
    margin-top: 12px;
}

input,
textarea,
button {
    width: 100%;
    padding: 13px;
    margin-top: 7px;
    border-radius: 10px;
    font-size: 16px;
}

input,
textarea {
    border: 1px solid #d1d5db;
}

textarea {
    min-height: 80px;
    resize: vertical;
}

button {
    border: 0;
    background: #111827;
    color: white;
    font-weight: bold;
    margin-top: 16px;
}

.item {
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.item:last-child {
    border-bottom: 0;
}

.food {
    font-weight: bold;
    font-size: 17px;
}

.quantity {
    color: #6b7280;
    margin-top: 4px;
}

.notes {
    color: #4b5563;
    margin-top: 5px;
}

.nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.nav a {
    text-decoration: none;
    text-align: center;
    background: #eef2ff;
    color: #111827;
    padding: 13px;
    border-radius: 10px;
    font-weight: bold;
}

.empty {
    color: #6b7280;
    text-align: center;
    padding: 15px;
}
</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>🍽️ Lunch Food Log</h1>
        <p>Track your daily lunch and view your history.</p>
    </div>

    <div class="card">
        <h2>Add Lunch</h2>

        <form method="post" action="save.php">

            <label>Date</label>
            <input
                type="date"
                name="lunch_date"
                value="<?= htmlspecialchars($today) ?>"
                required
            >

            <label>Food Item</label>
            <input
                type="text"
                name="food_item"
                placeholder="Example: Rice + Fish Curry"
                required
            >

            <label>Quantity</label>
            <input
                type="text"
                name="quantity"
                placeholder="Example: 1 plate"
            >

            <label>Notes</label>
            <textarea
                name="notes"
                placeholder="Optional notes"
            ></textarea>

            <button type="submit">Save Lunch</button>

        </form>
    </div>

    <div class="card">
        <h2>Today's Lunch</h2>

        <?php if (!$lunches): ?>

            <div class="empty">
                No lunch recorded for today.
            </div>

        <?php else: ?>

            <?php foreach ($lunches as $item): ?>

                <div class="item">

                    <div class="food">
                        <?= htmlspecialchars($item['food_item']) ?>
                    </div>

                    <?php if ($item['quantity']): ?>
                        <div class="quantity">
                            Quantity:
                            <?= htmlspecialchars($item['quantity']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($item['notes']): ?>
                        <div class="notes">
                            <?= htmlspecialchars($item['notes']) ?>
                        </div>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="card">
        <h2>Reports & History</h2>

        <div class="nav">
            <a href="history.php">📅 History</a>
            <a href="reports.php">📊 Reports</a>
        </div>
    </div>

</div>

</body>
</html>
