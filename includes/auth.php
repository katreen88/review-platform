<?php

declare(strict_types=1);

function find_user_by_email(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT id, username, email, password, role, created_at FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function register_user(PDO $pdo, string $username, string $email, string $password): array
{
    $username = sanitize_string($username);
    $email = strtolower(trim($email));

    if ($username === '' || $email === '' || $password === '') {
        return ['success' => false, 'message' => 'All fields are required.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        return ['success' => false, 'message' => 'Username must be between 3 and 50 characters.'];
    }

    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    if (find_user_by_email($pdo, $email)) {
        return ['success' => false, 'message' => 'This email is already registered.'];
    }

    $checkUsername = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $checkUsername->execute(['username' => $username]);
    if ($checkUsername->fetch()) {
        return ['success' => false, 'message' => 'This username is already taken.'];
    }

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
    $stmt->execute([
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    return ['success' => true, 'message' => 'Account created successfully. You can login now.'];
}

function login_user(PDO $pdo, string $email, string $password): array
{
    $user = find_user_by_email($pdo, strtolower(trim($email)));

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    session_regenerate_id(true);
    unset($user['password']);
    $_SESSION['user'] = $user;

    return ['success' => true, 'message' => 'Welcome back, ' . $user['username'] . '!'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
