<?php
require_once dirname(__DIR__).'/config.php';
requireUser(); $uid=$_SESSION['user_id'];
$cid=intval((isset($_GET['course_id']) ? $_GET['course_id'] : 0));
$e=$conn->query("SELECT * FROM enrollments WHERE user_id=$uid AND course_id=$cid")->fetch_assoc();
if(!$e){ flash('error','Please enroll first.'); redirect(SITE_URL.'/user/courses.php'); }
$course=$conn->query("SELECT * FROM courses WHERE id=$cid")->fetch_assoc();
$videos=$conn->query("SELECT v.*,(SELECT completed FROM video_progress WHERE user_id=$uid AND video_id=v.id) done FROM videos v WHERE v.course_id=$cid ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$vid_id=intval(isset($_GET['video_id']) ? $_GET['video_id'] : (isset($videos[0]['id']) ? $videos[0]['id'] : 0));
$curVideo=null; foreach($videos as $v){ if($v['id']==$vid_id){ $curVideo=$v; break; } }
if(!$curVideo && $videos) $curVideo=$videos[0];

// Mark as watched
if(isset($_GET['mark']) && $curVideo){
  $conn->query("INSERT INTO video_progress(user_id,video_id,completed) VALUES($uid,{$curVideo['id']},1) ON DUPLICATE KEY UPDATE completed=1");
  $done=$conn->query("SELECT COUNT(*) c FROM video_progress WHERE user_id=$uid AND video_id IN (SELECT id FROM videos WHERE course_id=$cid) AND completed=1")->fetch_assoc()['c'];
  $total=count($videos); $prog=$total>0?round(($done/$total)*100):0;
  $conn->query("UPDATE enrollments SET progress=$prog".(($prog>=100)?" ,status='completed'":'')." WHERE user_id=$uid AND course_id=$cid");
  if($prog>=100 && $conn->query("SELECT COUNT(*) c FROM certificates WHERE user_id=$uid AND course_id=$cid")->fetch_assoc()['c']==0){
    $cn=genCertNo(); $conn->query("INSERT INTO certificates(user_id,course_id,cert_no) VALUES($uid,$cid,'$cn')");
    flash('success','🎉 Course completed! Your certificate is ready!');
  } else flash('success','Lesson marked as complete!');
  redirect("watch-course.php?course_id=$cid&video_id={$curVideo['id']}");
}

// Helper: extract YouTube video ID from any YT URL format
function getYouTubeId($url){
  // Handles: youtu.be/ID, youtube.com/watch?v=ID, youtube.com/embed/ID, youtube.com/shorts/ID
  $patterns = [
    '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
    '/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/',
    '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
    '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
    '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
  ];
  foreach($patterns as $p){
    if(preg_match($p, $url, $m)) return $m[1];
  }
  return '';
}

function isYouTube($url){
  return strpos($url,'youtube.com')!==false || strpos($url,'youtu.be')!==false;
}

$doneCount = count(array_filter($videos, function($v){ return $v['done']; }));
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Watch: <?=htmlspecialchars($course['title'])?> — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
/* ── Watch Page Styles ── */
.watch-topbar {
  position:sticky; top:0; z-index:999;
  background:#fff; border-bottom:1px solid var(--gray-200);
  height:56px; display:flex; align-items:center; justify-content:space-between;
  padding:0 1.25rem; box-shadow:var(--shadow-sm);
}
.watch-topbar .brand {
  display:flex; align-items:center; gap:.5rem; text-decoration:none;
}
.watch-topbar .brand svg { width:30px; height:30px; }
.watch-topbar .brand-name { font-size:1rem; font-weight:800; }
.watch-topbar .brand-name span { color:#1AB87A; }
.course-title-bar {
  flex:1; text-align:center; font-size:.88rem; font-weight:600;
  color:var(--gray-700); padding:0 1rem;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.progress-pill {
  background:var(--primary-50); border:1.5px solid var(--primary-200);
  border-radius:50px; padding:.3rem .85rem;
  font-size:.8rem; font-weight:700; color:var(--primary); white-space:nowrap;
}

.watch-layout { display:grid; grid-template-columns:1fr 340px; gap:1.25rem; max-width:1380px; margin:1.25rem auto; padding:0 1.25rem; }

/* Video box */
.video-box {
  position:relative; width:100%; padding-bottom:56.25%;
  background:#0F172A; border-radius:var(--radius-lg); overflow:hidden;
}
.video-box iframe,
.video-box video {
  position:absolute; inset:0; width:100%; height:100%; border:none;
}
.video-placeholder {
  position:absolute; inset:0; display:flex; flex-direction:column;
  align-items:center; justify-content:center; color:rgba(255,255,255,.55);
}
.video-placeholder .pl-icon { font-size:4rem; margin-bottom:1rem; }

/* YouTube branding badge */
.yt-badge {
  display:inline-flex; align-items:center; gap:.35rem;
  background:#FF0000; color:#fff; border-radius:6px;
  padding:.25rem .65rem; font-size:.75rem; font-weight:700;
  margin-bottom:.75rem;
}
.yt-badge svg { width:16px; height:16px; fill:#fff; }

/* Lesson card */
.lesson-card {
  background:#fff; border:1px solid var(--gray-200); border-radius:var(--radius-lg);
  padding:1.35rem; margin-top:1.1rem;
}
.lesson-actions { display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; }

/* Playlist sidebar */
.playlist-box {
  background:#fff; border:1px solid var(--gray-200);
  border-radius:var(--radius-lg); overflow:hidden;
  display:flex; flex-direction:column; max-height:calc(100vh - 90px); position:sticky; top:70px;
}
.playlist-head {
  padding:1rem 1.1rem; border-bottom:1px solid var(--gray-200);
  background:var(--gray-50);
}
.playlist-head h4 { font-size:.95rem; margin-bottom:.25rem; }
.pl-progress-bar { background:var(--gray-200); border-radius:50px; height:5px; margin-top:.5rem; }
.pl-progress-fill { background:var(--primary); border-radius:50px; height:5px; transition:.4s; }

.playlist-scroll { overflow-y:auto; flex:1; }
.pl-item {
  display:flex; align-items:center; gap:.8rem;
  padding:.8rem 1rem; border-bottom:1px solid var(--gray-100);
  text-decoration:none; color:inherit; transition:var(--trans); cursor:pointer;
}
.pl-item:hover { background:var(--primary-50); }
.pl-item.active { background:var(--primary-50); border-left:3px solid var(--primary); }
.pl-item.done   { opacity:.7; }
.pl-num {
  width:28px; height:28px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:.75rem; font-weight:700;
  background:var(--gray-100); color:var(--gray-500);
}
.pl-item.active .pl-num { background:var(--primary); color:#fff; }
.pl-item.done   .pl-num { background:var(--success-bg); color:var(--success); }
.pl-info { flex:1; min-width:0; }
.pl-title { font-size:.86rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.pl-meta  { font-size:.75rem; color:var(--gray-400); margin-top:.15rem; display:flex; gap:.5rem; }
.pl-yt { color:#FF0000; font-weight:700; }

.cert-banner {
  margin:1rem; background:linear-gradient(135deg,#D1FAE5,#A7F3D0);
  border:1.5px solid #10B981; border-radius:var(--radius); padding:1rem;
  text-align:center; color:#065F46;
}
.cert-banner h4 { margin-bottom:.5rem; }

@media(max-width:900px){
  .watch-layout { grid-template-columns:1fr; }
  .playlist-box { max-height:400px; position:static; }
}
</style>
</head><body style="background:var(--gray-50);">

<!-- Top bar with SkillBloom logo -->
<div class="watch-topbar">
  <a href="<?=SITE_URL?>/user/dashboard.php" class="brand" style="text-decoration:none;display:flex;align-items:center;">
    <div style="overflow:hidden;height:38px;display:flex;align-items:center;">
      <img src="<?=SITE_URL?>/assets/images/logo.png" alt="SkillBloom"
        style="height:150px;width:auto;display:block;margin-top:-56px;margin-bottom:-56px;">
    </div>
  </a>
  <div class="course-title-bar"><?=htmlspecialchars($course['title'])?></div>
  <div style="display:flex;align-items:center;gap:.75rem;">
    <div class="progress-pill">📈 <?=$e['progress']?>% done</div>
    <a href="my-courses.php" class="btn btn-ghost btn-sm">← Back</a>
  </div>
</div>

<?php if($flash): ?><div class="alert alert-<?=$flash['type']==='error'?'error':'success'?>" style="margin:0;border-radius:0;border-left:none;border-right:none;"><?=$flash['type']==='success'?'✅':'❌'?> <?=htmlspecialchars($flash['msg'])?></div><?php endif; ?>

<div class="watch-layout">

<!-- LEFT: Video + Info -->
<div>
  <!-- Video Player -->
  <div class="video-box">
    <?php if($curVideo && !empty($curVideo['video_url'])): ?>
      <?php if(isYouTube($curVideo['video_url'])): ?>
        <?php $ytId = getYouTubeId($curVideo['video_url']); ?>
        <?php if($ytId): ?>
          <iframe
            src="https://www.youtube.com/embed/<?=$ytId?>?rel=0&modestbranding=1&autoplay=0"
            title="<?=htmlspecialchars($curVideo['title'])?>"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen>
          </iframe>
        <?php else: ?>
          <div class="video-placeholder"><div class="pl-icon">⚠️</div><p>Invalid YouTube link</p></div>
        <?php endif; ?>
      <?php else: ?>
        <!-- Direct video file (MP4 etc.) -->
        <video controls controlsList="nodownload">
          <source src="<?=htmlspecialchars($curVideo['video_url'])?>" type="video/mp4">
          Your browser does not support HTML5 video.
        </video>
      <?php endif; ?>
    <?php else: ?>
      <div class="video-placeholder">
        <div class="pl-icon">🎬</div>
        <p style="font-size:.95rem;">No video added for this lesson yet</p>
        <p style="font-size:.8rem;margin-top:.35rem;opacity:.6;">Admin can add a YouTube or direct video URL</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Lesson Info Card -->
  <div class="lesson-card">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
      <div style="flex:1;min-width:0;">
        <!-- YouTube badge if YT video -->
        <?php if($curVideo && !empty($curVideo['video_url']) && isYouTube($curVideo['video_url'])): ?>
        <div class="yt-badge">
          <svg viewBox="0 0 24 24"><path d="M21.8 8s-.2-1.4-.8-2c-.8-.8-1.6-.8-2-.9C16.2 5 12 5 12 5s-4.2 0-7 .1c-.4.1-1.2.1-2 .9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.5c0 1.6.2 3.2.2 3.2s.2 1.4.8 2c.8.8 1.8.8 2.3.9C6.8 19 12 19 12 19s4.2 0 7-.1c.4-.1 1.2-.1 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2v-1.5C22 9.6 21.8 8 21.8 8zM9.7 14.5V9l5.4 2.8-5.4 2.7z"/></svg>
          YouTube Video
        </div>
        <?php endif; ?>

        <h3 style="font-size:1.1rem;margin-bottom:.35rem;"><?=htmlspecialchars($curVideo ? $curVideo['title'] : 'No Lesson Selected')?></h3>
        <div style="font-size:.83rem;color:var(--gray-400);margin-bottom:.75rem;display:flex;gap:.75rem;flex-wrap:wrap;">
          <?php if($curVideo): ?>
          <span>⏱ <?=$curVideo['duration_min']?> min</span>
          <span>📖 Lesson <?php foreach($videos as $i=>$v){ if($v['id']==$vid_id){ echo ($i+1).'/'.count($videos); break; } } ?></span>
          <?php if($curVideo['is_preview']): ?><span style="color:var(--success);font-weight:700;">🆓 Free Preview</span><?php endif; ?>
          <?php if($curVideo['done']): ?><span style="color:var(--success);font-weight:700;">✅ Completed</span><?php endif; ?>
          <?php endif; ?>
        </div>
        <?php if($curVideo && $curVideo['description']): ?>
        <p style="font-size:.875rem;color:var(--gray-600);line-height:1.65;"><?=nl2br(htmlspecialchars($curVideo['description']))?></p>
        <?php endif; ?>
      </div>

      <!-- Action buttons -->
      <div class="lesson-actions">
        <?php
        $prevId=$nextId=null;
        for($i=0;$i<count($videos);$i++){
          if($videos[$i]['id']==$vid_id){
            if($i>0) $prevId=$videos[$i-1]['id'];
            if($i<count($videos)-1) $nextId=$videos[$i+1]['id'];
          }
        }
        ?>
        <?php if($prevId): ?><a href="?course_id=<?=$cid?>&video_id=<?=$prevId?>" class="btn btn-outline btn-sm">← Prev</a><?php endif; ?>
        <?php if($curVideo && !$curVideo['done']): ?>
          <a href="?course_id=<?=$cid?>&video_id=<?=$vid_id?>&mark=1" class="btn btn-success btn-sm">✅ Mark Complete</a>
        <?php elseif($curVideo && $curVideo['done']): ?>
          <span class="btn btn-ghost btn-sm" style="cursor:default;color:var(--success);">✅ Completed</span>
        <?php endif; ?>
        <?php if($nextId): ?><a href="?course_id=<?=$cid?>&video_id=<?=$nextId?>" class="btn btn-primary btn-sm">Next Lesson →</a><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- RIGHT: Playlist Sidebar -->
<div class="playlist-box">
  <div class="playlist-head">
    <h4>📋 Course Content</h4>
    <div style="font-size:.8rem;color:var(--gray-500);"><?=$doneCount?>/<?=count($videos)?> lessons completed</div>
    <div class="pl-progress-bar">
      <div class="pl-progress-fill" style="width:<?=$e['progress']?>%;"></div>
    </div>
  </div>

  <div class="playlist-scroll">
    <?php if($videos): ?>
    <?php foreach($videos as $i=>$v): ?>
    <a href="?course_id=<?=$cid?>&video_id=<?=$v['id']?>" class="pl-item <?=$v['id']==$vid_id?'active':''?> <?=$v['done']?'done':''?>">
      <div class="pl-num"><?=$v['done']?'✓':($i+1)?></div>
      <div class="pl-info">
        <div class="pl-title"><?=htmlspecialchars($v['title'])?></div>
        <div class="pl-meta">
          <span>⏱ <?=$v['duration_min']?> min</span>
          <?php if(!empty($v['video_url']) && isYouTube($v['video_url'])): ?>
          <span class="pl-yt">▶ YT</span>
          <?php elseif(!empty($v['video_url'])): ?>
          <span>🎬 Video</span>
          <?php else: ?>
          <span style="color:var(--gray-300);">No video</span>
          <?php endif; ?>
          <?php if($v['is_preview']): ?><span style="color:var(--success);">Free</span><?php endif; ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php else: ?>
    <div style="text-align:center;padding:2.5rem 1rem;color:var(--gray-400);">
      <div style="font-size:2.5rem;margin-bottom:.75rem;">📭</div>
      <p style="font-size:.875rem;">No lessons added yet</p>
    </div>
    <?php endif; ?>
  </div>

  <?php if($e['progress']>=100): ?>
  <div class="cert-banner">
    <h4>🏆 Course Completed!</h4>
    <a href="certificate.php?course_id=<?=$cid?>" class="btn btn-success btn-block">Download Certificate</a>
  </div>
  <?php endif; ?>
</div>

</div>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
