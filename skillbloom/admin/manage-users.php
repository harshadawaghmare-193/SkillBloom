<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();

// Delete
if(isset($_GET['delete'])){
  $id=intval($_GET['delete']);
  $conn->query("DELETE FROM users WHERE id=$id");
  flash('success','User deleted.'); redirect('manage-users.php');
}
// Toggle Status
if(isset($_GET['toggle'])){
  $id=intval($_GET['toggle']);
  $u=$conn->query("SELECT status FROM users WHERE id=$id")->fetch_assoc();
  $ns=$u['status']==='active'?'banned':'active';
  $conn->query("UPDATE users SET status='$ns' WHERE id=$id");
  flash('success','User status updated.'); redirect('manage-users.php');
}
// Add/Edit
if($_SERVER['REQUEST_METHOD']==='POST'){
  $uid=intval((isset($_POST['uid']) ? $_POST['uid'] : 0));
  $name=clean((isset($_POST['name']) ? $_POST['name'] : '')); $email=clean((isset($_POST['email']) ? $_POST['email'] : '')); $status=clean((isset($_POST['status']) ? $_POST['status'] : 'active'));
  if($uid){
    $conn->query("UPDATE users SET name='$name',status='$status' WHERE id=$uid");
    flash('success','User updated.');
  } else {
    $pw=password_hash((isset($_POST['password']) ? $_POST['password'] : 'Password@1'),PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users(name,email,password,status) VALUES('$name','$email','$pw','$status')");
    flash('success','User added.');
  }
  redirect('manage-users.php');
}

$search=clean((isset($_GET['q']) ? $_GET['q'] : ''));
$where='1'; if($search) $where="(name LIKE '%$search%' OR email LIKE '%$search%')";
$users=$conn->query("SELECT u.*,(SELECT COUNT(*) FROM enrollments WHERE user_id=u.id) enrolled FROM users u WHERE $where ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$editUser=null; if(isset($_GET['edit'])) $editUser=$conn->query("SELECT * FROM users WHERE id=".intval($_GET['edit']))->fetch_assoc();
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Users — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <h3>👥 Manage Users</h3>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <form method="GET" style="display:flex;gap:.5rem;">
      <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users..." class="form-control" style="width:220px;">
      <button type="submit" class="btn btn-outline btn-sm">Search</button>
    </form>
    <button class="btn btn-primary btn-sm" data-modal="addUserModal">+ Add User</button>
  </div>
</div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<div class="table-box">
  <div class="table-top"><h3>All Users <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?php echo count($users); ?> total)</span></h3></div>
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Enrolled</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($users as $i=>$u): ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td><div style="display:flex;align-items:center;gap:.65rem;"><div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0;"><?php echo strtoupper(substr($u['name'],0,1)); ?></div><div><?php echo htmlspecialchars($u['name']); ?></div></div></td>
      <td><?php echo htmlspecialchars($u['email']); ?></td>
      <td><span class="badge"><?php echo $u['enrolled']; ?> courses</span></td>
      <td><span class="badge <?php echo $u['status']==='active'?'badge-success':'badge-danger'; ?>"><?php echo ucfirst($u['status']); ?></span></td>
      <td style="font-size:.82rem;color:var(--gray-400);"><?php echo date('M d, Y',strtotime($u['created_at'])); ?></td>
      <td>
        <div class="td-actions">
          <a href="?edit=<?php echo $u['id']; ?>" class="btn btn-outline btn-sm" data-modal="addUserModal">✏️</a>
          <a href="?toggle=<?php echo $u['id']; ?>" class="btn btn-sm <?php echo $u['status']==='active'?'btn-danger':'btn-success'; ?>" onclick="return confirmDel('<?php echo $u['status']==='active'?'Ban':'Unban'; ?> this user?')"><?php echo $u['status']==='active'?'🚫':'✅'; ?></a>
          <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$users): ?><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-400);">No users found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>

<!-- Add/Edit User Modal -->
<div id="addUserModal" class="modal-overlay <?php echo $editUser?'open':''; ?>">
<div class="modal">
  <div class="modal-header"><h3><?php echo $editUser?'✏️ Edit User':'➕ Add New User'; ?></h3><button class="modal-close" data-modal-close>×</button></div>
  <form method="POST">
    <div class="modal-body">
      <input type="hidden" name="uid" value="<?php echo (isset($editUser['id']) ? $editUser['id'] : 0); ?>">
      <div class="form-group"><label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars((isset($editUser['name']) ? $editUser['name'] : '')); ?>" required>
      </div>
      <?php if(!$editUser): ?>
      <div class="form-group"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Default: Password@1">
      </div>
      <?php endif; ?>
      <div class="form-group"><label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="active" <?php echo ((isset($editUser['status']) ? $editUser['status'] : ''))==='active'?'selected':''; ?>>Active</option>
          <option value="banned" <?php echo ((isset($editUser['status']) ? $editUser['status'] : ''))==='banned'?'selected':''; ?>>Banned</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
      <button type="submit" class="btn btn-primary"><?php echo $editUser?'💾 Save Changes':'➕ Add User'; ?></button>
    </div>
  </form>
</div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
