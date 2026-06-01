<?php
// require_once 'includes/db.php';
// require_once 'includes/auth.php';
// require_once 'includes/functions.php';
require_once 'db.php';
require_once 'auth.php';
require_once 'functions.php';

session_start();
requireLogin();

$user = getCurrentUser($conn);
$uid = (int)$_SESSION['user_id'];

// Quick stats
$total_cars = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cars WHERE user_id = $uid AND status != 'rejected'"))[0];
$total_preds = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM predictions WHERE user_id = $uid"))[0];
$wishlist_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM wishlist WHERE user_id = $uid"))[0];

$name_parts = explode(' ', $user['full_name']);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — CAReva</title>
<link rel="stylesheet" href="style.css">
<style>
.welcome-banner {
    background: linear-gradient(135deg, rgba(0,212,255,0.08) 0%, rgba(0,99,150,0.05) 100%);
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative; overflow: hidden;
}
.welcome-banner::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--neon), transparent);
}
.welcome-name {
    font-family: var(--font-display); font-size: 1.8rem; font-weight: 700;
    color: var(--neon); text-shadow: var(--neon-glow-sm);
}
.welcome-time { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.3rem; }
</style>
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
    <a href="index.php" class="topbar-logo">CAReva</a>
    <div class="topbar-actions">
        <span class="topbar-user">👤 <?= htmlspecialchars($user['full_name']) ?></span>
        <a href="logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= $initials ?></div>
            <div class="sidebar-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="active"><span class="nav-icon">🏠</span> Dashboard</a></li>
            <li><a href="buy-cars.php"><span class="nav-icon">🚗</span> Buy Cars</a></li>
            <li><a href="sell-car.php"><span class="nav-icon">💰</span> Sell Car</a></li>
            <li><a href="future-predictor.php"><span class="nav-icon">🔮</span> Future Predictor</a></li>
            <li><a href="nearby-cars.php"><span class="nav-icon">📍</span> Nearby Cars</a></li>
            <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
            <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-name">Welcome back, <?= htmlspecialchars($name_parts[0]) ?>! 👋</div>
            <div class="welcome-time"><?= date('l, d F Y — h:i A') ?> IST</div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🚗</div>
                <div class="stat-value" data-count="<?= $total_cars ?>"><?= $total_cars ?></div>
                <div class="stat-label">Cars Listed</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔮</div>
                <div class="stat-value" data-count="<?= $total_preds ?>"><?= $total_preds ?></div>
                <div class="stat-label">Predictions Made</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-value" data-count="<?= $wishlist_count ?>"><?= $wishlist_count ?></div>
                <div class="stat-label">Wishlist Items</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏙️</div>
                <div class="stat-value" style="font-size:1.3rem;"><?= htmlspecialchars($user['city']) ?></div>
                <div class="stat-label">Your City</div>
            </div>
        </div>

        <!-- Main Navigation Cards -->
        <div class="page-header">
            <div class="page-title">Quick Access</div>
            <div class="page-subtitle">Navigate to any section from here</div>
        </div>

        <div class="dash-cards">
            <a href="buy-cars.php" class="dash-card">
                <span class="dash-card-icon">🚗</span>
                <div class="dash-card-title">Buy Cars</div>
                <div class="dash-card-desc">Browse thousands of verified listings</div>
            </a>
            <a href="sell-car.php" class="dash-card">
                <span class="dash-card-icon">💰</span>
                <div class="dash-card-title">Sell Car</div>
                <div class="dash-card-desc">List your car and reach buyers fast</div>
            </a>
            <a href="future-predictor.php" class="dash-card">
                <span class="dash-card-icon">🔮</span>
                <div class="dash-card-title">Future Predictor</div>
                <div class="dash-card-desc">AI-powered 5, 10 & 20 year value forecasts</div>
            </a>
            <a href="nearby-cars.php" class="dash-card">
                <span class="dash-card-icon">📍</span>
                <div class="dash-card-title">Nearby Cars</div>
                <div class="dash-card-desc">Find cars close to your location</div>
            </a>
            <a href="profile.php" class="dash-card">
                <span class="dash-card-icon">👤</span>
                <div class="dash-card-title">My Profile</div>
                <div class="dash-card-desc">Manage your account and history</div>
            </a>
            <a href="logout.php" class="dash-card" data-confirm="Are you sure you want to logout?">
                <span class="dash-card-icon">🚪</span>
                <div class="dash-card-title">Logout</div>
                <div class="dash-card-desc">Sign out of your account safely</div>
            </a>
        </div>

    </main>
</div>

<script src="js/script.js"></script>
</body>
</html>