<?php

declare(strict_types=1);

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function requireAuth(): void
{
    ensureSessionStarted();

    if (empty($_SESSION['user_id'])) {
        ensureSessionStarted();
        $_SESSION['flash']['error'] = 'Please log in to continue.';
        header('Location: login.php');
        exit;
    }
}

function redirectIfAuthenticated(): void
{
    ensureSessionStarted();

    if (!empty($_SESSION['user_id'])) {
        header('Location: home.php');
        exit;
    }
}

function generateCsrfToken(): string
{
    ensureSessionStarted();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    ensureSessionStarted();

    if (!is_string($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
