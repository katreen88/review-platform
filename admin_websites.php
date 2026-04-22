<?php
$pageTitle = 'Manage Websites';
include __DIR__ . '/components/header.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT w.id, w.title, w.url, w.description, w.created_at, u.username,
               COALESCE(AVG(r.rating), 0) AS average_rating,
               COUNT(r.id) AS review_count
        FROM websites w
        JOIN users u ON u.id = w.user_id
        LEFT JOIN reviews r ON r.website_id = w.id";

if ($search !== '') {
    $sql .= ' WHERE w.title LIKE :title OR w.url LIKE :url OR w.description LIKE :description OR u.username LIKE :username';
    $value = '%' . $search . '%';
    $params = [
        'title' => $value,
        'url' => $value,
        'description' => $value,
        'username' => $value,
    ];
}

$sql .= ' GROUP BY w.id, w.title, w.url, w.description, w.created_at, u.username ORDER BY w.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$websites = $stmt->fetchAll();
$flash = get_flash();
?>

<div class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Manage Websites</h1>
            <p class="text-muted mb-0">Edit, delete, and review every website on the platform.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="add_website.php" class="btn btn-primary">Add Website</a>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search by title, URL, description, or creator...">
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-dark">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Creator</th>
                            <th>Reviews</th>
                            <th>Average</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$websites): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No websites found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($websites as $website): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($website['title']) ?></div>
                                    <div class="small text-muted text-break"><?= e($website['url']) ?></div>
                                </td>
                                <td><?= e($website['username']) ?></td>
                                <td><?= (int) $website['review_count'] ?></td>
                                <td><?= e(rating_label((float) $website['average_rating'])) ?></td>
                                <td><?= e(date('M j, Y', strtotime($website['created_at']))) ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <a href="website.php?id=<?= (int) $website['id'] ?>" class="btn btn-outline-dark btn-sm">View</a>
                                        <a href="edit_website.php?id=<?= (int) $website['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                        <form method="post" action="delete_website.php" class="m-0" onsubmit="return confirm('Delete this website and all related reviews?');">
                                            <input type="hidden" name="website_id" value="<?= (int) $website['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
