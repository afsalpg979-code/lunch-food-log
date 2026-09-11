<?php
require __DIR__ . '/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!verifyCaptcha($_POST['captcha'] ?? '')) $error = 'Incorrect CAPTCHA answer.';
    elseif ($name === '' || mb_strlen($name) > 80) $error = 'Enter a valid name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email address.';
    elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        try {
            $stmt = $db->prepare('INSERT INTO users(name,email,password_hash,created_at,updated_at) VALUES(?,?,?,?,?)');
            $now = date('Y-m-d H:i:s');
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$now,$now]);
            $id = (int)$db->lastInsertId();
            // Preserve existing single-user data when the first account is created.
            $db->prepare('UPDATE lunches SET user_id=? WHERE user_id IS NULL')->execute([$id]);
            loginUser($id);
            header('Location: index.php?welcome=1'); exit;
        } catch (PDOException $e) { $error = $e->getCode() === '23000' ? 'An account with this email already exists.' : 'Unable to create account.'; }
    }
}
setCaptcha();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f172a"><title>Create Account · Lunch Food Log</title><style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:18px;background:linear-gradient(135deg,#eef4ff,#f8fafc);font-family:Inter,system-ui,sans-serif;color:#0f172a}.box{width:min(100%,430px);background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:28px;box-shadow:0 20px 55px #0f172a12}.logo{font-size:28px;font-weight:900}.muted{color:#64748b;font-size:13px;margin:6px 0 22px}label{display:block;font-size:12px;font-weight:800;margin:13px 0 6px}input{width:100%;padding:12px;border:1px solid #d7dee9;border-radius:11px;font:inherit}input:focus{outline:2px solid #bfdbfe;border-color:#2563eb}.captcha{background:#eff6ff;padding:12px;border-radius:11px;font-weight:900;color:#1d4ed8}button{width:100%;padding:13px;border:0;border-radius:11px;background:#0f172a;color:#fff;font-weight:800;margin-top:16px}.error{background:#fef2f2;color:#b91c1c;padding:10px;border-radius:10px;font-size:13px}.link{text-align:center;margin-top:17px;font-size:13px}.link a{color:#2563eb;font-weight:800;text-decoration:none}</style></head><body><div class="box"><div class="logo">🍽️ Create account</div><div class="muted">Secure your personal food log with your own account.</div><?php if($error):?><div class="error">⚠ <?=h($error)?></div><?php endif;?><form method="post"><label>Name</label><input name="name" maxlength="80" autocomplete="name" required value="<?=h($_POST['name']??'')?>"><label>Email</label><input type="email" name="email" maxlength="190" autocomplete="email" required value="<?=h($_POST['email']??'')?>"><label>Password</label><input type="password" name="password" minlength="8" autocomplete="new-password" required><label>Confirm password</label><input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required><label>Security check</label><div class="captcha">🤖 <?=h(captchaQuestion())?></div><input name="captcha" inputmode="numeric" placeholder="Enter answer" required><button>Create Account</button></form><div class="link">Already have an account? <a href="login.php">Log in</a></div></div></body></html>
