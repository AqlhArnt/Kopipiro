<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_owner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
}

function is_cashier() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'kasir';
}

function is_customer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}
?>