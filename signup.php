<?php
require_once 'db.php';
 
$error = '';
$success = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
 
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            if ($insert->execute([$fullName, $email, $hash])) {
                $_SESSION['user_id']   = $pdo->lastInsertId();
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['role']      = 'customer';
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Account creation failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Empire Ventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300..700&family=Fraunces:opsz,wght@9..144,400..600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php renderNav(''); ?>
 
    <main class="auth-main">
        <div class="auth-card">
            <h1>Create Account</h1>
            <p class="auth-subtitle">Sign up to place sack orders and manage wholesale applications.</p>
 
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
 
            <form method="POST" action="signup.php" class="auth-form">
                <div class="field">
                    <label for="full_name">Full Name</label>
                    <input class="input" type="text" id="full_name" name="full_name" required placeholder="e.g. Juan Dela Cruz">
                </div>
 
                <div class="field">
                    <label for="email">Email Address</label>
                    <input class="input" type="email" id="email" name="email" required placeholder="name@example.com">
                </div>
 
                <div class="field">
                    <label for="password">Password</label>
                    <input class="input" type="password" id="password" name="password" required placeholder="At least 6 characters">
                </div>
 
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input class="input" type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                </div>
 
                <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
            </form>
 
            <p class="auth-footer-text">
                Already have an account? <a href="login.php">Log In here</a>
            </p>
        </div>
    </main>
<?php renderFooter(); ?>
</body>
</html>