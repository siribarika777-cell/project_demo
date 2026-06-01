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

$prediction = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand        = sanitize($conn, $_POST['brand'] ?? '');
    $model        = sanitize($conn, $_POST['model'] ?? '');
    $purchase_price = (float)($_POST['purchase_price'] ?? 0);
    $maintenance  = (float)($_POST['maintenance_cost'] ?? 0);
    $p_year       = (int)($_POST['purchase_year'] ?? 0);
    $fuel         = sanitize($conn, $_POST['fuel_type'] ?? '');

    if (empty($brand) || empty($model) || !$purchase_price || !$p_year || empty($fuel)) {
        $error = 'Please fill all required fields.';
    } elseif ($p_year > date('Y') || $p_year < 1980) {
        $error = 'Please enter a valid purchase year.';
    } else {
        // Calculate current value first
        $current_val = $purchase_price;
        for ($i = 0; $i < (date('Y') - $p_year); $i++) {
            $rate = getDepreciationRate($fuel, $p_year + $i);
            $current_val *= (1 - $rate);
        }
        $current_val = max($current_val, $purchase_price * 0.05);

        $v5  = calculateFutureValue($purchase_price, $fuel, $p_year, 5);
        $v10 = calculateFutureValue($purchase_price, $fuel, $p_year, 10);
        $v20 = calculateFutureValue($purchase_price, $fuel, $p_year, 20);

        $m5  = calculateFutureMaintenance($maintenance, 5);
        $m10 = calculateFutureMaintenance($maintenance, 10);
        $m20 = calculateFutureMaintenance($maintenance, 20);

        $dep5  = round((($current_val - $v5) / $current_val) * 100, 1);
        $dep10 = round((($current_val - $v10) / $current_val) * 100, 1);
        $dep20 = round((($current_val - $v20) / $current_val) * 100, 1);

        $prediction = compact('brand','model','purchase_price','maintenance','p_year','fuel',
            'current_val','v5','v10','v20','m5','m10','m20','dep5','dep10','dep20');

        // Save to DB
        $sql = "INSERT INTO predictions (user_id, brand, model, purchase_price, maintenance_cost, purchase_year,
                value_5yr, value_10yr, value_20yr, maintenance_5yr, maintenance_10yr, maintenance_20yr)
                VALUES ($uid, '$brand', '$model', $purchase_price, $maintenance, $p_year,
                $v5, $v10, $v20, $m5, $m10, $m20)";
        mysqli_query($conn, $sql);
    }
}

$brands = getCarBrands();
$cur_year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Future Predictor — CAReva</title>
<link rel="stylesheet" href="style.css">
<style>
.pred-banner {
    background: linear-gradient(135deg, rgba(0,212,255,0.06), rgba(0,99,150,0.04));
    border: 1px solid var(--border-glass);
    border-radius: var(--radius-lg);
    padding: 2rem; margin-bottom: 2rem;
    position: relative; overflow: hidden;
}
.pred-banner::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; background: linear-gradient(90deg, transparent, var(--neon), transparent); }
.dep-bar-label { display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted); margin-bottom:0.3rem; }
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
            <li><a href="future-predictor.php" class="active"><span class="nav-icon">🔮</span> Future Predictor</a></li>
            <li><a href="nearby-cars.php"><span class="nav-icon">📍</span> Nearby Cars</a></li>
            <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
            <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">🔮 Future Car Predictor</div>
            <div class="page-subtitle">AI-powered 5, 10 and 20-year value forecasts</div>
        </div>

        <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>

        <div class="card">
            <div class="card-header">Enter Car Details</div>
            <div class="card-body">
                <form method="POST" novalidate>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <select name="brand" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b ?>" <?= (($prediction['brand'] ?? '') === $b) ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model *</label>
                            <input type="text" name="model" class="form-control" placeholder="Swift, Creta..." value="<?= htmlspecialchars($prediction['model'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fuel Type *</label>
                            <select name="fuel_type" class="form-control" required>
                                <option value="">Select Fuel</option>
                                <option value="petrol" <?= (($prediction['fuel'] ?? '') === 'petrol') ? 'selected' : '' ?>>Petrol</option>
                                <option value="diesel" <?= (($prediction['fuel'] ?? '') === 'diesel') ? 'selected' : '' ?>>Diesel</option>
                                <option value="electric" <?= (($prediction['fuel'] ?? '') === 'electric') ? 'selected' : '' ?>>Electric</option>
                                <option value="hybrid" <?= (($prediction['fuel'] ?? '') === 'hybrid') ? 'selected' : '' ?>>Hybrid</option>
                                <option value="cng" <?= (($prediction['fuel'] ?? '') === 'cng') ? 'selected' : '' ?>>CNG</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Purchase Year *</label>
                            <select name="purchase_year" class="form-control" required>
                                <option value="">Select Year</option>
                                <?php for ($y = $cur_year; $y >= 2000; $y--): ?>
                                    <option value="<?= $y ?>" <?= (($prediction['p_year'] ?? 0) == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Purchase Price (₹) *</label>
                            <input type="number" name="purchase_price" class="form-control" placeholder="800000" min="10000" value="<?= htmlspecialchars($prediction['purchase_price'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Annual Maintenance Cost (₹)</label>
                            <input type="number" name="maintenance_cost" class="form-control" placeholder="25000" min="0" value="<?= htmlspecialchars($prediction['maintenance'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">🔮 Generate Prediction</button>
                </form>
            </div>
        </div>

        <?php if ($prediction): ?>
        <div class="pred-banner mt-3">
            <div style="font-family:var(--font-display);font-size:1.4rem;color:var(--neon);text-shadow:var(--neon-glow-sm);">
                <?= htmlspecialchars($prediction['brand'] . ' ' . $prediction['model']) ?>
            </div>
            <div style="color:var(--text-muted);font-size:0.9rem;margin-top:0.3rem;">
                <?= $prediction['p_year'] ?> · <?= ucfirst($prediction['fuel']) ?> ·
                Purchase Price: <?= formatPrice($prediction['purchase_price']) ?> ·
                Current Value: <span style="color:var(--success);"><?= formatPrice($prediction['current_val']) ?></span>
            </div>
        </div>

        <!-- Prediction Cards -->
        <div class="section-title">📈 Future Value Predictions</div>
        <div class="prediction-grid">
            <div class="prediction-item">
                <div class="prediction-yr">5 YEARS</div>
                <div class="prediction-val"><?= formatPrice($prediction['v5']) ?></div>
                <div class="prediction-dep">▼ <?= $prediction['dep5'] ?>% from current</div>
                <div style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;">Year <?= $cur_year + 5 ?></div>
            </div>
            <div class="prediction-item">
                <div class="prediction-yr">10 YEARS</div>
                <div class="prediction-val"><?= formatPrice($prediction['v10']) ?></div>
                <div class="prediction-dep">▼ <?= $prediction['dep10'] ?>% from current</div>
                <div style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;">Year <?= $cur_year + 10 ?></div>
            </div>
            <div class="prediction-item">
                <div class="prediction-yr">20 YEARS</div>
                <div class="prediction-val"><?= formatPrice($prediction['v20']) ?></div>
                <div class="prediction-dep">▼ <?= $prediction['dep20'] ?>% from current</div>
                <div style="color:var(--text-muted);font-size:0.8rem;margin-top:0.5rem;">Year <?= $cur_year + 20 ?></div>
            </div>
        </div>

        <!-- Maintenance Forecast -->
        <div class="section-title mt-3">🔧 Maintenance Cost Forecast</div>
        <div class="card">
            <div class="card-body">
                <div style="margin-bottom:1.2rem;">
                    <div class="dep-bar-label"><span>5-Year Cumulative Maintenance</span><span style="color:var(--warning);"><?= formatPrice($prediction['m5']) ?></span></div>
                    <div class="progress"><div class="progress-bar" style="width:33%;background:linear-gradient(90deg,#ffaa00,#ffcc44);"></div></div>
                </div>
                <div style="margin-bottom:1.2rem;">
                    <div class="dep-bar-label"><span>10-Year Cumulative Maintenance</span><span style="color:var(--warning);"><?= formatPrice($prediction['m10']) ?></span></div>
                    <div class="progress"><div class="progress-bar" style="width:66%;background:linear-gradient(90deg,#ffaa00,#ff6600);"></div></div>
                </div>
                <div>
                    <div class="dep-bar-label"><span>20-Year Cumulative Maintenance</span><span style="color:var(--danger);"><?= formatPrice($prediction['m20']) ?></span></div>
                    <div class="progress"><div class="progress-bar" style="width:100%;background:linear-gradient(90deg,#ff6600,#ff4455);"></div></div>
                </div>
            </div>
        </div>

        <!-- Depreciation Chart -->
        <div class="chart-container">
            <div class="chart-title">📊 Depreciation Chart</div>
            <canvas id="depreciationChart" width="700" height="300" style="width:100%;max-width:700px;"></canvas>
        </div>

        <script>
        window.chartData = {
            current: <?= round($prediction['current_val']) ?>,
            v5: <?= round($prediction['v5']) ?>,
            v10: <?= round($prediction['v10']) ?>,
            v20: <?= round($prediction['v20']) ?>
        };
        </script>
        <?php endif; ?>

    </main>
</div>
<script src="js/script.js"></script>
</body>
</html>