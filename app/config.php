<?php
declare(strict_types=1);

const APP_NAME = 'KasiSwap';
const BUYER_PROTECTION_RATE = 0.025;

function env_value(string $key, string $default): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function app_config(): array
{
    $config = [
        'db_host' => env_value('DB_HOST', '127.0.0.1'),
        'db_port' => env_value('DB_PORT', '3306'),
        'db_name' => env_value('DB_NAME', 'kasiswap'),
        'db_user' => env_value('DB_USER', 'kasiswap'),
        'db_pass' => env_value('DB_PASS', 'kasiswap'),
    ];

    $localConfig = __DIR__ . '/config.local.php';
    if (is_file($localConfig)) {
        $overrides = require $localConfig;
        if (is_array($overrides)) {
            $config = array_merge($config, $overrides);
        }
    }

    return $config;
}
