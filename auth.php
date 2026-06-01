<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?msg=Please login to continue');
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: admin-panel.php?msg=Admin access required');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getCurrentUser($conn) {
    if (!isset($_SESSION['user_id'])) return null;
    $id = (int)$_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
    return mysqli_fetch_assoc($result);
}

function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}
?>