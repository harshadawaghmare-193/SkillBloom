<?php
require_once dirname(__DIR__).'/config.php';
$flash=getFlash();

// ── Filters ──────────────────────────────────────────────────────
$where = "c.status='published'";
$cat_id = intval(isset($_GET['cat']) ? $_GET['cat'] : 0);
$level  = isset($_GET['level']) ? trim(strip_tags($_GET['level'])) : '';
// FIX: Use real_escape_string directly for search (clean() htmlencodes which breaks LIKE)
$search = isset($_GET['q']) ? trim($conn->real_escape_string(strip_tags($_GET['q']))) : '';

if($cat_id) $where .= " AND c.category_id=$cat_id";
if($level && $level !== 'All Levels') $where .= " AND c.level='$level'";
if($search) $where .= " AND (c.title LIKE '%$search%' OR c.instructor LIKE '%$search%' OR c.description LIKE '%$search%')";

$sort  = isset($_GET['sort']) ? $_GET['sort'] : 'popular';
$order = $sort==='newest' ? 'c.id DESC' : ($sort==='price_low' ? 'c.price ASC' : ($sort==='price_high' ? 'c.price DESC' : ($sort==='rating' ? 'c.rating DESC' : 'c.students DESC')));

$page    = max(1, intval(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 9;
$offset  = ($page-1)*$perPage;

$courses    = $conn->query("SELECT c.*,cat.name cat_name FROM courses c LEFT JOIN categories cat ON cat.id=c.category_id WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
$total      = $conn->query("SELECT COUNT(*) c FROM courses c WHERE $where")->fetch_assoc()['c'];
$pages      = ceil($total/$perPage);
$totalAll   = $conn->query("SELECT COUNT(*) c FROM courses c WHERE c.status='published'")->fetch_assoc()['c'];
$categories = $conn->query("SELECT cat.*,COUNT(c.id) cnt FROM categories cat LEFT JOIN courses c ON c.category_id=cat.id AND c.status='published' GROUP BY cat.id ORDER BY cat.name")->fetch_all(MYSQLI_ASSOC);

function catIcon($n){
  $m=['Web Development'=>'💻','Data Science'=>'📊','UI/UX Design'=>'🎨','Business'=>'💼','Mobile Apps'=>'📱','Cybersecurity'=>'🔐','AI/ML'=>'🤖','AI/MI'=>'🤖'];
  return isset($m[$n]) ? $m[$n] : '📚';
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Browse Courses — SkillBloom</title>
<link rel="stylesheet" href="<?=SITE_URL?>/assets/css/style.css">
<style>
/* ── Hero ── */
.courses-hero {
  background: linear-gradient(135deg,#EFF6FF 0%,#DBEAFE 60%,#BAE6FD 100%);
  padding: 3rem 1.5rem 2.5rem;
  text-align: center;
  border-bottom: 1px solid #BFDBFE;
}
.courses-hero h1 {
  font-size: 2.1rem;
  font-weight: 800;
  color: #0F172A;
  margin-bottom: .5rem;
}
.courses-hero p { color: #4B5563; margin-bottom: 1.4rem; font-size: 1rem; }
.hero-search {
  display: flex;
  max-width: 520px;
  margin: 0 auto;
  background: #fff;
  border-radius: 50px;
  padding: .35rem;
  box-shadow: 0 4px 24px rgba(37,99,235,.14);
  border: 1.5px solid #BFDBFE;
}
.hero-search input {
  flex: 1; border: none; outline: none;
  padding: .65rem 1.2rem; font-size: .95rem;
  color: #111; background: transparent; border-radius: 50px;
}
.hero-search button {
  background: #2563EB; color: #fff; border: none;
  padding: .65rem 1.6rem; border-radius: 50px;
  font-weight: 700; cursor: pointer; font-size: .9rem;
  transition: background .2s;
}
.hero-search button:hover { background: #1D4ED8; }

/* ── Layout ── */
.courses-wrap {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.75rem 1.5rem;
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
}

/* ── Sidebar ── */
.filter-sidebar {
  width: 220px;
  flex-shrink: 0;
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 1.3rem;
  position: sticky;
  top: 80px;
}
.filter-sidebar h4 { font-size: .95rem; margin-bottom: 1rem; color: #111; }
.filter-group {
  margin-bottom: 1.2rem;
  padding-bottom: 1.2rem;
  border-bottom: 1px solid #F3F4F6;
}
.filter-group:last-child { border: none; margin: 0; padding: 0; }
.filter-group h5 {
  font-size: .8rem; font-weight: 700;
  color: #6B7280; letter-spacing: .05em;
  text-transform: uppercase; margin-bottom: .65rem;
}
.filter-group label {
  display: flex; align-items: center; gap: .5rem;
  font-size: .86rem; color: #374151;
  cursor: pointer; padding: .28rem 0;
  border-radius: 6px; transition: color .15s;
}
.filter-group label:hover { color: #2563EB; }
.filter-group label input { accent-color: #2563EB; }
.filter-count {
  margin-left: auto;
  font-size: .75rem;
  color: #9CA3AF;
  background: #F3F4F6;
  border-radius: 20px;
  padding: 1px 7px;
}

/* ── Main area ── */
.courses-main { flex: 1; min-width: 0; }
.courses-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.1rem;
  flex-wrap: wrap;
  gap: .75rem;
}
.courses-count { font-size: .9rem; color: #6B7280; }
.courses-count strong { color: #111; }
.sort-select {
  border: 1.5px solid #E5E7EB;
  border-radius: 8px;
  padding: .38rem .75rem;
  font-size: .875rem;
  outline: none;
  color: #374151;
  background: #fff;
  cursor: pointer;
}

/* ── Course Cards ── */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 1.25rem;
}
.course-card {
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  overflow: hidden;
  transition: transform .2s, box-shadow .2s, border-color .2s;
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
}
.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(37,99,235,.12);
  border-color: #BFDBFE;
}
.course-card .thumb {
  width: 100%; height: 172px;
  overflow: hidden;
  background: #EFF6FF;
  flex-shrink: 0;
}
.course-card .thumb img {
  width: 100%; height: 100%;
  object-fit: cover; display: block;
  transition: transform .3s;
}
.course-card:hover .thumb img { transform: scale(1.04); }

.card-body { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
.card-badges { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .6rem; }
.badge-cat {
  font-size: .72rem; font-weight: 700;
  padding: .25rem .65rem;
  border-radius: 20px;
  background: #EFF6FF; color: #2563EB;
}
.badge-feat {
  font-size: .72rem; font-weight: 700;
  padding: .25rem .65rem;
  border-radius: 20px;
  background: #FEF3C7; color: #92400E;
}
.card-title {
  font-size: .95rem;
  font-weight: 700;
  color: #0F172A;
  line-height: 1.4;
  margin-bottom: .45rem;
}
.card-instructor {
  font-size: .8rem;
  color: #9CA3AF;
  margin-bottom: .55rem;
}
.card-meta {
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .78rem;
  color: #6B7280;
  margin-bottom: .8rem;
  flex-wrap: wrap;
}
.stars { color: #F59E0B; }
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: auto;
  padding-top: .8rem;
  border-top: 1px solid #F3F4F6;
}
.price { font-size: 1.1rem; font-weight: 800; color: #2563EB; }
.price-old { font-size: .85rem; color: #9CA3AF; text-decoration: line-through; margin-left: .4rem; }
.price-free { font-size: 1rem; font-weight: 800; color: #059669; }
.btn-view {
  font-size: .8rem; font-weight: 600;
  color: #2563EB; border: 1.5px solid #BFDBFE;
  background: #EFF6FF;
  padding: .35rem .9rem;
  border-radius: 8px;
  transition: all .15s;
  white-space: nowrap;
}
.course-card:hover .btn-view {
  background: #2563EB; color: #fff; border-color: #2563EB;
}

/* ── Empty State ── */
.empty-box {
  background: #fff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 4rem 2rem;
  text-align: center;
}
.empty-box .ei { font-size: 3.5rem; margin-bottom: 1rem; }
.empty-box h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: .5rem; color: #111; }
.empty-box p  { color: #6B7280; margin-bottom: 1.5rem; }

/* ── Search highlight ── */
.search-active-bar {
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 10px;
  padding: .6rem 1rem;
  font-size: .88rem;
  color: #1D4ED8;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ── Pagination ── */
.pagination { display: flex; gap: .4rem; margin-top: 1.5rem; flex-wrap: wrap; }
.pg {
  width: 34px; height: 34px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: .875rem; font-weight: 600;
  border: 1.5px solid #E5E7EB; color: #374151;
  text-decoration: none; transition: all .15s;
}
.pg:hover { border-color: #2563EB; color: #2563EB; }
.pg.active { background: #2563EB; border-color: #2563EB; color: #fff; }
</style>
</head><body>
<?php include dirname(__DIR__).'/includes/navbar.php'; ?>

<!-- Hero -->
<div class="courses-hero">
  <h1>Discover Your Next Skill 🚀</h1>
  <p>Over <?=number_format($totalAll)?>+ courses to explore — start learning today</p>
  <form method="GET" class="hero-search" id="searchForm">
    <?php if($cat_id): ?><input type="hidden" name="cat" value="<?=$cat_id?>"><?php endif; ?>
    <?php if($level): ?><input type="hidden" name="level" value="<?=htmlspecialchars($level)?>"><?php endif; ?>
    <input type="text" name="q"
      value="<?=htmlspecialchars(isset($_GET['q']) ? strip_tags($_GET['q']) : '')?>"
      placeholder="Search courses, topics, instructors...">
    <button type="submit">Search</button>
  </form>
</div>

<div class="courses-wrap">

<!-- Sidebar -->
<aside class="filter-sidebar">
  <h4>🔍 Filters</h4>
  <form method="GET" id="filterForm">
    <?php if($search): ?><input type="hidden" name="q" value="<?=htmlspecialchars(isset($_GET['q']) ? strip_tags($_GET['q']) : '')?>"><?php endif; ?>

    <div class="filter-group">
      <h5>Category</h5>
      <?php foreach($categories as $cat): ?>
      <label>
        <input type="radio" name="cat" value="<?=$cat['id']?>"
          <?=$cat_id==$cat['id']?'checked':''?>
          onchange="document.getElementById('filterForm').submit()">
        <?=catIcon($cat['name'])?> <?=htmlspecialchars($cat['name'])?>
        <span class="filter-count"><?=$cat['cnt']?></span>
      </label>
      <?php endforeach; ?>
      <label>
        <input type="radio" name="cat" value="0" <?=$cat_id==0?'checked':''?>
          onchange="document.getElementById('filterForm').submit()">
        🌐 All Categories
      </label>
    </div>

    <div class="filter-group">
      <h5>Level</h5>
      <?php foreach([''=>'All Levels','Beginner'=>'Beginner','Intermediate'=>'Intermediate','Advanced'=>'Advanced'] as $val=>$label): ?>
      <label>
        <input type="radio" name="level" value="<?=$val?>"
          <?=$level===$val?'checked':''?>
          onchange="document.getElementById('filterForm').submit()">
        <?=$label?>
      </label>
      <?php endforeach; ?>
    </div>

    <?php if($cat_id || $level || $search): ?>
    <a href="courses.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:.5rem;">✕ Clear Filters</a>
    <?php endif; ?>
  </form>
</aside>

<!-- Main -->
<div class="courses-main">

  <?php if($search): ?>
  <div class="search-active-bar">
    <span>🔍 Results for: <strong>"<?=htmlspecialchars(isset($_GET['q']) ? strip_tags($_GET['q']) : '')?>"</strong></span>
    <a href="courses.php<?=$cat_id?"?cat=$cat_id":''?>" style="color:#2563EB;font-weight:600;font-size:.82rem;">✕ Clear search</a>
  </div>
  <?php endif; ?>

  <div class="courses-toolbar">
    <p class="courses-count">
      Showing <strong><?=number_format($total)?></strong> course<?=$total!=1?'s':''?>
      <?php if($search): ?> for "<?=htmlspecialchars(isset($_GET['q']) ? strip_tags($_GET['q']) : '')?>"<?php endif; ?>
    </p>
    <select class="sort-select" onchange="window.location='?sort='+this.value+'<?=$cat_id?"&cat=$cat_id":''?><?=$level?"&level=".urlencode($level):''?><?=$search?"&q=".urlencode(isset($_GET['q']) ? strip_tags($_GET['q']) : ''):''?>'">
      <option value="popular" <?=$sort==='popular'?'selected':''?>>Most Popular</option>
      <option value="rating"  <?=$sort==='rating'?'selected':''?>>Top Rated</option>
      <option value="newest"  <?=$sort==='newest'?'selected':''?>>Newest</option>
      <option value="price_low"  <?=$sort==='price_low'?'selected':''?>>Price: Low to High</option>
      <option value="price_high" <?=$sort==='price_high'?'selected':''?>>Price: High to Low</option>
    </select>
  </div>

  <?php if($courses): ?>
  <div class="courses-grid">
  <?php foreach($courses as $c):
    $catName = isset($c['cat_name']) ? $c['cat_name'] : 'Technology';
    if(!empty($c['thumbnail'])){
      $turl = SITE_URL.'/uploads/'.htmlspecialchars($c['thumbnail']);
    } else {
      $turl = SITE_URL.'/user/course-thumbnail.php?title='.urlencode($c['title']).'&cat='.urlencode($catName);
    }
  ?>
  <a href="course-details.php?id=<?=$c['id']?>" class="course-card">
    <div class="thumb">
      <img src="<?=$turl?>" alt="<?=htmlspecialchars($c['title'])?>" loading="lazy">
    </div>
    <div class="card-body">
      <div class="card-badges">
        <span class="badge-cat"><?=catIcon($catName)?> <?=htmlspecialchars($catName)?></span>
        <?php if($c['featured']): ?><span class="badge-feat">⭐ Featured</span><?php endif; ?>
      </div>
      <div class="card-title"><?=htmlspecialchars($c['title'])?></div>
      <div class="card-instructor">👨‍🏫 <?=htmlspecialchars($c['instructor'])?></div>
      <div class="card-meta">
        <span class="stars"><?=str_repeat('★',floor((isset($c['rating']) ? $c['rating'] : 0)))?></span>
        <span><?=number_format((isset($c['rating']) ? $c['rating'] : 0),1)?> (<?=number_format((isset($c['students']) ? $c['students'] : 0))?>)</span>
        <span>·</span>
        <span>⏱ <?=$c['hours']?>h</span>
        <span>·</span>
        <span>📊 <?=$c['level']?></span>
      </div>
      <div class="card-footer">
        <div>
          <?php if($c['price']==0): ?>
            <span class="price-free">FREE</span>
          <?php else: ?>
            <span class="price"><?=CURRENCY?><?=number_format($c['price'])?></span>
            <?php if((isset($c['old_price']) ? $c['old_price'] : 0)>$c['price']): ?><span class="price-old"><?=CURRENCY?><?=number_format($c['old_price'])?></span><?php endif; ?>
          <?php endif; ?>
        </div>
        <span class="btn-view">View →</span>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
  </div>

  <?php if($pages>1): ?>
  <div class="pagination">
    <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?page=<?=$i?><?=$cat_id?"&cat=$cat_id":''?><?=$level?"&level=".urlencode($level):''?><?=$search?"&q=".urlencode(isset($_GET['q']) ? strip_tags($_GET['q']) : ''):''?>&sort=<?=$sort?>" class="pg <?=$i===$page?'active':''?>"><?=$i?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="empty-box">
    <div class="ei">🔍</div>
    <h3>No courses found</h3>
    <p><?=$search ? 'No results for "'.$search.'". Try different keywords.' : 'Try adjusting your filters.'?></p>
    <a href="courses.php" class="btn btn-primary">Browse All Courses</a>
  </div>
  <?php endif; ?>

</div>
</div>

<?php include dirname(__DIR__).'/includes/footer.php'; ?>
<script src="<?=SITE_URL?>/assets/js/main.js"></script>
<script>
// Fix broken filter form tag (typo fix)
document.querySelectorAll('#filterForm input[type=hidden]').forEach(function(el){
  if(!el.value) el.remove();
});
</script>
</body></html>
