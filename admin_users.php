<?php
$pageTitle = 'Manage Users';
include __DIR__ . '/components/header.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT u.id, u.username, u.email, u.role, u.created_at,
               COUNT(DISTINCT w.id) AS website_count,
               COUNT(DISTINCT r.id) AS review_count
        FROM users u
        LEFT JOIN websites w ON w.user_id = u.id
        LEFT JOIN reviews r ON r.user_id = u.id";

if ($search !== '') {
    $sql .= ' WHERE u.username LIKE :username OR u.email LIKE :email OR u.role LIKE :role';
    $value = '%' . $search . '%';
    $params = [
        'username' => $value,
        'email' => $value,
        'role' => $value,
    ];
}

$sql .= ' GROUP BY u.id, u.username, u.email, u.role, u.created_at ORDER BY u.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
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
            <h1 class="h2 mb-1">Manage Users</h1>
            <p class="text-muted mb-0">Edit account details, switch roles, or remove accounts.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-10">
                    <input type="text" name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search by username, email, or role...">
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
                            <th>User</th>
                            <th>Role</th>
                            <th>Websites</th>
                            <th>Reviews</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$users): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($account['username']) ?></div>
                                    <div class="small text-muted"><?= e($account['email']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $account['role'] === 'admin' ? 'text-bg-dark' : 'text-bg-secondary' ?>">
                                        <?= e(ucfirst($account['role'])) ?>
                                    </span>
                                    <?php if ((int) $account['id'] === current_user_id()): ?>
                                        <div class="small text-muted mt-1">Current account</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= (int) $account['website_count'] ?></td>
                                <td><?= (int) $account['review_count'] ?></td>
                                <td><?= e(date('M j, Y', strtotime($account['created_at']))) ?></td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <a href="edit_user.php?id=<?= (int) $account['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                        <?php if ((int) $account['id'] !== current_user_id()): ?>
                                            <form method="post" action="delete_user.php" class="m-0" onsubmit="return confirm('Delete this user account? Related websites and reviews may also be removed.');">
                                                <input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        <?php endif; ?>
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
