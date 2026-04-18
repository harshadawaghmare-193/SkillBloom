<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$user=$conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$tab=clean((isset($_GET['tab']) ? $_GET['tab'] : 'profile'));
$errors=[]; $pwErrors=[]; $success='';

// Update Profile
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])){
  $name=clean((isset($_POST['name']) ? $_POST['name'] : '')); $phone=clean((isset($_POST['phone']) ? $_POST['phone'] : '')); $bio=clean((isset($_POST['bio']) ? $_POST['bio'] : ''));
  if(strlen($name)<2) $errors['name']='Name must be at least 2 characters.';
  if(empty($errors)){
    $conn->query("UPDATE users SET name='$name',phone='$phone',bio='$bio' WHERE id=$uid");
    $_SESSION['user_name']=$name;
    flash('success','Profile updated successfully!'); redirect('profile.php?tab=profile');
  }
}
// Change Password
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['change_password'])){
  $cur=(isset($_POST['current_password']) ? $_POST['current_password'] : ''); $new=(isset($_POST['new_password']) ? $_POST['new_password'] : ''); $conf=(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');
  if(!password_verify($cur,$user['password'])) $pwErrors['current_password']='Current password is incorrect.';
  if(strlen($new)<8) $pwErrors['new_password']='Password must be at least 8 characters.';
  elseif(!preg_match('/[A-Z]/',$new)) $pwErrors['new_password']='Must contain an uppercase letter.';
  if($new!==$conf) $pwErrors['confirm_password']='Passwords do not match.';
  if(empty($pwErrors)){
    $h=password_hash($new,PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$h' WHERE id=$uid");
    flash('success','Password changed successfully!'); redirect('profile.php?tab=password');
  }
}
// Stats
$stats=[
  'enrolled'=>$conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid")->fetch_assoc()['c'],
  'completed'=>$conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid AND status='completed'")->fetch_assoc()['c'],
  'certs'=>$conn->query("SELECT COUNT(*) c FROM certificates WHERE user_id=$uid")->fetch_assoc()['c'],
];
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profile — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div class="page-layout">
<?php include dirname(__DIR__).'/includes/sidebar.php'; ?>
<main class="main-content">
<?php if($flash): ?><div class="alert alert-<?=$flash['type']==='error'?'error':'success'?>"><?=$flash['type']==='success'?'✅':'❌'?> <?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

<div class="page-header"><h2>👤 My Profile</h2><p>Manage your account details and preferences.</p></div>

<!-- Stats Row -->
<div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.75rem;">
  <div class="stat-card"><div class="stat-icon blue">📚</div><div><div class="stat-val"><?=$stats['enrolled']?></div><div class="stat-label">Enrolled</div></div></div>
  <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-val"><?=$stats['completed']?></div><div class="stat-label">Completed</div></div></div>
  <div class="stat-card"><div class="stat-icon yellow">🏆</div><div><div class="stat-val"><?=$stats['certs']?></div><div class="stat-label">Certificates</div></div></div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:.25rem;border-bottom:2px solid var(--gray-200);margin-bottom:1.75rem;">
  <a href="?tab=profile" style="padding:.55rem 1.1rem;font-weight:600;font-size:.9rem;border-bottom:3px solid <?=$tab==='profile'?'var(--primary)':'transparent'?>;color:<?=$tab==='profile'?'var(--primary)':'var(--gray-400)'?>;margin-bottom:-2px;text-decoration:none;">👤 Profile Info</a>
  <a href="?tab=password" style="padding:.55rem 1.1rem;font-weight:600;font-size:.9rem;border-bottom:3px solid <?=$tab==='password'?'var(--primary)':'transparent'?>;color:<?=$tab==='password'?'var(--primary)':'var(--gray-400)'?>;margin-bottom:-2px;text-decoration:none;">🔒 Change Password</a>
</div>

<?php if($tab==='profile'): ?>
<div class="card" style="max-width:620px;">
<div class="card-body">
  <div class="profile-avatar-box">
    <div class="profile-avatar"><?=strtoupper(substr($user['name'],0,1))?></div>
    <div>
      <div style="font-weight:700;font-size:1.1rem;"><?=htmlspecialchars($user['name'])?></div>
      <div style="font-size:.875rem;color:var(--gray-400);"><?=htmlspecialchars($user['email'])?></div>
      <div style="font-size:.8rem;color:var(--gray-400);margin-top:.2rem;">Member since <?=date('M Y',strtotime($user['created_at']))?></div>
    </div>
  </div>
  <form method="POST" class="needs-val">
    <input type="hidden" name="update_profile" value="1">
    <div class="form-group"><label class="form-label">Full Name</label>
      <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['name'])?>" required minlength="2">
      <?php if(isset($errors['name'])): ?><span class="err-msg"><?=$errors['name']?></span><?php endif; ?>
    </div>
    <div class="form-group"><label class="form-label">Email Address</label>
      <input type="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" disabled style="background:var(--gray-100);cursor:not-allowed;">
      <span class="form-hint">Email cannot be changed.</span>
    </div>
    <div class="form-group"><label class="form-label">Phone Number</label>
      <input type="tel" name="phone" class="form-control" value="<?=htmlspecialchars((isset($user['phone']) ? $user['phone'] : ''))?>" placeholder="+91 9876543210">
    </div>
    <div class="form-group"><label class="form-label">Bio</label>
      <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..."><?=htmlspecialchars((isset($user['bio']) ? $user['bio'] : ''))?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
  </form>
</div>
</div>

<?php else: ?>
<div class="card" style="max-width:500px;">
<div class="card-body">
  <form method="POST" class="needs-val">
    <input type="hidden" name="change_password" value="1">
    <div class="form-group"><label class="form-label">Current Password</label>
      <div class="pw-wrap"><input type="password" name="current_password" class="form-control" required><button type="button" class="pw-eye">👁️</button></div>
      <?php if(isset($pwErrors['current_password'])): ?><span class="err-msg"><?=$pwErrors['current_password']?></span><?php endif; ?>
    </div>
    <div class="form-group"><label class="form-label">New Password</label>
      <div class="pw-wrap"><input type="password" id="new_password" name="new_password" class="form-control" required minlength="8"><button type="button" class="pw-eye">👁️</button></div>
      <?php if(isset($pwErrors['new_password'])): ?><span class="err-msg"><?=$pwErrors['new_password']?></span><?php endif; ?>
    </div>
    <div class="form-group"><label class="form-label">Confirm New Password</label>
      <div class="pw-wrap"><input type="password" id="confirm_password" name="confirm_password" class="form-control" required><button type="button" class="pw-eye">👁️</button></div>
      <?php if(isset($pwErrors['confirm_password'])): ?><span class="err-msg"><?=$pwErrors['confirm_password']?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">🔐 Update Password</button>
  </form>
</div>
</div>
<?php endif; ?>

</main></div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
