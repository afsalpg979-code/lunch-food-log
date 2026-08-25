<?php

require __DIR__ . '/database.php';

$date = $_POST['lunch_date'] ?? '';
$food = trim($_POST['food_item'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$mealType = trim($_POST['meal_type'] ?? 'Lunch');
$mealTime = trim($_POST['meal_time'] ?? '12:00-13:00');

if (!$date || !$food) {
    exit('Date and food item are required.');
}

$allowedMeals = [
    'Breakfast' => '08:00-09:00',
    'Morning Snack' => '10:30-11:00',
    'Lunch' => '12:00-13:00',
    'Evening Snack' => '16:30-17:00',
    'During Duty' => '20:00-21:00',
    'Dinner' => '22:15-22:45'
];

if (!isset($allowedMeals[$mealType])) {
    $mealType = 'Lunch';
}
$mealTime = $allowedMeals[$mealType];

$stmt = $db->prepare(
    'INSERT INTO lunches
    (lunch_date, food_item, quantity, notes, meal_type, meal_time, recorded_time)
    VALUES (?, ?, ?, ?, ?, ?, ?)'
);

$stmt->execute([
    $date,
    $food,
    $quantity,
    $notes,
    $mealType,
    $mealTime,
    date('Y-m-d H:i:s')
]);

header('Location: index.php');
exit;
