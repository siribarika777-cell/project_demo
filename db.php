<?php
// Database Configuration
define('DB_HOST', ' sql12.freesqldatabase.com');
define('DB_USER', 'sql12828828');
define('DB_PASS', ' vHXCibv1Dd');
define('DB_NAME', 'sql12828828');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:sans-serif;background:#0a0a0a;color:#ff4444;padding:40px;text-align:center;min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;">
        <h2>⚠️ Database Connection Failed</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p style="color:#888;font-size:14px;">Please ensure XAMPP MySQL is running and import <code>database/careva_db.sql</code></p>
    </div>');
}

mysqli_set_charset($conn, 'utf8mb4');
?>