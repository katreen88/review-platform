<?php
$pageTitle = 'Manage Reviews';
include __DIR__ . '/components/header.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT r.id, r.website_id, r.user_id, r.rating, r.comment, r.created_at,
               w.title AS website_title, u.username
        FROM reviews r
        JOIN websites w ON w.id = r.website_id
        JOIN users u ON u.id = r.user_id";

if ($search !== '') {
    $sql .= ' WHERE w.title LIKE :website OR u.username LIKE :username OR r.comment LIKE :comment';
    $value = '%' . $search . '%';
    $params = [
        'website' => $value,
        'username' => $value,
        'comment' => $value,
    ];
}

$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();
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
            <h1 class="h2 mb-1">Manage Reviews</h1>
            <p class="text-muted mb-0">Moderate ratings and comments for all websites.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search by website, username, or comment text...">
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
                            <th>Review</th>
                            <th>Website</th>
                            <th>User</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$reviews): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No reviews found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td style="min-width: 260px;">
                                    <div class="fw-semibold mb-1"><?= str_repeat('★', (int) $review['rating']) ?></div>
                                    <div class="small text-muted admin-comment-preview"><?= e($review['comment']) ?></div>
                                </td>
                                <td><a href="website.php?id=<?= (int) $review['website_id'] ?>" class="text-decoration-none"><?= e($review['website_title']) ?></a></td>
                                <td><?= e($review['username']) ?></td>
                                <td><?= e(date('M j, Y', strtotime($review['created_at']))) ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <a href="edit_review.php?id=<?= (int) $review['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                        <form method="post" action="delete_review.php" class="m-0" onsubmit="return confirm('Delete this review?');">
                                            <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                            <input type="hidden" name="website_id" value="<?= (int) $review['website_id'] ?>">
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
