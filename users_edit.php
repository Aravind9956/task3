<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $conn->prepare('SELECT id, username, email, role_id FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$target = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$target) { flash('error', 'User not found.'); header('Location: users_list.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 2);
    $newPassword = $_POST['new_password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!in_array($roleId, [1, 2], true)) $errors[] = 'Invalid role selected.';
    if ($newPassword !== '' && strlen($newPassword) < 8) $errors[] = 'New password must be at least 8 characters.';
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows) $errors[] = 'That email is already in use.';
        $stmt->close();
    }
    if (!$errors) {
        if ($newPassword !== '') {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET email = ?, role_id = ?, password_hash = ? WHERE id = ?');
            $stmt->bind_param('sisi', $email, $roleId, $hash, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET email = ?, role_id = ? WHERE id = ?');
            $stmt->bind_param('sii', $email, $roleId, $id);
        }
        $stmt->execute();
        $stmt->close();
        flash('success', 'User updated.');
        header('Location: users_list.php');
        exit;
    }
    $target['email'] = $email;
    $target['role_id'] = $roleId;
}
$pageTitle = 'users_edit.php — Task 3';
$activeTab = 'users';
include __DIR__ . '/includes/header.php';
?>
<h1>Edit user</h1>
<?php foreach ($errors as $error): ?><div class="alert-box"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$target['id'] ?>">
  <label>Username<input class="form-control" value="<?= htmlspecialchars($target['username']) ?>" disabled></label>
  <label>Email<input class="form-control" type="email" name="email" value="<?= htmlspecialchars($target['email']) ?>" required></label>
  <label>Role<select class="form-control" name="role_id"><option value="2" <?= $target['role_id'] == 2 ? 'selected' : '' ?>>user</option><option value="1" <?= $target['role_id'] == 1 ? 'selected' : '' ?>>admin</option></select></label>
  <label>New password<input class="form-control" type="password" name="new_password" minlength="8"></label>
  <button class="run-btn" type="submit">save changes</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>