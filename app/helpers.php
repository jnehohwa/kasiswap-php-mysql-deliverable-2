<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function money(float|string|int $amount): string
{
    return 'R' . number_format((float) $amount, 2);
}

function is_active(string $path): string
{
    $current = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: 'index.php');
    return $current === $path ? 'active' : '';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function old(string $key, string $default = ''): string
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'listing';
}

function selected(string $actual, string $expected): string
{
    return $actual === $expected ? 'selected' : '';
}

function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}

function page_url(string $path): string
{
    return '/' . ltrim($path, '/');
}
