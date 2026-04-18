<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();
if(isset($_GET['delete'])){ $conn->query("DELETE FROM certificates WHERE id=".intval($_GET['delete'])); flash('success','Certificate revoked.'); redirect('manage-certificates.php'); }
// Grant Certificate
if($_SERVER['REQUEST_METHOD']==='POST'){
  $uid=intval((isset($_POST['user_id']) ? $_POST['user_id'] : 0)); $cid=intval((isset($_POST['course_id']) ? $_POST['course_id'] : 0));
  if($uid && $cid){
    $cn=genCertNo();
    $conn->query("INSERT IGNORE INTO certificates(user_id,course_id,cert_no) VALUES($uid,$cid,'$cn')");
    flash('success','Certificate granted!'); redirect('manage-certificates.php');
  }
}
$search=clean((isset($_GET['q']) ? $_GET['q'] : '')); $where='1';
if($search) $where="(u.name LIKE '%$search%' OR c.title LIKE '%$search%' OR cert.cert_no LIKE '%$search%')";
$certs=$conn->query("SELECT cert.*,u.name uname,u.email uemail,c.title ctitle,c.instructor FROM certificates cert JOIN users u ON u.id=cert.user_id JOIN courses c ON c.id=cert.course_id WHERE $where ORDER BY cert.issued_at DESC")->fetch_all(MYSQLI_ASSOC);
$users=$conn->query("SELECT id,name FROM users ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$courses=$conn->query("SELECT id,title FROM courses WHERE status='published' ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Certificates — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <h3>🏆 Manage Certificates</h3>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <form method="GET" style="display:flex;gap:.5rem;">
      <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." class="form-control" style="width:200px;">
      <button type="submit" class="btn btn-outline btn-sm">Search</button>
    </form>
    <button class="btn btn-primary btn-sm" data-modal="grantModal">+ Grant Certificate</button>
  </div>
</div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<div class="table-box">
  <div class="table-top"><h3>All Certificates <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?php echo count($certs); ?> total)</span></h3></div>
  <table>
    <thead><tr><th>#</th><th>Certificate No</th><th>Student</th><th>Course</th><th>Instructor</th><th>Issued</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($certs as $i=>$c): ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td style="font-family:monospace;font-size:.85rem;font-weight:700;color:var(--primary);"><?php echo htmlspecialchars($c['cert_no']); ?></td>
      <td><div style="font-weight:600;"><?php echo htmlspecialchars($c['uname']); ?></div><div style="font-size:.78rem;color:var(--gray-400);"><?php echo htmlspecialchars($c['uemail']); ?></div></td>
      <td style="font-size:.875rem;"><?php echo htmlspecialchars(substr($c['ctitle'],0,30)); ?></td>
      <td style="font-size:.875rem;"><?php echo htmlspecialchars($c['instructor']); ?></td>
      <td style="font-size:.82rem;color:var(--gray-400);"><?php echo date('M d, Y',strtotime($c['issued_at'])); ?></td>
      <td><div class="td-actions">
        <a href="<?php echo SITE_URL; ?>/user/certificate.php?course_id=<?php echo $c['course_id']; ?>" target="_blank" class="btn btn-outline btn-sm">👁️</a>
        <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDel('Revoke this certificate?')">🗑️</a>
      </div></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$certs): ?><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-400);">No certificates found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>

<!-- Grant Modal -->
<div id="grantModal" class="modal-overlay">
<div class="modal">
  <div class="modal-header"><h3>🏆 Grant Certificate</h3><button class="modal-close" data-modal-close>×</button></div>
  <form method="POST">
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Student *</label>
        <select name="user_id" class="form-select" required>
          <option value="">-- Select Student --</option>
          <?php foreach($users as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Course *</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- Select Course --</option>
          <?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
      <button type="submit" class="btn btn-primary">🏆 Grant Certificate</button>
    </div>
  </form>
</div>
</div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
