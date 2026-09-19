<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.email, r.name AS role, u.created_at
         FROM users u JOIN roles r ON u.role_id = r.id
         WHERE u.username LIKE ? OR u.email LIKE ?
         ORDER BY u.id DESC'
    );
    $stmt->bind_param('ss', $like, $like);
} else {
    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.email, r.name AS role, u.created_at
         FROM users u JOIN roles r ON u.role_id = r.id
         ORDER BY u.id DESC'
    );
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'users.php — Task 3';
$activeTab = 'users';
$wide = true;
include __DIR__ . '/includes/header.php';
?>
<p class="editor-heading"><span class="kw">function</span> <span class="fn">manageUsers</span>() {</p>
<p class="editor-subtitle">Fetch, search, edit, and delete user records.</p>

<div class="users-toolbar">
  <form method="get" class="search-form">
    <input type="text" class="form-control" name="q" placeholder="search username or email..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="run-btn run-btn-inline" aria-label="Search"><i class="bi bi-search"></i></button>
  </form>
  <a href="users_add.php" class="run-btn run-btn-inline"><i class="bi bi-plus-lg"></i> add user</a>
</div>

<div class="table-wrap">
<table class="data-table">
  <thead>
    <tr>
      <th>id</th><th>username</th><th>email</th><th>role</th><th>joined</th><th>actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="role-badge role-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
      <td><?= htmlspecialchars(date('M j, Y', strtotime($u['created_at']))) ?></td>
      <td class="actions-cell">
        <a href="users_edit.php?id=<?= (int)$u['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
        <form method="post" action="users_delete.php" class="inline-form" onsubmit="return confirm('Delete this user? This cannot be undone.');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button type="submit" title="Delete"><i class="bi bi-trash"></i></button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?>
      <tr><td colspan="6" class="empty-row">no users found</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
