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

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand    = sanitize($conn, $_POST['brand'] ?? '');
    $model    = sanitize($conn, $_POST['model'] ?? '');
    $variant  = sanitize($conn, $_POST['variant'] ?? '');
    $year     = (int)($_POST['year'] ?? 0);
    $fuel     = sanitize($conn, $_POST['fuel_type'] ?? '');
    $trans    = sanitize($conn, $_POST['transmission'] ?? '');
    $km       = (int)($_POST['km_driven'] ?? 0);
    $price    = (float)($_POST['expected_price'] ?? 0);
    $s_name   = sanitize($conn, $_POST['seller_name'] ?? '');
    $s_phone  = sanitize($conn, $_POST['seller_contact'] ?? '');
    $s_addr   = sanitize($conn, $_POST['seller_address'] ?? '');
    $city     = sanitize($conn, $_POST['city'] ?? '');
    $state    = sanitize($conn, $_POST['state'] ?? '');

    if (empty($brand) || empty($model) || !$year || empty($fuel) || empty($trans) || !$km || !$price || empty($s_name) || empty($s_phone)) {
        $error = 'Please fill all required fields.';
    } else {
        // Handle number plate image
        $plate_img = null;
        if (!empty($_FILES['number_plate_image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['number_plate_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $plate_img = 'uploads/cars/plate_' . time() . '_' . rand(100,999) . '.' . $ext;
                move_uploaded_file($_FILES['number_plate_image']['tmp_name'], $plate_img);
            }
        }

        $sql = "INSERT INTO cars (user_id, brand, model, variant, year, fuel_type, transmission, km_driven, expected_price, seller_name, seller_contact, seller_address, city, state, number_plate_image)
                VALUES ($uid, '$brand', '$model', '$variant', $year, '$fuel', '$trans', $km, $price, '$s_name', '$s_phone', '$s_addr', '$city', '$state', " . ($plate_img ? "'$plate_img'" : "NULL") . ")";

        if (mysqli_query($conn, $sql)) {
            $car_id = mysqli_insert_id($conn);

            // Handle car images
            if (!empty($_FILES['car_images']['name'][0])) {
                $is_primary = 1;
                foreach ($_FILES['car_images']['tmp_name'] as $idx => $tmp) {
                    if (empty($tmp) || $_FILES['car_images']['error'][$idx] !== 0) continue;
                    $ext = strtolower(pathinfo($_FILES['car_images']['name'][$idx], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
                    $img_path = 'uploads/cars/car_' . $car_id . '_' . time() . '_' . $idx . '.' . $ext;
                    if (move_uploaded_file($tmp, $img_path)) {
                        mysqli_query($conn, "INSERT INTO car_images (car_id, image_path, is_primary) VALUES ($car_id, '$img_path', $is_primary)");
                        $is_primary = 0;
                    }
                }
            }

            $success = 'Car listed successfully! Listing is pending review.';
        } else {
            $error = 'Failed to list car: ' . mysqli_error($conn);
        }
    }
}

$brands = getCarBrands();
$states = getIndianStates();
$cur_year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell Car — CAReva</title>
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
            <li><a href="buy-cars.php"><span class="nav-icon">🚗</span> Buy Cars</a></li>
            <li><a href="sell-car.php" class="active"><span class="nav-icon">💰</span> Sell Car</a></li>
            <li><a href="future-predictor.php"><span class="nav-icon">🔮</span> Future Predictor</a></li>
            <li><a href="nearby-cars.php"><span class="nav-icon">📍</span> Nearby Cars</a></li>
            <li><a href="profile.php"><span class="nav-icon">👤</span> My Profile</a></li>
            <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">💰 Sell Your Car</div>
            <div class="page-subtitle">Fill in the details below to list your car</div>
        </div>

        <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" novalidate>

                    <div class="section-title">🚗 Car Information</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Brand *</label>
                            <select name="brand" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= $b ?>" <?= (($_POST['brand'] ?? '') === $b) ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Model *</label>
                            <input type="text" name="model" class="form-control" placeholder="Swift, Creta..." value="<?= htmlspecialchars($_POST['model'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Variant</label>
                            <input type="text" name="variant" class="form-control" placeholder="ZXI, SX..." value="<?= htmlspecialchars($_POST['variant'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Year *</label>
                            <select name="year" class="form-control" required>
                                <option value="">Select Year</option>
                                <?php for ($y = $cur_year; $y >= 2000; $y--): ?>
                                    <option value="<?= $y ?>" <?= (($_POST['year'] ?? '') == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fuel Type *</label>
                            <select name="fuel_type" class="form-control" required>
                                <option value="">Select Fuel</option>
                                <option value="petrol">Petrol</option>
                                <option value="diesel">Diesel</option>
                                <option value="electric">Electric</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="cng">CNG</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Transmission *</label>
                            <select name="transmission" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="manual">Manual</option>
                                <option value="automatic">Automatic</option>
                                <option value="amt">AMT</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kilometers Driven *</label>
                            <input type="number" name="km_driven" class="form-control" placeholder="35000" min="0" value="<?= htmlspecialchars($_POST['km_driven'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Expected Price (₹) *</label>
                            <input type="number" name="expected_price" class="form-control" placeholder="750000" min="0" value="<?= htmlspecialchars($_POST['expected_price'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="section-title">📷 Car Images</div>
                    <div class="form-group">
                        <label class="form-label">Upload Car Photos (Multiple)</label>
                        <label class="upload-zone" for="car_images">
                            <input type="file" name="car_images[]" id="car_images" multiple accept="image/*">
                            <div class="upload-zone-icon">📸</div>
                            <p>Click to upload car photos (JPG, PNG, WEBP)</p>
                            <p style="color:var(--text-muted);font-size:0.8rem;">First image will be used as thumbnail</p>
                        </label>
                        <div id="imgPreviewGrid" class="img-preview-grid"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Number Plate Image</label>
                        <label class="upload-zone" for="number_plate_image">
                            <input type="file" name="number_plate_image" id="number_plate_image" accept="image/*">
                            <div class="upload-zone-icon">🪪</div>
                            <p>Upload number plate photo for verification</p>
                        </label>
                        <img id="platePrev" style="display:none;max-width:200px;margin-top:0.5rem;border-radius:8px;border:1px solid var(--border-glass2);" alt="Plate Preview">
                    </div>

                    <div class="section-title">👤 Seller Information</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label class="form-label">Seller Name *</label>
                            <input type="text" name="seller_name" class="form-control" placeholder="Your name" value="<?= htmlspecialchars($_POST['seller_name'] ?? $user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" name="seller_contact" class="form-control" placeholder="9876543210" value="<?= htmlspecialchars($_POST['seller_contact'] ?? $user['mobile']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control" placeholder="Mumbai" value="<?= htmlspecialchars($_POST['city'] ?? $user['city']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <select name="state" class="form-control" required>
                                <option value="">Select State</option>
                                <?php foreach ($states as $s): ?>
                                    <option value="<?= $s ?>" <?= (($_POST['state'] ?? $user['state']) === $s) ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Address *</label>
                            <input type="text" name="seller_address" class="form-control" placeholder="Street, Area, City" value="<?= htmlspecialchars($_POST['seller_address'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="neon-line"></div>
                    <button type="submit" class="btn btn-primary btn-full btn-lg">🚀 List My Car</button>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="js/script.js"></script>
</body>
</html>