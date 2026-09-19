<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $old = ['username' => $username, 'email' => $email];

    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'That username or email is already registered.';
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash, role_id) VALUES (?, ?, ?, 2)');
        $stmt->bind_param('sss', $username, $email, $hash);
        $stmt->execute();
        $stmt->close();

        flash('success', 'Account created. Sign in to continue.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'register.php — Task 3';
$activeTab = 'register';
include __DIR__ . '/includes/header.php';
?>
<p class="editor-heading"><span class="kw">function</span> <span class="fn">register</span>() {</p>
<p class="editor-subtitle">Backed by MySQL now — passwords are hashed with <code>password_hash()</code>.</p>

<?php if ($errors): ?>
  <div class="alert-box">
    <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="field">
    <label class="field-label" for="username">username</label>
    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($old['username']) ?>" required minlength="3">
  </div>
  <div class="field">
    <label class="field-label" for="email">email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required>
  </div>
  <div class="field">
    <label class="field-label" for="password">password</label>
    <div class="input-wrap">
      <input type="password" class="form-control" id="password" name="password" required minlength="8">
      <button type="button" class="toggle-visibility" data-target="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <div class="field">
    <label class="field-label" for="confirm_password">confirm password</label>
    <div class="input-wrap">
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      <button type="button" class="toggle-visibility" data-target="confirm_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <button type="submit" class="run-btn"><i class="bi bi-play-fill"></i> run register()</button>
</form>
<p class="switch-line">already have an account? <a href="login.php">login.php →</a></p>
<?php include __DIR__ . '/includes/footer.php'; ?>
