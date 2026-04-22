<?php
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/components/header.php';
require_admin();

$stats = [
    'websites' => (int) $pdo->query('SELECT COUNT(*) FROM websites')->fetchColumn(),
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'reviews' => (int) $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    'admins' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
];

$recentWebsites = $pdo->query(
    'SELECT w.id, w.title, w.created_at, u.username
     FROM websites w
     JOIN users u ON u.id = w.user_id
     ORDER BY w.created_at DESC
     LIMIT 5'
)->fetchAll();

$recentReviews = $pdo->query(
    'SELECT r.id, r.rating, r.created_at, w.title AS website_title, u.username
     FROM reviews r
     JOIN websites w ON w.id = r.website_id
     JOIN users u ON u.id = r.user_id
     ORDER BY r.created_at DESC
     LIMIT 5'
)->fetchAll();

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
            <h1 class="h2 mb-1">Admin Dashboard</h1>
            <p class="text-muted mb-0">Manage websites, users, and reviews from one place.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="add_website.php" class="btn btn-primary">Add Website</a>
            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stats-box h-100">
                <strong><?= $stats['websites'] ?></strong>
                <span>Total Websites</span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-box h-100">
                <strong><?= $stats['users'] ?></strong>
                <span>Total Users</span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-box h-100">
                <strong><?= $stats['reviews'] ?></strong>
                <span>Total Reviews</span>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stats-box h-100">
                <strong><?= $stats['admins'] ?></strong>
                <span>Admin Accounts</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 dashboard-card">
                <div class="card-body p-4">
                    <h2 class="h4 mb-2">Manage Websites</h2>
                    <p class="text-muted">Edit titles, URLs, descriptions, or remove websites from the platform.</p>
                    <a href="admin_websites.php" class="btn btn-dark">Open Websites</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 dashboard-card">
                <div class="card-body p-4">
                    <h2 class="h4 mb-2">Manage Users</h2>
                    <p class="text-muted">Update usernames, emails, roles, or delete user accounts safely.</p>
                    <a href="admin_users.php" class="btn btn-dark">Open Users</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 dashboard-card">
                <div class="card-body p-4">
                    <h2 class="h4 mb-2">Manage Reviews</h2>
                    <p class="text-muted">Moderate ratings and comments, then edit or remove unwanted reviews.</p>
                    <a href="admin_reviews.php" class="btn btn-dark">Open Reviews</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Recent Websites</h2>
                        <a href="admin_websites.php" class="small text-decoration-none">View all</a>
                    </div>
                    <?php if (!$recentWebsites): ?>
                        <p class="text-muted mb-0">No websites added yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Added By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentWebsites as $website): ?>
                                        <tr>
                                            <td><a href="website.php?id=<?= (int) $website['id'] ?>" class="text-decoration-none"><?= e($website['title']) ?></a></td>
                                            <td><?= e($website['username']) ?></td>
                                            <td><?= e(date('M j, Y', strtotime($website['created_at']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Recent Reviews</h2>
                        <a href="admin_reviews.php" class="small text-decoration-none">View all</a>
                    </div>
                    <?php if (!$recentReviews): ?>
                        <p class="text-muted mb-0">No reviews available yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Website</th>
                                        <th>User</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentReviews as $review): ?>
                                        <tr>
                                            <td><?= e($review['website_title']) ?></td>
                                            <td><?= e($review['username']) ?></td>
                                            <td><?= str_repeat('★', (int) $review['rating']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
