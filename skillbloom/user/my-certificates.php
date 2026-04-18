<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$certs=$conn->query("SELECT cert.*,c.title,c.instructor,c.hours,cat.name cat_name FROM certificates cert JOIN courses c ON c.id=cert.course_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE cert.user_id=$uid ORDER BY cert.issued_at DESC")->fetch_all(MYSQLI_ASSOC);
function icon($n){$m=['Web Development'=>'💻','Data Science'=>'📊','UI/UX Design'=>'🎨','Business'=>'💼','Mobile Apps'=>'📱','Cybersecurity'=>'🔐'];return isset($m[$n]) ? $m[$n] : '📚';}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Certificates — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div class="page-layout">
<?php include dirname(__DIR__).'/includes/sidebar.php'; ?>
<main class="main-content">
<div class="page-header">
  <h2>🏆 My Certificates</h2>
  <p>Your earned credentials — share them proudly!</p>
</div>

<?php if($certs): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
<?php foreach($certs as $c): ?>
<div class="card">
  <div style="background:linear-gradient(135deg,var(--primary) 0%,var(--accent) 100%);padding:1.5rem;text-align:center;color:#fff;">
    <div style="font-size:3rem;margin-bottom:.5rem;"><?=icon((isset($c['cat_name']) ? $c['cat_name'] : ''))?></div>
    <div style="font-size:.78rem;opacity:.8;margin-bottom:.3rem;">CERTIFICATE OF COMPLETION</div>
    <div style="font-weight:800;font-size:1rem;"><?=SITE_NAME?></div>
  </div>
  <div class="card-body">
    <div style="font-weight:700;font-size:1rem;color:var(--dark);margin-bottom:.3rem;"><?=htmlspecialchars($c['title'])?></div>
    <div style="font-size:.85rem;color:var(--gray-400);margin-bottom:.75rem;">👨‍🏫 <?=htmlspecialchars($c['instructor'])?></div>
    <div style="display:flex;gap:.75rem;font-size:.8rem;color:var(--gray-500);margin-bottom:.9rem;">
      <span>📅 <?=date('M d, Y',strtotime($c['issued_at']))?></span>
      <span>🆔 <?=htmlspecialchars($c['cert_no'])?></span>
    </div>
    <div style="display:flex;gap:.5rem;">
      <a href="certificate.php?course_id=<?=$c['course_id']?>" class="btn btn-primary btn-sm">👁️ View</a>
      <a href="certificate.php?course_id=<?=$c['course_id']?>&download=1" class="btn btn-outline btn-sm">⬇️ Download</a>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state card"><div class="card-body">
  <div class="es-icon">🏆</div>
  <h3>No certificates yet</h3>
  <p style="margin:.5rem 0 1rem;">Complete a course to earn your certificate!</p>
  <a href="my-courses.php" class="btn btn-primary">Go to My Courses</a>
</div></div>
<?php endif; ?>
</main></div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
