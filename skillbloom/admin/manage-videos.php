<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();
$cid=intval((isset($_GET['course_id']) ? $_GET['course_id'] : 0));
$course=$cid?$conn->query("SELECT * FROM courses WHERE id=$cid")->fetch_assoc():null;
// Delete
if(isset($_GET['delete'])){ $conn->query("DELETE FROM videos WHERE id=".intval($_GET['delete'])); flash('success','Video deleted.'); redirect('manage-videos.php'.($cid?"?course_id=$cid":'')); }
// Save
if($_SERVER['REQUEST_METHOD']==='POST'){
  $vid=intval((isset($_POST['vid']) ? $_POST['vid'] : 0)); $cid2=intval(isset($_POST['course_id']) ? $_POST['course_id'] : $cid);
  $title=clean((isset($_POST['title']) ? $_POST['title'] : '')); $url=clean((isset($_POST['video_url']) ? $_POST['video_url'] : ''));
  $dur=intval((isset($_POST['duration_min']) ? $_POST['duration_min'] : 0)); $sort=intval((isset($_POST['sort_order']) ? $_POST['sort_order'] : 0));
  $prev=isset($_POST['is_preview'])?1:0; $desc=clean((isset($_POST['description']) ? $_POST['description'] : ''));
  if($vid){ $conn->query("UPDATE videos SET course_id=$cid2,title='$title',video_url='$url',duration_min=$dur,sort_order=$sort,is_preview=$prev,description='$desc' WHERE id=$vid"); flash('success','Video updated.'); }
  else { $conn->query("INSERT INTO videos(course_id,title,video_url,duration_min,sort_order,is_preview,description) VALUES($cid2,'$title','$url',$dur,$sort,$prev,'$desc')"); flash('success','Video added.'); }
  redirect('manage-videos.php'.($cid?"?course_id=$cid":''));
}
$where=$cid?"v.course_id=$cid":'1';
$videos=$conn->query("SELECT v.*,c.title ctitle FROM videos v JOIN courses c ON c.id=v.course_id WHERE $where ORDER BY v.course_id,v.sort_order")->fetch_all(MYSQLI_ASSOC);
$courses=$conn->query("SELECT id,title FROM courses ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$editVideo=null; if(isset($_GET['edit'])) $editVideo=$conn->query("SELECT * FROM videos WHERE id=".intval($_GET['edit']))->fetch_assoc();
$flash=getFlash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Videos — SkillBloom Admin</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head><body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">
<div class="admin-topbar">
  <div style="display:flex;align-items:center;gap:1rem;">
    <a href="manage-courses.php" class="btn btn-ghost btn-sm">← Courses</a>
    <h3>🎥 Manage Videos<?php if($course): ?> — <?php echo htmlspecialchars(substr($course['title'],0,35)); ?><?php endif; ?></h3>
  </div>
  <button class="btn btn-primary btn-sm" data-modal="videoModal">+ Add Video</button>
</div>
<div class="admin-content">
<?php if($flash): ?><div class="alert alert-<?php echo $flash['type']==='error'?'error':'success'; ?>"><?php echo $flash['type']==='success'?'✅':'❌'; ?> <?php echo htmlspecialchars($flash['msg']); ?></div><?php endif; ?>

<div class="table-box">
  <div class="table-top"><h3>Videos <span style="font-size:.85rem;color:var(--gray-400);font-weight:400;">(<?php echo count($videos); ?> total)</span></h3>
    <div class="search-input"><span class="si">🔍</span><input type="text" id="tblSearch" placeholder="Search..."></div>
  </div>
  <table>
    <thead><tr><th>#</th><th>Title</th><th>Course</th><th>Duration</th><th>Preview</th><th>Order</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($videos as $i=>$v): ?>
    <tr>
      <td><?php echo $i+1; ?></td>
      <td><div style="font-weight:600;font-size:.9rem;"><?php echo htmlspecialchars($v['title']); ?></div><?php if($v['video_url']): ?><div style="font-size:.75rem;color:var(--primary);">🔗 <?php echo substr($v['video_url'],0,35).'...'; ?></div><?php endif; ?></td>
      <td style="font-size:.85rem;"><?php echo htmlspecialchars(substr($v['ctitle'],0,30)); ?></td>
      <td><?php echo $v['duration_min']; ?> min</td>
      <td><?php echo $v['is_preview']?'<span class="badge badge-success">✅ Yes</span>':'<span class="badge badge-gray">No</span>'; ?></td>
      <td><?php echo $v['sort_order']; ?></td>
      <td><div class="td-actions">
        <a href="?edit=<?php echo $v['id']; ?><?php echo $cid?"&course_id=$cid":''; ?>" class="btn btn-outline btn-sm">✏️</a>
        <a href="?delete=<?php echo $v['id']; ?><?php echo $cid?"&course_id=$cid":''; ?>" class="btn btn-danger btn-sm" onclick="return confirmDel()">🗑️</a>
      </div></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$videos): ?><tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--gray-400);">No videos found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
</div>
</div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="modal-overlay <?php echo $editVideo?'open':''; ?>">
<div class="modal">
  <div class="modal-header"><h3><?php echo $editVideo?'✏️ Edit Video':'➕ Add Video'; ?></h3><button class="modal-close" data-modal-close>×</button></div>
  <form method="POST">
    <div class="modal-body">
      <input type="hidden" name="vid" value="<?php echo (isset($editVideo['id']) ? $editVideo['id'] : 0); ?>">
      <div class="form-group"><label class="form-label">Course *</label>
        <select name="course_id" class="form-select" required>
          <?php foreach($courses as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo (isset($editVideo['course_id']) ? $editVideo['course_id'] : $cid)==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['title']); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Video Title *</label>
        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars((isset($editVideo['title']) ? $editVideo['title'] : '')); ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Video URL (YouTube or direct MP4)</label>
        <input type="url" name="video_url" id="videoUrlInput" class="form-control" value="<?php echo htmlspecialchars((isset($editVideo['video_url']) ? $editVideo['video_url'] : '')); ?>" placeholder="https://youtube.com/watch?v=... or https://youtu.be/..." oninput="previewYT(this.value)">
        <div style="font-size:.78rem;color:var(--gray-400);margin-top:.35rem;">
          Supported: <strong>youtube.com/watch?v=ID</strong> · <strong>youtu.be/ID</strong> · <strong>youtube.com/shorts/ID</strong> · Direct MP4 URL
        </div>
        <div id="ytPreview" style="display:none;margin-top:.75rem;border-radius:8px;overflow:hidden;aspect-ratio:16/9;background:#000;max-width:360px;">
          <iframe id="ytFrame" src="" frameborder="0" allowfullscreen style="width:100%;height:100%;"></iframe>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Duration (min)</label>
          <input type="number" name="duration_min" class="form-control" value="<?php echo (isset($editVideo['duration_min']) ? $editVideo['duration_min'] : 0); ?>" min="0">
        </div>
        <div class="form-group"><label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?php echo (isset($editVideo['sort_order']) ? $editVideo['sort_order'] : 0); ?>" min="0">
        </div>
      </div>
      <div class="form-group"><label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars((isset($editVideo['description']) ? $editVideo['description'] : '')); ?></textarea>
      </div>
      <label style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;cursor:pointer;">
        <input type="checkbox" name="is_preview" <?php echo ((isset($editVideo['is_preview']) ? $editVideo['is_preview'] : 0))?'checked':''; ?> style="accent-color:var(--primary);width:16px;height:16px;"> Free Preview (accessible without enrollment)
      </label>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
      <button type="submit" class="btn btn-primary"><?php echo $editVideo?'💾 Save':'➕ Add'; ?></button>
    </div>
  </form>
</div>
</div>

<script>
function getYTid(url){
  var patterns=[/youtu\.be\/([a-zA-Z0-9_-]{11})/,/youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/,/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/];
  for(var p of patterns){ var m=url.match(p); if(m) return m[1]; }
  return '';
}
function previewYT(url){
  var id=getYTid(url);
  var prev=document.getElementById('ytPreview');
  var frame=document.getElementById('ytFrame');
  if(id && prev && frame){
    frame.src='https://www.youtube.com/embed/'+id+'?rel=0&modestbranding=1';
    prev.style.display='block';
  } else if(prev){
    prev.style.display='none';
    if(frame) frame.src='';
  }
}
// Init preview if editing
document.addEventListener('DOMContentLoaded',function(){
  var inp=document.getElementById('videoUrlInput');
  if(inp && inp.value) previewYT(inp.value);
});
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
