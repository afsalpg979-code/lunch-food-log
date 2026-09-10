<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true)) {
    throw new RuntimeException('Unable to create the data directory.');
}

$db = new PDO('sqlite:' . $dataDir . '/lunch.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec('PRAGMA busy_timeout = 5000');

// Create the table automatically for a fresh clone/install.
$db->exec("CREATE TABLE IF NOT EXISTS lunches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lunch_date TEXT NOT NULL,
    food_item TEXT NOT NULL,
    quantity TEXT DEFAULT '',
    notes TEXT DEFAULT '',
    meal_type TEXT DEFAULT 'Lunch',
    meal_time TEXT DEFAULT '12:00-13:00',
    recorded_time TEXT
)");

// Add meal timetable fields to older databases without deleting existing data.
$columns = $db->query('PRAGMA table_info(lunches)')->fetchAll();
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
