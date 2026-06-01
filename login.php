<?php
// require_once 'includes/db.php';
// require_once 'includes/auth.php';
require_once 'db.php';
require_once 'auth.php';


session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }

$error = '';
$msg = htmlspecialchars($_GET['msg'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        $error = 'Email and password are required.';
    } else {
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND is_active = 1");
        $user = mysqli_fetch_assoc($result);
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}");
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — CAReva</title>
<link rel="stylesheet" href="style.css">
<style>
.pass-wrap { position: relative; }
.pass-wrap .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.1rem; }
.login-decor {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background:
        radial-gradient(ellipse at 15% 50%, rgba(0,212,255,0.07) 0%, transparent 50%),
        radial-gradient(ellipse at 85% 30%, rgba(0,99,150,0.05) 0%, transparent 50%),
        var(--bg-deep);
    z-index: -1;
}
</style>
</head>
<body>
<div class="login-decor"></div>
<nav class="navbar">
    <a href="index.php" class="nav-logo">CAR<span>eva</span></a>
    <div class="nav-actions">
        <a href="login.php" class="btn btn-ghost">Login</a>
        <a href="signup.php" class="btn btn-primary">Sign Up</a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <a href="index.php" class="auth-logo">CAReva</a>
            <p class="auth-title">Welcome Back</p>
        </div>
        <div class="auth-body">
            <?php if ($msg): ?><div class="alert alert-success">✅ <?= $msg ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="password" id="pass1" class="form-control" placeholder="Your password" required>
                        <button type="button" class="toggle-password" data-target="pass1">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full btn-lg mt-2">Sign In →</button>
            </form>

            <div class="neon-line"></div>
            <p class="text-center" style="color:var(--text-muted);font-size:0.9rem;">
                Don't have an account? <a href="signup.php" style="color:var(--neon);">Create one free</a>
            </p>
            <p class="text-center mt-1" style="color:var(--text-muted);font-size:0.82rem;">
                Admin? <a href="admin-panel.php" style="color:var(--text-secondary);">Admin Login →</a>
            </p>
            <div class="alert alert-info mt-2" style="font-size:0.8rem;">
                🔑 Demo: <strong>demo@careva.com</strong> / <strong>password</strong>
            </div>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>