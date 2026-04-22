<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (!is_post_request()) {
    redirect('admin_users.php');
}

require_admin();
verify_csrf();

$userId = (int) ($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    set_flash('danger', 'Invalid user ID.');
    redirect('admin_users.php');
}

if ($userId === current_user_id()) {
    set_flash('danger', 'You cannot delete your own account from the admin area.');
    redirect('admin_users.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $userId]);
$account = $stmt->fetch();

if (!$account) {
    set_flash('danger', 'User not found.');
    redirect('admin_users.php');
}

$pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $userId]);

set_flash('success', 'User deleted successfully.');
redirect('admin_users.php');
