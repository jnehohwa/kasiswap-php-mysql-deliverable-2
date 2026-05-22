<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $posted = $_POST['csrf_token'] ?? '';
    if (!is_string($posted) || !hash_equals(csrf_token(), $posted)) {
        http_response_code(419);
        exit('Security check failed. Please go back and try again.');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND status = "active" LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('warning', 'Please sign in to continue.');
        redirect('/auth.php');
    }

    return $user;
}

function require_role(array|string $roles): array
{
    $user = require_login();
    $allowed = is_array($roles) ? $roles : [$roles];

    if (!in_array($user['role'], $allowed, true)) {
        http_response_code(403);
        render_header('Access denied');
        echo '<section class="panel narrow"><h1>Access denied</h1><p>Your account does not have permission to open this page.</p><a class="button" href="/dashboard.php">Back to dashboard</a></section>';
        render_footer();
        exit;
    }

    return $user;
}

function sign_in(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    log_audit('login', 'user', (int) $user['id'], 'User signed in.');
    return true;
}

function sign_out(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        log_audit('logout', 'user', (int) $userId, 'User signed out.');
    }

    $_SESSION = [];
    session_destroy();
}

function register_account(array $data): int
{
    $role = in_array($data['role'] ?? 'buyer', ['buyer', 'seller'], true) ? $data['role'] : 'buyer';
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, username, email, phone, password_hash, role, township, city, verification_level)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($data['full_name']),
            trim($data['username']),
            trim($data['email']),
            trim($data['phone'] ?? ''),
            $passwordHash,
            $role,
            trim($data['township'] ?? ''),
            trim($data['city'] ?? ''),
            trim($data['phone'] ?? '') !== '' ? 'phone' : 'none',
        ]);
        $userId = (int) $pdo->lastInsertId();

        if ($role === 'seller') {
            $storeName = trim($data['store_name'] ?? '') ?: trim($data['full_name']) . ' Store';
            $seller = $pdo->prepare('INSERT INTO seller_profiles (user_id, store_name, store_bio) VALUES (?, ?, ?)');
            $seller->execute([$userId, $storeName, 'New KasiSwap seller.']);
        }

        log_audit('register', 'user', $userId, 'New account created.', $userId);
        $pdo->commit();
        return $userId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function log_audit(string $action, string $entityType, ?int $entityId, ?string $details = null, ?int $actorId = null): void
{
    $actorId ??= $_SESSION['user_id'] ?? null;
    $stmt = db()->prepare('INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$actorId, $action, $entityType, $entityId, $details]);
}
