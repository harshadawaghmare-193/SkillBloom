<?php
$cur=basename($_SERVER['PHP_SELF']);
$flash=(isset($flash) ? $flash : null);
?>
<nav class="navbar">
  <a href="<?=SITE_URL?>/user/dashboard.php" class="nav-brand" style="text-decoration:none;display:flex;align-items:center;">
    <div style="overflow:hidden;height:52px;display:flex;align-items:center;">
      <img src="<?=SITE_URL?>/assets/images/logo.png" alt="SkillBloom"
        style="height:200px;width:auto;display:block;margin-top:-74px;margin-bottom:-74px;">
    </div>
  </a>
  <ul class="nav-links">
    <li><a href="<?=SITE_URL?>/user/dashboard.php" class="<?=$cur==='dashboard.php'?'active':''?>">🏠 Home</a></li>
    <li><a href="<?=SITE_URL?>/user/courses.php" class="<?=$cur==='courses.php'?'active':''?>">🎓 Courses</a></li>
    <li><a href="<?=SITE_URL?>/user/my-courses.php" class="<?=$cur==='my-courses.php'?'active':''?>">📚 My Learning</a></li>
    <li><a href="<?=SITE_URL?>/user/my-certificates.php" class="<?=$cur==='my-certificates.php'?'active':''?>">🏆 Certificates</a></li>
    <li><a href="<?=SITE_URL?>/user/feedback.php" class="<?=$cur==='feedback.php'?'active':''?>">💬 Feedback</a></li>
  </ul>
  <div class="nav-right">
    <?php if(isUser()): ?>
    <div class="avatar-wrap">
      <button class="avatar-btn"><?=strtoupper(substr($_SESSION['user_name'],0,1))?></button>
      <div class="dropdown-menu">
        <div class="dd-user-info"><strong><?=htmlspecialchars($_SESSION['user_name'])?></strong><?=htmlspecialchars((isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''))?></div>
        <hr>
        <a href="<?=SITE_URL?>/user/profile.php">👤 My Profile</a>
        <a href="<?=SITE_URL?>/user/my-courses.php">📚 My Courses</a>
        <a href="<?=SITE_URL?>/user/my-certificates.php">🏆 Certificates</a>
        <a href="<?=SITE_URL?>/user/feedback.php">💬 Feedback</a>
        <hr>
        <a href="<?=SITE_URL?>/user/logout.php" class="danger">🚪 Sign Out</a>
      </div>
    </div>
    <?php else: ?>
    <a href="<?=SITE_URL?>/user/login.php" class="btn btn-ghost btn-sm">Sign In</a>
    <a href="<?=SITE_URL?>/user/register.php" class="btn btn-primary btn-sm">Get Started</a>
    <?php endif; ?>
  </div>
</nav>
