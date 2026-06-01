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

// Filters
$where = ["c.status = 'active'"];
$brand_f  = sanitize($conn, $_GET['brand'] ?? '');
$fuel_f   = sanitize($conn, $_GET['fuel'] ?? '');
$city_f   = sanitize($conn, $_GET['city'] ?? '');
$pmin_f   = (int)($_GET['pmin'] ?? 0);
$pmax_f   = (int)($_GET['pmax'] ?? 0);

if ($brand_f) $where[] = "c.brand = '$brand_f'";
if ($fuel_f)  $where[] = "c.fuel_type = '$fuel_f'";
if ($city_f)  $where[] = "c.city LIKE '%$city_f%'";
if ($pmin_f)  $where[] = "c.expected_price >= $pmin_f";
if ($pmax_f)  $where[] = "c.expected_price <= $pmax_f";

$where_sql = 'WHERE ' . implode(' AND ', $where);

$cars = mysqli_query($conn, "SELECT c.*, u.full_name as owner_name FROM cars c JOIN users u ON c.user_id = u.id $where_sql ORDER BY c.created_at DESC");

// Get distinct brands
$brands_res = mysqli_query($conn, "SELECT DISTINCT brand FROM cars WHERE status='active' ORDER BY brand");
$brands = [];
while ($r = mysqli_fetch_assoc($brands_res)) $brands[] = $r['brand'];

// Wishlist
$wish_res = mysqli_query($conn, "SELECT car_id FROM wishlist WHERE user_id = $uid");
$wishlist = [];
while ($w = mysqli_fetch_assoc($wish_res)) $wishlist[] = $w['car_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Buy Cars — CAReva</title>
<link rel="stylesheet" href="style.css">
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
            <li><a href="buy-cars.php" class="active"><span class="nav-icon">🚗</span> Buy Cars</a></li>
            <li><a href="sell-car.php"><span class="nav-icon">💰</span> Sell Car</a></li>
            <li><a href="future-predictor.php"><span class="nav-icon">🔮</span> Future Predictor</a></li>
            <li><a href="nearby-cars.php"><span class="nav-icon">📍</span> Nearby Cars</a></li>
            <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
            <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">🚗 Buy Cars</div>
            <div class="page-subtitle">Browse <?= mysqli_num_rows($cars) ?> available listings</div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters-bar auto-submit">
            <div class="form-group">
                <label class="form-label">Brand</label>
                <select name="brand" class="form-control">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b ?>" <?= $brand_f === $b ? 'selected' : '' ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fuel Type</label>
                <select name="fuel" class="form-control">
                    <option value="">All Fuels</option>
                    <option value="petrol" <?= $fuel_f === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="diesel" <?= $fuel_f === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="electric" <?= $fuel_f === 'electric' ? 'selected' : '' ?>>Electric</option>
                    <option value="hybrid" <?= $fuel_f === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    <option value="cng" <?= $fuel_f === 'cng' ? 'selected' : '' ?>>CNG</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Min Price (₹)</label>
                <input type="number" name="pmin" class="form-control" placeholder="e.g. 200000" value="<?= $pmin_f ?: '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Max Price (₹)</label>
                <input type="number" name="pmax" class="form-control" placeholder="e.g. 1500000" value="<?= $pmax_f ?: '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" placeholder="Mumbai..." value="<?= htmlspecialchars($city_f) ?>">
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="buy-cars.php" class="btn btn-ghost">Reset</a>
            </div>
        </form>

        <!-- Cars Grid -->
        <?php if (mysqli_num_rows($cars) === 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🚘</div>
                <h3>No Cars Found</h3>
                <p>Try adjusting your filters or check back later.</p>
            </div>
        <?php else: ?>
        <div class="cars-grid">
            <?php while ($car = mysqli_fetch_assoc($cars)):
                $img = getCarImagePath($car['id'], $conn);
                $inWish = in_array($car['id'], $wishlist);
            ?>
            <div class="card car-card">
                <div style="position:relative;">
                    <img src="<?= $img ? htmlspecialchars($img) : getCarPlaceholder($car['brand']) ?>"
                         alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>"
                         class="car-card-img"
                         onerror="this.src='<?= getCarPlaceholder($car['brand']) ?>'">
                    <button class="wishlist-btn <?= $inWish ? 'active' : '' ?>"
                            data-car="<?= $car['id'] ?>"
                            style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,0.6);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                        <?= $inWish ? '❤️' : '🤍' ?>
                    </button>
                </div>
                <div class="car-card-body">
                    <div class="car-card-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                    <div class="car-card-price"><?= formatPrice($car['expected_price']) ?></div>
                    <div class="car-badges">
                        <span class="badge badge-year"><?= $car['year'] ?></span>
                        <span class="badge badge-fuel"><?= ucfirst($car['fuel_type']) ?></span>
                        <span class="badge badge-trans"><?= ucfirst($car['transmission']) ?></span>
                    </div>
                    <div class="car-meta">
                        <span>🏃 <?= number_format($car['km_driven']) ?> km</span>
                        <span>📍 <?= htmlspecialchars($car['city']) ?></span>
                    </div>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <a href="tel:<?= htmlspecialchars($car['seller_contact']) ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;">
                            📞 Contact Seller
                        </a>
                        <span style="color:var(--text-muted);font-size:0.8rem;">
                            <?= htmlspecialchars($car['seller_name']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<script src="js/script.js"></script>
</body>
</html>