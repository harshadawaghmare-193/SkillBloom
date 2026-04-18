<?php
require_once dirname(__DIR__).'/config.php';
requireUser();
$uid = $_SESSION['user_id'];

$enrolled  = $conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid AND status='completed'")->fetch_assoc()['c'];
$certs     = $conn->query("SELECT COUNT(*) c FROM certificates WHERE user_id=$uid")->fetch_assoc()['c'];
$avgp      = round($conn->query("SELECT COALESCE(AVG(progress),0) p FROM enrollments WHERE user_id=$uid")->fetch_assoc()['p']);

$myCourses   = $conn->query("SELECT c.id,c.title,c.instructor,c.thumbnail,c.hours,c.level,e.progress,e.status,cat.name cat_name FROM enrollments e JOIN courses c ON c.id=e.course_id LEFT JOIN categories cat ON cat.id=c.category_id WHERE e.user_id=$uid ORDER BY e.enrolled_at DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$recommended = $conn->query("SELECT c.*,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id WHERE c.status='published' AND c.id NOT IN (SELECT course_id FROM enrollments WHERE user_id=$uid) ORDER BY c.students DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);

$flash = getFlash();
$uname = explode(' ', isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
/* ── User Dashboard – Clean & Light ── */
.dash-kpi {
  display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.75rem;
}
.dash-kpi-card {
  background:#fff; border-radius:14px; border:1px solid #E8EDF3;
  padding:1.15rem 1.1rem; display:flex; align-items:center; gap:.85rem;
  box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.dash-kpi-icon {
  width:44px; height:44px; border-radius:12px;
  display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0;
}
.dash-kpi-icon.blue   { background:#EFF6FF; }
.dash-kpi-icon.green  { background:#ECFDF5; }
.dash-kpi-icon.amber  { background:#FFFBEB; }
.dash-kpi-icon.purple { background:#F5F3FF; }
.dash-kpi-val   { font-size:1.5rem; font-weight:800; color:#0F172A; line-height:1; }
.dash-kpi-label { font-size:.74rem; color:#94A3B8; margin-top:.2rem; }

.sec-hdr {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:1rem;
}
.sec-hdr h3 { font-size:1rem; font-weight:700; color:#0F172A; }

/* Course card */
.c-card {
  background:#fff; border:1px solid #E8EDF3; border-radius:14px;
  overflow:hidden; transition:.2s; display:flex; flex-direction:column;
}
.c-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(37,99,235,.1); border-color:#BFDBFE; }
.c-thumb { width:100%; height:168px; overflow:hidden; flex-shrink:0; }
.c-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.c-body { padding:1rem; flex:1; display:flex; flex-direction:column; }

.prog-bg   { background:#F1F5F9; border-radius:50px; height:6px; margin:.35rem 0 .75rem; }
.prog-fill { height:6px; border-radius:50px; background:linear-gradient(90deg,#2563EB,#0EA5E9); }
.prog-fill.done { background:linear-gradient(90deg,#059669,#34D399); }

.tag-pill {
  display:inline-flex; align-items:center;
  padding:.2rem .65rem; border-radius:50px; font-size:.72rem; font-weight:700;
}
.tag-active    { background:#FEF3C7; color:#92400E; }
.tag-completed { background:#D1FAE5; color:#065F46; }
.tag-cat       { background:#EFF6FF; color:#1D4ED8; }
</style>
</head>
<body style="background:#F8FAFC;">
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div class="page-layout">
<?php include dirname(__DIR__).'/includes/sidebar.php'; ?>
<main class="main-content">

<?php if($flash): ?>
<div class="alert alert-<?=$flash['type']==='error'?'error':'success'?>"><?=$flash['type']==='success'?'&#9989;':'&#10060;'?> <?=htmlspecialchars($flash['msg'])?></div>
<?php endif; ?>

<!-- Welcome header -->
<div class="page-header">
  <h2>Welcome back, <?=htmlspecialchars($uname[0])?>! &#128075;</h2>
  <p>Here's your learning overview for today.</p>
</div>

<!-- KPI Cards (Clickable) -->
<div class="dash-kpi">
  <a href="<?=SITE_URL?>/user/my-courses.php" class="dash-kpi-card" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="dash-kpi-icon blue">&#128218;</div>
    <div><div class="dash-kpi-val"><?=$enrolled?></div><div class="dash-kpi-label">Enrolled Courses</div></div>
  </a>
  <a href="<?=SITE_URL?>/user/my-courses.php" class="dash-kpi-card" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="dash-kpi-icon green">&#9989;</div>
    <div><div class="dash-kpi-val"><?=$completed?></div><div class="dash-kpi-label">Completed</div></div>
  </a>
  <a href="<?=SITE_URL?>/user/my-courses.php" class="dash-kpi-card" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="dash-kpi-icon amber">&#128200;</div>
    <div><div class="dash-kpi-val"><?=$avgp?>%</div><div class="dash-kpi-label">Avg Progress</div></div>
  </a>
  <a href="<?=SITE_URL?>/user/my-certificates.php" class="dash-kpi-card" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="dash-kpi-icon purple">&#127942;</div>
    <div><div class="dash-kpi-val"><?=$certs?></div><div class="dash-kpi-label">Certificates</div></div>
  </a>
</div>

<!-- Continue Learning -->
<div class="sec-hdr">
  <h3>&#9654;&#65039; Continue Learning</h3>
  <a href="my-courses.php" class="btn btn-ghost btn-sm">View All &rarr;</a>
</div>

<?php if($myCourses): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(265px,1fr));gap:1.15rem;margin-bottom:2rem;">
<?php foreach($myCourses as $c):
  $catN = isset($c['cat_name']) ? $c['cat_name'] : 'Technology';
  if(!empty($c['thumbnail'])) $tu = SITE_URL.'/uploads/'.htmlspecialchars($c['thumbnail']);
  else $tu = SITE_URL.'/user/course-thumbnail.php?title='.urlencode($c['title']).'&cat='.urlencode($catN);
  $done = ($c['status']==='completed');
?>
<div class="c-card">
  <div class="c-thumb"><img src="<?=$tu?>" alt="<?=htmlspecialchars($c['title'])?>"></div>
  <div class="c-body">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.55rem;">
      <span class="tag-pill tag-cat"><?=htmlspecialchars($catN)?></span>
      <span class="tag-pill <?=$done?'tag-completed':'tag-active'?>"><?=$done?'&#10003; Done':'&#8635; Active'?></span>
    </div>
    <div style="font-weight:700;font-size:.9rem;color:#0F172A;margin-bottom:.25rem;line-height:1.35;"><?=htmlspecialchars($c['title'])?></div>
    <div style="font-size:.8rem;color:#64748B;margin-bottom:.6rem;">&#128104;&#8205;&#127979; <?=htmlspecialchars($c['instructor'])?></div>
    <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#94A3B8;margin-bottom:.2rem;">
      <span>Progress</span><span style="font-weight:700;color:<?=$done?'#059669':'#2563EB'?>;"><?=$c['progress']?>%</span>
    </div>
    <div class="prog-bg"><div class="prog-fill <?=$done?'done':''?>" style="width:<?=$c['progress']?>%;"></div></div>
    <div style="margin-top:auto;display:flex;gap:.5rem;">
      <a href="watch-course.php?course_id=<?=$c['id']?>" class="btn btn-primary btn-sm">&#9654;&#65039; <?=$done?'Review':'Continue'?></a>
      <?php if($done): ?><a href="certificate.php?course_id=<?=$c['id']?>" class="btn btn-success btn-sm">&#127942; Cert</a><?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div style="background:#fff;border:1px solid #E8EDF3;border-radius:14px;padding:3rem;text-align:center;margin-bottom:2rem;">
  <div style="font-size:3.5rem;margin-bottom:1rem;">&#127891;</div>
  <h3 style="margin-bottom:.5rem;">No courses yet</h3>
  <p style="color:#64748B;margin-bottom:1.25rem;">Start learning something amazing today!</p>
  <a href="courses.php" class="btn btn-primary">Browse Courses</a>
</div>
<?php endif; ?>

<!-- Recommended -->
<div class="sec-hdr">
  <h3>&#127775; Recommended for You</h3>
  <a href="courses.php" class="btn btn-ghost btn-sm">Explore All &rarr;</a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(265px,1fr));gap:1.15rem;">
<?php foreach($recommended as $c):
  $catN = isset($c['cat_name']) ? $c['cat_name'] : 'Technology';
  if(!empty($c['thumbnail'])) $tu = SITE_URL.'/uploads/'.htmlspecialchars($c['thumbnail']);
  else $tu = SITE_URL.'/user/course-thumbnail.php?title='.urlencode($c['title']).'&cat='.urlencode($catN);
?>
<div class="c-card">
  <div class="c-thumb"><img src="<?=$tu?>" alt="<?=htmlspecialchars($c['title'])?>"></div>
  <div class="c-body">
    <span class="tag-pill tag-cat" style="margin-bottom:.6rem;"><?=htmlspecialchars($catN)?></span>
    <div style="font-weight:700;font-size:.9rem;color:#0F172A;margin-bottom:.25rem;line-height:1.35;"><?=htmlspecialchars($c['title'])?></div>
    <div style="font-size:.8rem;color:#64748B;margin-bottom:.7rem;">&#128104;&#8205;&#127979; <?=htmlspecialchars($c['instructor'])?></div>
    <div style="display:flex;gap:.75rem;font-size:.78rem;color:#64748B;margin-bottom:.9rem;">
      <span>&#11088; <?=$c['rating']?></span>
      <span>&#128101; <?=number_format($c['students'])?></span>
      <span>&#9201; <?=$c['hours']?>h</span>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto;">
      <div>
        <span style="font-size:1rem;font-weight:800;color:#0F172A;"><?=CURRENCY?><?=number_format($c['price'])?></span>
        <?php if($c['old_price']>$c['price']): ?><span style="font-size:.8rem;text-decoration:line-through;color:#94A3B8;margin-left:.35rem;"><?=CURRENCY?><?=number_format($c['old_price'])?></span><?php endif; ?>
      </div>
      <a href="course-details.php?id=<?=$c['id']?>" class="btn btn-outline btn-sm">View</a>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

</main>
</div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
