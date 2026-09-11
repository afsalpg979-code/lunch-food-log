<?php
require __DIR__ . '/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!verifyCaptcha($_POST['captcha'] ?? '')) $error = 'Incorrect CAPTCHA answer.';
    else {
        $stmt = $db->prepare('SELECT id,password_hash FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]); $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) $error = 'Invalid email or password.';
        else { loginUser((int)$user['id']); header('Location: index.php'); exit; }
    }
}
setCaptcha();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f172a"><title>Login · Lunch Food Log</title><style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:18px;background:linear-gradient(135deg,#eef4ff,#f8fafc);font-family:Inter,system-ui,sans-serif;color:#0f172a}.box{width:min(100%,430px);background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:28px;box-shadow:0 20px 55px #0f172a12}.logo{font-size:28px;font-weight:900}.muted{color:#64748b;font-size:13px;margin:6px 0 22px}label{display:block;font-size:12px;font-weight:800;margin:13px 0 6px}input{width:100%;padding:12px;border:1px solid #d7dee9;border-radius:11px;font:inherit}.captcha{background:#eff6ff;padding:12px;border-radius:11px;font-weight:900;color:#1d4ed8}button{width:100%;padding:13px;border:0;border-radius:11px;background:#0f172a;color:#fff;font-weight:800;margin-top:16px}.error{background:#fef2f2;color:#b91c1c;padding:10px;border-radius:10px;font-size:13px}.link{text-align:center;margin-top:17px;font-size:13px}.link a{color:#2563eb;font-weight:800;text-decoration:none}</style></head><body><div class="box"><div class="logo">🍽️ Welcome back</div><div class="muted">Log in to access your personal food records.</div><?php if($error):?><div class="error">⚠ <?=h($error)?></div><?php endif;?><form method="post"><label>Email</label><input type="email" name="email" autocomplete="email" required value="<?=h($_POST['email']??'')?>"><label>Password</label><input type="password" name="password" autocomplete="current-password" required><label>Security check</label><div class="captcha">🤖 <?=h(captchaQuestion())?></div><input name="captcha" inputmode="numeric" placeholder="Enter answer" required><button>Log In</button></form><div class="link">New user? <a href="signup.php">Create account</a></div></div></body></html>
