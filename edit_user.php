<?php
$pageTitle = 'Edit User';
include __DIR__ . '/components/header.php';
require_admin();

$userId = (int) ($_GET['id'] ?? $_POST['user_id'] ?? 0);
if ($userId < 1) {
    set_flash('warning', 'Invalid user selected.');
    redirect('admin_users.php');
}

$stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$account = $stmt->fetch();

if (!$account) {
    set_flash('warning', 'User not found.');
    redirect('admin_users.php');
}

if (is_post_request()) {
    verify_csrf();

    $username = sanitize_string($_POST['username'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $role = $_POST['role'] ?? 'user';

    if ($username === '' || $email === '') {
        $error = 'Username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($role, ['admin', 'user'], true)) {
        $error = 'Invalid role selected.';
    } else {
        $dup = $pdo->prepare('SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id LIMIT 1');
        $dup->execute([
            'email' => $email,
            'username' => $username,
            'id' => $userId,
        ]);

        if ($dup->fetch()) {
            $error = 'This username or email is already used by another account.';
        } elseif ($userId === current_user_id() && $role !== 'admin') {
            $error = 'You cannot remove admin access from your current account here.';
        } else {
            $update = $pdo->prepare('UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id');
            $update->execute([
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'id' => $userId,
            ]);

            if ($userId === current_user_id()) {
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['role'] = $role;
            }

            set_flash('success', 'User updated successfully.');
            redirect('admin_users.php');
        }
    }
}
?>

<div class="container" style="max-width: 760px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-3">Edit User</h1>
            <p class="text-muted">Update account information and role permissions.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="user_id" value="<?= (int) $userId ?>">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= e($_POST['username'] ?? $account['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? $account['email']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Role</label>
                    <?php $selectedRole = $_POST['role'] ?? $account['role']; ?>
                    <select name="role" class="form-select" required>
                        <option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary">Save User</button>
                    <a href="admin_users.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
