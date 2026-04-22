<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in.'
    ]);
    exit;
}

if (!is_admin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Only admin can add websites.'
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

$title = trim($_POST['title'] ?? '');
$url = trim($_POST['url'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '' || $url === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Title and URL are required.'
    ]);
    exit;
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid URL.'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO websites (title, url, description, user_id)
    VALUES (:title, :url, :description, :user_id)
");

$stmt->execute([
    'title' => $title,
    'url' => $url,
    'description' => $description,
    'user_id' => $_SESSION['user_id']
]);

echo json_encode([
    'success' => true,
    'message' => 'Website added successfully.'
]);