<?php
require __DIR__ . '/auth.php';
requireLogin();

$user = currentUser();
$error = '';
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($name === '' || mb_strlen($name) > 80) {
            $error = 'Enter a valid name (maximum 80 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $error = 'Enter a valid email address.';
        } else {
            try {
                $stmt = $db->prepare('UPDATE users SET name=?, email=?, updated_at=? WHERE id=?');
                $stmt->execute([$name, $email, date('Y-m-d H:i:s'), $user['id']]);
                $saved = true;
                $user = $db->prepare('SELECT id,name,email,created_at,updated_at FROM users WHERE id=?');
                $user->execute([$user['id']]);
                $user = $user->fetch() ?: currentUser();
            } catch (PDOException $e) {
                $error = $e->getCode() === '23000' ? 'That email address is already in use.' : 'Unable to update profile.';
            }
        }
    } elseif ($action === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare('SELECT password_hash FROM users WHERE id=?');
        $stmt->execute([$user['id']]);
        $account = $stmt->fetch();

        if (!$account || !password_verify($currentPassword, $account['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif ($newPassword === $currentPassword) {
            $error = 'New password must be different from the current password.';
        } else {
            $stmt = $db->prepare('UPDATE users SET password_hash=?, updated_at=? WHERE id=?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), date('Y-m-d H:i:s'), $user['id']]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $saved = true;
        }
    }
}

$stmt = $db->prepare('SELECT COUNT(*) AS entries, COUNT(DISTINCT lunch_date) AS days, MIN(lunch_date) AS first_date, MAX(lunch_date) AS last_date FROM lunches WHERE user_id=?');
$stmt->execute([$user['id']]);
$stats = $stmt->fetch() ?: ['entries'=>0,'days'=>0,'first_date'=>null,'last_date'=>null];

function profileDate(?string $date): string {
    if (!$date) return '—';
    $t = strtotime($date);
    return $t ? date('d-m-Y', $t) : $date;
}

$initial = strtoupper(mb_substr(trim($user['name'] ?? 'U'), 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0f172a">
<title>My Profile · Lunch Food Log</title>
<link rel="stylesheet" href="assets/app.css">
<style>
.profile-hero{display:flex;align-items:center;gap:16px}.avatar{width:72px;height:72px;border-radius:22px;display:grid;place-items:center;background:linear-gradient(135deg,#2563eb,#0f172a);color:#fff;font-size:28px;font-weight:900;box-shadow:0 12px 28px rgba(37,99,235,.22)}.hero-actions{display:flex;gap:8px;flex-wrap:wrap}.hero-actions a{display:inline-block;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.08);border-radius:11px;padding:9px 12px;font-size:12px;font-weight:800}.profile-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:15px}.section-title{margin:0;font-size:18px}.section-sub{margin:4px 0 14px;color:var(--muted);font-size:12px}.stat-icon{font-size:18px;margin-bottom:4px}.danger-note{font-size:11px;color:var(--muted);margin-top:10px}@media(max-width:700px){.profile-grid{grid-template-columns:1fr}.profile-hero{align-items:flex-start}.avatar{width:60px;height:60px;border-radius:18px;font-size:23px}}
</style>
</head>
<body>
<div class="container">
  <div class="hero">
    <div class="profile-hero">
      <div class="avatar"><?=h($initial)?></div>
      <div style="flex:1"><div class="eyebrow">Account</div><h1>My Profile</h1><p><?=h($user['email'])?></p></div>
    </div>
    <div class="hero-actions" style="margin-top:18px"><a href="index.php">← Dashboard</a><a href="reports.php">Reports</a><a href="logout.php">Sign out</a></div>
  </div>

  <?php if ($error): ?><div class="error">⚠ <?=h($error)?></div><?php elseif ($saved): ?><div class="ok">✓ Changes saved successfully.</div><?php endif; ?>

  <div class="stats">
    <div class="stat"><div class="stat-icon">🍽️</div><strong><?=h((string)$stats['entries'])?></strong><span>Total food entries</span></div>
    <div class="stat"><div class="stat-icon">📅</div><strong><?=h((string)$stats['days'])?></strong><span>Days logged</span></div>
    <div class="stat"><div class="stat-icon">🗓️</div><strong><?=h(profileDate($stats['last_date']))?></strong><span>Latest entry</span></div>
  </div>

  <div class="profile-grid">
    <section class="card">
      <div class="head"><div><h2 class="section-title">Personal details</h2><div class="section-sub">Update the name and email used for your account.</div></div></div>
      <form method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="profile">
        <label for="name">Name</label><input id="name" name="name" maxlength="80" required value="<?=h($user['name'])?>" autocomplete="name">
        <label for="email">Email</label><input id="email" type="email" name="email" maxlength="190" required value="<?=h($user['email'])?>" autocomplete="email">
        <button class="primary" type="submit">Save Profile</button>
      </form>
    </section>

    <section class="card">
      <div class="head"><div><h2 class="section-title">Change password</h2><div class="section-sub">Verify your current password before setting a new one.</div></div></div>
      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="password">
        <label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        <label for="new_password">New password</label><input id="new_password" type="password" name="new_password" minlength="8" required autocomplete="new-password">
        <label for="confirm_password">Confirm new password</label><input id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password">
        <button class="primary" type="submit">Update Password</button>
      </form>
      <div class="danger-note">Use a strong password of at least 8 characters.</div>
    </section>
  </div>

  <section class="card">
    <div class="head"><div><h2 class="section-title">Account overview</h2><div class="section-sub">Your Lunch Food Log account information.</div></div></div>
    <div class="summary-grid">
      <div class="mini"><strong><?=h(profileDate($user['created_at'] ?? null))?></strong><span>Account created</span></div>
      <div class="mini"><strong><?=h(profileDate($user['updated_at'] ?? null))?></strong><span>Profile last updated</span></div>
      <div class="mini"><strong><?=h(profileDate($stats['first_date']))?></strong><span>First food entry</span></div>
      <div class="mini"><strong><?=h(profileDate($stats['last_date']))?></strong><span>Most recent food entry</span></div>
    </div>
  </section>

  <div class="footer">Lunch Food Log · Your account data is separated by user.</div>
</div>
<div class="mobile-nav"><a href="index.php">🏠<br>Home</a><a href="reports.php">📊<br>Reports</a><a href="profile.php">👤<br>Profile</a></div>
</body>
</html>
