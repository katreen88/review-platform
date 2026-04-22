<?php $user = current_user(); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Review Platform</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_website.php">Add Website</a></li>
                <?php endif; ?>
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex gap-2 align-items-center">
                <?php if ($user): ?>
                    <span class="text-white-50 small">
                        Hi, <?= e($user['username']) ?>
                        <span class="badge text-bg-secondary ms-1"><?= e(ucfirst($user['role'])) ?></span>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
                    <a class="btn btn-primary btn-sm" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
