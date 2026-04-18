<?php
require_once dirname(__DIR__).'/config.php';
if(isUser()) redirect(SITE_URL.'/user/dashboard.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=clean((isset($_POST['email']) ? $_POST['email'] : '')); $pw=(isset($_POST['password']) ? $_POST['password'] : '');
  if(empty($email)||empty($pw)){ $error='Please fill in all fields.'; }
  else{
    $s=$conn->prepare("SELECT id,name,email,password,status FROM users WHERE email=?");
    $s->bind_param('s',$email); $s->execute(); $u=$s->get_result()->fetch_assoc();
    if($u && password_verify($pw,$u['password'])){
      if($u['status']==='banned'){ $error='Your account is suspended. Contact support.'; }
      else{
        $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['name']; $_SESSION['user_email']=$u['email'];
        flash('success','Welcome back, '.$u['name'].'! 👋');
        redirect(SITE_URL.'/user/dashboard.php');
      }
    } else { $error='Invalid email or password.'; }
  }
}
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
body{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#0F172A 0%,#1E3A8A 100%);
  padding:2rem 1rem;
}
.card{
  background:#fff;border-radius:20px;
  box-shadow:0 20px 60px rgba(0,0,0,.3);
  padding:2.5rem 2rem;width:100%;max-width:380px;
}
.card-icon{
  width:58px;height:58px;border-radius:16px;
  background:#2563EB;
  display:flex;align-items:center;justify-content:center;
  font-size:1.6rem;margin:0 auto 1.1rem;
  box-shadow:0 4px 16px rgba(37,99,235,.35);
}
.card h2{font-size:1.5rem;font-weight:800;color:#0f172a;text-align:center;margin-bottom:.3rem;}
.card .sub{font-size:.88rem;color:#6b7280;text-align:center;margin-bottom:1.75rem;}
.lbl{display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;}
.inp{width:100%;padding:.72rem 1rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.9rem;color:#0f172a;background:#fff;outline:none;transition:.18s;font-family:inherit;}
.inp:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.fg{margin-bottom:1.1rem;}
.pw-wrap{position:relative;}
.pw-wrap .inp{padding-right:3rem;}
.pw-eye{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;color:#9ca3af;}
.row-lbl{display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;}
.forgot{color:#2563EB;font-weight:500;font-size:.82rem;text-decoration:none;}
.forgot:hover{text-decoration:underline;}
.btn-go{width:100%;padding:.82rem;border-radius:10px;border:none;cursor:pointer;background:#2563EB;color:#fff;font-size:1rem;font-weight:700;font-family:inherit;transition:.18s;margin-top:.25rem;box-shadow:0 4px 16px rgba(37,99,235,.35);}
.btn-go:hover{background:#1D4ED8;transform:translateY(-1px);}
.err{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;margin-bottom:1rem;}
.suc{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;margin-bottom:1rem;}
.bottom{text-align:center;font-size:.875rem;color:#6b7280;margin-top:1.1rem;}
.bottom a{color:#2563EB;font-weight:700;text-decoration:none;}
.bottom a:hover{text-decoration:underline;}
</style>
</head><body>
<div class="card">
  <div class="card-icon">🎓</div>
  <h2>Welcome Back</h2>
  <p class="sub">Sign in to continue learning</p>

  <?php if($flash): ?><div class="suc">✅ <?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>
  <?php if($error): ?><div class="err">❌ <?=htmlspecialchars($error)?></div><?php endif; ?>

  <form method="POST" class="needs-val">
    <div class="fg">
      <label class="lbl">Email Address</label>
      <input type="email" name="email" class="inp" placeholder="you@example.com" value="<?=htmlspecialchars((isset($_POST['email']) ? $_POST['email'] : ''))?>" required>
    </div>
    <div class="fg">
      <div class="row-lbl">
        <label class="lbl" style="margin:0;">Password</label>
        
      </div>
      <div class="pw-wrap">
        <input type="password" name="password" class="inp" placeholder="Enter your password" required>
        <button type="button" class="pw-eye">👁️</button>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.2rem;font-size:.875rem;color:#6b7280;">
      <input type="checkbox" name="remember" style="accent-color:#2563EB;width:15px;height:15px;cursor:pointer;">
      <label style="cursor:pointer;">Keep me signed in</label>
    </div>
    <button type="submit" class="btn-go">🔐 Sign In</button>
  </form>
  <div class="bottom">Don't have an account? <a href="register.php">Create Account</a></div>
</div>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
