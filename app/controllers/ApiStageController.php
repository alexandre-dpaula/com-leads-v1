<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Lead;
use App\Models\Stage;

final class ApiStageController extends Controller
{
    public function store(): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $payload = $this->jsonPayload();
        $name = trim((string) ($payload['name'] ?? ''));

        $validator = new Validator();
        $validator->require('name', $name, 'Informe o nome da etapa.');

        if ($validator->fails()) {
            Response::json(['errors' => $validator->errors()], 422);
            return;
        }

        $position = Stage::nextPosition($user->id);
        $stage = Stage::create($user->id, $name, $position);

        Response::json([
            'data' => [
                'id' => $stage->id,
                'name' => $stage->name,
                'position' => $stage->position,
            ],
        ], 201);
    }

    public function update(int $id): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $payload = $this->jsonPayload();
        $name = trim((string) ($payload['name'] ?? ''));
        $validator = new Validator();
        $validator->require('name', $name, 'Informe o nome da etapa.');

        if ($validator->fails()) {
            Response::json(['errors' => $validator->errors()], 422);
            return;
        }

        $stage = Stage::findById($id, $user->id);
        if (!$stage) {
            Response::json(['message' => 'Etapa não encontrada.'], 404);
            return;
        }

        Stage::update($stage->id, $user->id, $name);
        Response::json(['data' => ['id' => $stage->id, 'name' => $name]]);
    }

    public function delete(int $id): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $payload = $this->jsonPayload();
        $targetStageId = isset($payload['target_stage_id']) ? (int) $payload['target_stage_id'] : null;

        $stage = Stage::findById($id, $user->id);
        if (!$stage) {
            Response::json(['message' => 'Etapa não encontrada.'], 404);
            return;
        }

        if (Lead::countInStage($stage->id, $user->id) > 0) {
            if (!$targetStageId) {
                Response::json([
                    'message' => 'Não é possível excluir uma etapa com leads. Forneça um target_stage_id para mover os leads.',
                    'requires_target_stage' => true,
                ], 409);
                return;
            }

            $targetStage = Stage::findById($targetStageId, $user->id);
            if (!$targetStage) {
                Response::json(['message' => 'Etapa destino inválida.'], 422);
                return;
            }

            if ($targetStage->id === $stage->id) {
                Response::json(['message' => 'A etapa destino deve ser diferente.'], 422);
                return;
            }

            Lead::moveAllToStage($stage->id, $targetStage->id, $user->id);
            Lead::reindexPositions($targetStage->id, $user->id);
        }

        Stage::delete($stage->id, $user->id);
        Response::json(['message' => 'Etapa removida.']);
    }

    public function reorder(): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $payload = $this->jsonPayload();
        $order = $payload['order'] ?? [];

        if (!is_array($order) || empty($order)) {
            Response::json(['message' => 'Ordem inválida.'], 422);
            return;
        }

        $stageIds = array_map('intval', $order);
        Stage::reorder($user->id, $stageIds);
        Response::json(['message' => 'Ordem atualizada.']);
    }

    private function jsonPayload(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

