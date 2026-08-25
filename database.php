<?php

$db = new PDO(
    'sqlite:' . __DIR__ . '/data/lunch.sqlite'
);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

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
