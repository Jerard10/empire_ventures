<?php
// db.php - connection, session, auth + upload helpers
if (session_status() === PHP_SESSION_NONE) session_start();
 
$host = '127.0.0.1'; $db = 'empire_ventures'; $user = 'root'; $pass = ''; $charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
 
function sanitize($d) { return htmlspecialchars(trim((string)$d), ENT_QUOTES, 'UTF-8'); }
function isLoggedIn() { return isset($_SESSION['user_id']); }
function requireLogin() { if (!isLoggedIn()) { header("Location: login.php"); exit; } }
 
// Always read the user (and role) from the DB, never trust the session role for admin checks.
function currentUser() {
    global $pdo; static $u = false;
    if ($u === false) {
        $u = null;
        if (isLoggedIn()) {
            $s = $pdo->prepare("SELECT id, full_name, email, role, avatar FROM users WHERE id = ?");
            $s->execute([$_SESSION['user_id']]);
            $u = $s->fetch() ?: null;
        }
    }
    return $u;
}
function isAdmin() { $u = currentUser(); return $u && $u['role'] === 'admin'; }
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) { http_response_code(403); die('403 - Admins only. <a href="dashboard.php">Back to dashboard</a>'); }
}
 
function csrfToken() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrfField() { return '<input type="hidden" name="csrf" value="' . csrfToken() . '">'; }
function checkCsrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid form token. Reload the page and try again.'); }
}
 
// Validates and stores an uploaded JPG/PNG/WebP (max 2 MB). Returns the new file name.
function saveImage(array $f, string $dir): string {
    if (($f['error'] ?? 0) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed. Please try again.');
    if ($f['size'] > 2 * 1024 * 1024) throw new RuntimeException('Image must be under 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('Use a JPG, PNG, or WebP image.');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) throw new RuntimeException('Could not save the image.');
    return $name;
}
 
// ---- Shared layout: navbar, footer, variety card ----
 
const SOCIALS = [
    'facebook'  => 'https://facebook.com/',          // <- put your real links here
    'instagram' => 'https://instagram.com/',
    'gmail'     => 'mailto:hello@empireventures.ph',
];
 
function renderNav(string $active = '') {
    $u = currentUser();
    $links = ['home' => ['index.php', 'Home'], 'varieties' => ['varieties.php', 'Rice Varieties'],
              'order' => ['order.php', 'Order Sacks'], 'wholesale' => ['registration.php', 'Wholesale Partner']];
    echo '<header class="header"><nav class="navbar" aria-label="Main">
      <a href="index.php" class="nav-brand"><img class="brand-mark" src="logo.svg" alt="Empire Ventures"><span class="brand-name">EMPIRE <span>VENTURES</span></span></a>
      <div class="nav-menu" id="nav-menu">';
    foreach ($links as $k => [$href, $label])
        echo '<a href="' . $href . '" class="nav-links' . ($k === $active ? ' active' : '') . '">' . $label . '</a>';
    echo '</div><div class="nav-auth-group">';
    if ($u) {
        $av = $u['avatar'] ? '<img src="uploads/avatars/' . sanitize($u['avatar']) . '" alt="">' : '<span>' . sanitize(mb_strtoupper(mb_substr($u['full_name'], 0, 1))) . '</span>';
        echo '<div class="user-menu"><button type="button" class="user-btn" aria-expanded="false" aria-haspopup="true"><span class="avatar avatar-sm">' . $av . '</span>' . sanitize(explode(' ', $u['full_name'])[0]) . '</button>
          <div class="user-drop" role="menu"><a href="dashboard.php">My dashboard</a><a href="profile.php">Edit profile</a>'
          . (isAdmin() ? '<a href="admin.php">Admin panel</a><a href="admin_products.php">Product pricing</a>' : '') .
          '<a href="logout.php">Log out</a></div></div>';
    } else {
        echo '<a href="login.php" class="nav-back">Log In</a><a href="signup.php" class="nav-cta">Sign Up</a>';
    }
    echo '</div><button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Open menu"><span></span><span></span><span></span></button></nav></header>';
}
 
function renderFooter() {
    $i = [
      'facebook'  => '<path fill="currentColor" stroke="none" d="M13.5 21v-8h2.7l.4-3.2h-3.1V7.8c0-.9.3-1.5 1.6-1.5h1.7V3.4c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.1v2.4H7.7V13h2.7v8z"/>',
      'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor"/>',
      'gmail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
    ];
    echo '<footer class="site-footer"><div class="footer-inner"><div><strong>Empire Ventures</strong><p>Premium rice, supplied by the sack.</p></div><div class="socials">';
    foreach (SOCIALS as $name => $url)
        echo '<a href="' . sanitize($url) . '" class="social" aria-label="' . ucfirst($name) . '" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $i[$name] . '</svg></a>';
    echo '</div></div><p class="footer-copy">&copy; ' . date('Y') . ' Empire Ventures. All rights reserved.</p></footer><script src="main.js" defer></script>';
}
 
function productImage($v) { return $v['image'] ? 'uploads/products/' . sanitize($v['image']) : ''; }
 
function renderVarietyCard(array $v) {
    $img = productImage($v);
    echo '<article class="rice-card"><div class="rice-img">' .
         ($img ? '<img src="' . $img . '" alt="' . sanitize($v['name']) . '" loading="lazy">' : '<span class="rice-img-empty">Product photo</span>') .
         '</div><div class="rice-body"><h3>' . sanitize($v['name']) . '</h3><p>' . sanitize($v['description']) . '</p>
         <div class="rice-foot"><div><small>Per ' . (int)$v['sack_weight_kg'] . 'kg sack</small><strong>₱' . number_format($v['price_per_sack'], 2) . '</strong></div>
         <a href="order.php?variety_id=' . (int)$v['id'] . '" class="btn btn-primary btn-sm">Order sack</a></div></div></article>';
}