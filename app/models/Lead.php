<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Lead extends Model
{
    public int $id;
    public int $user_id;
    public int $stage_id;
    public string $name;
    public ?string $company;
    public ?string $email;
    public ?string $phone;
    public ?string $value;
    public ?string $tags;
    public ?string $notes;
    public int $position;
    public string $created_at;
    public string $updated_at;

    public static function fromArray(array $data): self
    {
        $lead = new self();
        $lead->id = (int) $data['id'];
        $lead->user_id = (int) $data['user_id'];
        $lead->stage_id = (int) $data['stage_id'];
        $lead->name = $data['name'];
        $lead->company = $data['company'] ?? null;
        $lead->email = $data['email'] ?? null;
        $lead->phone = $data['phone'] ?? null;
        $lead->value = $data['value'] !== null ? (string) $data['value'] : null;
        $lead->tags = $data['tags'] ?? null;
        $lead->notes = $data['notes'] ?? null;
        $lead->position = (int) $data['position'];
        $lead->created_at = $data['created_at'];
        $lead->updated_at = $data['updated_at'];
        return $lead;
    }

    /**
     * @return Lead[]
     */
    public static function allForUserGroupedByStage(int $userId, ?string $search = null, ?string $tag = null): array
    {
        $params = ['user_id' => $userId];
        $filters = '';

        if ($search) {
            $filters .= ' AND (l.name LIKE :search OR l.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($tag) {
            $filters .= ' AND l.tags LIKE :tag';
            $params['tag'] = '%' . $tag . '%';
        }

        $stmt = self::db()->prepare(
            'SELECT l.* FROM leads l WHERE l.user_id = :user_id' . $filters . ' ORDER BY l.stage_id, l.position'
        );
        $stmt->execute($params);

        $grouped = [];
        while ($row = $stmt->fetch()) {
            $lead = self::fromArray($row);
            $grouped[$lead->stage_id][] = $lead;
        }

        return $grouped;
    }

    public static function nextPosition(int $stageId, int $userId): int
    {
        $stmt = self::db()->prepare(
            'SELECT COALESCE(MAX(position), 0) + 1 FROM leads WHERE stage_id = :stage_id AND user_id = :user_id'
        );
        $stmt->execute([
            'stage_id' => $stageId,
            'user_id' => $userId,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(array $data): self
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO leads (user_id, stage_id, name, company, email, phone, value, tags, notes, position, created_at, updated_at)
             VALUES (:user_id, :stage_id, :name, :company, :email, :phone, :value, :tags, :notes, :position, NOW(), NOW())'
        );
        $stmt->execute([
            'user_id' => $data['user_id'],
            'stage_id' => $data['stage_id'],
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'value' => $data['value'] ?? null,
            'tags' => $data['tags'] ?? null,
            'notes' => $data['notes'] ?? null,
            'position' => $data['position'],
        ]);

        $id = (int) $pdo->lastInsertId();
        $lead = self::findById($id, (int) $data['user_id']);
        if (!$lead) {
            throw new \RuntimeException('Não foi possível criar o lead.');
        }
        return $lead;
    }

    public static function findById(int $id, int $userId): ?self
    {
        $stmt = self::db()->prepare('SELECT * FROM leads WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
        $data = $stmt->fetch();
        return $data ? self::fromArray($data) : null;
    }

    public static function update(int $id, int $userId, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE leads SET stage_id = :stage_id, name = :name, company = :company, email = :email,
             phone = :phone, value = :value, tags = :tags, notes = :notes, updated_at = NOW()
             WHERE id = :id AND user_id = :user_id'
        );

        $stmt->execute([
            'stage_id' => $data['stage_id'],
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'value' => $data['value'] ?? null,
            'tags' => $data['tags'] ?? null,
            'notes' => $data['notes'] ?? null,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public static function delete(int $id, int $userId): void
    {
        $stmt = self::db()->prepare('DELETE FROM leads WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public static function updatePosition(int $id, int $userId, int $stageId, int $position): void
    {
        $pdo = Database::connection();

        $pdo->beginTransaction();
        try {
            // Shift positions of leads in target stage to make room
            $stmtShift = $pdo->prepare(
                'UPDATE leads SET position = position + 1
                 WHERE stage_id = :stage_id AND user_id = :user_id AND position >= :position'
            );
            $stmtShift->execute([
                'stage_id' => $stageId,
                'user_id' => $userId,
                'position' => $position,
            ]);

            $stmt = $pdo->prepare(
                'UPDATE leads SET stage_id = :stage_id, position = :position, updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                'stage_id' => $stageId,
                'position' => $position,
                'id' => $id,
                'user_id' => $userId,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function reindexPositions(int $stageId, int $userId): void
    {
        $pdo = self::db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id FROM leads WHERE stage_id = :stage_id AND user_id = :user_id ORDER BY position'
            );
            $stmt->execute([
                'stage_id' => $stageId,
                'user_id' => $userId,
            ]);

            $leads = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $update = $pdo->prepare('UPDATE leads SET position = :position WHERE id = :id');
            $position = 1;
            foreach ($leads as $leadId) {
                $update->execute([
                    'position' => $position,
                    'id' => $leadId,
                ]);
                $position++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function countInStage(int $stageId, int $userId): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) FROM leads WHERE stage_id = :stage_id AND user_id = :user_id');
        $stmt->execute([
            'stage_id' => $stageId,
            'user_id' => $userId,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function moveAllToStage(int $fromStageId, int $toStageId, int $userId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $nextPosition = self::nextPosition($toStageId, $userId);
            $stmt = $pdo->prepare(
                'SELECT id FROM leads WHERE stage_id = :from_stage AND user_id = :user_id ORDER BY position'
            );
            $stmt->execute([
                'from_stage' => $fromStageId,
                'user_id' => $userId,
            ]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $update = $pdo->prepare(
                'UPDATE leads SET stage_id = :stage_id, position = :position, updated_at = NOW() WHERE id = :id'
            );

            foreach ($ids as $id) {
                $update->execute([
                    'stage_id' => $toStageId,
                    'position' => $nextPosition,
                    'id' => $id,
                ]);
                $nextPosition++;
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}

