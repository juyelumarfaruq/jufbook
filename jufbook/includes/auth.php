<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php';

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_admin(): void {
    if (!is_admin_logged_in()) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}