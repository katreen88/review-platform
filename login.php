<?php
$pageTitle = 'Login';
include __DIR__ . '/components/header.php';

if (is_logged_in()) {
    redirect('index.php');
}

$flash = get_flash();

if (is_post_request()) {
    verify_csrf();
    $result = login_user($pdo, $_POST['email'] ?? '', $_POST['password'] ?? '');

    if ($result['success']) {
        set_flash('success', $result['message']);
        redirect('index.php');
    }

    $error = $result['message'];
}
?>

<div class="container auth-container">
    <div class="card border-0 shadow-sm auth-card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-3">Welcome Back</h1>
            <p class="text-muted">Login to add websites and post reviews.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-dark w-100">Login</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
