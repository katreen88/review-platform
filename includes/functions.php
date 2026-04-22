<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize_string(string $value): string
{
    return trim($value);
}

function is_post_request(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function current_user(): ?array
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }

    return $_SESSION['user'];
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please login first.');
        redirect('login.php');
    }
}

function current_user_id(): int
{
    return (int) (current_user()['id'] ?? 0);
}

function current_user_role(): string
{
    return (string) (current_user()['role'] ?? 'guest');
}

function is_admin(): bool
{
    return current_user_role() === 'admin';
}

function is_regular_user(): bool
{
    return current_user_role() === 'user';
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        set_flash('danger', 'Only admins can access that page.');
        redirect('index.php');
    }
}

function require_regular_user(): void
{
    require_login();

    if (!is_regular_user()) {
        set_flash('danger', 'Only regular users can perform that action.');
        redirect('index.php');
    }
}

function can_manage_website(array $website): bool
{
    return is_admin();
}

function can_manage_review(array $review): bool
{
    if (!is_logged_in()) {
        return false;
    }

    return is_admin() || (int) ($review['user_id'] ?? 0) === current_user_id();
}

function valid_url(string $url): bool
{
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
}

function rating_label(float $rating): string
{
    return number_format($rating, 1) . ' / 5';
}
