<?php
require __DIR__ . '/database.php';

// Dashboard currently uses a confirmation link. Keep GET support for compatibility,
// but require the same session CSRF token before deleting anything.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = $_GET['csrf_token'] ?? '';

if (!$id) {
    http_response_code(400);
    exit('Invalid entry ID.');
}

if (!is_string($token) || !$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    exit('Invalid security token.');
}

$stmt = $db->prepare('DELETE FROM lunches WHERE id = ?');
$stmt->execute([$id]);

header('Location: index.php?deleted=1');
exit;
