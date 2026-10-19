<?php
require_once 'db.php';
 
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
 
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']      = $user['role'];
 
            header("Location: " . ($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
            exit;
        } else {
            $error = 'Invalid email or password combination.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Empire Ventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&family=Fraunces:opsz,wght@9..144,400..600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php renderNav(''); ?>
 
    <main class="auth-main">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Log in to view your dashboard, order history, and wholesale records.</p>
 
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
 
            <form method="POST" action="login.php" class="auth-form">
                <div class="field">
                    <label for="email">Email Address</label>
                    <input class="input" type="email" id="email" name="email" required placeholder="name@example.com">
                </div>
 
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required placeholder="Enter password">
                </div>
 
                <button type="submit" class="btn btn-primary btn-block">Log In</button>
            </form>
 
            <p class="auth-footer-text">
                Don't have an account yet? <a href="signup.php">Sign Up here</a>
            </p>
        </div>
    </main>
<?php renderFooter(); ?>
</body>
</html>