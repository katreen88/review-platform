<?php
$pageTitle = 'Profile';
include __DIR__ . '/components/header.php';
require_login();

$user = current_user();

$stmt = $pdo->prepare(
    'SELECT r.id, r.website_id, r.user_id, r.rating, r.comment, r.created_at, w.title
     FROM reviews r
     JOIN websites w ON w.id = r.website_id
     WHERE r.user_id = :user_id
     ORDER BY r.created_at DESC'
);
$stmt->execute(['user_id' => $user['id']]);
$myReviews = $stmt->fetchAll();
?>

<div class="container py-4" style="max-width: 950px;">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-1"><?= e($user['username']) ?>'s Profile</h1>
                    <p class="text-muted mb-0">Email: <?= e($user['email']) ?></p>
                </div>
                <div class="text-md-end">
                    <span class="badge text-bg-dark"><?= e(ucfirst($user['role'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">My Reviews</h2>
                <span class="text-muted small"><?= count($myReviews) ?> total</span>
            </div>

            <?php if (!$myReviews): ?>
                <div class="text-muted">You have not posted any reviews yet.</div>
            <?php else: ?>
                <div class="d-grid gap-3">
                    <?php foreach ($myReviews as $review): ?>
                        <div class="border rounded-4 p-3 bg-light-subtle">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <a href="website.php?id=<?= (int) $review['website_id'] ?>" class="fw-semibold text-decoration-none">
                                        <?= e($review['title']) ?>
                                    </a>
                                    <div class="text-muted small">
                                        <?= e(date('M j, Y g:i A', strtotime($review['created_at']))) ?>
                                    </div>
                                </div>

                                <span class="badge text-bg-warning text-dark">
                                    ⭐ <?= (int) $review['rating'] ?>/5
                                </span>
                            </div>

                            <p class="mb-3 text-secondary"><?= nl2br(e($review['comment'])) ?></p>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="edit_review.php?id=<?= (int) $review['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    Edit
                                </a>

                                <form method="post" action="delete_review.php" class="m-0" onsubmit="return confirm('Delete this review?');">
                                    <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                    <input type="hidden" name="website_id" value="<?= (int) $review['website_id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include __DIR__ . '/components/footer.php'; ?>