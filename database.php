<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true)) throw new RuntimeException('Unable to create data directory.');

$db = new PDO('sqlite:' . $dataDir . '/lunch.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec('PRAGMA busy_timeout = 5000');

$db->exec("CREATE TABLE IF NOT EXISTS users (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 name TEXT NOT NULL,
 email TEXT NOT NULL UNIQUE,
 password_hash TEXT NOT NULL,
 created_at TEXT NOT NULL,
 updated_at TEXT NOT NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS lunches (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 user_id INTEGER,
 lunch_date TEXT NOT NULL,
 food_item TEXT NOT NULL,
 quantity TEXT DEFAULT '',
 notes TEXT DEFAULT '',
 meal_type TEXT DEFAULT 'Lunch',
 meal_time TEXT DEFAULT '12:00-13:00',
 recorded_time TEXT,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$columns = $db->query('PRAGMA table_info(lunches)')->fetchAll();
$names = array_column($columns, 'name');
if (!in_array('user_id', $names, true)) $db->exec('ALTER TABLE lunches ADD COLUMN user_id INTEGER');
if (!in_array('meal_type', $names, true)) $db->exec("ALTER TABLE lunches ADD COLUMN meal_type TEXT DEFAULT 'Lunch'");
if (!in_array('meal_time', $names, true)) $db->exec("ALTER TABLE lunches ADD COLUMN meal_time TEXT DEFAULT '12:00-13:00'");
if (!in_array('recorded_time', $names, true)) $db->exec('ALTER TABLE lunches ADD COLUMN recorded_time TEXT');

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) { http_response_code(403); exit('Invalid security token.'); }
}
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
}
function h(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
