<?php
require_once __DIR__ . '/database.php';

function setCaptcha(): void {
    $a = random_int(2, 9); $b = random_int(2, 9);
    $_SESSION['captcha_question'] = "$a + $b";
    $_SESSION['captcha_answer'] = (string)($a + $b);
}
function captchaQuestion(): string {
    if (empty($_SESSION['captcha_answer'])) setCaptcha();
    return $_SESSION['captcha_question'] . ' = ?';
}
function verifyCaptcha(string $answer): bool {
    $ok = hash_equals((string)($_SESSION['captcha_answer'] ?? ''), trim($answer));
    unset($_SESSION['captcha_answer'], $_SESSION['captcha_question']);
    return $ok;
}
function currentUser(): ?array {
    static $user = false;
    if ($user !== false) return $user;
    if (empty($_SESSION['user_id'])) return $user = null;
    $stmt = $GLOBALS['db']->prepare('SELECT id,name,email,created_at FROM users WHERE id=?');
    $stmt->execute([$_SESSION['user_id']]);
    return $user = ($stmt->fetch() ?: null);
}
function loginUser(int $id): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}
