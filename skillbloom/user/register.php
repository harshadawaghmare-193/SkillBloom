<?php
require_once dirname(__DIR__).'/config.php';
if(isUser()) redirect(SITE_URL.'/user/dashboard.php');
$errs=[]; $old=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $old=$_POST;
  $name=clean((isset($_POST['name']) ? $_POST['name'] : '')); $email=clean((isset($_POST['email']) ? $_POST['email'] : ''));
  $pw=(isset($_POST['password']) ? $_POST['password'] : ''); $cpw=(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');
  if(strlen($name)<2) $errs['name']='Name must be at least 2 characters.';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errs['email']='Enter a valid email.';
  else{
    $r=$conn->prepare("SELECT id FROM users WHERE email=?"); $r->bind_param('s',$email); $r->execute();
    if($r->get_result()->num_rows) $errs['email']='Email already registered.';
  }
  if(strlen($pw)<8) $errs['password']='Password must be at least 8 characters.';
  elseif(!preg_match('/[A-Z]/',$pw)) $errs['password']='Must contain an uppercase letter.';
  if($pw!==$cpw) $errs['confirm_password']='Passwords do not match.';
  if(!isset($_POST['terms'])) $errs['terms']='You must accept the terms.';
  if(empty($errs)){
    $h=password_hash($pw,PASSWORD_DEFAULT);
    $s=$conn->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)");
    $s->bind_param('sss',$name,$email,$h);
    if($s->execute()){ flash('success','Account created! Please sign in.'); redirect(SITE_URL.'/user/login.php'); }
    else $errs['general']='Registration failed. Try again.';
  }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Account — SkillBloom</title>
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
  padding:2.5rem 2.5rem;width:100%;max-width:480px;
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
.inp{width:100%;padding:.68rem 1rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.88rem;color:#0f172a;background:#fff;outline:none;transition:.18s;font-family:inherit;}
.inp:focus{border-color:#2563EB;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.inp.err-inp{border-color:#ef4444;}
.fg{margin-bottom:.95rem;}
.pw-wrap{position:relative;}
.pw-wrap .inp{padding-right:3rem;}
.pw-eye{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;color:#9ca3af;}
.err-msg{font-size:.77rem;color:#dc2626;margin-top:.25rem;display:block;}
.err-box{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:.7rem 1rem;font-size:.875rem;margin-bottom:1rem;}
.btn-go{width:100%;padding:.82rem;border-radius:10px;border:none;cursor:pointer;background:#2563EB;color:#fff;font-size:1rem;font-weight:700;font-family:inherit;transition:.18s;margin-top:.25rem;box-shadow:0 4px 16px rgba(37,99,235,.35);}
.btn-go:hover{background:#1D4ED8;transform:translateY(-1px);}
.bottom{text-align:center;font-size:.875rem;color:#6b7280;margin-top:1.1rem;}
.bottom a{color:#2563EB;font-weight:700;text-decoration:none;}
.bottom a:hover{text-decoration:underline;}
</style>
</head><body>
<div class="card">
  <div class="card-icon">👤</div>
  <h2>Create Account</h2>
  <p class="sub">Start your learning journey today</p>

  <?php if(isset($errs['general'])): ?><div class="err-box">❌ <?=$errs['general']?></div><?php endif; ?>

  <form method="POST" class="needs-val">
    <div class="fg">
      <label class="lbl">Full Name</label>
      <input type="text" name="name" class="inp <?=isset($errs['name'])?'err-inp':''?>" value="<?=htmlspecialchars((isset($old['name']) ? $old['name'] : ''))?>" placeholder="Your full name" required minlength="2">
      <?php if(isset($errs['name'])): ?><span class="err-msg">⚠ <?=$errs['name']?></span><?php endif; ?>
    </div>
    <div class="fg">
      <label class="lbl">Email Address</label>
      <input type="email" name="email" class="inp <?=isset($errs['email'])?'err-inp':''?>" value="<?=htmlspecialchars((isset($old['email']) ? $old['email'] : ''))?>" placeholder="you@example.com" required>
      <?php if(isset($errs['email'])): ?><span class="err-msg">⚠ <?=$errs['email']?></span><?php endif; ?>
    </div>
    <div class="fg">
      <label class="lbl">Password</label>
      <div class="pw-wrap">
        <input type="password" id="password" name="password" class="inp <?=isset($errs['password'])?'err-inp':''?>" placeholder="Min 8 chars, 1 uppercase" required minlength="8">
        <button type="button" class="pw-eye">👁️</button>
      </div>
      <?php if(isset($errs['password'])): ?><span class="err-msg">⚠ <?=$errs['password']?></span><?php endif; ?>
    </div>
    <div class="fg">
      <label class="lbl">Confirm Password</label>
      <div class="pw-wrap">
        <input type="password" id="confirm_password" name="confirm_password" class="inp <?=isset($errs['confirm_password'])?'err-inp':''?>" placeholder="Repeat your password" required>
        <button type="button" class="pw-eye">👁️</button>
      </div>
      <?php if(isset($errs['confirm_password'])): ?><span class="err-msg">⚠ <?=$errs['confirm_password']?></span><?php endif; ?>
    </div>
    <div class="fg">
      <label style="display:flex;align-items:flex-start;gap:.6rem;font-size:.855rem;cursor:pointer;color:#4b5563;line-height:1.5;">
        <input type="checkbox" name="terms" <?=isset($old['terms'])?'checked':''?> style="accent-color:#2563EB;width:15px;height:15px;margin-top:2px;flex-shrink:0;cursor:pointer;">
        I agree to the <a href="#" style="color:#2563EB;font-weight:600;">Terms</a> &amp; <a href="#" style="color:#2563EB;font-weight:600;">Privacy Policy</a>
      </label>
      <?php if(isset($errs['terms'])): ?><span class="err-msg">⚠ <?=$errs['terms']?></span><?php endif; ?>
    </div>
    <button type="submit" class="btn-go">🚀 Create Account</button>
  </form>
  <div class="bottom">Already have an account? <a href="login.php">Sign In</a></div>
</div>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
