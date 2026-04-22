<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

if (!is_post_request()) {
    redirect('index.php');
}

require_login();
verify_csrf();

$reviewId = (int) ($_POST['review_id'] ?? 0);
$websiteId = (int) ($_POST['website_id'] ?? 0);

if ($reviewId < 1) {
    set_flash('danger', 'Invalid review ID.');
    redirect($websiteId > 0 ? 'website.php?id=' . $websiteId : 'index.php');
}

$stmt = $pdo->prepare('SELECT id, website_id, user_id FROM reviews WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $reviewId]);
$review = $stmt->fetch();

if (!$review) {
    set_flash('danger', 'Review not found.');
    redirect($websiteId > 0 ? 'website.php?id=' . $websiteId : 'index.php');
}

if (!can_manage_review($review)) {
    set_flash('danger', 'You are not authorized to delete this review.');
    redirect('website.php?id=' . (int) $review['website_id']);
}

$deleteStmt = $pdo->prepare('DELETE FROM reviews WHERE id = :id');
$deleteStmt->execute(['id' => $reviewId]);

set_flash('success', 'Review deleted successfully.');
redirect('website.php?id=' . (int) $review['website_id']);
