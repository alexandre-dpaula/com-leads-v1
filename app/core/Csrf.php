<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::TOKEN_KEY);
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }

        return $token;
    }

    public static function validate(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        $stored = Session::get(self::TOKEN_KEY);
        return is_string($stored) && hash_equals($stored, $token);
    }

    public static function assertValidFromRequest(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate(is_string($token) ? $token : null)) {
            http_response_code(419);
            throw new \RuntimeException('Token CSRF inválido ou ausente.');
        }
    }
}

