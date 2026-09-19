<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $me = current_user();
    if ($id === (int)$me['id']) {
        flash('error', 'You cannot delete your own account.');
    } else {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        flash($stmt->affected_rows ? 'success' : 'error', $stmt->affected_rows ? 'User deleted.' : 'User not found.');
        $stmt->close();
    }
}
header('Location: users_list.php');
exit;