<?php
require __DIR__ . '/auth.php'; requireLogin();
$user = currentUser(); $error=''; $saved=false;

function profileInitials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($parts as $part) { if ($part !== '') $initials .= mb_strtoupper(mb_substr($part, 0, 1)); }
    return mb_substr($initials ?: 'U', 0, 2);
}
function profilePhotoPath(?string $photo): ?string {
    if (!$photo || !preg_match('/^profile_[a-f0-9]{16}\.\.(?:jpg|jpeg|png|webp)$/', $photo)) return null;
    $full = __DIR__ . '/uploads/' . $photo;
    return is_file($full) ? 'uploads/' . $photo : null;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'profile';
    try {
        if ($action === 'photo') {
            if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Please choose a photo.');
            $file = $_FILES['profile_photo'];
            if ($file['size'] > 3 * 1024 * 1024) throw new RuntimeException('Photo must be 3 MB or smaller.');
            $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($file['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) throw new RuntimeException('Use a JPG, PNG, or WebP image.');
            $dir = __DIR__ . '/uploads'; if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('Unable to create upload folder.');
            $new = 'profile_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $new)) throw new RuntimeException('Unable to save the photo.');
            $old = $user['profile_photo'] ?? null;
            $stmt=$db->prepare('UPDATE users SET profile_photo=?,updated_at=? WHERE id=?'); $stmt->execute([$new,date('Y-m-d H:i:s'),$user['id']]);
            if ($old && preg_match('/^profile_[a-f0-9]{16}\.(?:jpg|jpeg|png|webp)$/',$old)) @unlink($dir.'/'.$old);
            $saved=true; $user=currentUser();
        } elseif ($action === 'remove_photo') {
            $old = $user['profile_photo'] ?? null;
            $stmt=$db->prepare('UPDATE users SET profile_photo=NULL,updated_at=? WHERE id=?'); $stmt->execute([date('Y-m-d H:i:s'),$user['id']]);
            if ($old && preg_match('/^profile_[a-f0-9]{16}\.(?:jpg|jpeg|png|webp)$/',$old)) @unlink(__DIR__.'/uploads/'.$old);
            $saved=true; $user=currentUser();
        } else {
            $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $currentPassword=$_POST['current_password']??''; $newPassword=$_POST['new_password']??''; $confirmPassword=$_POST['confirm_password']??'';
            if($name===''||mb_strlen($name)>80) $error='Enter a valid name.';
            elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Enter a valid email.';
            elseif($newPassword!=='' && !password_verify($currentPassword,$user['password_hash'] ?? '')) $error='Enter your current password to change the password.';
            elseif($newPassword!=='' && strlen($newPassword)<8) $error='New password must be at least 8 characters.';
            elseif($newPassword!=='' && $newPassword!==$confirmPassword) $error='New password and confirmation do not match.';
            elseif($newPassword!=='' && password_verify($newPassword,$user['password_hash'] ?? '')) $error='New password must be different from your current password.';
            else {
                if($newPassword!=='') { $stmt=$db->prepare('UPDATE users SET name=?,email=?,password_hash=?,updated_at=? WHERE id=?'); $stmt->execute([$name,$email,password_hash($newPassword,PASSWORD_DEFAULT),date('Y-m-d H:i:s'),$user['id']]); session_regenerate_id(true); $_SESSION['user_id']=$user['id']; }
                else { $stmt=$db->prepare('UPDATE users SET name=?,email=?,updated_at=? WHERE id=?'); $stmt->execute([$name,$email,date('Y-m-d H:i:s'),$user['id']]); }
                $saved=true; $user=currentUser();
            }
        }
    } catch (PDOException $e) { $error=$e->getCode()==='23000'?'That email is already in use.':'Unable to update profile.'; }
      catch (Throwable $e) { $error=$e->getMessage(); }
}

$photo = profilePhotoPath($user['profile_photo'] ?? null);
$initials = profileInitials($user['name'] ?? 'User');
$created = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '—';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#0f172a"><title>Profile · Lunch Food Log</title><link rel="stylesheet" href="assets/app.css"><style>
body{background:#f5f7fb}.profile-wrap{width:min(100% - 16px,720px);margin:0 auto;padding:10px 0 80px}.profile-hero{position:relative;overflow:hidden;background:linear-gradient(145deg,#0f172a,#1d4ed8);color:#fff;border-radius:28px;padding:28px 20px 25px;box-shadow:0 18px 45px rgba(15,23,42,.18)}.profile-hero:after{content:'';position:absolute;width:180px;height:180px;border-radius:50%;right:-70px;top:-80px;background:rgba(255,255,255,.08)}.profile-top{position:relative;z-index:1;display:flex;align-items:center;gap:17px}.avatar{width:92px;height:92px;flex:0 0 92px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,.8);background:#dbeafe;color:#1e3a8a;display:grid;place-items:center;font-size:28px;font-weight:900;box-shadow:0 8px 24px rgba(0,0,0,.2)}.profile-name{font-size:25px;font-weight:900;line-height:1.1}.profile-email{font-size:13px;color:#dbeafe;margin-top:6px;word-break:break-word}.profile-badge{display:inline-block;margin-top:10px;padding:5px 9px;border-radius:99px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800}.photo-actions{position:relative;z-index:2;display:flex;gap:8px;margin-top:20px}.photo-btn{border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.1);color:#fff;padding:10px 12px;border-radius:12px;font-weight:800;font-size:12px;cursor:pointer}.photo-btn.danger{color:#fecaca}.section{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:18px;margin-top:14px;box-shadow:0 12px 35px rgba(15,23,42,.06)}.section-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}.section-icon{width:36px;height:36px;border-radius:11px;background:#eff6ff;display:grid;place-items:center}.section h2{font-size:17px;margin:0}.section p{margin:3px 0 0;color:#64748b;font-size:11px}.account-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #eef2f7}.account-row:last-child{border-bottom:0}.account-row span{font-size:12px;color:#64748b}.account-row strong{font-size:12px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}.full{grid-column:1/-1}.save-profile{width:100%;border:0;background:#0f172a;color:#fff;font-weight:900;padding:13px;border-radius:13px;margin-top:15px;cursor:pointer}.password-note{background:#f8fafc;border-radius:12px;padding:10px;font-size:11px;color:#64748b;margin-top:12px}.danger-zone{display:flex;justify-content:space-between;align-items:center;gap:12px}.back{display:block;text-align:center;text-decoration:none;color:#334155;font-weight:800;font-size:12px;margin-top:14px;padding:13px}.flash{margin-bottom:14px}@media(max-width:600px){.profile-wrap{padding-top:6px}.profile-hero{border-radius:23px}.profile-top{gap:13px}.avatar{width:78px;height:78px;flex-basis:78px;font-size:23px}.profile-name{font-size:21px}.form-grid{grid-template-columns:1fr}.full{grid-column:auto}.photo-actions{flex-wrap:wrap}.photo-btn{flex:1}.section{padding:16px}}
</style></head><body><main class="profile-wrap">
<section class="profile-hero"><div class="profile-top"><div class="avatar"><?php if($photo):?><img src="<?=h($photo)?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%" loading="lazy"><?php else:?><?=h($initials)?><?php endif;?></div><div><div class="profile-name"><?=h($user['name'])?></div><div class="profile-email"><?=h($user['email'])?></div><span class="profile-badge">✓ ACCOUNT ACTIVE</span></div></div>
<div class="photo-actions"><form method="post" enctype="multipart/form-data" style="display:flex;gap:8px;flex:1"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="photo"><label class="photo-btn" style="display:block;text-align:center;flex:1">📷 Change Photo<input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()" style="display:none"></label></form><?php if($photo):?><form method="post"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="remove_photo"><button class="photo-btn danger" type="submit">Remove</button></form><?php endif;?></div></section>
<?php if($error):?><div class="error flash">⚠ <?=h($error)?></div><?php elseif($saved):?><div class="ok flash">✓ Profile updated successfully.</div><?php endif;?>
<section class="section"><div class="section-head"><div class="section-icon">👤</div><div><h2>Personal information</h2><p>Keep your account details up to date</p></div></div><form method="post"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="profile"><div class="form-grid"><div><label>Name</label><input name="name" maxlength="80" required value="<?=h($user['name'])?>"></div><div><label>Email</label><input type="email" name="email" maxlength="190" required value="<?=h($user['email'])?>"></div></div><button class="save-profile">Save Profile</button></form></section>
<section class="section"><div class="section-head"><div class="section-icon">🔐</div><div><h2>Security</h2><p>Change your password securely</p></div></div><form method="post"><input type="hidden" name="csrf_token" value="<?=h(csrfToken())?>"><input type="hidden" name="action" value="profile"><label>Current password</label><input type="password" name="current_password" autocomplete="current-password" placeholder="Required only for password change"><div class="form-grid"><div><label>New password</label><input type="password" name="new_password" minlength="8" autocomplete="new-password" placeholder="Minimum 8 characters"></div><div><label>Confirm new password</label><input type="password" name="confirm_password" minlength="8" autocomplete="new-password" placeholder="Repeat new password"></div></div><div class="password-note">🔒 Leave all password fields empty if you only want to update your profile details.</div><button class="save-profile">Update Security</button></form></section>
<section class="section"><div class="section-head"><div class="section-icon">ℹ️</div><div><h2>Account</h2><p>Your Lunch Food Log account</p></div></div><div class="account-row"><span>Member since</span><strong><?=h($created)?></strong></div><div class="account-row"><span>Profile status</span><strong>Active</strong></div></section>
<section class="section danger-zone"><div><strong style="font-size:13px">Ready to continue?</strong><div class="muted" style="margin-top:3px">Return to your food dashboard.</div></div><a class="action primary" href="index.php">Dashboard →</a></section><a class="back" href="reports.php">View Reports</a></main></body></html>