<?php
require_once dirname(__DIR__).'/config.php';
requireUser();

// Auto-create feedback table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS feedback(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(150),
  subject VARCHAR(191),
  type VARCHAR(60) DEFAULT 'General',
  rating TINYINT DEFAULT 5,
  message TEXT,
  status ENUM('new','read','resolved') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");

$success = ''; $error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $name    = clean(isset($_POST['name'])    ? $_POST['name']    : '');
  $subject = clean(isset($_POST['subject']) ? $_POST['subject'] : '');
  $type    = clean(isset($_POST['type'])    ? $_POST['type']    : 'General');
  $rating  = intval(isset($_POST['rating']) ? $_POST['rating']  : 0);
  $message = clean(isset($_POST['message']) ? $_POST['message'] : '');
  $uid     = $_SESSION['user_id'];

  if(empty($name) || empty($subject) || empty($message)){
    $error = 'Please fill in all required fields.';
  } elseif($rating < 1 || $rating > 5){
    $error = 'Please select a rating (1-5 stars).';
  } else {
    $stmt = $conn->prepare("INSERT INTO feedback(user_id,name,subject,type,rating,message) VALUES(?,?,?,?,?,?)");
    $stmt->bind_param('isssis', $uid, $name, $subject, $type, $rating, $message);
    $stmt->execute();
    $success = 'Your feedback has been submitted! Thank you.';
  }
}

// Previous feedbacks by this user
$uid2 = $_SESSION['user_id'];
$userFeedbacks = $conn->query("SELECT * FROM feedback WHERE user_id=" . (int)$uid2 . " ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Feedback — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
.fb-hero {
  background:linear-gradient(135deg,#EFF6FF 0%,#DBEAFE 60%,#BAE6FD 100%);
  padding:2.5rem 1.5rem; text-align:center;
  border-bottom:1px solid #BFDBFE;
}
.fb-hero h2 { font-size:2rem; color:#0F172A; margin-bottom:.4rem; }
.fb-hero p  { opacity:.8; font-size:.95rem; color:#4B5563; }
.fb-wrap { max-width:780px; margin:0 auto; padding:2rem 1.5rem 3rem; }
.fb-card {
  background:#fff; border-radius:var(--radius-lg);
  box-shadow:var(--shadow-lg); padding:2.25rem; margin-bottom:1.5rem;
}
.star-row {
  display:flex; flex-direction:row-reverse;
  justify-content:flex-end; gap:.4rem; margin-top:.5rem;
}
.star-row input { display:none; }
.star-row label {
  font-size:2.1rem; cursor:pointer;
  color:var(--gray-300); transition:color .15s; line-height:1;
}
.star-row label:hover,
.star-row label:hover ~ label,
.star-row input:checked ~ label { color:var(--warning); }

.type-pills { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; }
.type-pills input { display:none; }
.type-pills label {
  padding:.35rem 1rem; border-radius:50px; font-size:.83rem; font-weight:600;
  border:2px solid var(--gray-200); color:var(--gray-500);
  cursor:pointer; transition:var(--trans);
}
.type-pills input:checked + label {
  border-color:var(--primary); background:var(--primary-50); color:var(--primary);
}
.type-pills label:hover { border-color:var(--primary-200); }

.success-box {
  display:flex; align-items:center; gap:1rem;
  background:linear-gradient(135deg,#D1FAE5,#A7F3D0);
  border:1.5px solid #10B981; border-radius:var(--radius);
  padding:1.1rem 1.4rem; margin-bottom:1.5rem; color:#065F46;
}
.success-box .sb-icon { font-size:2rem; }

.info-grid {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr));
  gap:1rem; margin-bottom:1.5rem;
}
.info-tile {
  background:#fff; border:1px solid var(--gray-200);
  border-radius:var(--radius); padding:1.2rem; text-align:center;
}
.info-tile .ic { font-size:1.8rem; margin-bottom:.5rem; }
.info-tile h4 { font-size:.88rem; color:var(--dark); margin-bottom:.2rem; }
.info-tile p  { font-size:.8rem; color:var(--gray-400); margin:0; }

.prev-new      { background:var(--warning-bg); color:#92400E; }
.prev-read     { background:var(--primary-100); color:var(--primary); }
.prev-resolved { background:var(--success-bg); color:#065F46; }
</style>
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>

<div class="fb-hero">
  <h2>&#128172; Share Your Feedback</h2>
  <p>Help us improve SkillBloom &mdash; every message is read by our team!</p>
</div>

<div class="fb-wrap">

<?php if($success): ?>
<div class="success-box">
  <div class="sb-icon">&#127881;</div>
  <div>
    <strong><?=htmlspecialchars($success)?></strong>
    <div style="font-size:.85rem;margin-top:.2rem;opacity:.75;">We'll get back to you within 24 hours.</div>
  </div>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-error" style="margin-bottom:1.5rem;">&#10060; <?=htmlspecialchars($error)?></div>
<?php endif; ?>

<div class="fb-card">
  <h3 style="margin-bottom:1.5rem;">&#9997;&#65039; Write Your Message</h3>
  <form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
      <div class="form-group">
        <label class="form-label">Your Name *</label>
        <input type="text" name="name" class="form-control"
          value="<?=htmlspecialchars($userName)?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Subject *</label>
        <input type="text" name="subject" class="form-control"
          placeholder="Brief description of your feedback" required>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Feedback Type</label>
      <div class="type-pills">
        <?php foreach(array('General','Course Quality','Technical Issue','Suggestion','Complaint','Praise') as $tp): ?>
        <div>
          <input type="radio" name="type" id="tp_<?=preg_replace('/\s+/','_',$tp)?>"
            value="<?=$tp?>" <?=$tp==='General'?'checked':''?>>
          <label for="tp_<?=preg_replace('/\s+/','_',$tp)?>"><?=$tp?></label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Overall Rating *</label>
      <div class="star-row">
        <?php for($s=5; $s>=1; $s--): ?>
        <input type="radio" name="rating" id="sr<?=$s?>" value="<?=$s?>">
        <label for="sr<?=$s?>" title="<?=$s?> Star">&#9733;</label>
        <?php endfor; ?>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Your Message *</label>
      <textarea name="message" class="form-control" rows="6"
        placeholder="Share your experience, suggestions, or any issues you've encountered..."
        required></textarea>
    </div>

    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <button type="submit" class="btn btn-primary btn-lg" style="min-width:180px;">
        &#128232; Send Feedback
      </button>
      <span style="font-size:.82rem;color:var(--gray-400);">All feedback is confidential &amp; reviewed by our team</span>
    </div>
  </form>
</div>

<!-- Info tiles -->
<div class="info-grid">
  <div class="info-tile">
    <div class="ic">&#9889;</div>
    <h4>Quick Response</h4>
    <p>We respond within 24 hours</p>
  </div>
  <div class="info-tile">
    <div class="ic">&#128274;</div>
    <h4>Confidential</h4>
    <p>Your feedback stays private</p>
  </div>
  <div class="info-tile">
    <div class="ic">&#128161;</div>
    <h4>Real Impact</h4>
    <p>Your ideas shape our platform</p>
  </div>
</div>

<!-- Previous feedbacks -->
<?php if($userFeedbacks): ?>
<div class="fb-card" style="padding:1.5rem;">
  <h3 style="margin-bottom:1.1rem;font-size:.95rem;color:var(--gray-600);">&#128203; Your Previous Feedback</h3>
  <?php foreach($userFeedbacks as $fb): ?>
  <div style="border-bottom:1px solid var(--gray-100);padding:.85rem 0;display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
    <div>
      <div style="font-weight:700;font-size:.9rem;margin-bottom:.2rem;"><?=htmlspecialchars($fb['subject'])?></div>
      <div style="font-size:.82rem;color:var(--gray-500);"><?=htmlspecialchars(substr($fb['message'],0,100)).(strlen($fb['message'])>100?'...':'')?></div>
      <div style="font-size:.78rem;color:var(--gray-400);margin-top:.3rem;">
        <?=date('M d, Y', strtotime($fb['created_at']))?> &middot;
        <?php for($i=0;$i<$fb['rating'];$i++) echo '&#9733;'; ?>
      </div>
    </div>
    <span class="badge prev-<?=$fb['status']?>" style="font-size:.72rem;padding:.18rem .55rem;border-radius:50px;"><?=ucfirst($fb['status'])?></span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

</div><!-- .fb-wrap -->
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
</body></html>
