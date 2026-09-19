<?php $navUser = function_exists('current_user') ? current_user() : null; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'ApexPlanet — Task 3') ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="editor-window<?= !empty($wide) ? ' editor-window-wide' : '' ?>">
  <div class="title-bar"><span class="title-bar-label">apexplanet-internship — task-3 backend</span></div>
  <nav class="tab-bar">
    <?php if ($navUser): ?>
      <a href="dashboard.php" class="tab">dashboard.php</a>
      <a href="profile.php" class="tab">profile.php</a>
      <?php if ($navUser['role'] === 'admin'): ?><a href="users_list.php" class="tab">users.php</a><?php endif; ?>
      <a href="logout.php" class="tab">logout.php</a>
    <?php else: ?>
      <a href="login.php" class="tab">login.php</a>
      <a href="register.php" class="tab">register.php</a>
    <?php endif; ?>
  </nav>
  <div class="editor-body">
    <?php foreach (get_flashes() as $f): ?>
      <div class="flash flash-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['message']) ?></div>
    <?php endforeach; ?>