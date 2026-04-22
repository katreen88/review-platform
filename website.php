<?php
$pageTitle = 'Website Details';
include __DIR__ . '/components/header.php';

$websiteId = (int) ($_GET['id'] ?? 0);

if ($websiteId <= 0) {
    echo '<div class="container py-4"><div class="alert alert-danger">Invalid website ID.</div></div>';
    include __DIR__ . '/components/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT w.id, w.title, w.url, w.description, w.user_id, w.created_at,
           COALESCE(AVG(r.rating), 0) AS average_rating,
           COUNT(r.id) AS review_count
    FROM websites w
    LEFT JOIN reviews r ON r.website_id = w.id
    WHERE w.id = :id
    GROUP BY w.id, w.title, w.url, w.description, w.user_id, w.created_at
");
$stmt->execute(['id' => $websiteId]);
$website = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$website) {
    echo '<div class="container py-4"><div class="alert alert-danger">Website not found.</div></div>';
    include __DIR__ . '/components/footer.php';
    exit;
}

$currentUserId = current_user_id();
$isAdmin = is_admin();
$isLoggedIn = is_logged_in();
?>

<div class="container py-4" style="max-width: 1000px;">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h1 class="mb-2"><?= e($website['title']) ?></h1>
                    <p class="mb-2">
                        <a href="<?= e($website['url']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                            <?= e($website['url']) ?>
                        </a>
                    </p>
                    <p class="text-secondary mb-0"><?= e($website['description']) ?></p>
                </div>

                <div class="text-lg-end">
                    <div class="badge text-bg-warning text-dark fs-6 mb-2">
                        ⭐ <?= number_format((float) $website['average_rating'], 1) ?> / 5
                    </div>
                    <div class="text-muted small">
                        <?= (int) $website['review_count'] ?> review(s)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isLoggedIn && !$isAdmin): ?>
        <div class="card border-0 shadow-sm mb-4" id="reviewFormCard">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Add Your Review</h2>
                <div id="reviewMessage"></div>

                <form id="reviewForm">
                    <input type="hidden" name="website_id" value="<?= (int) $website['id'] ?>">

                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select name="rating" id="rating" class="form-select" required>
                            <option value="">Choose rating</option>
                            <option value="1">1 Star</option>
                            <option value="2">2 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="5">5 Stars</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Write your review..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Reviews</h2>
                <span id="reviewCountLabel" class="text-muted small"></span>
            </div>

            <div id="reviewsContainer">
                <p>Loading reviews...</p>
            </div>
        </div>
    </div>

</div>

<script>
const CURRENT_USER_ID = <?= (int) $currentUserId ?>;
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
const IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
const WEBSITE_ID = <?= (int) $website['id'] ?>;

async function loadReviews(websiteId) {
    const container = document.getElementById('reviewsContainer');
    const countLabel = document.getElementById('reviewCountLabel');
    container.innerHTML = '<p>Loading reviews...</p>';

    try {
        const response = await fetch('api/get_reviews.php?website_id=' + encodeURIComponent(websiteId));
        const result = await response.json();

        if (!result.success) {
            container.innerHTML = '<p class="text-danger">Error loading reviews.</p>';
            return;
        }

        const reviews = result.data || [];
        countLabel.textContent = `${reviews.length} review(s)`;

        const userAlreadyReviewed = reviews.some(r => Number(r.user_id) === CURRENT_USER_ID);

        const reviewFormCard = document.getElementById('reviewFormCard');
        if (reviewFormCard) {
            reviewFormCard.style.display = userAlreadyReviewed ? 'none' : 'block';
        }

        if (reviews.length === 0) {
            container.innerHTML = '<div class="text-muted">No reviews yet.</div>';
            return;
        }

        container.innerHTML = reviews.map(r => {
            const canManage = IS_ADMIN || Number(r.user_id) === CURRENT_USER_ID;

            return `
                <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>
                            <div class="fw-semibold">${escapeHtml(r.username)}</div>
                            <div class="text-muted small">${formatDate(r.created_at)}</div>
                        </div>
                        <span class="badge text-bg-warning text-dark">⭐ ${Number(r.rating)}/5</span>
                    </div>

                    <p class="mb-3 text-secondary">${escapeHtml(r.comment ?? '')}</p>

                    ${canManage ? `
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="edit_review.php?id=${Number(r.id)}" class="btn btn-outline-primary btn-sm">Edit</a>

                            <form method="post" action="delete_review.php" onsubmit="return confirm('Delete this review?');" class="m-0">
                                <input type="hidden" name="review_id" value="${Number(r.id)}">
                                <input type="hidden" name="website_id" value="${Number(r.website_id)}">
                                <input type="hidden" name="csrf_token" value="${escapeHtml(CSRF_TOKEN)}">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error(error);
        container.innerHTML = '<p class="text-danger">⚠️ Server error while loading reviews.</p>';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function formatDate(value) {
    const date = new Date(value.replace(' ', 'T'));
    if (isNaN(date.getTime())) return value;
    return date.toLocaleString();
}

const reviewForm = document.getElementById('reviewForm');

if (reviewForm) {
    reviewForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const messageBox = document.getElementById('reviewMessage');
        const formData = new FormData(reviewForm);

        messageBox.innerHTML = '<div class="alert alert-info">Submitting review...</div>';

        try {
            const response = await fetch('api/add_review.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                messageBox.innerHTML = `<div class="alert alert-success">${escapeHtml(result.message)}</div>`;
                reviewForm.reset();

                await loadReviews(WEBSITE_ID);

                document.getElementById('reviewsContainer').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            } else {
                messageBox.innerHTML = `<div class="alert alert-danger">${escapeHtml(result.message)}</div>`;
            }
        } catch (error) {
            console.error(error);
            messageBox.innerHTML = '<div class="alert alert-danger">⚠️ Error occurred while submitting the review.</div>';
        }
    });
}

loadReviews(WEBSITE_ID);
</script>

<?php include __DIR__ . '/components/footer.php'; ?>