<?php
require __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$dateDisplay = trim($_POST['lunch_date'] ?? '');
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

if (!$id || !preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dateDisplay, $m)) {
    http_response_code(400);
    exit('Please enter a valid date in DD-MM-YYYY format.');
}

$date = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
$parsed = DateTime::createFromFormat('Y-m-d', $date);
if (!$parsed || $parsed->format('Y-m-d') !== $date || $food === '') {
    http_response_code(400);
    exit('Please enter a valid date and food item.');
}

if (!isset($allowedMeals[$mealType])) $mealType = 'Lunch';
$mealTime = $allowedMeals[$mealType];

$stmt = $db->prepare('UPDATE lunches SET lunch_date=?, food_item=?, quantity=?, notes=?, meal_type=?, meal_time=? WHERE id=?');
$stmt->execute([$date, $food, $quantity, $notes, $mealType, $mealTime, $id]);

header('Location: index.php?updated=1');
exit;
