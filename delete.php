<?php
require __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verifyCsrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid entry ID.');
}

$stmt = $db->prepare('DELETE FROM lunches WHERE id = ?');
$stmt->execute([$id]);

header('Location: index.php?deleted=1');
exit;
