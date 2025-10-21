<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class PasswordReset extends Model
{
    public static function create(int $userId, string $token, \DateTimeImmutable $expiresAt): void
    {
        $hash = hash('sha256', $token);
        $stmt = self::db()->prepare(
            'INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'token' => $hash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public static function findValid(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = self::db()->prepare(
            'SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL LIMIT 1'
        );
        $stmt->execute(['token' => $hash]);
        $reset = $stmt->fetch();
        if (!$reset) {
            return null;
        }

        $now = new \DateTimeImmutable();
        if (new \DateTimeImmutable($reset['expires_at']) < $now) {
            return null;
        }

        return $reset;
    }

    public static function markUsed(int $id): void
    {
        $stmt = self::db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

