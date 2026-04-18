<?php
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');

$title = isset($_GET['title']) ? strip_tags(substr($_GET['title'], 0, 50)) : 'Course';
$cat   = isset($_GET['cat'])   ? strip_tags(substr($_GET['cat'],   0, 30)) : 'Technology';
$catLow = strtolower($cat.' '.$title);

if     (strpos($catLow,'web')!==false   || strpos($catLow,'html')!==false)  $clr='blue';
elseif (strpos($catLow,'python')!==false|| strpos($catLow,'data')!==false)  $clr='violet';
elseif (strpos($catLow,'design')!==false|| strpos($catLow,'ui')!==false)    $clr='pink';
elseif (strpos($catLow,'java')!==false  || strpos($catLow,'android')!==false) $clr='orange';
elseif (strpos($catLow,'php')!==false   || strpos($catLow,'backend')!==false) $clr='teal';
elseif (strpos($catLow,'ai')!==false    || strpos($catLow,'machine')!==false) $clr='emerald';
elseif (strpos($catLow,'flutter')!==false||strpos($catLow,'mobile')!==false) $clr='rose';
elseif (strpos($catLow,'cloud')!==false || strpos($catLow,'devops')!==false) $clr='sky';
elseif (strpos($catLow,'sql')!==false   || strpos($catLow,'database')!==false) $clr='amber';
elseif (strpos($catLow,'cyber')!==false || strpos($catLow,'security')!==false) $clr='slate';
elseif (strpos($catLow,'marketing')!==false||strpos($catLow,'business')!==false) $clr='lime';
else $clr = 'blue';

// LIGHT pastel themes: [bg_outer, bg_inner, accent, text_dark, icon_bg]
$themes = array(
  'blue'    => array('#EFF6FF','#DBEAFE','#2563EB','#1E3A8A','#BFDBFE'),
  'violet'  => array('#F5F3FF','#EDE9FE','#7C3AED','#3B0764','#C4B5FD'),
  'pink'    => array('#FDF4FF','#FAE8FF','#9333EA','#581C87','#E9D5FF'),
  'orange'  => array('#FFF7ED','#FFEDD5','#EA580C','#7C2D12','#FED7AA'),
  'teal'    => array('#F0FDFA','#CCFBF1','#0D9488','#134E4A','#99F6E4'),
  'emerald' => array('#ECFDF5','#D1FAE5','#059669','#064E3B','#6EE7B7'),
  'rose'    => array('#FFF1F2','#FFE4E6','#E11D48','#881337','#FECDD3'),
  'sky'     => array('#F0F9FF','#E0F2FE','#0369A1','#0C4A6E','#BAE6FD'),
  'amber'   => array('#FFFBEB','#FEF3C7','#D97706','#78350F','#FDE68A'),
  'slate'   => array('#F8FAFC','#F1F5F9','#475569','#0F172A','#CBD5E1'),
  'lime'    => array('#F7FEE7','#ECFCCB','#65A30D','#365314','#BEF264'),
);

$th   = $themes[$clr];
$bg1  = $th[0]; $bg2 = $th[1]; $acc = $th[2]; $dark = $th[3]; $light = $th[4];

$labels = array(
  'blue'=>array('HTML','CSS','JS'), 'violet'=>array('Python','ML','Data'),
  'pink'=>array('Figma','UI','UX'), 'orange'=>array('Java','OOP','API'),
  'teal'=>array('PHP','MySQL','Laravel'), 'emerald'=>array('AI','Deep','Learn'),
  'rose'=>array('Flutter','iOS','App'), 'sky'=>array('AWS','Cloud','K8s'),
  'amber'=>array('SQL','DB','Query'), 'slate'=>array('Cyber','Net','Sec'),
  'lime'=>array('SEO','Ads','Social'),
);
$lbs = isset($labels[$clr]) ? $labels[$clr] : array('Code','Build','Ship');

// Word wrap
$words = explode(' ', $title);
$lines = array(); $cur = '';
foreach($words as $w){
  if($cur!=='' && strlen($cur.' '.$w)>20){ $lines[]=$cur; $cur=$w; }
  else { $cur = $cur!=='' ? $cur.' '.$w : $w; }
}
if($cur!=='') $lines[]=$cur;
$lines = array_slice($lines,0,3);

$titleSVG='';
$yS = 148 - count($lines)*12;
foreach($lines as $i=>$ln)
  $titleSVG .= '<text x="18" y="'.($yS+$i*24).'" font-family="Inter,sans-serif" font-size="15" font-weight="800" fill="'.htmlspecialchars($dark).'">'.htmlspecialchars($ln).'</text>';

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="190" viewBox="0 0 320 190">
<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0%" stop-color="<?=$bg1?>"/>
    <stop offset="100%" stop-color="<?=$bg2?>"/>
  </linearGradient>
  <filter id="sh"><feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="<?=$acc?>" flood-opacity="0.12"/></filter>
  <filter id="soft2"><feGaussianBlur stdDeviation="25"/></filter>
  <clipPath id="cl"><rect width="320" height="190"/></clipPath>
</defs>
<g clip-path="url(#cl)">
  <!-- Light background -->
  <rect width="320" height="190" fill="url(#bg)"/>
  <!-- Subtle glow blobs -->
  <circle cx="290" cy="20"  r="80" fill="<?=$acc?>" opacity="0.07" filter="url(#soft2)"/>
  <circle cx="40"  cy="185" r="65" fill="<?=$acc?>" opacity="0.06" filter="url(#soft2)"/>
  <!-- Top accent stripe -->
  <rect x="0" y="0" width="320" height="4" fill="<?=$acc?>"/>

  <!-- Laptop screen (light card style) -->
  <rect x="192" y="24" width="114" height="80" rx="8" fill="white" opacity="0.9" filter="url(#sh)"/>
  <rect x="192" y="24" width="114" height="22" rx="8" fill="<?=$acc?>"/>
  <rect x="192" y="38"  width="114" height="8" fill="<?=$acc?>"/>
  <!-- Window dots -->
  <circle cx="202" cy="35" r="3" fill="white" opacity="0.6"/>
  <circle cx="212" cy="35" r="3" fill="white" opacity="0.6"/>
  <circle cx="222" cy="35" r="3" fill="white" opacity="0.6"/>
  <!-- Code lines -->
  <rect x="200" y="52" width="55" height="5" rx="2.5" fill="<?=$acc?>" opacity="0.25"/>
  <rect x="200" y="61" width="85" height="4" rx="2"   fill="<?=$acc?>" opacity="0.15"/>
  <rect x="200" y="69" width="70" height="4" rx="2"   fill="<?=$acc?>" opacity="0.20"/>
  <rect x="200" y="77" width="50" height="4" rx="2"   fill="<?=$acc?>" opacity="0.15"/>
  <rect x="200" y="85" width="75" height="4" rx="2"   fill="<?=$acc?>" opacity="0.18"/>
  <!-- Label badge on screen -->
  <rect x="200" y="52" width="<?=strlen($lbs[0])*6+14?>" height="14" rx="4" fill="<?=$acc?>"/>
  <text x="207" y="63" font-family="monospace" font-size="7.5" font-weight="700" fill="white"><?=htmlspecialchars($lbs[0])?></text>
  <!-- Laptop base -->
  <rect x="184" y="104" width="130" height="8" rx="4" fill="<?=$light?>" opacity="0.7"/>
  <rect x="200" y="111" width="114" height="3" rx="1.5" fill="<?=$acc?>" opacity="0.15"/>

  <!-- Floating tag pills -->
  <rect x="192" y="120" width="<?=strlen($lbs[1])*7+18?>" height="19" rx="9.5" fill="<?=$acc?>" opacity="0.13"/>
  <rect x="192" y="120" width="<?=strlen($lbs[1])*7+18?>" height="19" rx="9.5" fill="none" stroke="<?=$acc?>" stroke-width="1.2" opacity="0.35"/>
  <text x="201" y="133" font-family="Inter,sans-serif" font-size="8.5" font-weight="700" fill="<?=$dark?>"><?=htmlspecialchars($lbs[1])?></text>

  <?php $px2 = strlen($lbs[1])*7 + 218; ?>
  <rect x="<?=$px2?>" y="120" width="<?=strlen($lbs[2])*7+18?>" height="19" rx="9.5" fill="<?=$acc?>" opacity="0.13"/>
  <rect x="<?=$px2?>" y="120" width="<?=strlen($lbs[2])*7+18?>" height="19" rx="9.5" fill="none" stroke="<?=$acc?>" stroke-width="1.2" opacity="0.35"/>
  <text x="<?=$px2+9?>" y="133" font-family="Inter,sans-serif" font-size="8.5" font-weight="700" fill="<?=$dark?>"><?=htmlspecialchars($lbs[2])?></text>

  <!-- Category badge -->
  <rect x="14" y="13" width="<?=min(strlen($cat)*7+20,145)?>" height="23" rx="11.5" fill="<?=$acc?>"/>
  <text x="23" y="28.5" font-family="Inter,sans-serif" font-size="10.5" font-weight="700" fill="white"><?=htmlspecialchars($cat)?></text>

  <!-- Title (dark readable text) -->
  <?=$titleSVG?>

  <!-- Bottom strip -->
  <rect x="0" y="177" width="320" height="13" fill="<?=$acc?>" opacity="0.08"/>
  <text x="16" y="188" font-family="Inter,sans-serif" font-size="9" font-weight="800" fill="<?=$acc?>">SkillBloom</text>
</g>
</svg>
