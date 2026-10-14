<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();
ensureSessionStarted();

$user = null;
$successMessage = getFlash('success');
$errorMessage = getFlash('error');

try {
    $pdo = getDbConnection();
    $statement = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $_SESSION['user_id']]);
    $user = $statement->fetch();
} catch (Throwable $exception) {
    $user = null;
    $errorMessage = 'Unable to load your profile right now.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDine | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            <a href="index.php" class="brand">
                <span class="brand-mark">S</span>
                SmartDine
            </a>
            <nav class="nav-links" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <a href="home.php" class="active">Dashboard</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <?php if ($successMessage): ?>
        <div class="flash-banner success"><?php echo e($successMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="flash-banner error"><?php echo e($errorMessage); ?></div>
    <?php endif; ?>

    <main class="container dashboard-page">
        <section class="dashboard-hero">
            <div>
                <p class="eyebrow">User dashboard</p>
                <h1>Welcome back, <?php echo e($user['name'] ?? $_SESSION['user_name'] ?? 'Guest'); ?>!</h1>
            </div>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </section>

        <section class="dashboard-grid">
            <article class="panel info-panel">
                <h2>Account information</h2>
                <ul class="detail-list">
                    <li><span>Name</span><strong><?php echo e($user['name'] ?? $_SESSION['user_name'] ?? ''); ?></strong></li>
                    <li><span>Email</span><strong><?php echo e($user['email'] ?? $_SESSION['user_email'] ?? ''); ?></strong></li>
                    <li><span>Role</span><strong><?php echo e(ucfirst((string) ($user['role'] ?? 'customer'))); ?></strong></li>
                </ul>
            </article>

            <article class="panel activity-panel">
                <h2>Quick actions</h2>
                <div class="action-stack">
                    <a href="index.php" class="action-item">View home page</a>
                    <a href="logout.php" class="action-item danger">Log out</a>
                </div>
            </article>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
