<?php
require __DIR__ . '/auth.php'; requireLogin();
$user = currentUser(); $error=''; $saved=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verifyCsrf(); $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $newPassword=$_POST['new_password']??'';
 if($name===''||mb_strlen($name)>80) $error='Enter a valid name.';
 elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Enter a valid email.';
 elseif($newPassword!==''&&strlen($newPassword)<8) $error='New password must be at least 8 characters.';
 else { try {
   if($newPassword!=='') { $stmt=$db->prepare('UPDATE users SET name=?,email=?,password_hash=?,updated_at=? WHERE id=?'); $stmt->execute([$name,$email,password_hash($newPassword,PASSWORD_DEFAULT),date('Y-m-d H:i:s'),$user['id']]); }
   else { $stmt=$db->prepare('UPDATE users SET name=?,email=?,updated_at=? WHERE id=?'); $stmt->execute([$name,$email,date('Y-m-d H:i:s'),$user['id']]); }
   $saved=true; $user=currentUser();
 } catch(PDOException $e){$error=$e->getCode()==='23000'?'That email is already in use.':'Unable to update profile.';} }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Profile · Lunch Food Log</title><style>*{box-sizing:border-box}body{margin:0;background:#f4f7fb;font-family:Inter,system-ui,sans-serif;color:#0f172a}.wrap{width:min(100% - 20px,620px);margin:30px auto}.card{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:24px;box-shadow:0 15px 40px #0f172a0d}h1{margin:0 0 6px}.muted{color:#64748b;font-size:13px}label{display:block;font-size:12px;font-weight:800;margin:16px 0 6px}input{width:100%;padding:12px;border:1px solid #d7dee9;border-radius:11px;font:inherit}button,.nav{display:inline-block;padding:12px 15px;border-radius:11px;border:0;background:#0f172a;color:#fff;font-weight:800;text-decoration:none;margin-top:16px}.nav{background:#e2e8f0;color:#0f172a;margin-left:6px}.error,.ok{padding:11px;border-radius:10px;margin:15px 0;font-size:13px}.error{background:#fef2f2;color:#b91c1c}.ok{background:#ecfdf5;color:#166534}</style></head><body><div class="wrap"><div class="card"><h1>👤 My Profile</h1><div class="muted">Manage your account details and password.</div><?php if($error):?><div class="error">⚠ <?=h($error)?></div><?php elseif($saved):?><div class="ok">✓ Profile updated successfully.</div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><label>Name</label><input name="name" maxlength="80" required value="<?=h($user['name'])?>"><label>Email</label><input type="email" name="email" maxlength="190" required value="<?=h($user['email'])?>"><label>New password <span class="muted">(leave blank to keep current)</span></label><input type="password" name="new_password" minlength="8" autocomplete="new-password"><button>Save Changes</button><a class="nav" href="index.php">← Dashboard</a></form></div></div></body></html>
