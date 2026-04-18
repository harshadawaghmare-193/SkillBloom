<?php
$cur=basename($_SERVER['PHP_SELF']);
$adminMenu=[
  ['Dashboard','dashboard.php','📊'],
  ['Users','manage-users.php','👥'],
  ['Courses','manage-courses.php','🎓'],
  ['Videos','manage-videos.php','🎥'],
  ['Categories','manage-categories.php','🗂'],
  ['Payments','manage-payments.php','💳'],
  ['Certificates','manage-certificates.php','🏆'],
  ['Feedback','manage-feedback.php','💬'],
];
?>
<aside class="admin-sidebar">
  <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" style="display:flex;align-items:center;justify-content:center;padding:.75rem 1rem;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08);background:#fff;">
    <div style="overflow:hidden;height:52px;display:flex;align-items:center;">
      <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="SkillBloom"
        style="height:200px;width:auto;display:block;margin-top:-74px;margin-bottom:-74px;">
    </div>
  </a>
  <div style="padding:.6rem 0;">
    <div class="sb-label">Main Menu</div>
    <?php foreach($adminMenu as $menuItem): $label=$menuItem[0]; $file=$menuItem[1]; $ico=$menuItem[2]; ?>
    <a href="<?php echo SITE_URL.'/admin/'.$file; ?>" class="sb-link <?php echo $cur===$file?'active':''; ?>">
      <span><?php echo $ico; ?></span><?php echo $label; ?>
    </a>
    <?php endforeach; ?>
    <div class="sb-label">Account</div>
    <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="sb-link" target="_blank"><span>🌐</span>View Site</a>
    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="sb-link" style="color:rgba(255,80,80,.8);"><span>🚪</span>Sign Out</a>
  </div>
</aside>
