<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$status=clean((isset($_GET['status']) ? $_GET['status'] : ''));
$cid=intval((isset($_GET['course_id']) ? $_GET['course_id'] : 0));
$course=$cid?$conn->query("SELECT * FROM courses WHERE id=$cid")->fetch_assoc():null;
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payment Status — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
<div style="text-align:center;max-width:480px;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:3rem 2rem;box-shadow:var(--shadow-lg);">
<?php if($status==='success'): ?>
  <div style="font-size:5rem;margin-bottom:1rem;">🎉</div>
  <h2 style="color:var(--success);margin-bottom:.5rem;">Payment Successful!</h2>
  <p style="color:var(--gray-600);margin-bottom:1.5rem;">Congratulations! You're now enrolled<?=$course?' in <strong>'.htmlspecialchars($course['title']).'</strong>':''?>. Start learning right away!</p>
  <div style="display:flex;flex-direction:column;gap:.75rem;">
    <?php if($cid): ?>
    <a href="watch-course.php?course_id=<?=$cid?>" class="btn btn-primary btn-lg">▶️ Start Learning Now</a>
    <?php endif; ?>
    <a href="my-courses.php" class="btn btn-outline">📚 My Courses</a>
  </div>
<?php else: ?>
  <div style="font-size:5rem;margin-bottom:1rem;">❌</div>
  <h2 style="color:var(--danger);margin-bottom:.5rem;">Payment Failed</h2>
  <p style="color:var(--gray-600);margin-bottom:1.5rem;">Something went wrong with your payment. Please try again or contact support.</p>
  <div style="display:flex;flex-direction:column;gap:.75rem;">
    <?php if($cid): ?>
    <a href="checkout.php?course_id=<?=$cid?>" class="btn btn-primary btn-lg">🔁 Try Again</a>
    <?php endif; ?>
    <a href="courses.php" class="btn btn-ghost">← Back to Courses</a>
  </div>
<?php endif; ?>
</div>
</div>
<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
