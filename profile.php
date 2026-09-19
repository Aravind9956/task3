<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $bio   = trim($_POST['bio'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $user['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors[] = 'That email is already in use by another account.';
        $stmt->close();
    }
    if (strlen($bio) > 255) $errors[] = 'Bio must be 255 characters or fewer.';

    $newPicturePath = $user['profile_picture'];

    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $maxBytes = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload failed. Please try again.';
        } elseif ($file['size'] > $maxBytes) {
            $errors[] = 'Image must be smaller than 2MB.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset($allowed[$mime])) {
                $errors[] = 'Only JPG, PNG, or GIF images are allowed.';
            } else {
                $ext = $allowed[$mime];
                $filename = 'user_' . $user['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destination = __DIR__ . '/uploads/profile_pictures/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $newPicturePath = 'uploads/profile_pictures/' . $filename;
                } else {
                    $errors[] = 'Could not save the uploaded image.';
                }
            }
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare('UPDATE users SET email = ?, bio = ?, profile_picture = ? WHERE id = ?');
        $stmt->bind_param('sssi', $email, $bio, $newPicturePath, $user['id']);
        $stmt->execute();
        $stmt->close();

        flash('success', 'Profile updated.');
        header('Location: profile.php');
        exit;
    }

    // keep the form populated with attempted values on error
    $user['email'] = $email;
    $user['bio'] = $bio;
}

$pageTitle = 'profile.php — Task 3';
$activeTab = 'profile';
include __DIR__ . '/includes/header.php';
?>
<p class="editor-heading"><span class="kw">function</span> <span class="fn">editProfile</span>() {</p>
<p class="editor-subtitle">Update your details and profile picture.</p>

<?php if ($errors): ?>
  <div class="alert-box">
    <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="profile-picture-preview">
  <?php if (!empty($user['profile_picture'])): ?>
    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile picture">
  <?php else: ?>
    <div class="avatar-placeholder"><?= strtoupper(htmlspecialchars(substr($user['username'], 0, 1))) ?></div>
  <?php endif; ?>
</div>

<form method="post" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="field">
    <label class="field-label">username</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
  </div>
  <div class="field">
    <label class="field-label" for="email">email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
  </div>
  <div class="field">
    <label class="field-label" for="bio">bio</label>
    <textarea class="form-control" id="bio" name="bio" rows="3" maxlength="255"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
  </div>
  <div class="field">
    <label class="field-label" for="profile_picture">profile picture</label>
    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.gif">
    <div class="field-hint">JPG, PNG, or GIF · max 2MB</div>
  </div>
  <button type="submit" class="run-btn"><i class="bi bi-play-fill"></i> save changes</button>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
