<?php
function formatPrice($price) {
    if ($price >= 10000000) {
        return '₹' . number_format($price / 10000000, 2) . ' Cr';
    } elseif ($price >= 100000) {
        return '₹' . number_format($price / 100000, 2) . ' L';
    } else {
        return '₹' . number_format($price);
    }
}

function getDepreciationRate($fuel_type, $year) {
    $age = date('Y') - $year;
    $base_rates = [
        'petrol' => 0.15,
        'diesel' => 0.13,
        'electric' => 0.10,
        'hybrid' => 0.11,
        'cng' => 0.14
    ];
    $rate = $base_rates[$fuel_type] ?? 0.15;
    if ($age > 5) $rate += 0.02;
    if ($age > 10) $rate += 0.03;
    return $rate;
}

function calculateFutureValue($purchase_price, $fuel_type, $purchase_year, $years) {
    $current_age = date('Y') - $purchase_year;
    $current_value = $purchase_price;
    for ($i = 0; $i < $current_age; $i++) {
        $rate = getDepreciationRate($fuel_type, $purchase_year + $i);
        $current_value *= (1 - $rate);
    }
    $future_value = $current_value;
    for ($i = 0; $i < $years; $i++) {
        $rate = getDepreciationRate($fuel_type, date('Y') + $i);
        $future_value *= (1 - $rate);
    }
    return max($future_value, $purchase_price * 0.05);
}

function calculateFutureMaintenance($base_cost, $years) {
    $inflation_rate = 0.07;
    return $base_cost * $years * (1 + $inflation_rate * ($years / 2));
}

function getCarBrands() {
    return [
        'Maruti Suzuki', 'Hyundai', 'Tata', 'Mahindra', 'Honda',
        'Toyota', 'Kia', 'Renault', 'Volkswagen', 'Skoda',
        'Ford', 'Mercedes-Benz', 'BMW', 'Audi', 'Jeep',
        'MG', 'Nissan', 'Isuzu', 'Force Motors', 'Other'
    ];
}

function getIndianStates() {
    return [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
        'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand',
        'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur',
        'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
        'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
        'Delhi', 'Chandigarh', 'Puducherry', 'Jammu & Kashmir', 'Ladakh'
    ];
}

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

function getCarImagePath($car_id, $conn) {
    $result = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id = $car_id AND is_primary = 1 LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['image_path'];
    }
    $result2 = mysqli_query($conn, "SELECT image_path FROM car_images WHERE car_id = $car_id LIMIT 1");
    if ($row2 = mysqli_fetch_assoc($result2)) {
        return $row2['image_path'];
    }
    return null;
}

function getCarPlaceholder($brand = '') {
    return 'data:image/svg+xml;base64,' . base64_encode('
    <svg xmlns="http://www.w3.org/2000/svg" width="400" height="250" viewBox="0 0 400 250">
      <rect width="400" height="250" fill="#0d1117"/>
      <rect x="50" y="80" width="300" height="110" rx="20" fill="#1a1f2e"/>
      <rect x="100" y="55" width="200" height="80" rx="15" fill="#141820"/>
      <circle cx="110" cy="190" r="30" fill="#1e2436" stroke="#00d4ff" stroke-width="2"/>
      <circle cx="110" cy="190" r="15" fill="#0a0e18"/>
      <circle cx="290" cy="190" r="30" fill="#1e2436" stroke="#00d4ff" stroke-width="2"/>
      <circle cx="290" cy="190" r="15" fill="#0a0e18"/>
      <rect x="80" y="115" width="80" height="50" rx="8" fill="#0d1117" opacity="0.6"/>
      <rect x="240" y="115" width="80" height="50" rx="8" fill="#0d1117" opacity="0.6"/>
      <text x="200" y="225" font-family="Arial" font-size="14" fill="#00d4ff" text-anchor="middle">' . htmlspecialchars($brand ?: 'CAReva') . '</text>
    </svg>');
}
?>