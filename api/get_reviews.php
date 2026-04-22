<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

$websiteId = (int) ($_GET['website_id'] ?? 0);

if ($websiteId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid website ID'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT r.id, r.website_id, r.user_id, r.rating, r.comment, r.created_at, u.username
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.website_id = :id
    ORDER BY r.created_at DESC
");

$stmt->execute(['id' => $websiteId]);

$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $reviews
]);