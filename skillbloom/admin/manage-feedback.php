<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();

$conn->query("CREATE TABLE IF NOT EXISTS feedback(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(150),
  subject VARCHAR(255),
  type VARCHAR(60) DEFAULT 'General',
  rating TINYINT DEFAULT 5,
  message TEXT,
  status ENUM('new','read','resolved') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if(isset($_GET['setstatus']) && isset($_GET['id'])){
  $fid = intval($_GET['id']);
  $st  = in_array($_GET['setstatus'],['new','read','resolved']) ? $_GET['setstatus'] : 'read';
  $conn->query("UPDATE feedback SET status='$st' WHERE id=$fid");
  flash('success','Status updated.'); redirect('manage-feedback.php'.(isset($_GET['filter'])?'?filter='.$_GET['filter']:''));
}
if(isset($_GET['delete'])){
  $conn->query("DELETE FROM feedback WHERE id=".intval($_GET['delete']));
  flash('success','Feedback deleted.'); redirect('manage-feedback.php');
}

$filter = in_array((isset($_GET['filter'])?$_GET['filter']:'all'),['all','new','read','resolved']) ? (isset($_GET['filter'])?$_GET['filter']:'all') : 'all';
$wh  = $filter !== 'all' ? "WHERE status='$filter'" : '';
$all = $conn->query("SELECT * FROM feedback $wh ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$cnt = [
  'all'      => $conn->query("SELECT COUNT(*) c FROM feedback")->fetch_assoc()['c'],
  'new'      => $conn->query("SELECT COUNT(*) c FROM feedback WHERE status='new'")->fetch_assoc()['c'],
  'read'     => $conn->query("SELECT COUNT(*) c FROM feedback WHERE status='read'")->fetch_assoc()['c'],
  'resolved' => $conn->query("SELECT COUNT(*) c FROM feedback WHERE status='resolved'")->fetch_assoc()['c'],
];
$flash = getFlash();
// Avg rating
$avgR = $conn->query("SELECT AVG(rating) r FROM feedback")->fetch_assoc()['r'];
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Feedback — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<style>
.ftab { display:inline-flex;align-items:center;gap:.4rem;padding:.42rem 1.1rem;border-radius:50px;font-size:.83rem;font-weight:700;border:2px solid var(--gray-200);color:var(--gray-500);text-decoration:none;transition:var(--trans); }
.ftab:hover,.ftab.on { border-color:var(--primary);background:var(--primary);color:#fff; }
.msg-text { font-size:.855rem;color:var(--gray-600);line-height:1.55;max-width:440px; }
.star-disp { color:var(--warning);font-size:.95rem;letter-spacing:1px; }
</style>
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <h3>💬 Manage Feedback</h3>
  <div class="avatar-wrap">
    <button class="avatar-btn"><?php echo strtoupper(substr($_SESSION['admin_name'],0,1)); ?></button>
    <div class="dropdown-menu">
      <div class="dd-user-info"><strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>Admin</div><hr>
      <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="danger">🚪 Sign Out</a>
    </div>
  </div>
</div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<!-- Stats -->
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem;">
  <div class="stat-card"><div class="stat-icon blue">💬</div><div><div class="stat-val"><?=$cnt['all']?></div><div class="stat-label">Total Messages</div></div></div>
  <div class="stat-card"><div class="stat-icon purple">🆕</div><div><div class="stat-val"><?=$cnt['new']?></div><div class="stat-label">New</div></div></div>
  <div class="stat-card"><div class="stat-icon yellow">👁️</div><div><div class="stat-val"><?=$cnt['read']?></div><div class="stat-label">Read</div></div></div>
  <div class="stat-card"><div class="stat-icon green">⭐</div><div><div class="stat-val"><?=number_format(floatval($avgR),1)?></div><div class="stat-label">Avg Rating</div></div></div>
</div>

<!-- Filter tabs -->
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
  <?php foreach(['all'=>'All','new'=>'🆕 New','read'=>'👁️ Read','resolved'=>'✅ Resolved'] as $k=>$lbl): ?>
  <a href="?filter=<?=$k?>" class="ftab <?=$filter===$k?'on':''?>"><?=$lbl?> (<?=$cnt[$k]?>)</a>
  <?php endforeach; ?>
</div>

<div class="table-box">
  <div class="table-top">
    <h3>Messages <span style="font-size:.82rem;color:var(--gray-400);font-weight:400;">(<?=count($all)?> shown)</span></h3>
    <div class="search-input"><span class="si">🔍</span><input type="text" id="tblSearch" placeholder="Search..."></div>
  </div>
  <?php if($all): ?>
  <table>
    <thead><tr><th>#</th><th>From</th><th>Subject &amp; Message</th><th>Type</th><th>Rating</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($all as $i=>$fb): ?>
    <tr>
      <td style="color:var(--gray-400);font-size:.82rem;"><?=$i+1?></td>
      <td><div style="font-weight:700;font-size:.88rem;"><?=htmlspecialchars($fb['name'])?></div></td>
      <td>
        <div style="font-weight:700;font-size:.88rem;margin-bottom:.25rem;"><?=htmlspecialchars($fb['subject'])?></div>
        <div class="msg-text"><?=htmlspecialchars(substr($fb['message'],0,130)).(strlen($fb['message'])>130?'…':'')?></div>
      </td>
      <td><span class="badge" style="font-size:.72rem;"><?=htmlspecialchars($fb['type'])?></span></td>
      <td>
        <div class="star-disp"><?=str_repeat('★',$fb['rating'])?><?=str_repeat('☆',5-$fb['rating'])?></div>
        <div style="font-size:.75rem;color:var(--gray-400);"><?=$fb['rating']?>/5</div>
      </td>
      <td>
        <?php
          $bc = $fb['status']==='new'?'badge-warning':($fb['status']==='resolved'?'badge-success':'badge');
        ?>
        <span class="badge <?=$bc?>"><?=ucfirst($fb['status'])?></span>
      </td>
      <td style="font-size:.78rem;color:var(--gray-400);"><?=date('M d, Y',strtotime($fb['created_at']))?><br><?=date('H:i',strtotime($fb['created_at']))?></td>
      <td>
        <div class="td-actions" style="flex-wrap:wrap;">
          <?php if($fb['status']!=='read'): ?><a href="?id=<?=$fb['id']?>&setstatus=read&filter=<?=$filter?>" class="btn btn-ghost btn-sm" title="Mark Read">👁️</a><?php endif; ?>
          <?php if($fb['status']!=='resolved'): ?><a href="?id=<?=$fb['id']?>&setstatus=resolved&filter=<?=$filter?>" class="btn btn-success btn-sm" title="Resolve">✅</a><?php endif; ?>
          <?php if($fb['status']!=='new'): ?><a href="?id=<?=$fb['id']?>&setstatus=new&filter=<?=$filter?>" class="btn btn-outline btn-sm" title="Mark New">🔄</a><?php endif; ?>
          <a href="?delete=<?=$fb['id']?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state" style="padding:3rem;">
    <div class="es-icon">💬</div>
    <h3>No feedback found</h3>
    <p>No messages for the selected filter yet.</p>
  </div>
  <?php endif; ?>
</div>

</div></div></div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
