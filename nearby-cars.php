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
$name_parts = explode(' ', $user['full_name']);
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));

$nearby_cars = [];
$user_lat = (float)($_POST['user_lat'] ?? 0);
$user_lng = (float)($_POST['user_lng'] ?? 0);
$searched = false;

if ($user_lat && $user_lng) {
    $searched = true;
    $result = mysqli_query($conn, "SELECT c.*, u.full_name as owner_name FROM cars c JOIN users u ON c.user_id = u.id WHERE c.status = 'active' AND c.latitude IS NOT NULL AND c.longitude IS NOT NULL");
    while ($car = mysqli_fetch_assoc($result)) {
        $dist = haversineDistance($user_lat, $user_lng, $car['latitude'], $car['longitude']);
        $car['distance'] = round($dist, 1);
        $nearby_cars[] = $car;
    }
    usort($nearby_cars, fn($a, $b) => $a['distance'] <=> $b['distance']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nearby Cars — CAReva</title>
<link rel="stylesheet" href="style.css">
<style>
.geo-banner {
    background: var(--bg-glass);
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    text-align: center;
    margin-bottom: 2rem;
}
.geo-icon { font-size: 3.5rem; margin-bottom: 1rem; display:block; }
.geo-title { font-family: var(--font-display); font-size: 1.3rem; color: var(--text-primary); letter-spacing: 2px; margin-bottom: 0.5rem; }
.geo-desc { color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem; }
.dist-badge {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.25rem 0.8rem;
    background: rgba(0,255,170,0.12);
    border: 1px solid rgba(0,255,170,0.3);
    border-radius: 20px;
    color: var(--success); font-size: 0.8rem; font-weight: 600;
}
</style>
</head>
<body>
<nav class="topbar">
    <a href="index.php" class="topbar-logo">CAReva</a>
    <div class="topbar-actions">
        <span class="topbar-user">👤 <?= htmlspecialchars($user['full_name']) ?></span>
        <a href="logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
</nav>

<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= $initials ?></div>
            <div class="sidebar-name"><?= htmlspecialchars($user['full_name']) ?></div>
            <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php"><span class="nav-icon">🏠</span> Dashboard</a></li>
            <li><a href="buy-cars.php"><span class="nav-icon">🚗</span> Buy Cars</a></li>
            <li><a href="sell-car.php"><span class="nav-icon">💰</span> Sell Car</a></li>
            <li><a href="future-predictor.php"><span class="nav-icon">🔮</span> Future Predictor</a></li>
            <li><a href="nearby-cars.php" class="active"><span class="nav-icon">📍</span> Nearby Cars</a></li>
            <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
            <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">📍 Nearby Cars</div>
            <div class="page-subtitle">Find cars close to your current location</div>
        </div>

        <!-- Geolocation form -->
        <form method="POST" id="nearbyForm">
            <input type="hidden" name="user_lat" id="user_lat" value="<?= htmlspecialchars($user_lat) ?>">
            <input type="hidden" name="user_lng" id="user_lng" value="<?= htmlspecialchars($user_lng) ?>">
        </form>

        <?php if (!$searched): ?>
        <div class="geo-banner">
            <span class="geo-icon">📡</span>
            <div class="geo-title">Enable Location Access</div>
            <p class="geo-desc">Allow location access to find cars near you. Sorted by nearest distance.</p>
            <button id="detectLocation" class="btn btn-primary btn-lg">📍 Detect My Location</button>
            <p style="color:var(--text-muted);font-size:0.8rem;margin-top:1rem;">Your location is only used for this search and is never stored.</p>
        </div>

        <!-- Map visualization placeholder -->
        <div class="map-container">
            <div style="text-align:center;">
                <div style="font-size:3rem;margin-bottom:1rem;">🗺️</div>
                <div>Detect location to see nearby cars on map</div>
            </div>
        </div>

        <?php else: ?>

        <div class="alert alert-info mb-3">
            📡 Showing cars near coordinates: <?= round($user_lat, 4) ?>, <?= round($user_lng, 4) ?>
            — <a href="nearby-cars.php" style="color:var(--neon);">Search again</a>
        </div>

        <?php if (empty($nearby_cars)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No Nearby Cars Found</h3>
                <p>There are no cars with location data in our database yet.</p>
            </div>
        <?php else: ?>

        <div style="margin-bottom:1rem;color:var(--text-secondary);font-size:0.9rem;">
            Found <strong style="color:var(--neon);"><?= count($nearby_cars) ?></strong> cars sorted by distance
        </div>

        <div class="cars-grid">
            <?php foreach ($nearby_cars as $car):
                $img = getCarImagePath($car['id'], $conn);
            ?>
            <div class="card car-card">
                <img src="<?= $img ? htmlspecialchars($img) : getCarPlaceholder($car['brand']) ?>"
                     alt="<?= htmlspecialchars($car['brand']) ?>"
                     class="car-card-img"
                     onerror="this.src='<?= getCarPlaceholder($car['brand']) ?>'">
                <div class="car-card-body">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.5rem;">
                        <div class="car-card-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                        <span class="dist-badge">📍 <?= $car['distance'] ?> km</span>
                    </div>
                    <div class="car-card-price"><?= formatPrice($car['expected_price']) ?></div>
                    <div class="car-badges">
                        <span class="badge badge-year"><?= $car['year'] ?></span>
                        <span class="badge badge-fuel"><?= ucfirst($car['fuel_type']) ?></span>
                    </div>
                    <div class="car-meta">
                        <span>🏃 <?= number_format($car['km_driven']) ?> km</span>
                        <span>📍 <?= htmlspecialchars($car['city']) ?></span>
                    </div>
                    <a href="tel:<?= htmlspecialchars($car['seller_contact']) ?>" class="btn btn-primary btn-sm btn-full">
                        📞 Contact: <?= htmlspecialchars($car['seller_name']) ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </main>
</div>
<script src="js/script.js"></script>
</body>
</html>