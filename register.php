<?php
$pageTitle = 'Register';
include __DIR__ . '/components/header.php';

if (is_logged_in()) {
    redirect('index.php');
}

if (is_post_request()) {
    verify_csrf();
    $result = register_user($pdo, $_POST['username'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');

    if ($result['success']) {
        set_flash('success', $result['message']);
        redirect('login.php');
    }

    $error = $result['message'];
}
?>

<div class="container auth-container">
    <div class="card border-0 shadow-sm auth-card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-3">Create Account</h1>
            <p class="text-muted">Join the platform and start reviewing websites.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
                <button class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
