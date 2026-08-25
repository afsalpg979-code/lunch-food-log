<?php

require __DIR__ . '/database.php';

$date = $_POST['lunch_date'] ?? '';
$food = trim($_POST['food_item'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if (!$date || !$food) {
    exit('Date and food item are required.');
}

$stmt = $db->prepare(
    'INSERT INTO lunches
    (lunch_date, food_item, quantity, notes)
    VALUES (?, ?, ?, ?)'
);

$stmt->execute([
    $date,
    $food,
    $quantity,
    $notes
]);

header('Location: index.php');
exit;
