<?php
// require_once 'includes/db.php';
// require_once 'includes/auth.php';
// require_once 'includes/functions.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'functions.php';
session_start();
if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit(); }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($conn, $_POST['full_name'] ?? '');
    $dob     = sanitize($conn, $_POST['dob'] ?? '');
    $gender  = sanitize($conn, $_POST['gender'] ?? '');
    $mobile  = sanitize($conn, $_POST['mobile'] ?? '');
    $email   = sanitize($conn, $_POST['email'] ?? '');
    $city    = sanitize($conn, $_POST['city'] ?? '');
    $state   = sanitize($conn, $_POST['state'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($dob) || empty($gender) || empty($mobile) || empty($email) || empty($city) || empty($state) || empty($pass)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $error = 'Invalid mobile number.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $chk = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($chk) > 0) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, dob, gender, mobile, email, city, state, password) VALUES ('$name','$dob','$gender','$mobile','$email','$city','$state','$hashed')";
            if (mysqli_query($conn, $sql)) {
                header('Location: login.php?msg=Registration successful! Please login.');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$states = getIndianStates();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — CAReva</title>
<link rel="stylesheet" href="style.css">
<style>
.auth-wrapper { align-items: flex-start; padding-top: 6rem; }
.auth-card { max-width: 680px; }
.pass-wrap { position: relative; }
.pass-wrap .toggle-password { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.1rem; }
</style>
</head>
<body>
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
            <p class="auth-title">Create Your Account</p>
        </div>
        <div class="auth-body">
            <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>

            <form method="POST" novalidate>
                <div class="section-title">Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= (($_POST['gender'] ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" placeholder="9876543210" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="section-title">Location</div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" placeholder="Mumbai" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <select name="state" class="form-control" required>
                            <option value="">Select State</option>
                            <?php foreach ($states as $s): ?>
                                <option value="<?= $s ?>" <?= (($_POST['state'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="section-title">Security</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="pass-wrap">
                            <input type="password" name="password" id="pass1" class="form-control" placeholder="Min 6 characters" required>
                            <button type="button" class="toggle-password" data-target="pass1">👁</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div class="pass-wrap">
                            <input type="password" name="confirm_password" id="pass2" class="form-control" placeholder="Repeat password" required>
                            <button type="button" class="toggle-password" data-target="pass2">👁</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg mt-2">Create Account →</button>
                <p class="text-center mt-2" style="color:var(--text-muted);font-size:0.9rem;">Already have an account? <a href="login.php" style="color:var(--neon);">Sign In</a></p>
            </form>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>