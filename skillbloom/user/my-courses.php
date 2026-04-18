<?php
require_once dirname(__DIR__).'/config.php';
requireUser();
$uid = $_SESSION['user_id'];

$tab   = clean(isset($_GET['tab']) ? $_GET['tab'] : 'all');
$where = "e.user_id=$uid";
if($tab === 'active')    $where .= " AND e.status='active'";
elseif($tab === 'completed') $where .= " AND e.status='completed'";

$myCourses = $conn->query(
  "SELECT c.*, cat.name cat_name, e.progress, e.status enroll_status, e.enrolled_at
   FROM enrollments e
   JOIN courses c ON c.id = e.course_id
   LEFT JOIN categories cat ON cat.id = c.category_id
   WHERE $where
   ORDER BY e.enrolled_at DESC"
)->fetch_all(MYSQLI_ASSOC);

// Count for tabs
$countAll       = $conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid")->fetch_assoc()['c'];
$countActive    = $conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid AND status='active'")->fetch_assoc()['c'];
$countCompleted = $conn->query("SELECT COUNT(*) c FROM enrollments WHERE user_id=$uid AND status='completed'")->fetch_assoc()['c'];
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Courses &mdash; SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
.tab-bar {
  display:flex; gap:0; border-bottom:2px solid var(--gray-200);
  margin-bottom:1.75rem;
}
.tab-link {
  padding:.6rem 1.3rem; font-weight:600; font-size:.9rem;
  text-decoration:none; color:var(--gray-400);
  border-bottom:3px solid transparent; margin-bottom:-2px;
  transition:var(--trans);
}
.tab-link.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-link .cnt {
  display:inline-flex; align-items:center; justify-content:center;
  background:var(--gray-200); color:var(--gray-600);
  border-radius:50px; font-size:.72rem; font-weight:700;
  padding:.1rem .45rem; margin-left:.35rem;
}
.tab-link.active .cnt { background:var(--primary-100); color:var(--primary); }

.my-course-card {
  background:#fff; border:1px solid var(--gray-200);
  border-radius:var(--radius-lg); overflow:hidden;
  transition:var(--trans); display:flex; flex-direction:column;
}
.my-course-card:hover {
  transform:translateY(-3px); box-shadow:var(--shadow-lg);
  border-color:var(--primary-200);
}
.my-course-thumb {
  width:100%; height:175px; overflow:hidden;
  background:linear-gradient(135deg,var(--primary-100),#BAE6FD);
  flex-shrink:0;
}
.my-course-thumb img { width:100%; height:100%; object-fit:cover; display:block; }

.prog-bar-bg  { background:var(--gray-200); border-radius:50px; height:7px; }
.prog-bar-fill {
  height:7px; border-radius:50px;
  background:linear-gradient(90deg, var(--primary), var(--accent));
  transition:width .6s ease;
}
.prog-bar-fill.done { background:linear-gradient(90deg,var(--success),#34D399); }

.enroll-badge-active    { background:var(--warning-bg); color:#92400E; }
.enroll-badge-completed { background:var(--success-bg); color:#065F46; }
</style>
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div class="page-layout">
<?php include dirname(__DIR__).'/includes/sidebar.php'; ?>
<main class="main-content">

<div class="page-header">
  <h2>&#128218; My Learning</h2>
  <p>Track your progress and continue learning.</p>
</div>

<!-- Tabs -->
<div class="tab-bar">
  <a href="?tab=all" class="tab-link <?=$tab==='all'?'active':''?>">
    All Courses <span class="cnt"><?=$countAll?></span>
  </a>
  <a href="?tab=active" class="tab-link <?=$tab==='active'?'active':''?>">
    In Progress <span class="cnt"><?=$countActive?></span>
  </a>
  <a href="?tab=completed" class="tab-link <?=$tab==='completed'?'active':''?>">
    Completed <span class="cnt"><?=$countCompleted?></span>
  </a>
</div>

<?php if($myCourses): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1.35rem;">
<?php foreach($myCourses as $c):
  // Build thumbnail URL safely (no nested ternary)
  $catName = isset($c['cat_name']) ? $c['cat_name'] : 'Technology';
  if(!empty($c['thumbnail'])){
    $thumbUrl = SITE_URL . '/uploads/' . htmlspecialchars($c['thumbnail']);
  } else {
    $thumbUrl = SITE_URL . '/user/course-thumbnail.php?title=' . urlencode($c['title']) . '&cat=' . urlencode($catName);
  }
  $isDone = ($c['enroll_status'] === 'completed');
?>
<div class="my-course-card">
  <!-- Thumbnail -->
  <div class="my-course-thumb">
    <img src="<?=$thumbUrl?>" alt="<?=htmlspecialchars($c['title'])?>">
  </div>

  <div class="card-content">
    <!-- Badges row -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem;">
      <span class="badge"><?=htmlspecialchars($catName)?></span>
      <?php if($isDone): ?>
        <span class="badge enroll-badge-completed">&#10003; Completed</span>
      <?php else: ?>
        <span class="badge enroll-badge-active">&#8635; In Progress</span>
      <?php endif; ?>
    </div>

    <div class="course-title"><?=htmlspecialchars($c['title'])?></div>
    <div class="course-instructor" style="margin-bottom:.9rem;">
      &#128104;&#8205;&#127979; <?=htmlspecialchars($c['instructor'])?>
    </div>

    <!-- Progress bar -->
    <div style="margin-bottom:.9rem;">
      <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--gray-500);margin-bottom:.4rem;">
        <span>Progress</span>
        <span style="font-weight:700;color:<?=$isDone?'var(--success)':'var(--primary)'?>;"><?=$c['progress']?>%</span>
      </div>
      <div class="prog-bar-bg">
        <div class="prog-bar-fill <?=$isDone?'done':''?>" style="width:<?=$c['progress']?>%;"></div>
      </div>
    </div>

    <!-- Meta info -->
    <div style="font-size:.78rem;color:var(--gray-400);margin-bottom:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">
      <span>&#128197; <?=date('M d, Y', strtotime($c['enrolled_at']))?></span>
      <span>&#9201; <?=$c['hours']?>h</span>
      <span>&#128202; <?=$c['level']?></span>
    </div>

    <!-- Action buttons -->
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <a href="watch-course.php?course_id=<?=$c['id']?>" class="btn btn-primary btn-sm">
        &#9654;&#65039; <?=$isDone ? 'Review' : 'Continue'?>
      </a>
      <a href="course-details.php?id=<?=$c['id']?>" class="btn btn-ghost btn-sm">Details</a>
      <?php if($isDone): ?>
        <a href="certificate.php?course_id=<?=$c['id']?>" class="btn btn-success btn-sm">&#127942; Certificate</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php else: ?>
<!-- Empty state -->
<div style="background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:4rem 2rem;text-align:center;">
  <div style="font-size:4rem;margin-bottom:1rem;">&#128218;</div>
  <h3 style="margin-bottom:.5rem;">No courses here</h3>
  <p style="color:var(--gray-500);margin-bottom:1.5rem;">Start learning something amazing!</p>
  <a href="courses.php" class="btn btn-primary">Browse Courses</a>
</div>
<?php endif; ?>

</main>
</div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
