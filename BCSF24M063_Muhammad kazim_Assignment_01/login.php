<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

ensureSessionStarted();
redirectIfAuthenticated();

$errorMessage = getFlash('error') ?? '';
$submittedEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedEmail = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? null;

    if (!verifyCsrfToken($csrfToken)) {
        $errorMessage = 'Security check failed. Please try again.';
    } elseif ($submittedEmail === '' || $password === '') {
        $errorMessage = 'Please enter both email and password.';
    } else {
        try {
            $pdo = getDbConnection();
            $statement = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
            $statement->execute(['email' => $submittedEmail]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                header('Location: home.php');
                exit;
            }

            $errorMessage = 'Invalid email or password.';
        } catch (Throwable $exception) {
            $errorMessage = 'Unable to log in at the moment. Please try again.';
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartDine | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <header class="topbar">
        <div class="container nav">
            <a href="index.php" class="brand">
                <span class="brand-mark">S</span>
                SmartDine
            </a>
            <nav class="nav-links" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <a href="login.php" class="active">Login</a>
                <a href="signup.php">Sign Up</a>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <section class="auth-box">
            <p class="eyebrow center">Welcome back</p>
            <h1>Login to SmartDine</h1>

            <?php if ($errorMessage !== ''): ?>
                <div class="message-box error-box" role="alert"><?php echo e($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

                <div class="field-group">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="<?php echo e($submittedEmail); ?>" required>
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Login</button>
            </form>

            <p class="switch-link">Need an account? <a href="signup.php">Sign up here</a></p>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
