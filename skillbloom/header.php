<?php
require_once __DIR__ . '/config.php';
$flash = getFlash();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
</head>
<body>

<nav class="navbar">
    <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="nav-brand" style="text-decoration:none;display:flex;align-items:center;">
        <div style="overflow:hidden;height:52px;display:flex;align-items:center;">
          <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="SkillBloom"
            style="height:200px;width:auto;display:block;margin-top:-74px;margin-bottom:-74px;">
        </div>
    </a>
    
    <ul class="nav-links">
        <li><a href="<?php echo SITE_URL; ?>/user/courses.php" class="<?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">🎓 Courses</a></li>
        <li><a href="<?php echo SITE_URL; ?>/user/my-courses.php" class="<?php echo $current_page == 'my-courses.php' ? 'active' : ''; ?>">📚 My Learning</a></li>
        <li><a href="<?php echo SITE_URL; ?>/user/my-certificates.php" class="<?php echo $current_page == 'my-certificates.php' ? 'active' : ''; ?>">🏆 Certificates</a></li>
    </ul>

    <div class="nav-right">
        <?php if (isUser()): ?>
            <?php
            $user_init = strtoupper(substr((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'U'), 0, 1));
            ?>
            <div class="dropdown">
                <div class="avatar dropdown-toggle" title="<?php echo htmlspecialchars((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '')); ?>">
                    <?php echo $user_init; ?>
                </div>
                <div class="dropdown-menu">
                    <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200);">
                        <div style="font-weight: 700; font-size: 0.9rem;"><?php echo htmlspecialchars((isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '')); ?></div>
                        <div style="font-size: 0.8rem; color: var(--gray-400);"><?php echo htmlspecialchars((isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '')); ?></div>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/user/dashboard.php">🏠 Dashboard</a>
                    <a href="<?php echo SITE_URL; ?>/user/profile.php">👤 Profile</a>
                    <a href="<?php echo SITE_URL; ?>/user/my-courses.php">📚 My Courses</a>
                    <a href="<?php echo SITE_URL; ?>/user/my-certificates.php">🏆 Certificates</a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo SITE_URL; ?>/user/logout.php" class="text-danger">🚪 Sign Out</a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/user/login.php" class="btn btn-ghost btn-sm">Sign In</a>
            <a href="<?php echo SITE_URL; ?>/user/register.php" class="btn btn-primary btn-sm">Get Started</a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($flash): ?>
<div class="alert alert-<?php echo $flash['type']; ?>" style="margin: 0; border-radius: 0; border-left: none; border-right: none;">
    <span><?php echo $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'danger' ? '❌' : 'ℹ️'); ?></span>
    <?php echo htmlspecialchars($flash['msg']); ?>
    <button class="close-btn" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 1.1rem;">×</button>
</div>
<?php endif; ?>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js" defer></script>
