<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Stage extends Model
{
    public int $id;
    public int $user_id;
    public string $name;
    public int $position;
    public string $created_at;

    public static function fromArray(array $data): self
    {
        $stage = new self();
        $stage->id = (int) $data['id'];
        $stage->user_id = (int) $data['user_id'];
        $stage->name = $data['name'];
        $stage->position = (int) $data['position'];
        $stage->created_at = $data['created_at'];
        return $stage;
    }

    /**
     * @return Stage[]
     */
    public static function allForUser(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM stages WHERE user_id = :user_id ORDER BY position ASC');
        $stmt->execute(['user_id' => $userId]);
        return array_map([self::class, 'fromArray'], $stmt->fetchAll());
    }

    public static function findById(int $id, int $userId): ?self
    {
        $stmt = self::db()->prepare('SELECT * FROM stages WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
        $data = $stmt->fetch();
        return $data ? self::fromArray($data) : null;
    }

    public static function create(int $userId, string $name, int $position): self
    {
        $stmt = self::db()->prepare(
            'INSERT INTO stages (user_id, name, position, created_at) VALUES (:user_id, :name, :position, NOW())'
        );
        $stmt->execute([
            'user_id' => $userId,
            'name' => $name,
            'position' => $position,
        ]);

        $id = (int) self::db()->lastInsertId();
        $stage = self::findById($id, $userId);
        if (!$stage) {
            throw new \RuntimeException('Não foi possível criar a etapa.');
        }
        return $stage;
    }

    public static function update(int $id, int $userId, string $name): void
    {
        $stmt = self::db()->prepare('UPDATE stages SET name = :name WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
        ]);
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = self::db()->prepare('DELETE FROM stages WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public static function nextPosition(int $userId): int
    {
        $stmt = self::db()->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM stages WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function reorder(int $userId, array $orderedIds): void
    {
        $pdo = self::db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE stages SET position = :position WHERE id = :id AND user_id = :user_id');
            $position = 1;
            foreach ($orderedIds as $id) {
                $stmt->execute([
                    'position' => $position,
                    'id' => (int) $id,
                    'user_id' => $userId,
                ]);
                $position++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function hasLeads(int $id, int $userId): bool
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM leads WHERE stage_id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
        return (bool) $stmt->fetchColumn();
    }
}
