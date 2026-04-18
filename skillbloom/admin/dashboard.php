<?php
require_once dirname(__DIR__).'/config.php';
requireAdmin();

// Stats
$s = array(
  'users'    => $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
  'courses'  => $conn->query("SELECT COUNT(*) c FROM courses")->fetch_assoc()['c'],
  'enrolled' => $conn->query("SELECT COUNT(*) c FROM enrollments")->fetch_assoc()['c'],
  'revenue'  => $conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success'")->fetch_assoc()['v'],
  'certs'    => $conn->query("SELECT COUNT(*) c FROM certificates")->fetch_assoc()['c'],
);
$conn->query("CREATE TABLE IF NOT EXISTS feedback(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,name VARCHAR(150),subject VARCHAR(191),type VARCHAR(60) DEFAULT 'General',rating TINYINT DEFAULT 5,message TEXT,status ENUM('new','read','resolved') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC");
$s['feedback'] = $conn->query("SELECT COUNT(*) c FROM feedback WHERE status='new'")->fetch_assoc()['c'];

// Last month comparison for revenue
$thisM  = date('Y-m');
$lastM  = date('Y-m', strtotime('-1 month'));
$revTM  = (float)$conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success' AND DATE_FORMAT(paid_at,'%Y-%m')='$thisM'")->fetch_assoc()['v'];
$revLM  = (float)$conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success' AND DATE_FORMAT(paid_at,'%Y-%m')='$lastM'")->fetch_assoc()['v'];
$revChg = $revLM > 0 ? round(($revTM - $revLM) / $revLM * 100) : 0;

// This week vs last week users
$thisW = $conn->query("SELECT COUNT(*) c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['c'];
$lastW = $conn->query("SELECT COUNT(*) c FROM users WHERE created_at BETWEEN DATE_SUB(NOW(),INTERVAL 14 DAY) AND DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetch_assoc()['c'];
$usrChg = $lastW > 0 ? round(($thisW - $lastW) / $lastW * 100) : 0;

// Tables
$recentUsers    = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recentPayments = $conn->query("SELECT p.*,u.name uname,c.title ctitle FROM payments p JOIN users u ON u.id=p.user_id JOIN courses c ON c.id=p.course_id ORDER BY p.paid_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$topCourses     = $conn->query("SELECT c.title,c.students,c.rating,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id ORDER BY c.students DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Chart data
$monthly = array();
for($i=11;$i>=0;$i--){
  $ym  = date('Y-m', strtotime("-$i months"));
  $lbl = date('M', strtotime("-$i months"));
  $monthly[] = array(
    'label'       => $lbl,
    'revenue'     => (float)$conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success' AND DATE_FORMAT(paid_at,'%Y-%m')='$ym'")->fetch_assoc()['v'],
    'enrollments' => (int)$conn->query("SELECT COUNT(*) c FROM enrollments WHERE DATE_FORMAT(enrolled_at,'%Y-%m')='$ym'")->fetch_assoc()['c'],
    'users'       => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE DATE_FORMAT(created_at,'%Y-%m')='$ym'")->fetch_assoc()['c'],
  );
}
$daily = array();
for($i=29;$i>=0;$i--){
  $dt  = date('Y-m-d', strtotime("-$i days"));
  $lbl = date('d M', strtotime("-$i days"));
  $daily[] = array(
    'label'       => $lbl,
    'revenue'     => (float)$conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success' AND DATE(paid_at)='$dt'")->fetch_assoc()['v'],
    'enrollments' => (int)$conn->query("SELECT COUNT(*) c FROM enrollments WHERE DATE(enrolled_at)='$dt'")->fetch_assoc()['c'],
    'users'       => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE DATE(created_at)='$dt'")->fetch_assoc()['c'],
  );
}
$yearly = array();
for($i=4;$i>=0;$i--){
  $yr = date('Y', strtotime("-$i years"));
  $yearly[] = array(
    'label'       => $yr,
    'revenue'     => (float)$conn->query("SELECT COALESCE(SUM(amount),0) v FROM payments WHERE status='success' AND YEAR(paid_at)='$yr'")->fetch_assoc()['v'],
    'enrollments' => (int)$conn->query("SELECT COUNT(*) c FROM enrollments WHERE YEAR(enrolled_at)='$yr'")->fetch_assoc()['c'],
    'users'       => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE YEAR(created_at)='$yr'")->fetch_assoc()['c'],
  );
}
$catPie = $conn->query("SELECT cat.name, COUNT(e.id) cnt FROM enrollments e JOIN courses c ON c.id=e.course_id JOIN categories cat ON cat.id=c.category_id GROUP BY cat.id ORDER BY cnt DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — SkillBloom</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
body { background:#F1F5F9; }
.admin-content { padding:1.25rem 1.5rem; }

/* ─── Mini stat cards (Image 2 style) ─── */
.kpi-row {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(155px,1fr));
  gap:.9rem; margin-bottom:1.4rem;
}
.kpi-box {
  background:#fff; border-radius:14px; border:1px solid #E2E8F0;
  padding:1.1rem 1rem; display:flex; align-items:center; gap:.8rem;
  box-shadow:0 1px 3px rgba(0,0,0,.05); transition:.18s;
}
.kpi-box:hover { box-shadow:0 4px 18px rgba(37,99,235,.15); transform:translateY(-3px); border-color:#BFDBFE; cursor:pointer; }
.kpi-box:active { transform:translateY(-1px); }
.kpi-ico {
  width:44px; height:44px; border-radius:11px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center; font-size:1.3rem;
}
.ic-blue   { background:#EFF6FF; }
.ic-purple { background:#F5F3FF; }
.ic-sky    { background:#F0F9FF; }
.ic-green  { background:#ECFDF5; }
.ic-amber  { background:#FFFBEB; }
.ic-pink   { background:#FFF1F2; }
.kpi-num   { font-size:1.4rem; font-weight:800; color:#0F172A; line-height:1; }
.kpi-lbl   { font-size:.72rem; color:#94A3B8; font-weight:500; margin-top:.2rem; }
.kpi-chg   { font-size:.7rem; font-weight:700; margin-top:.15rem; }
.up  { color:#059669; } .dn { color:#DC2626; }

/* ─── Chart cards (Image 3 style) ─── */
.cc {
  background:#fff; border-radius:14px; border:1px solid #E2E8F0;
  padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.05);
}
.cc-hdr {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:1rem; flex-wrap:wrap; gap:.5rem;
}
.cc-title { font-size:.9rem; font-weight:700; color:#0F172A; }
.cht-tabs { display:flex; gap:.25rem; }
.cht-tab {
  padding:.25rem .75rem; border-radius:20px; font-size:.73rem; font-weight:700;
  border:none; cursor:pointer; background:#F1F5F9; color:#64748B; transition:.15s;
}
.cht-tab.on { background:#2563EB; color:#fff; }

/* ─── Tables (Image 3 style) ─── */
.dt-card {
  background:#fff; border-radius:14px; border:1px solid #E2E8F0;
  overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05);
}
.dt-hdr { display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.15rem;border-bottom:1px solid #F1F5F9; }
.dt-hdr h4 { font-size:.875rem; font-weight:700; color:#0F172A; }
.dt-card table { width:100%; border-collapse:collapse; }
.dt-card th { background:#F8FAFC; padding:.55rem 1rem; font-size:.7rem; font-weight:700; color:#94A3B8; text-align:left; text-transform:uppercase; letter-spacing:.06em; }
.dt-card td { padding:.65rem 1rem; font-size:.83rem; color:#374151; border-top:1px solid #F8FAFC; }
.dt-card tr:hover td { background:#FAFBFC; }
.av { width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#2563EB,#0EA5E9);display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;color:#fff; }
</style>
</head>
<body>
<div class="admin-layout">
<?php include dirname(__DIR__).'/includes/admin_nav.php'; ?>
<div class="admin-main">

<!-- Topbar -->
<div class="admin-topbar">
  <div>
    <h3 style="font-size:.95rem;font-weight:700;color:#0F172A;">&#128202; Dashboard Overview</h3>
    <div style="font-size:.73rem;color:#94A3B8;"><?php echo date('l, F j Y'); ?></div>
  </div>
  <div class="avatar-wrap">
    <button class="avatar-btn"><?php echo strtoupper(substr($_SESSION['admin_name'],0,1)); ?></button>
    <div class="dropdown-menu">
      <div class="dd-user-info"><strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>Admin</div>
      <hr>
      <a href="<?php echo SITE_URL; ?>/user/dashboard.php" target="_blank">&#127758; View Site</a>
      <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="danger">&#128682; Sign Out</a>
    </div>
  </div>
</div>

<div class="admin-content">

<!-- ─── KPI Cards (Clickable) ─── -->
<div class="kpi-row">
  <a href="<?php echo SITE_URL; ?>/admin/manage-users.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-blue">&#128101;</div>
    <div>
      <div class="kpi-num"><?php echo number_format($s['users']); ?></div>
      <div class="kpi-lbl">Total Users</div>
      <?php if($usrChg!=0): ?><div class="kpi-chg <?php echo $usrChg>0?'up':'dn'; ?>"><?php echo ($usrChg>0?'&#9650;':'&#9660;').abs($usrChg); ?>% this week</div><?php endif; ?>
    </div>
  </a>
  <a href="<?php echo SITE_URL; ?>/admin/manage-courses.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-purple">&#127891;</div>
    <div><div class="kpi-num"><?php echo number_format($s['courses']); ?></div><div class="kpi-lbl">Courses</div></div>
  </a>
  <a href="<?php echo SITE_URL; ?>/admin/manage-payments.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-sky">&#128218;</div>
    <div><div class="kpi-num"><?php echo number_format($s['enrolled']); ?></div><div class="kpi-lbl">Enrollments</div></div>
  </a>
  <a href="<?php echo SITE_URL; ?>/admin/manage-payments.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-green">&#128176;</div>
    <div>
      <div class="kpi-num"><?php echo CURRENCY.number_format($s['revenue']); ?></div>
      <div class="kpi-lbl">Revenue</div>
      <?php if($revChg!=0): ?><div class="kpi-chg <?php echo $revChg>0?'up':'dn'; ?>"><?php echo ($revChg>0?'&#9650;':'&#9660;').abs($revChg); ?>% vs last month</div><?php endif; ?>
    </div>
  </a>
  <a href="<?php echo SITE_URL; ?>/admin/manage-certificates.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-amber">&#127942;</div>
    <div><div class="kpi-num"><?php echo number_format($s['certs']); ?></div><div class="kpi-lbl">Certificates</div></div>
  </a>
  <a href="<?php echo SITE_URL; ?>/admin/manage-feedback.php" class="kpi-box" style="text-decoration:none;color:inherit;cursor:pointer;">
    <div class="kpi-ico ic-pink">&#128172;</div>
    <div><div class="kpi-num"><?php echo number_format($s['feedback']); ?></div><div class="kpi-lbl">New Feedback</div></div>
  </a>
</div>

<!-- ─── Revenue + Enrollments FULL WIDTH ─── -->
<div class="cc" style="margin-bottom:1.15rem;">
  <div class="cc-hdr">
    <span class="cc-title">&#128200; Revenue &amp; Enrollments</span>
    <div class="cht-tabs">
      <button class="cht-tab on" onclick="swM('monthly',this)">Monthly</button>
      <button class="cht-tab" onclick="swM('daily',this)">Daily</button>
      <button class="cht-tab" onclick="swM('yearly',this)">Yearly</button>
    </div>
  </div>
  <canvas id="mainCht" height="90"></canvas>
</div>

<!-- ─── Category Donut + Recent Payments HALF HALF ─── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.15rem;margin-bottom:1.5rem;">

  <!-- Category Donut -->
  <div class="cc">
    <div class="cc-hdr"><span class="cc-title">&#127891; Courses by Category</span></div>
    <canvas id="catCht" height="200"></canvas>
  </div>

  <!-- Recent Payments -->
  <div class="dt-card">
    <div class="dt-hdr">
      <h4>&#128179; Recent Payments</h4>
      <a href="manage-payments.php" class="btn btn-ghost btn-sm" style="font-size:.76rem;">View All &rarr;</a>
    </div>
    <table>
      <thead><tr><th>Student</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach($recentPayments as $p): ?>
      <tr>
        <td style="font-weight:600;font-size:.82rem;"><?php echo htmlspecialchars($p['uname']); ?></td>
        <td style="font-weight:800;color:#059669;font-size:.82rem;"><?php echo CURRENCY.number_format($p['amount']); ?></td>
        <td>
          <?php $bc = $p['status']==='success'?'badge-success':($p['status']==='failed'?'badge-danger':'badge-warning'); ?>
          <span class="badge <?php echo $bc; ?>"><?php echo ucfirst($p['status']); ?></span>
        </td>
        <td style="color:#94A3B8;font-size:.75rem;"><?php echo date('M d',strtotime($p['paid_at'])); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(!$recentPayments): ?><tr><td colspan="4" style="text-align:center;padding:2rem;color:#94A3B8;">No payments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

</div><!-- /admin-content -->
</div>
</div>

<script>
var mD  = <?php echo json_encode($monthly); ?>;
var dD  = <?php echo json_encode($daily);   ?>;
var yD  = <?php echo json_encode($yearly);  ?>;
var cat = <?php echo json_encode($catPie);  ?>;
var CUR = <?php echo json_encode(CURRENCY); ?>;

Chart.defaults.font.family = "'Inter',system-ui,sans-serif";
Chart.defaults.plugins.tooltip.backgroundColor = '#fff';
Chart.defaults.plugins.tooltip.titleColor = '#0F172A';
Chart.defaults.plugins.tooltip.bodyColor   = '#374151';
Chart.defaults.plugins.tooltip.borderColor = '#E2E8F0';
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.padding     = 10;

var P=['#2563EB','#0EA5E9','#10B981','#F59E0B','#8B5CF6','#F43F5E','#F97316'];

// Main chart
var mI;
function buildM(d){
  if(mI) mI.destroy();
  mI = new Chart(document.getElementById('mainCht'),{
    data:{
      labels:d.map(function(x){return x.label;}),
      datasets:[
        {type:'bar',label:'Revenue ('+CUR+')',data:d.map(function(x){return x.revenue;}),
         backgroundColor:'#2563EB18',borderColor:'#2563EB',borderWidth:2,borderRadius:6,yAxisID:'yR'},
        {type:'line',label:'Enrollments',data:d.map(function(x){return x.enrollments;}),
         borderColor:'#0EA5E9',backgroundColor:'#0EA5E910',borderWidth:2.5,
         pointRadius:3,pointBackgroundColor:'#0EA5E9',tension:.4,fill:true,yAxisID:'yE'}
      ]
    },
    options:{
      responsive:true,interaction:{mode:'index',intersect:false},
      plugins:{legend:{position:'top',labels:{usePointStyle:true,padding:14,font:{size:11}}}},
      scales:{
        yR:{type:'linear',position:'left',grid:{color:'#F1F5F9'},ticks:{color:'#94A3B8',font:{size:10}}},
        yE:{type:'linear',position:'right',grid:{drawOnChartArea:false},ticks:{color:'#0EA5E9',font:{size:10}}},
        x:{grid:{display:false},ticks:{color:'#94A3B8',font:{size:10},maxRotation:30}}
      }
    }
  });
}
buildM(mD);

function swM(t,b){
  document.querySelectorAll('.cht-tab').forEach(function(x){x.classList.remove('on');});
  b.classList.add('on');
  if(t==='monthly') buildM(mD);
  else if(t==='daily') buildM(dD);
  else buildM(yD);
}

// Category donut
var cLbls = cat.map(function(d){return d.name;});
var cVals = cat.map(function(d){return parseInt(d.cnt);});
var cTot  = cVals.reduce(function(a,b){return a+b;},0)||1;
new Chart(document.getElementById('catCht'),{
  type:'doughnut',
  data:{labels:cLbls.length?cLbls:['No data'],datasets:[{data:cVals.length?cVals:[1],backgroundColor:P,borderColor:'#fff',borderWidth:3,hoverOffset:8}]},
  options:{
    responsive:true,cutout:'65%',
    plugins:{
      legend:{position:'right',labels:{padding:10,font:{size:10},usePointStyle:true,
        generateLabels:function(ch){
          return ch.data.labels.map(function(l,i){
            return {text:l+' '+Math.round((cVals[i]||0)/cTot*100)+'%',fillStyle:P[i],strokeStyle:P[i],pointStyle:'circle'};
          });
        }
      }},
      tooltip:{callbacks:{label:function(c){return ' '+c.label+': '+c.parsed;}}}
    }
  }
});


</script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body></html>
