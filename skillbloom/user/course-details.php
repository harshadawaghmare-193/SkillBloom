<?php
require_once dirname(__DIR__).'/config.php';
$id=intval((isset($_GET['id']) ? $_GET['id'] : 0));
$c=$conn->query("SELECT c.*,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id WHERE c.id=$id AND c.status='published'")->fetch_assoc();
if(!$c){ echo '<h2>Course not found.</h2>'; exit(); }

$videos=$conn->query("SELECT * FROM videos WHERE course_id=$id ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$totalMins=array_sum(array_column($videos,'duration_min'));

$enrolled=false; $cert=false;
if(isUser()){
  $uid=$_SESSION['user_id'];
  $e=$conn->query("SELECT id FROM enrollments WHERE user_id=$uid AND course_id=$id")->fetch_assoc();
  $enrolled=(bool)$e;
  if($enrolled){
    $cert=$conn->query("SELECT id FROM certificates WHERE user_id=$uid AND course_id=$id")->fetch_assoc();
  }
}

function icon($n){$m=['Web Development'=>'💻','Data Science'=>'📊','UI/UX Design'=>'🎨','Business'=>'💼','Mobile Apps'=>'📱','Cybersecurity'=>'🔐'];return isset($m[$n]) ? $m[$n] : '📚';}
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($c['title'])?> — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>
<?php if($flash): ?><div class="alert alert-<?=$flash['type']==='error'?'error':'success'?>" style="margin:0;border-radius:0;border-left:none;border-right:none;"><?=$flash['type']==='success'?'✅':'❌'?> <?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--dark) 0%,#1E3A8A 100%);color:#fff;padding:2.5rem 0;">
  <div style="max-width:1100px;margin:0 auto;padding:0 1.75rem;display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start;">
    <div>
      <div style="margin-bottom:.75rem;"><span style="background:rgba(255,255,255,.15);padding:.2rem .75rem;border-radius:99px;font-size:.8rem;"><?=htmlspecialchars((isset($c['cat_name']) ? $c['cat_name'] : ''))?></span></div>
      <h1 style="font-size:1.85rem;margin-bottom:.75rem;color:#fff;"><?=htmlspecialchars($c['title'])?></h1>
      <p style="opacity:.8;margin-bottom:1.1rem;font-size:.95rem;"><?=htmlspecialchars((isset($c['short_desc']) ? $c['short_desc'] : ''))?></p>
      <div style="display:flex;flex-wrap:wrap;gap:1rem;font-size:.875rem;opacity:.8;margin-bottom:1rem;">
        <span class="stars"><?=str_repeat('★',floor((isset($c['rating']) ? $c['rating'] : 0)))?></span>
        <span>⭐ <?=$c['rating']?></span>
        <span>👥 <?=number_format($c['students'])?> students</span>
        <span>⏱ <?=round($totalMins/60,1)?> hours</span>
        <span>📊 <?=$c['level']?></span>
        <span>🌐 English</span>
      </div>
      <p style="opacity:.7;font-size:.875rem;">👨‍🏫 Instructor: <strong><?=htmlspecialchars($c['instructor'])?></strong></p>
    </div>
    <!-- CTA Card -->
    <div style="background:var(--white);border-radius:var(--radius-lg);padding:1.5rem;color:var(--dark);box-shadow:0 10px 40px rgba(0,0,0,.3);">
      <?php
      $_cat3 = isset($c['cat_name']) ? $c['cat_name'] : 'Technology';
      if(!empty($c['thumbnail'])){
        $_ctu = SITE_URL.'/uploads/'.htmlspecialchars($c['thumbnail']);
      } else {
        $_ctu = SITE_URL.'/user/course-thumbnail.php?title='.urlencode($c['title']).'&cat='.urlencode($_cat3);
      }
      ?>
      <div style="text-align:center;margin-bottom:.75rem;border-radius:var(--radius);overflow:hidden;">
        <img src="<?=$_ctu?>" alt="<?=htmlspecialchars($c['title'])?>" style="width:100%;border-radius:var(--radius);display:block;">
      </div>
      <?php if($enrolled): ?>
        <div class="alert alert-success" style="text-align:center;">✅ You're enrolled!</div>
        <a href="watch-course.php?course_id=<?=$id?>" class="btn btn-primary btn-block btn-lg">▶️ Continue Learning</a>
        <?php if($cert): ?>
        <a href="certificate.php?course_id=<?=$id?>" class="btn btn-success btn-block" style="margin-top:.6rem;">🏆 View Certificate</a>
        <?php endif; ?>
      <?php else: ?>
        <div style="text-align:center;margin-bottom:1rem;">
          <?php if($c['price']==0): ?>
            <div style="font-size:1.75rem;font-weight:800;color:var(--success);">FREE</div>
          <?php else: ?>
            <div style="font-size:1.75rem;font-weight:800;color:var(--primary);"><?=CURRENCY?><?=number_format($c['price'])?></div>
            <?php if($c['old_price']>$c['price']): ?>
            <div style="font-size:.9rem;color:var(--gray-400);text-decoration:line-through;"><?=CURRENCY?><?=number_format($c['old_price'])?></div>
            <div style="background:var(--danger-bg);color:var(--danger);font-weight:700;font-size:.85rem;padding:.25rem .65rem;border-radius:99px;display:inline-block;margin-top:.3rem;">
              <?=round((1-$c['price']/$c['old_price'])*100)?>% OFF
            </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <a href="checkout.php?course_id=<?=$id?>" class="btn btn-primary btn-block btn-lg">
          <?=$c['price']==0?'🚀 Enroll Free':'💳 Buy Now'?>
        </a>
        <p style="font-size:.78rem;color:var(--gray-400);text-align:center;margin-top:.6rem;">30-Day Money-Back Guarantee</p>
      <?php endif; ?>
      <div class="divider"></div>
      <div style="font-size:.85rem;color:var(--gray-600);">
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">📹 Videos<strong><?=count($videos)?></strong></div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">⏱ Total Duration<strong><?=$c['hours']?> hours</strong></div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">📊 Level<strong><?=$c['level']?></strong></div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">🔄 Lifetime Access<strong>✅ Yes</strong></div>
        <div style="display:flex;justify-content:space-between;padding:.3rem 0;">📜 Certificate<strong>✅ Yes</strong></div>
      </div>
    </div>
  </div>
</div>

<!-- Course Content -->
<div style="max-width:1100px;margin:2rem auto;padding:0 1.75rem;display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:flex-start;">
  <div>
    <!-- What you'll learn -->
    <?php if($c['short_desc']): ?>
    <div class="card" style="margin-bottom:1.5rem;">
      <div class="card-header"><h3>📋 About This Course</h3></div>
      <div class="card-body">
        <p style="color:var(--gray-600);line-height:1.8;"><?=nl2br(htmlspecialchars(isset($c['description']) && $c['description'] ? $c['description'] : $c['short_desc']))?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Curriculum -->
    <div class="card">
      <div class="card-header">
        <h3>📚 Course Curriculum</h3>
        <span style="font-size:.85rem;color:var(--gray-400);"><?=count($videos)?> lectures · <?=round($totalMins/60,1)?> hours</span>
      </div>
      <div>
      <?php foreach($videos as $i=>$v): ?>
      <div style="display:flex;align-items:center;gap:.875rem;padding:.9rem 1.4rem;border-bottom:1px solid var(--gray-100);">
        <div style="width:28px;height:28px;background:<?=$v['is_preview']?'var(--primary-100)':'var(--gray-100)'?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:<?=$v['is_preview']?'var(--primary)':'var(--gray-500)'?>;flex-shrink:0;"><?=$i+1?></div>
        <div style="flex:1;">
          <div style="font-size:.9rem;font-weight:600;color:var(--dark);"><?=htmlspecialchars($v['title'])?></div>
          <?php if($v['is_preview']): ?><span style="font-size:.75rem;color:var(--success);font-weight:600;">▶ Preview</span><?php endif; ?>
        </div>
        <span style="font-size:.82rem;color:var(--gray-400);">⏱ <?=$v['duration_min']?> min</span>
        <?php if($v['is_preview']): ?><a href="watch-course.php?course_id=<?=$id?>&video_id=<?=$v['id']?>" class="btn btn-outline btn-sm">Watch</a><?php endif; ?>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div style="position:sticky;top:80px;">
    <!-- Instructor -->
    <div class="card">
      <div class="card-header"><h3>👨‍🏫 Instructor</h3></div>
      <div class="card-body" style="text-align:center;">
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;font-weight:700;margin:0 auto .75rem;"><?=strtoupper(substr($c['instructor'],0,1))?></div>
        <div style="font-weight:700;font-size:1rem;"><?=htmlspecialchars($c['instructor'])?></div>
        <div style="font-size:.85rem;color:var(--gray-400);margin-top:.25rem;">Expert Instructor</div>
      </div>
    </div>
  </div>
</div>

<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
