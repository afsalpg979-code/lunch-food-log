<?php
require __DIR__ . '/auth.php'; requireLogin(); verifyCsrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('POST required.'); }
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if(!$id){http_response_code(400);exit('Invalid entry ID.');}
$stmt=$db->prepare('DELETE FROM lunches WHERE id=? AND user_id=?');
$stmt->execute([$id,$_SESSION['user_id']]);
header('Location: index.php?deleted=1'); exit;
