<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to add a review.'
    ]);
    exit;
}

if (is_admin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Admins cannot add reviews.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$websiteId = (int) ($_POST['website_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId = current_user_id();

if ($websiteId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid website.'
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Rating must be between 1 and 5.'
    ]);
    exit;
}

if ($comment === '' || mb_strlen($comment) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Comment must be at least 3 characters.'
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM websites WHERE id = :id");
$stmt->execute(['id' => $websiteId]);
$website = $stmt->fetch();

if (!$website) {
    echo json_encode([
        'success' => false,
        'message' => 'Website not found.'
    ]);
    exit;
}

$checkStmt = $pdo->prepare("
    SELECT id
    FROM reviews
    WHERE website_id = :website_id AND user_id = :user_id
");
$checkStmt->execute([
    'website_id' => $websiteId,
    'user_id' => $userId
]);

if ($checkStmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'You already reviewed this website.'
    ]);
    exit;
}

$insertStmt = $pdo->prepare("
    INSERT INTO reviews (website_id, user_id, rating, comment)
    VALUES (:website_id, :user_id, :rating, :comment)
");

$insertStmt->execute([
    'website_id' => $websiteId,
    'user_id' => $userId,
    'rating' => $rating,
    'comment' => $comment
]);

echo json_encode([
    'success' => true,
    'message' => 'Review added successfully.'
]);