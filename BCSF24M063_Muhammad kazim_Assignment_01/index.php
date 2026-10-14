<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

ensureSessionStarted();

$user = null;
if (!empty($_SESSION['user_id'])) {
    try {
        $pdo = getDbConnection();
        $statement = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $_SESSION['user_id']]);
        $user = $statement->fetch();
    } catch (Throwable $exception) {
        $user = null;
    }
}

$successMessage = getFlash('success');
$errorMessage = getFlash('error');
$currentPage = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDine | Home</title>
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
                <a href="index.php" class="<?php echo activeNav('home', $currentPage); ?>">Home</a>
                <?php if ($user): ?>
                    <a href="home.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if ($successMessage): ?>
        <div class="flash-banner success"><?php echo e($successMessage); ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="flash-banner error"><?php echo e($errorMessage); ?></div>
    <?php endif; ?>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <p class="eyebrow">Fresh tastes, faster service</p>
                    <h1>Enjoy delicious meals from your favorite restaurant.</h1>
                    <p class="lead">
                        SmartDine brings together reservations, menu browsing, and secure customer access in one smart restaurant experience.
                    </p>
                    <div class="cta-row">
                        <?php if ($user): ?>
                            <a class="btn btn-primary" href="home.php">Go to dashboard</a>
                            <a class="btn btn-secondary" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="btn btn-primary" href="signup.php">Create account</a>
                            <a class="btn btn-secondary" href="login.php">Login</a>
                        <?php endif; ?>
                    </div>
                    <ul class="feature-pills">
                        <li>Fast login</li>
                        <li>Secure sessions</li>
                        <li>Smart restaurant experience</li>
                    </ul>
                </div>

                <div class="hero-card">
                    <div class="mini-card">
                        <span class="tag">Today special</span>
                        <h3>Fire Grill Combo</h3>
                        <p>Grilled chicken, seasonal salad, garlic bread, and a fresh drink.</p>
                        <div class="price-row">
                            <strong>$18.90</strong>
                            <span>4.8/5 rating</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container info-grid">
            <article class="info-card">
                <h2>For customers</h2>
                <p>Register quickly, log in securely, and manage your restaurant experience with tailored access.</p>
            </article>
            <article class="info-card">
                <h2>For restaurants</h2>
                <p>Keep guest data organized and improve reservations, menu browsing, and digital engagement.</p>
            </article>
            <article class="info-card">
                <h2>For students</h2>
                <p>Understand the LAMP stack clearly with structured PHP, MySQL, CSS, and HTML implementation.</p>
            </article>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
