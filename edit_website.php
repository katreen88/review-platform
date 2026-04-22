<?php
$pageTitle = 'Edit Website';
include __DIR__ . '/components/header.php';
require_admin();

$websiteId = (int) ($_GET['id'] ?? $_POST['website_id'] ?? 0);
if ($websiteId < 1) {
    set_flash('warning', 'Invalid website selected.');
    redirect('index.php');
}

$stmt = $pdo->prepare('SELECT * FROM websites WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $websiteId]);
$website = $stmt->fetch();

if (!$website) {
    set_flash('warning', 'Website not found.');
    redirect('index.php');
}

if (is_post_request()) {
    verify_csrf();

    $title = sanitize_string($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $description = sanitize_string($_POST['description'] ?? '');

    if ($title === '' || $url === '' || $description === '') {
        $error = 'All fields are required.';
    } elseif (!valid_url($url)) {
        $error = 'Please enter a valid website URL including http:// or https://';
    } else {
        $check = $pdo->prepare('SELECT id FROM websites WHERE url = :url AND id != :id LIMIT 1');
        $check->execute([
            'url' => $url,
            'id' => $websiteId,
        ]);

        if ($check->fetch()) {
            $error = 'Another website already uses this URL.';
        } else {
            $update = $pdo->prepare('UPDATE websites SET title = :title, url = :url, description = :description WHERE id = :id');
            $update->execute([
                'title' => $title,
                'url' => $url,
                'description' => $description,
                'id' => $websiteId,
            ]);

            set_flash('success', 'Website updated successfully.');
            redirect('website.php?id=' . $websiteId);
        }
    }
}
?>

<div class="container" style="max-width: 760px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-3">Edit Website</h1>
            <p class="text-muted">Only administrators can edit website information.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="website_id" value="<?= (int) $websiteId ?>">
                <div class="mb-3">
                    <label class="form-label">Website Title</label>
                    <input type="text" name="title" class="form-control" value="<?= e($_POST['title'] ?? $website['title']) ?>" maxlength="120" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Website URL</label>
                    <input type="url" name="url" class="form-control" value="<?= e($_POST['url'] ?? $website['url']) ?>" placeholder="https://example.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" required><?= e($_POST['description'] ?? $website['description']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="website.php?id=<?= (int) $websiteId ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
