<?php
session_start();

// ─── Database ───────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skillbloom');

// ─── Site ────────────────────────────────────────────────────────
define('SITE_NAME', 'SkillBloom');
define('SITE_URL',  'http://localhost/skillbloom');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('CURRENCY',  '₹');

// ─── DB Connection ───────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<h3 style="color:red;font-family:sans-serif;padding:2rem;">DB Error: ' . $conn->connect_error . '</h3>');
}
$conn->set_charset('utf8mb4');

// ─── Helpers ─────────────────────────────────────────────────────
function isUser()  { return isset($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['admin_id']); }

function requireUser() {
    if (!isUser()) { flash('error','Please login to continue.'); redirect(SITE_URL.'/user/login.php'); }
}
function requireAdmin() {
    if (!isAdmin()) { redirect(SITE_URL.'/admin/login.php'); }
}

function redirect($url) { header("Location: $url"); exit(); }

function clean($v) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string((string)$v))));
}

function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash() {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function genCertNo() {
    return 'SB-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Y');
}
