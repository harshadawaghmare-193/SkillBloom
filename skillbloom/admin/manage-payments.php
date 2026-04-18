<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();
if(isset($_GET['delete'])){ $conn->query("DELETE FROM payments WHERE id=".intval($_GET['delete'])); flash('success','Payment record deleted.'); redirect('manage-payments.php'); }
$status=clean((isset($_GET['status']) ? $_GET['status'] : ''));
$where='1'; if($status) $where="p.status='$status'";
$payments=$conn->query("SELECT p.*,u.name uname,u.email uemail,c.title ctitle FROM payments p JOIN users u ON u.id=p.user_id JOIN courses c ON c.id=p.course_id WHERE $where ORDER BY p.paid_at DESC")->fetch_all(MYSQLI_ASSOC);
$total=$conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='success'")->fetch_assoc()['s'];
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Payments — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar"><h3>💳 Manage Payments</h3>
  <div style="display:flex;gap:.5rem;">
    <?php foreach([''=>'All','success'=>'Success','failed'=>'Failed','pending'=>'Pending'] as $k=>$v): ?>
    <a href="?status=<?php echo $k; ?>" class="btn btn-sm <?php echo $status===$k?'btn-primary':'btn-ghost'; ?>"><?php echo $v; ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem;">
  <div class="stat-card"><div class="stat-icon green">💰</div><div><div class="stat-val"><?php echo CURRENCY.number_format($total); ?></div><div class="stat-label">Total Revenue</div></div></div>
  <div class="stat-card"><div class="stat-icon blue">✅</div><div><div class="stat-val"><?php echo $conn->query("SELECT COUNT(*) c FROM payments WHERE status='success'")->fetch_assoc()['c']; ?></div><div class="stat-label">Successful</div></div></div>
  <div class="stat-card"><div class="stat-icon red">❌</div><div><div class="stat-val"><?php echo $conn->query("SELECT COUNT(*) c FROM payments WHERE status='failed'")->fetch_assoc()['c']; ?></div><div class="stat-label">Failed</div></div></div>
</div>

<div class="table-box">
  <div class="table-top"><h3>Payment Records <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?php echo count($payments); ?>)</span></h3>
    <div class="search-input"><span class="si">🔍</span><input type="text" id="tblSearch" placeholder="Search..."></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Student</th><th>Course</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach($payments as $i=>$p): ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td><div style="font-weight:600;"><?php echo htmlspecialchars($p['uname']); ?></div><div style="font-size:.78rem;color:var(--gray-400);"><?php echo htmlspecialchars($p['uemail']); ?></div></td>
      <td style="font-size:.875rem;"><?php echo htmlspecialchars(substr($p['ctitle'],0,30)); ?></td>
      <td style="font-weight:800;color:var(--success);"><?php echo CURRENCY.number_format($p['amount']); ?></td>
      <td style="text-transform:capitalize;"><?php echo htmlspecialchars((isset($p['method']) ? $p['method'] : '—')); ?></td>
      <td style="font-size:.78rem;font-family:monospace;"><?php echo htmlspecialchars((isset($p['txn_id']) ? $p['txn_id'] : '—')); ?></td>
      <td><span class="badge <?php echo $p['status']==='success'?'badge-success':($p['status']==='failed'?'badge-danger':'badge-warning'); ?>"><?php echo ucfirst($p['status']); ?></span></td>
      <td style="font-size:.82rem;color:var(--gray-400);"><?php echo date('M d, Y',strtotime($p['paid_at'])); ?></td>
      <td><a href="?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$payments): ?><tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400);">No payments found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
