<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$cid=intval((isset($_GET['course_id']) ? $_GET['course_id'] : 0));
$cert=$conn->query("SELECT cert.*,c.title,c.instructor,c.hours,c.level,cat.name cat_name FROM certificates cert JOIN courses c ON c.id=cert.course_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE cert.user_id=$uid AND cert.course_id=$cid")->fetch_assoc();
if(!$cert){ flash('error','Certificate not found.'); redirect(SITE_URL.'/user/my-certificates.php'); }
$user=$conn->query("SELECT name FROM users WHERE id=$uid")->fetch_assoc();
$download=isset($_GET['download']);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Certificate — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=DM+Sans:wght@400;500;600&display=swap');

.cert-page-bg {
  background: linear-gradient(135deg,#EFF6FF,#DBEAFE,#EFF6FF);
  min-height: 100vh;
  padding-bottom: 3rem;
}
.cert-outer {
  background: linear-gradient(135deg,#1E3A8A,#2563EB,#0369A1);
  padding: 5px;
  border-radius: 24px;
  max-width: 860px;
  margin: 0 auto 2rem;
  box-shadow: 0 25px 70px rgba(37,99,235,0.28), 0 5px 20px rgba(0,0,0,0.1);
}
.cert-inner {
  background: #fff;
  border-radius: 20px;
  padding: 3rem 3.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
/* Corner ornaments */
.cert-inner::before, .cert-inner::after {
  content: '';
  position: absolute;
  width: 80px; height: 80px;
}
.cert-inner::before {
  top: 0; left: 0;
  border-top: 3px solid rgba(37,99,235,0.2);
  border-left: 3px solid rgba(37,99,235,0.2);
  border-radius: 20px 0 0 0;
}
.cert-inner::after {
  bottom: 0; right: 0;
  border-bottom: 3px solid rgba(37,99,235,0.2);
  border-right: 3px solid rgba(37,99,235,0.2);
  border-radius: 0 0 20px 0;
}
.cert-corner-tr {
  position:absolute; top:0; right:0;
  width:80px; height:80px;
  border-top: 3px solid rgba(37,99,235,0.2);
  border-right: 3px solid rgba(37,99,235,0.2);
  border-radius: 0 20px 0 0;
}
.cert-corner-bl {
  position:absolute; bottom:0; left:0;
  width:80px; height:80px;
  border-bottom: 3px solid rgba(37,99,235,0.2);
  border-left: 3px solid rgba(37,99,235,0.2);
  border-radius: 0 0 0 20px;
}
/* Watermark */
.cert-watermark {
  position:absolute; font-size:18rem; opacity:0.025;
  top:50%; left:50%; transform:translate(-50%,-50%);
  font-family:'Cormorant Garamond',serif;
  color:#2563EB; pointer-events:none; user-select:none;
  line-height:1;
}
.cert-logo-row {
  display:flex;align-items:center;justify-content:center;
  gap:.75rem;margin-bottom:1rem;
}
.cert-logo-icon {
  width:48px;height:48px;
  background:linear-gradient(135deg,#16a34a,#0891b2);
  border-radius:13px;
  display:flex;align-items:center;justify-content:center;
}
.cert-brand { font-size:1.5rem;font-weight:800;color:#2563EB; }
.cert-divider-top {
  height:2px;
  background:linear-gradient(90deg,transparent,#2563EB,#0891b2,transparent);
  margin:1.2rem 4rem;
}
.cert-number { font-size:.75rem;color:#9CA3AF;letter-spacing:.12em;margin-bottom:.5rem; }
.cert-label  { font-size:.78rem;color:#9CA3AF;letter-spacing:.2em;text-transform:uppercase;margin-bottom:1.5rem; }
.cert-certify { font-size:.95rem;color:#6B7280;margin-bottom:.3rem; font-family:'DM Sans',sans-serif; }
.cert-name {
  font-family:'Cormorant Garamond',serif;
  font-size:2.8rem;font-weight:700;
  color:#0F172A;
  margin:.3rem 0 .5rem;
  letter-spacing:.02em;
}
.cert-completed { font-size:.95rem;color:#6B7280;margin-bottom:.5rem; font-family:'DM Sans',sans-serif; }
.cert-course {
  font-size:1.2rem;font-weight:700;color:#2563EB;
  margin:.3rem 0 1.5rem;
}
.cert-divider {
  height:1.5px;
  background:linear-gradient(90deg,transparent,rgba(37,99,235,0.3),transparent);
  margin:0 2rem 2rem;
}
.cert-meta {
  display:flex;justify-content:center;gap:0;
  margin-bottom:2rem;
  border:1.5px solid #E5E7EB;border-radius:14px;overflow:hidden;
}
.cert-meta-item {
  flex:1;padding:1rem .5rem;
  text-align:center;font-size:.82rem;color:#6B7280;
  border-right:1.5px solid #E5E7EB;
  font-family:'DM Sans',sans-serif;
}
.cert-meta-item:last-child { border-right:none; }
.cert-meta-item strong {
  display:block;font-size:.95rem;color:#0F172A;
  font-weight:700;margin-bottom:.2rem;
}
.cert-verify {
  display:inline-block;
  border:1.5px solid #BFDBFE;
  background:#EFF6FF;
  padding:.65rem 2rem;border-radius:8px;
  font-size:.78rem;color:#3B82F6;
  letter-spacing:.02em;
}
.cert-seal {
  position:absolute;bottom:2.5rem;right:2.5rem;
  width:70px;height:70px;
  border-radius:50%;
  background:linear-gradient(135deg,#2563EB,#0891b2);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 4px 14px rgba(37,99,235,0.35);
  font-size:1.8rem;
}

@media print{
  nav,footer,.btn,.page-header-actions{display:none!important;}
  .cert-outer{margin:0;box-shadow:none;}
  body{background:white;}
}
</style>
</head>
<body class="cert-page-bg">
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div style="max-width:940px;margin:2rem auto;padding:0 1.25rem;">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div><h2>🏆 Certificate of Completion</h2><p style="color:var(--gray-400);">Congratulations on your achievement!</p></div>
  <div style="display:flex;gap:.5rem;">
    <a href="my-certificates.php" class="btn btn-ghost btn-sm">← Back</a>
    <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print</button>
    <a href="?course_id=<?=$cid?>&download=1" class="btn btn-primary btn-sm">⬇️ Download</a>
  </div>
</div>

<div class="cert-outer">
<div class="cert-inner">
  <!-- Corner decorations -->
  <div class="cert-corner-tr"></div>
  <div class="cert-corner-bl"></div>
  <div class="cert-watermark">✦</div>
  <div class="cert-seal">🏅</div>

  <!-- Logo -->
  <div class="cert-logo-row">
    <div style="overflow:hidden;height:50px;display:flex;align-items:center;">
      <img src="<?=SITE_URL?>/assets/images/logo.png" alt="SkillBloom"
        style="height:190px;width:auto;display:block;margin-top:-70px;margin-bottom:-70px;">
    </div>
  </div>

  <div class="cert-divider-top"></div>

  <div class="cert-number">Certificate No: <?=htmlspecialchars($cert['cert_no'])?></div>
  <div class="cert-label">Certificate of Completion</div>

  <p class="cert-certify">This is to certify that</p>
  <div class="cert-name"><?=htmlspecialchars((isset($user['name']) ? $user['name'] : 'Student'))?></div>
  <p class="cert-completed">has successfully completed the course</p>
  <div class="cert-course"><?=htmlspecialchars($cert['title'])?></div>

  <div class="cert-divider"></div>

  <div class="cert-meta">
    <div class="cert-meta-item">
      <strong><?=date('d M Y',strtotime($cert['issued_at']))?></strong>
      Date Issued
    </div>
    <div class="cert-meta-item">
      <strong><?=htmlspecialchars((isset($cert['instructor']) ? $cert['instructor'] : ''))?></strong>
      Instructor
    </div>
    <div class="cert-meta-item">
      <strong><?=$cert['hours']?> Hours</strong>
      Duration
    </div>
    <div class="cert-meta-item">
      <strong><?=(isset($cert['level']) ? $cert['level'] : 'All Levels')?></strong>
      Level
    </div>
  </div>

  <div class="cert-verify">
    🔗 Verify at: skillbloom.com/verify/<?=htmlspecialchars($cert['cert_no'])?>
  </div>
</div>
</div>

</div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
