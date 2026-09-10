<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new PDO('sqlite:' . __DIR__ . '/data/lunch.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON');

// Add meal timetable fields to existing databases without deleting old data.
$columns = $db->query("PRAGMA table_info(lunches)")->fetchAll();
$columnNames = array_column($columns, 'name');

if (!in_array('meal_type', $columnNames, true)) {
    $db->exec("ALTER TABLE lunches ADD COLUMN meal_type TEXT DEFAULT 'Lunch'");
}
if (!in_array('meal_time', $columnNames, true)) {
    $db->exec("ALTER TABLE lunches ADD COLUMN meal_time TEXT DEFAULT '12:00-13:00'");
}
if (!in_array('recorded_time', $columnNames, true)) {
    $db->exec("ALTER TABLE lunches ADD COLUMN recorded_time TEXT");
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid security token. Please go back and try again.');
    }
}
