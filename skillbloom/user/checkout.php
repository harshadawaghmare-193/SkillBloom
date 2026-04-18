<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$cid=intval((isset($_GET['course_id']) ? $_GET['course_id'] : 0));
$course=$conn->query("SELECT c.*,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id WHERE c.id=$cid AND c.status='published'")->fetch_assoc();
if(!$course){ flash('error','Course not found.'); redirect(SITE_URL.'/user/courses.php'); }
$enrolled=$conn->query("SELECT id FROM enrollments WHERE user_id=$uid AND course_id=$cid")->fetch_assoc();
if($enrolled){ flash('success','You are already enrolled!'); redirect(SITE_URL.'/user/watch-course.php?course_id='.$cid); }

$errs=[]; $success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $method=clean((isset($_POST['method']) ? $_POST['method'] : ''));
  $nameOnCard=clean((isset($_POST['card_name']) ? $_POST['card_name'] : ''));
  $cardNo=preg_replace('/\D/','',(isset($_POST['card_no']) ? $_POST['card_no'] : ''));
  $exp=clean((isset($_POST['card_exp']) ? $_POST['card_exp'] : ''));
  $cvv=preg_replace('/\D/','',(isset($_POST['card_cvv']) ? $_POST['card_cvv'] : ''));
  if(empty($method)) $errs['method']='Select a payment method.';
  if($method==='card'){
    if(strlen($nameOnCard)<2) $errs['card_name']='Name on card required.';
    if(strlen($cardNo)<16) $errs['card_no']='Enter a valid 16-digit card number.';
    if(!preg_match('/^\d{2}\/\d{2}$/',$exp)) $errs['card_exp']='Format: MM/YY';
    if(strlen($cvv)<3) $errs['card_cvv']='Enter valid CVV.';
  }
  if(empty($errs)){
    $txn='TXN'.strtoupper(substr(md5(uniqid()),0,12));
    $conn->query("INSERT INTO payments(user_id,course_id,amount,method,txn_id,status,paid_at) VALUES($uid,$cid,{$course['price']},'$method','$txn','success',NOW())");
    $conn->query("INSERT INTO enrollments(user_id,course_id,progress) VALUES($uid,$cid,0) ON DUPLICATE KEY UPDATE progress=progress");
    $conn->query("UPDATE courses SET students=students+1 WHERE id=$cid");
    flash('success','Payment successful! Welcome to the course 🎉'); redirect(SITE_URL.'/user/payment-status.php?status=success&course_id='.$cid);
  }
}
function icon($n){$m=['Web Development'=>'💻','Data Science'=>'📊','UI/UX Design'=>'🎨','Business'=>'💼','Mobile Apps'=>'📱','Cybersecurity'=>'🔐'];return isset($m[$n]) ? $m[$n] : '📚';}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div style="max-width:980px;margin:2rem auto;padding:0 1.25rem;">
<div class="page-header"><h2>💳 Secure Checkout</h2><p>Complete your purchase to get instant access.</p></div>

<div class="checkout-layout">
<div>
  <!-- Payment Method -->
  <form method="POST" class="needs-val">
  <div class="card" style="margin-bottom:1.25rem;">
    <div class="card-header"><h3>Payment Method</h3></div>
    <div class="card-body">
      <?php if(isset($errs['method'])): ?><div class="alert alert-error">❌ <?=$errs['method']?></div><?php endif; ?>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
        <?php foreach(['card'=>['💳','Credit/Debit Card'],'upi'=>['📱','UPI Payment'],'netbanking'=>['🏦','Net Banking']] as $k=>$pair): $ic=$pair[0]; $lb=$pair[1]; ?>
        <label style="border:2px solid <?=(((isset($_POST['method']) ? $_POST['method'] : ''))===$k)?'var(--primary)':'var(--gray-200)'?>;border-radius:10px;padding:.9rem;text-align:center;cursor:pointer;transition:var(--trans);">
          <input type="radio" name="method" value="<?=$k?>" <?=(((isset($_POST['method']) ? $_POST['method'] : ''))===$k)?'checked':''?> style="display:none;" onchange="togglePayForm()">
          <div style="font-size:1.6rem;"><?=$ic?></div>
          <div style="font-size:.82rem;font-weight:600;margin-top:.3rem;"><?=$lb?></div>
        </label>
        <?php endforeach; ?>
      </div>
      <!-- Card Form -->
      <div id="cardForm" style="display:<?=(((isset($_POST['method']) ? $_POST['method'] : ''))!=='card')?'none':'block'?>;">
        <div class="form-group"><label class="form-label">Name on Card</label>
          <input type="text" name="card_name" class="form-control" value="<?=htmlspecialchars((isset($_POST['card_name']) ? $_POST['card_name'] : ''))?>" placeholder="Enter card holder name">
          <?php if(isset($errs['card_name'])): ?><span class="err-msg"><?=$errs['card_name']?></span><?php endif; ?>
        </div>
        <div class="form-group"><label class="form-label">Card Number</label>
          <input type="text" name="card_no" class="form-control" value="<?=htmlspecialchars((isset($_POST['card_no']) ? $_POST['card_no'] : ''))?>" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCard(this)">
          <?php if(isset($errs['card_no'])): ?><span class="err-msg"><?=$errs['card_no']?></span><?php endif; ?>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group"><label class="form-label">Expiry</label>
            <input type="text" name="card_exp" class="form-control" value="<?=htmlspecialchars((isset($_POST['card_exp']) ? $_POST['card_exp'] : ''))?>" placeholder="MM/YY" maxlength="5">
            <?php if(isset($errs['card_exp'])): ?><span class="err-msg"><?=$errs['card_exp']?></span><?php endif; ?>
          </div>
          <div class="form-group"><label class="form-label">CVV</label>
            <input type="password" name="card_cvv" class="form-control" value="" placeholder="•••" maxlength="4">
            <?php if(isset($errs['card_cvv'])): ?><span class="err-msg"><?=$errs['card_cvv']?></span><?php endif; ?>
          </div>
        </div>
      </div>
      <!-- UPI Form -->
      <div id="upiForm" style="display:<?=(((isset($_POST['method']) ? $_POST['method'] : ''))==='upi')?'block':'none'?>;">
        <div class="form-group"><label class="form-label">UPI ID</label>
          <input type="text" name="upi_id" class="form-control" placeholder="yourname@upi">
        </div>
      </div>
      <!-- Net Banking -->
      <div id="nbForm" style="display:<?=(((isset($_POST['method']) ? $_POST['method'] : ''))==='netbanking')?'block':'none'?>;">
        <div class="form-group"><label class="form-label">Select Bank</label>
          <select name="bank" class="form-select">
            <option value="">-- Select Bank --</option>
            <?php foreach(['SBI','HDFC','ICICI','Axis','Kotak','PNB','Yes Bank'] as $b): ?>
            <option value="<?=$b?>"><?=$b?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary btn-block btn-lg">🔒 Pay <?=CURRENCY?><?=number_format($course['price'])?> Securely</button>
  <p style="text-align:center;font-size:.8rem;color:var(--gray-400);margin-top:.6rem;">🔐 SSL Secured • 30-Day Money-Back Guarantee</p>
  </form>
</div>

<!-- Order Summary -->
<div>
  <div class="card">
    <div class="card-header"><h3>📋 Order Summary</h3></div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);">
        <div style="width:52px;height:52px;background:var(--primary-100);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;"><?=icon((isset($course['cat_name']) ? $course['cat_name'] : ''))?></div>
        <div>
          <div style="font-weight:700;font-size:.9rem;color:var(--dark);"><?=htmlspecialchars($course['title'])?></div>
          <div style="font-size:.8rem;color:var(--gray-400);">👨‍🏫 <?=htmlspecialchars($course['instructor'])?></div>
        </div>
      </div>
      <div style="font-size:.875rem;">
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;color:var(--gray-600);">Original Price<span><?=CURRENCY?><?=number_format($course['old_price']?:$course['price'])?></span></div>
        <?php if($course['old_price']>$course['price']): ?>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;color:var(--success);">Discount<span>-<?=CURRENCY?><?=number_format($course['old_price']-$course['price'])?></span></div>
        <?php endif; ?>
        <div class="divider"></div>
        <div style="display:flex;justify-content:space-between;padding:.4rem 0;font-size:1rem;font-weight:800;color:var(--dark);">Total<span><?=CURRENCY?><?=number_format($course['price'])?></span></div>
      </div>
      <?php if($course['old_price']>$course['price']): ?>
      <div style="background:var(--success-bg);color:#065F46;font-size:.82rem;font-weight:700;text-align:center;padding:.45rem;border-radius:8px;margin-top:.75rem;">
        🎉 You save <?=CURRENCY?><?=number_format($course['old_price']-$course['price'])?> (<?=round((1-$course['price']/$course['old_price'])*100)?>% off)
      </div>
      <?php endif; ?>
    </div>
    <div class="card-footer">
      <div style="font-size:.8rem;color:var(--gray-400);display:flex;flex-direction:column;gap:.35rem;">
        <span>✅ Lifetime access</span>
        <span>✅ Certificate of completion</span>
        <span>✅ 30-day money-back guarantee</span>
      </div>
    </div>
  </div>
</div>
</div>
</div>

<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
<script>
function togglePayForm(){
  const v=document.querySelector('input[name="method"]:checked')?.value;
  document.getElementById('cardForm').style.display=v==='card'?'block':'none';
  document.getElementById('upiForm').style.display=v==='upi'?'block':'none';
  document.getElementById('nbForm').style.display=v==='netbanking'?'block':'none';
  // Highlight selected
  document.querySelectorAll('input[name="method"]').forEach(r=>{
    r.closest('label').style.borderColor=r.checked?'var(--primary)':'var(--gray-200)';
  });
}
function formatCard(el){
  let v=el.value.replace(/\D/g,'').substring(0,16);
  el.value=v.replace(/(.{4})/g,'$1 ').trim();
}
</script>
</body></html>
