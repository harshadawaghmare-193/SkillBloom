<?php
require_once dirname(__DIR__).'/config.php';
if(isAdmin()) redirect(SITE_URL.'/admin/dashboard.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=clean((isset($_POST['email']) ? $_POST['email'] : '')); $pw=(isset($_POST['password']) ? $_POST['password'] : '');
  if(empty($email)||empty($pw)){ $error='Please enter credentials.'; }
  else{
    $s=$conn->prepare("SELECT id,name,email,password FROM admins WHERE email=?");
    $s->bind_param('s',$email); $s->execute(); $a=$s->get_result()->fetch_assoc();
    if($a && password_verify($pw,$a['password'])){
      $_SESSION['admin_id']=$a['id']; $_SESSION['admin_name']=$a['name']; $_SESSION['admin_email']=$a['email'];
      redirect(SITE_URL.'/admin/dashboard.php');
    } else { $error='Invalid admin credentials.'; }
  }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — SkillBloom</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body class="auth-body" style="background:linear-gradient(135deg,var(--dark) 0%,#1E3A8A 100%);">
<div class="auth-card">
  <div class="auth-logo">
    <div class="icon">⚙️</div>
    <h2>Admin Portal</h2>
    <p>Sign in to manage SkillBloom</p>
  </div>
  <?php if($error): ?><div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="POST" class="needs-val">
    <div class="form-group"><label class="form-label">Admin Email</label>
      <input type="email" name="email" class="form-control" placeholder="admin@skillbloom.com" value="<?php echo htmlspecialchars((isset($_POST['email']) ? $_POST['email'] : '')); ?>" required>
    </div>
    <div class="form-group"><label class="form-label">Password</label>
      <div class="pw-wrap"><input type="password" name="password" class="form-control" placeholder="Password" required><button type="button" class="pw-eye">👁️</button></div>
    </div>
    <button type="submit" class="btn btn-primary btn-block btn-lg">🔐 Sign In to Admin</button>
  </form>
  <div class="auth-footer"><a href="<?php echo SITE_URL; ?>/user/login.php" style="color:var(--primary);">← Back to Student Site</a></div>
  <div style="text-align:center;margin-top:.6rem;font-size:.8rem;color:var(--gray-400);">skillbloom / admin</div>
</div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
