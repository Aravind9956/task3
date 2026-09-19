<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/csrf.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    global $conn;
    static $user = null;
    static $loaded = false;

    if (!is_logged_in()) return null;
    if ($loaded) return $user;

    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.email, u.profile_picture, u.bio, r.name AS role
         FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?'
    );
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $loaded = true;
    return $user;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin() {
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied.');
    }
}

function flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes() {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}