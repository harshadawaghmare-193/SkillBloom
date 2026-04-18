<?php $cur=basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar" style="background:#0F172A !important;border-right:none !important;">
  <div class="sidebar-section">
    <div class="sidebar-label" style="color:rgba(255,255,255,.3) !important;">Main</div>
    <a href="<?=SITE_URL?>/user/dashboard.php" class="sidebar-link <?=$cur==='dashboard.php'?'active':''?>"
      style="color:<?=$cur==='dashboard.php'?'#fff':'rgba(255,255,255,.6)'?>;<?=$cur==='dashboard.php'?'background:#1e3a8a;':''?>"><span class="s-icon">🏠</span> Dashboard</a>
    <a href="<?=SITE_URL?>/user/courses.php" class="sidebar-link <?=$cur==='courses.php'?'active':''?>"
      style="color:<?=$cur==='courses.php'?'#fff':'rgba(255,255,255,.6)'?>;<?=$cur==='courses.php'?'background:#1e3a8a;':''?>"><span class="s-icon">🎓</span> Browse Courses</a>
    <a href="<?=SITE_URL?>/user/my-courses.php" class="sidebar-link <?=$cur==='my-courses.php'?'active':''?>"
      style="color:<?=$cur==='my-courses.php'?'#fff':'rgba(255,255,255,.6)'?>;<?=$cur==='my-courses.php'?'background:#1e3a8a;':''?>"><span class="s-icon">📚</span> My Learning</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label" style="color:rgba(255,255,255,.3) !important;">Achievements</div>
    <a href="<?=SITE_URL?>/user/my-certificates.php" class="sidebar-link <?=$cur==='my-certificates.php'?'active':''?>"
      style="color:<?=$cur==='my-certificates.php'?'#fff':'rgba(255,255,255,.6)'?>;<?=$cur==='my-certificates.php'?'background:#1e3a8a;':''?>"><span class="s-icon">🏆</span> Certificates</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label" style="color:rgba(255,255,255,.3) !important;">Account</div>
    <a href="<?=SITE_URL?>/user/profile.php" class="sidebar-link <?=$cur==='profile.php'?'active':''?>"
      style="color:<?=$cur==='profile.php'?'#fff':'rgba(255,255,255,.6)'?>;<?=$cur==='profile.php'?'background:#1e3a8a;':''?>"><span class="s-icon">👤</span> Profile</a>
    <a href="<?=SITE_URL?>/user/logout.php" class="sidebar-link" style="color:rgba(255,80,80,.8);"><span class="s-icon">🚪</span> Sign Out</a>
  </div>
</aside>
