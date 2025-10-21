<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User extends Model
{
    public int $id;
    public string $name;
    public string $email;
    public string $password_hash;
    public string $created_at;

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = (int) $data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->created_at = $data['created_at'];
        return $user;
    }

    public static function findByEmail(string $email): ?self
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();
        return $data ? self::fromArray($data) : null;
    }

    public static function findById(int $id): ?self
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();
        return $data ? self::fromArray($data) : null;
    }

    public static function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $query = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];
        if ($ignoreId !== null) {
            $query .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }
        $stmt = self::db()->prepare($query);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public static function create(string $name, string $email, string $password): self
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, NOW())'
            );
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash,
            ]);
            $userId = (int) $pdo->lastInsertId();
            self::seedDefaultStages($userId);
            $pdo->commit();

            $user = self::findById($userId);
            if (!$user) {
                throw new \RuntimeException('Não foi possível carregar o usuário recém-criado.');
            }
            return $user;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = self::db()->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = self::db()->prepare('UPDATE users SET password_hash = :password WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'password' => $hash,
        ]);
    }

    private static function seedDefaultStages(int $userId): void
    {
        $stmt = self::db()->prepare('INSERT INTO stages (user_id, name, position, created_at) VALUES (:user_id, :name, :position, NOW())');
        $defaults = [
            ['name' => 'Novo', 'position' => 1],
            ['name' => 'Contato', 'position' => 2],
            ['name' => 'Proposta', 'position' => 3],
            ['name' => 'Fechado', 'position' => 4],
        ];

        foreach ($defaults as $stage) {
            $stmt->execute([
                'user_id' => $userId,
                'name' => $stage['name'],
                'position' => $stage['position'],
            ]);
        }
    }
}

