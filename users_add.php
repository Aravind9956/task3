<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
$errors = [];
$old = ['username' => '', 'email' => '', 'role_id' => 2];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 2);
    $old = compact('username', 'email', 'roleId');
    $old['role_id'] = $roleId;
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!in_array($roleId, [1, 2], true)) $errors[] = 'Invalid role selected.';
    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows) $errors[] = 'Username or email already exists.';
        $stmt->close();
    }
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $username, $email, $hash, $roleId);
        $stmt->execute();
        $stmt->close();
        flash('success', 'User created.');
        header('Location: users_list.php');
        exit;
    }
}
$pageTitle = 'users_add.php — Task 3';
$activeTab = 'users';
include __DIR__ . '/includes/header.php';
?>
<h1>Add user</h1>
<?php foreach ($errors as $error): ?><div class="alert-box"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
<form method="post">
  <?= csrf_field() ?>
  <label>Username<input class="form-control" name="username" value="<?= htmlspecialchars($old['username']) ?>" required></label>
  <label>Email<input class="form-control" type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required></label>
  <label>Password<input class="form-control" type="password" name="password" required minlength="8"></label>
  <label>Role<select class="form-control" name="role_id"><option value="2">user</option><option value="1">admin</option></select></label>
  <button class="run-btn" type="submit">create user</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>