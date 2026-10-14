<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function setFlash(string $type, string $message): void
{
    ensureSessionStarted();
    $_SESSION['flash'][$type] = $message;
}

function getFlash(string $type): ?string
{
    ensureSessionStarted();

    if (!isset($_SESSION['flash'][$type])) {
        return null;
    }

    $message = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);

    return $message;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function activeNav(string $pageName, string $currentPage): string
{
    return $pageName === $currentPage ? 'active' : '';
}
