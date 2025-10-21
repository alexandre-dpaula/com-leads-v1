<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        session_name(\SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => \SESSION_COOKIE_LIFETIME,
            'path' => \SESSION_COOKIE_PATH,
            'secure' => \SESSION_SECURE,
            'httponly' => true,
            'samesite' => \SESSION_SAMESITE,
        ]);

        if (!session_start()) {
            throw new \RuntimeException('Não foi possível iniciar a sessão.');
        }

        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        if (isset($_SESSION['_flash'][$key])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $value;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_id() !== '') {
            session_destroy();
        }
    }
}

