<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();

// Delete
if(isset($_GET['delete'])){
  $id = intval($_GET['delete']);
  // Also delete thumbnail file
  $old = $conn->query("SELECT thumbnail FROM courses WHERE id=$id")->fetch_assoc();
  if(!empty($old['thumbnail'])) @unlink(UPLOAD_DIR.$old['thumbnail']);
  $conn->query("DELETE FROM courses WHERE id=$id");
  flash('success','Course deleted.');
  redirect('manage-courses.php');
}

// Toggle Status
if(isset($_GET['toggle'])){
  $id=intval($_GET['toggle']);
  $c=$conn->query("SELECT status FROM courses WHERE id=$id")->fetch_assoc();
  $ns=$c['status']==='published'?'draft':'published';
  $conn->query("UPDATE courses SET status='$ns' WHERE id=$id");
  flash('success','Status updated to '.ucfirst($ns).'.');
  redirect('manage-courses.php');
}

// Save (Add / Edit)
if($_SERVER['REQUEST_METHOD']==='POST'){
  $cid   = intval(isset($_POST['cid']) ? $_POST['cid'] : 0);
  $cat   = intval(isset($_POST['category_id']) ? $_POST['category_id'] : 0);
  $title = clean(isset($_POST['title']) ? $_POST['title'] : '');
  $slug  = strtolower(preg_replace('/[^a-z0-9]+/','-',strip_tags($_POST['title']))).'-'.time();
  $desc  = clean(isset($_POST['description']) ? $_POST['description'] : '');
  $short = clean(isset($_POST['short_desc']) ? $_POST['short_desc'] : '');
  $instr = clean(isset($_POST['instructor']) ? $_POST['instructor'] : '');
  $price = floatval(isset($_POST['price']) ? $_POST['price'] : 0);
  $old   = floatval(isset($_POST['old_price']) ? $_POST['old_price'] : 0);
  $hrs   = floatval(isset($_POST['hours']) ? $_POST['hours'] : 0);
  $level  = clean(isset($_POST['level']) ? $_POST['level'] : 'All Levels');
  $status = clean(isset($_POST['status']) ? $_POST['status'] : 'draft');
  $feat   = isset($_POST['featured']) ? 1 : 0;

  // ── Thumbnail Upload ──────────────────────────────────────────
  $thumbSql = '';
  if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error']===0){
    $file    = $_FILES['thumbnail'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if(!in_array($ext,$allowed)){
      flash('error','Only JPG, PNG, WEBP, GIF images allowed.');
      redirect('manage-courses.php'.($cid?"?edit=$cid":''));
    }
    if($file['size'] > 3*1024*1024){
      flash('error','Image must be under 3MB.');
      redirect('manage-courses.php'.($cid?"?edit=$cid":''));
    }
    // Delete old thumbnail if editing
    if($cid){
      $oldThumb = $conn->query("SELECT thumbnail FROM courses WHERE id=$cid")->fetch_assoc();
      if(!empty($oldThumb['thumbnail'])) @unlink(UPLOAD_DIR.$oldThumb['thumbnail']);
    }
    $filename = 'course_'.time().'_'.rand(1000,9999).'.'.$ext;
    if(!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$filename);
    $thumbSql = ",thumbnail='$filename'";
  }

  $tnField = $thumbSql ? ',thumbnail' : '';
  $tnVal   = $thumbSql ? ",'$filename'" : '';
  if($cid){
    $conn->query("UPDATE courses SET category_id=$cat,title='$title',description='$desc',short_desc='$short',instructor='$instr',price=$price,old_price=$old,hours=$hrs,level='$level',status='$status',featured=$feat$thumbSql WHERE id=$cid");
    flash('success','Course updated successfully.');
  } else {
    $conn->query("INSERT INTO courses(category_id,title,slug,description,short_desc,instructor,price,old_price,hours,level,status,featured$tnField) VALUES($cat,'$title','$slug','$desc','$short','$instr',$price,$old,$hrs,'$level','$status',$feat$tnVal)");
    flash('success','Course added successfully.');
  }
  redirect('manage-courses.php');
}

$courses    = $conn->query("SELECT c.*,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id ORDER BY c.id DESC")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$editCourse = null;
if(isset($_GET['edit'])) $editCourse=$conn->query("SELECT * FROM courses WHERE id=".intval($_GET['edit']))->fetch_assoc();
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Courses — SkillBloom Admin</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
/* Thumbnail preview */
.thumb-preview-box {
  width:100%; height:160px;
  border:2px dashed #BFDBFE;
  border-radius:12px;
  background:#EFF6FF;
  display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  cursor:pointer; overflow:hidden;
  position:relative; transition:border-color .2s;
}
.thumb-preview-box:hover { border-color:#2563EB; }
.thumb-preview-box img {
  width:100%;height:100%;object-fit:cover;
  position:absolute;top:0;left:0;
}
.thumb-preview-box .upload-hint {
  font-size:.85rem;color:#6B7280;text-align:center;
  pointer-events:none;position:relative;z-index:1;
}
.thumb-preview-box .upload-hint .icon { font-size:2rem;display:block;margin-bottom:.4rem; }
.existing-thumb {
  width:80px;height:50px;border-radius:8px;
  object-fit:cover;border:1px solid #E5E7EB;
}
/* Status badge in table */
.status-pub  { background:#D1FAE5;color:#065F46; }
.status-draft{ background:#FEF3C7;color:#92400E; }
</style>
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <h3>🎓 Manage Courses</h3>
  <button class="btn btn-primary btn-sm" data-modal="courseModal">+ Add Course</button>
</div>
<div class="admin-content">
<?php if($flash): ?>
<div class="alert alert-<?=$flash['type']==='error'?'error':'success'?>">
  <?=$flash['type']==='success'?'✅':'❌'?> <?=htmlspecialchars($flash['msg'])?>
</div>
<?php endif; ?>

<div class="table-box">
  <div class="table-top">
    <h3>All Courses <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?=count($courses)?> total)</span></h3>
    <div class="search-input"><span class="si">🔍</span><input type="text" id="tblSearch" placeholder="Search..."></div>
  </div>
  <table>
    <thead>
      <tr><th>#</th><th>Thumbnail</th><th>Title</th><th>Category</th><th>Price</th><th>Students</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($courses as $i=>$c): ?>
    <tr>
      <td><?=$i+1?></td>
      <td>
        <?php if(!empty($c['thumbnail'])): ?>
          <img src="<?=SITE_URL?>/uploads/<?=htmlspecialchars($c['thumbnail'])?>" class="existing-thumb" alt="thumb">
        <?php else: ?>
          <div style="width:80px;height:50px;border-radius:8px;background:linear-gradient(135deg,#EFF6FF,#BFDBFE);display:flex;align-items:center;justify-content:center;font-size:1.4rem;">🖼️</div>
        <?php endif; ?>
      </td>
      <td>
        <div style="font-weight:600;font-size:.9rem;"><?=htmlspecialchars(substr($c['title'],0,30)).(strlen($c['title'])>30?'...':'')?></div>
        <div style="font-size:.78rem;color:var(--gray-400);">by <?=htmlspecialchars($c['instructor'])?></div>
      </td>
      <td><?=htmlspecialchars((isset($c['cat_name']) ? $c['cat_name'] : '—'))?></td>
      <td style="font-weight:700;"><?=CURRENCY.number_format($c['price'])?></td>
      <td><?=number_format($c['students'])?></td>
      <td>
        <span class="badge <?=$c['status']==='published'?'status-pub':'status-draft'?>"><?=ucfirst($c['status'])?></span>
        <?php if($c['featured']): ?> <span class="badge" style="background:var(--warning-bg);color:#92400E;">⭐</span><?php endif; ?>
      </td>
      <td>
        <div class="td-actions">
          <a href="manage-videos.php?course_id=<?=$c['id']?>" class="btn btn-ghost btn-sm" title="Videos">🎥</a>
          <a href="?edit=<?=$c['id']?>" class="btn btn-outline btn-sm">✏️</a>
          <a href="?toggle=<?=$c['id']?>"
             class="btn btn-sm <?=$c['status']==='published'?'btn-danger':'btn-success'?>"
             onclick="return confirm('<?=$c['status']==='published'?'Unpublish':'Publish'?> this course?')">
            <?=$c['status']==='published'?'Unpublish':'Publish'?>
          </a>
          <a href="?delete=<?=$c['id']?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>

<!-- Add/Edit Course Modal -->
<div id="courseModal" class="modal-overlay <?=$editCourse?'open':''?>">
<div class="modal" style="max-width:700px;">
  <div class="modal-header">
    <h3><?=$editCourse?'✏️ Edit Course':'➕ Add New Course'?></h3>
    <button class="modal-close" data-modal-close>×</button>
  </div>
  <!-- IMPORTANT: enctype for file upload -->
  <form method="POST" enctype="multipart/form-data">
    <div class="modal-body">
      <input type="hidden" name="cid" value="<?=isset($editCourse['id'])?$editCourse['id']:0?>">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

        <!-- Title -->
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Course Title *</label>
          <input type="text" name="title" class="form-control"
            value="<?=htmlspecialchars((isset($editCourse['title']) ? $editCourse['title'] : ''))?>" required placeholder="e.g. Complete Web Development Bootcamp">
        </div>

        <!-- Thumbnail Upload -->
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">📸 Course Thumbnail Image</label>
          <div class="thumb-preview-box" onclick="document.getElementById('thumbInput').click()">
            <?php if(!empty($editCourse['thumbnail'])): ?>
              <img src="<?=SITE_URL?>/uploads/<?=htmlspecialchars($editCourse['thumbnail'])?>" id="thumbPreview">
            <?php else: ?>
              <img id="thumbPreview" style="display:none;">
            <?php endif; ?>
            <div class="upload-hint" id="uploadHint" <?=!empty($editCourse['thumbnail'])?'style="display:none"':''?>>
              <span class="icon">🖼️</span>
              <strong>Click to upload image</strong><br>
              JPG, PNG, WEBP · Max 3MB<br>
              <span style="font-size:.75rem;color:#9CA3AF;">Recommended: 1280×720px</span>
            </div>
          </div>
          <input type="file" name="thumbnail" id="thumbInput" accept="image/*" style="display:none"
            onchange="previewThumb(this)">
          <p style="font-size:.75rem;color:#9CA3AF;margin-top:.4rem;">
            
          </p>
        </div>

        <!-- Category & Instructor -->
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-select">
            <?php foreach($categories as $cat): ?>
            <option value="<?=$cat['id']?>" <?=((isset($editCourse['category_id']) ? $editCourse['category_id'] : 0))==$cat['id']?'selected':''?>>
              <?=htmlspecialchars($cat['name'])?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Instructor Name</label>
          <input type="text" name="instructor" class="form-control"
            value="<?=htmlspecialchars((isset($editCourse['instructor']) ? $editCourse['instructor'] : ''))?>" placeholder="Dr. Priya Sharma">
        </div>

        <!-- Price -->
        <div class="form-group">
          <label class="form-label">Price (<?=CURRENCY?>)</label>
          <input type="number" name="price" class="form-control"
            value="<?=(isset($editCourse['price']) ? $editCourse['price'] : 0)?>" step="1" min="0" placeholder="999">
        </div>
        <div class="form-group">
          <label class="form-label">Original Price (<?=CURRENCY?>) <span style="color:#9CA3AF;font-size:.78rem;">for strikethrough</span></label>
          <input type="number" name="old_price" class="form-control"
            value="<?=(isset($editCourse['old_price']) ? $editCourse['old_price'] : 0)?>" step="1" min="0" placeholder="4999">
        </div>

        <!-- Hours & Level -->
        <div class="form-group">
          <label class="form-label">Duration (hours)</label>
          <input type="number" name="hours" class="form-control"
            value="<?=(isset($editCourse['hours']) ? $editCourse['hours'] : 0)?>" step="0.5" min="0" placeholder="24">
        </div>
        <div class="form-group">
          <label class="form-label">Level</label>
          <select name="level" class="form-select">
            <?php foreach(['Beginner','Intermediate','Advanced','All Levels'] as $lv): ?>
            <option value="<?=$lv?>" <?=((isset($editCourse['level']) ? $editCourse['level'] : ''))===$lv?'selected':''?>><?=$lv?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="draft"     <?=((isset($editCourse['status']) ? $editCourse['status'] : ''))==='draft'?'selected':''?>>📝 Draft (Hidden from users)</option>
            <option value="published" <?=((isset($editCourse['status']) ? $editCourse['status'] : ''))==='published'?'selected':''?>>✅ Published (Visible to users)</option>
          </select>
          <p style="font-size:.75rem;color:#9CA3AF;margin-top:.3rem;">⚠️ "Published" </p>
        </div>

        <!-- Featured -->
        <div class="form-group" style="display:flex;align-items:center;">
          <label style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;cursor:pointer;margin-top:1.5rem;">
            <input type="checkbox" name="featured" <?=((isset($editCourse['featured']) ? $editCourse['featured'] : 0))?'checked':''?>
              style="accent-color:var(--primary);width:16px;height:16px;">
            ⭐ Mark as Featured Course
          </label>
        </div>

        <!-- Short Desc -->
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Short Description</label>
          <input type="text" name="short_desc" class="form-control"
            value="<?=htmlspecialchars((isset($editCourse['short_desc']) ? $editCourse['short_desc'] : ''))?>"
            placeholder="one line summary">
        </div>

        <!-- Full Desc -->
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Full Description</label>
          <textarea name="description" class="form-control" rows="3"
            placeholder="description "><?=htmlspecialchars((isset($editCourse['description']) ? $editCourse['description'] : ''))?></textarea>
        </div>

      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
      <button type="submit" class="btn btn-primary"><?=$editCourse?'💾 Save Changes':'➕ Add Course'?></button>
    </div>
  </form>
</div>
</div>

<script src="<?=SITE_URL?>/assets/js/main.js"></script>
<script>
function previewThumb(input){
  if(input.files && input.files[0]){
    var reader = new FileReader();
    reader.onload = function(e){
      var img = document.getElementById('thumbPreview');
      var hint = document.getElementById('uploadHint');
      img.src = e.target.result;
      img.style.display = 'block';
      hint.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
// Open modal if edit mode
<?php if($editCourse): ?>
document.addEventListener('DOMContentLoaded',function(){
  document.getElementById('courseModal').classList.add('open');
});
<?php endif; ?>
</script>
</body></html>