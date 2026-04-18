<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();
if(isset($_GET['delete'])){ $conn->query("DELETE FROM categories WHERE id=".intval($_GET['delete'])); flash('success','Category deleted.'); redirect('manage-categories.php'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $cid=intval((isset($_POST['cid']) ? $_POST['cid'] : 0)); $name=clean((isset($_POST['name']) ? $_POST['name'] : '')); $slug=strtolower(preg_replace('/[^a-z0-9]+/','-',$name)); $icon=$conn->real_escape_string(trim((isset($_POST['icon']) ? $_POST['icon'] : '📚'))); $desc=clean((isset($_POST['description']) ? $_POST['description'] : ''));
  if($cid){ $conn->query("UPDATE categories SET name='$name',slug='$slug',icon='$icon',description='$desc' WHERE id=$cid"); flash('success','Category updated.'); }
  else { $conn->query("INSERT INTO categories(name,slug,icon,description) VALUES('$name','$slug','$icon','$desc')"); flash('success','Category added.'); }
  redirect('manage-categories.php');
}
$cats=$conn->query("SELECT cat.*,(SELECT COUNT(*) FROM courses WHERE category_id=cat.id) cnt FROM categories cat ORDER BY cat.name")->fetch_all(MYSQLI_ASSOC);
$editCat=null; if(isset($_GET['edit'])) $editCat=$conn->query("SELECT * FROM categories WHERE id=".intval($_GET['edit']))->fetch_assoc();
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Categories — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar"><h3>🗂 Manage Categories</h3><button class="btn btn-primary btn-sm" data-modal="catModal">+ Add Category</button></div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<div class="table-box">
  <div class="table-top"><h3>Categories <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?php echo count($cats); ?> total)</span></h3></div>
  <table>
    <thead><tr><th>#</th><th>Icon</th><th>Name</th><th>Slug</th><th>Description</th><th>Courses</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($cats as $i=>$c): ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td style="font-size:1.6rem;"><?php echo $c['icon']; ?></td>
      <td style="font-weight:700;"><?php echo htmlspecialchars($c['name']); ?></td>
      <td style="font-size:.82rem;color:var(--gray-400);"><?php echo $c['slug']; ?></td>
      <td style="font-size:.875rem;"><?php echo htmlspecialchars(substr((isset($c['description']) ? $c['description'] : ''),0,40)); ?></td>
      <td><span class="badge"><?php echo $c['cnt']; ?> courses</span></td>
      <td><div class="td-actions">
        <a href="?edit=<?php echo $c['id']; ?>" class="btn btn-outline btn-sm">✏️</a>
        <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a>
      </div></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>

<div id="catModal" class="modal-overlay <?php echo $editCat?'open':''; ?>">
<div class="modal">
  <div class="modal-header"><h3><?php echo $editCat?'✏️ Edit Category':'➕ Add Category'; ?></h3><button class="modal-close" data-modal-close>×</button></div>
  <form method="POST">
    <div class="modal-body">
      <input type="hidden" name="cid" value="<?php echo (isset($editCat['id']) ? $editCat['id'] : 0); ?>">
      <div style="display:grid;grid-template-columns:3fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Category Name *</label>
          <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars((isset($editCat['name']) ? $editCat['name'] : '')); ?>" required>
        </div>
        <div class="form-group"><label class="form-label">Icon (emoji)</label>
          <input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars((isset($editCat['icon']) ? $editCat['icon'] : '📚')); ?>">
        </div>
      </div>
      <div class="form-group"><label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars((isset($editCat['description']) ? $editCat['description'] : '')); ?></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
      <button type="submit" class="btn btn-primary"><?php echo $editCat?'💾 Save':'➕ Add'; ?></button>
    </div>
  </form>
</div>
</div>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
