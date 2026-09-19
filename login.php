<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$old = ['identifier' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';
    $old['identifier'] = $identifier;

    if ($identifier === '' || $password === '') {
        $errors[] = 'Username/email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT id, password_hash FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            flash('success', 'Signed in successfully.');
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username/email or password.';
        }
    }
}

$pageTitle = 'login.php — Task 3';
$activeTab = 'login';
include __DIR__ . '/includes/header.php';
?>
<p class="editor-heading"><span class="kw">function</span> <span class="fn">login</span>() {</p>
<p class="editor-subtitle">Session-based authentication against MySQL.</p>

<?php if ($errors): ?>
  <div class="alert-box">
    <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="field">
    <label class="field-label" for="identifier">username or email</label>
    <input type="text" class="form-control" id="identifier" name="identifier" value="<?= htmlspecialchars($old['identifier']) ?>" required>
  </div>
  <div class="field">
    <label class="field-label" for="password">password</label>
    <div class="input-wrap">
      <input type="password" class="form-control" id="password" name="password" required>
      <button type="button" class="toggle-visibility" data-target="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <button type="submit" class="run-btn"><i class="bi bi-play-fill"></i> run login()</button>
</form>
<p class="switch-line">no account? <a href="register.php">register.php →</a></p>
<?php include __DIR__ . '/includes/footer.php'; ?>
