<?php

declare(strict_types=1);

/**
 * Global configuration loader.
 *
 * This file loads environment variables from a .env file (if present) and
 * exposes helper functions for accessing configuration values.
 */

$projectRoot = dirname(__DIR__, 2);
$envPath = $projectRoot . '/.env';

if (is_readable($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv(sprintf('%s=%s', $key, $value));
        }
    }
}

/**
 * Retrieve a configuration value from environment variables.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }

    return $value;
}

// Application constants.
define('APP_NAME', 'CRM Leads');
define('APP_AUTHOR', 'Alexandre Dpaula');
define('APP_BASE_URL', env('APP_BASE_URL', ''));

define('DB_DSN', env('DB_DSN', 'mysql:host=localhost;dbname=crm_leads;charset=utf8mb4'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

define('SESSION_NAME', env('SESSION_NAME', 'crm_leads_session'));
define('SESSION_SECURE', filter_var(env('SESSION_SECURE', false), FILTER_VALIDATE_BOOL));
define('SESSION_SAMESITE', env('SESSION_SAMESITE', 'Lax'));
define('SESSION_COOKIE_LIFETIME', (int) env('SESSION_COOKIE_LIFETIME', 0));
define('SESSION_COOKIE_PATH', env('SESSION_COOKIE_PATH', '/'));

define('PASSWORD_RESET_TOKEN_TTL', (int) env('PASSWORD_RESET_TOKEN_TTL', 3600)); // seconds

