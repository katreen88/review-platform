<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (!is_post_request()) {
    redirect('index.php');
}

require_admin();
verify_csrf();

$websiteId = (int) ($_POST['website_id'] ?? 0);

if ($websiteId <= 0) {
    set_flash('danger', 'Invalid website ID.');
    redirect('index.php');
}

$stmt = $pdo->prepare('SELECT id FROM websites WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $websiteId]);
$website = $stmt->fetch();

if (!$website) {
    set_flash('danger', 'Website not found.');
    redirect('index.php');
}

$deleteStmt = $pdo->prepare('DELETE FROM websites WHERE id = :id');
$deleteStmt->execute(['id' => $websiteId]);

set_flash('success', 'Website deleted successfully.');
redirect('index.php');
