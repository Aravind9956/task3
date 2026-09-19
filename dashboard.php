<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$pageTitle = 'dashboard.php — Task 3';
$activeTab = 'dashboard';
include __DIR__ . '/includes/header.php';
?>
<p class="editor-heading"><span class="kw">function</span> <span class="fn">dashboard</span>() {</p>
<p class="editor-subtitle">Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong>.</p>
<div class="dashboard-grid"><a class="dash-card" href="profile.php">Edit profile</a><?php if ($user['role'] === 'admin'): ?><a class="dash-card" href="users_list.php">Manage users</a><?php endif; ?></div>
<?php include __DIR__ . '/includes/footer.php'; ?>