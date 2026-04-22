<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

$search = trim($_GET['q'] ?? '');
$params = [];

$sql = "SELECT w.id, w.title, w.url, w.description, w.created_at,
               COALESCE(AVG(r.rating), 0) AS average_rating,
               COUNT(r.id) AS review_count
        FROM websites w
        LEFT JOIN reviews r ON r.website_id = w.id";

if ($search !== '') {
    $sql .= " WHERE w.title LIKE :search_title
              OR w.description LIKE :search_description
              OR w.url LIKE :search_url";

    $searchValue = '%' . $search . '%';
    $params['search_title'] = $searchValue;
    $params['search_description'] = $searchValue;
    $params['search_url'] = $searchValue;
}

$sql .= " GROUP BY w.id, w.title, w.url, w.description, w.created_at
          ORDER BY w.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$websites = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $websites
]);