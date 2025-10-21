<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Lead;
use App\Models\Stage;

final class ApiLeadController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $search = isset($_GET['search']) ? trim((string) $_GET['search']) : null;
        $tag = isset($_GET['tag']) ? trim((string) $_GET['tag']) : null;

        $leads = Lead::allForUserGroupedByStage($user->id, $search, $tag);
        $formatted = [];

        foreach ($leads as $stageId => $items) {
            $formatted[(string) $stageId] = array_map(static function (Lead $lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'company' => $lead->company,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'value' => $lead->value,
                    'tags' => $lead->tags,
                    'notes' => $lead->notes,
                    'stage_id' => $lead->stage_id,
                    'position' => $lead->position,
                    'updated_at' => $lead->updated_at,
                ];
            }, $items);
        }

        Response::json(['data' => $formatted]);
    }

    public function store(): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $payload = $this->jsonPayload();
        $validator = $this->leadValidator($payload);

        if ($validator->fails()) {
            Response::json(['errors' => $validator->errors()], 422);
            return;
        }

        $stageId = (int) $payload['stage_id'];
        $stage = Stage::findById($stageId, $user->id);
        if (!$stage) {
            Response::json(['message' => 'Etapa inválida.'], 422);
            return;
        }

        $position = Lead::nextPosition($stage->id, $user->id);

        $lead = Lead::create([
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'name' => $payload['name'],
            'company' => $payload['company'] ?? null,
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'value' => $payload['value'] ?? null,
            'tags' => $payload['tags'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'position' => $position,
        ]);

        Response::json([
            'data' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'stage_id' => $lead->stage_id,
                'position' => $lead->position,
                'company' => $lead->company,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'value' => $lead->value,
                'tags' => $lead->tags,
                'notes' => $lead->notes,
                'updated_at' => $lead->updated_at,
            ],
        ], 201);
    }

    public function update(int $id): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $lead = Lead::findById($id, $user->id);
        if (!$lead) {
            Response::json(['message' => 'Lead não encontrado.'], 404);
            return;
        }

        $payload = $this->jsonPayload();
        $validator = $this->leadValidator($payload);

        if ($validator->fails()) {
            Response::json(['errors' => $validator->errors()], 422);
            return;
        }

        $stage = Stage::findById((int) $payload['stage_id'], $user->id);
        if (!$stage) {
            Response::json(['message' => 'Etapa inválida.'], 422);
            return;
        }

        Lead::update($lead->id, $user->id, [
            'stage_id' => $stage->id,
            'name' => $payload['name'],
            'company' => $payload['company'] ?? null,
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'value' => $payload['value'] ?? null,
            'tags' => $payload['tags'] ?? null,
            'notes' => $payload['notes'] ?? null,
        ]);

        Response::json(['message' => 'Lead atualizado.']);
    }

    public function delete(int $id): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $lead = Lead::findById($id, $user->id);
        if (!$lead) {
            Response::json(['message' => 'Lead não encontrado.'], 404);
            return;
        }

        Lead::delete($lead->id, $user->id);
        Response::json(['message' => 'Lead removido.']);
    }

    public function move(int $id): void
    {
        $user = $this->requireAuth();
        Csrf::assertValidFromRequest();

        $lead = Lead::findById($id, $user->id);
        if (!$lead) {
            Response::json(['message' => 'Lead não encontrado.'], 404);
            return;
        }

        $payload = $this->jsonPayload();
        $stageId = isset($payload['stage_id']) ? (int) $payload['stage_id'] : 0;
        $position = isset($payload['position']) ? (int) $payload['position'] : 0;

        if ($stageId <= 0 || $position <= 0) {
            Response::json(['message' => 'Dados inválidos.'], 422);
            return;
        }

        $stage = Stage::findById($stageId, $user->id);
        if (!$stage) {
            Response::json(['message' => 'Etapa inválida.'], 422);
            return;
        }

        Lead::updatePosition($lead->id, $user->id, $stage->id, $position);
        Lead::reindexPositions($stage->id, $user->id);

        if ($lead->stage_id !== $stage->id) {
            Lead::reindexPositions($lead->stage_id, $user->id);
        }

        Response::json(['message' => 'Lead movido.']);
    }

    private function leadValidator(array $payload): Validator
    {
        $validator = new Validator();
        $validator->require('name', $payload['name'] ?? null, 'Informe o nome do lead.')
            ->require('stage_id', isset($payload['stage_id']) ? (string) $payload['stage_id'] : null, 'Selecione uma etapa.')
            ->email('email', $payload['email'] ?? null, 'E-mail inválido.')
            ->numeric('value', $payload['value'] ?? null, 'Valor do lead precisa ser numérico.');

        return $validator;
    }

    private function jsonPayload(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

