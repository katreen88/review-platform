<?php
$pageTitle = 'Edit Review';
include __DIR__ . '/components/header.php';
require_login();

$reviewId = (int) ($_GET['id'] ?? $_POST['review_id'] ?? 0);
if ($reviewId < 1) {
    set_flash('warning', 'Invalid review selected.');
    redirect('index.php');
}

$stmt = $pdo->prepare(
    'SELECT r.*, w.title AS website_title
     FROM reviews r
     JOIN websites w ON w.id = r.website_id
     WHERE r.id = :id
     LIMIT 1'
);
$stmt->execute(['id' => $reviewId]);
$review = $stmt->fetch();

if (!$review) {
    set_flash('warning', 'Review not found.');
    redirect('index.php');
}

if (!can_manage_review($review)) {
    set_flash('danger', 'You are not allowed to edit this review.');
    redirect('website.php?id=' . (int) $review['website_id']);
}

if (is_post_request()) {
    verify_csrf();

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = sanitize_string($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please choose a rating between 1 and 5.';
    } elseif ($comment === '' || mb_strlen($comment) < 10) {
        $error = 'Comment must be at least 10 characters.';
    } else {
        $update = $pdo->prepare('UPDATE reviews SET rating = :rating, comment = :comment WHERE id = :id');
        $update->execute([
            'rating' => $rating,
            'comment' => $comment,
            'id' => $reviewId,
        ]);

        set_flash('success', 'Review updated successfully.');
        redirect('website.php?id=' . (int) $review['website_id']);
    }
}
?>

<div class="container" style="max-width: 760px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-3">Edit Review</h1>
            <p class="text-muted">Update your review for <strong><?= e($review['website_title']) ?></strong>.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="review_id" value="<?= (int) $reviewId ?>">
                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select" required>
                        <option value="">Choose rating</option>
                        <?php $selectedRating = $_POST['rating'] ?? $review['rating']; ?>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= (string) $i === (string) $selectedRating ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-control" rows="5" required><?= e($_POST['comment'] ?? $review['comment']) ?></textarea>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary">Save Review</button>
                    <a href="website.php?id=<?= (int) $review['website_id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
