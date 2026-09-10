<?php
require __DIR__ . '/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid entry ID.');
}

$stmt = $db->prepare('DELETE FROM lunches WHERE id = ?');
$stmt->execute([$id]);

header('Location: index.php?deleted=1');
exit;
