<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

ensureSessionStarted();
redirectIfAuthenticated();

$errors = [];
$submittedData = [
    'name' => '',
    'email' => '',
    'phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedData['name'] = trim((string) ($_POST['name'] ?? ''));
    $submittedData['email'] = strtolower(trim((string) ($_POST['email'] ?? '')));
    $submittedData['phone'] = trim((string) ($_POST['phone'] ?? ''));

    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Security check failed. Please try again.';
    }

    if ($submittedData['name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($submittedData['email'] === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($submittedData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) {
        $errors[] = 'Password must contain at least one letter and one number.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $existing = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $existing->execute(['email' => $submittedData['email']]);

            if ($existing->fetch()) {
                $errors[] = 'This email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $statement = $pdo->prepare(
                    'INSERT INTO users (name, email, password_hash, phone, role) VALUES (:name, :email, :password_hash, :phone, :role)'
                );

                $statement->execute([
                    'name' => $submittedData['name'],
                    'email' => $submittedData['email'],
                    'password_hash' => $hash,
                    'phone' => $submittedData['phone'] !== '' ? $submittedData['phone'] : null,
                    'role' => 'customer',
                ]);

                setFlash('success', 'Registration successful. Please log in to continue.');
                header('Location: login.php');
                exit;
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unable to complete registration right now. Please try again later.';
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
    <title>SmartDine | Sign Up</title>
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
                <a href="login.php">Login</a>
                <a href="signup.php" class="active">Sign Up</a>
            </nav>
        </div>
    </header>

    <main class="auth-wrapper">
        <section class="auth-box">
            <p class="eyebrow center">Create your account</p>
            <h1>Join SmartDine</h1>

            <?php if (!empty($errors)): ?>
                <div class="message-box error-box" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="signup.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">

                <div class="field-group">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="<?php echo e($submittedData['name']); ?>" required>
                </div>

                <div class="field-group">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="<?php echo e($submittedData['email']); ?>" required>
                </div>

                <div class="field-group">
                    <label for="phone">Phone number (optional)</label>
                    <input id="phone" name="phone" type="tel" value="<?php echo e($submittedData['phone']); ?>">
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="At least 8 characters" required>
                </div>

                <div class="field-group">
                    <label for="confirm_password">Confirm password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">Create account</button>
            </form>

            <p class="switch-link">Already have an account? <a href="login.php">Login here</a></p>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
