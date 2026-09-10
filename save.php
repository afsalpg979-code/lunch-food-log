<?php
require __DIR__ . '/database.php';

$date = trim($_POST['lunch_date'] ?? '');
$food = trim($_POST['food_item'] ?? '');
$quantity = trim($_POST['quantity'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$mealType = trim($_POST['meal_type'] ?? 'Lunch');

$allowedMeals = [
    'Breakfast' => '08:00-09:00',
    'Morning Snack' => '10:30-11:00',
    'Lunch' => '12:00-13:00',
    'Evening Snack' => '16:30-17:00',
    'During Duty' => '20:00-21:00',
    'Dinner' => '22:15-22:45'
];

$parsed = DateTime::createFromFormat('Y-m-d', $date);
if (!$parsed || $parsed->format('Y-m-d') !== $date || $food === '') {
    http_response_code(400);
    exit('Please enter a valid date and food item.');
}

if (!isset($allowedMeals[$mealType])) {
    $mealType = 'Lunch';
}
$mealTime = $allowedMeals[$mealType];

$stmt = $db->prepare(
    'INSERT INTO lunches
    (lunch_date, food_item, quantity, notes, meal_type, meal_time, recorded_time)
    VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$date, $food, $quantity, $notes, $mealType, $mealTime, date('Y-m-d H:i:s')]);

header('Location: index.php?saved=1');
exit;
