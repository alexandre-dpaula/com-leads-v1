<?php

use App\Core\Helpers;

/** @var \App\Models\Stage[] $stages */
?>
<div id="modal-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>

<dialog id="lead-modal" class="modal hidden" aria-labelledby="lead-modal-title">
    <form method="dialog" class="relative w-full max-w-xl rounded-lg border border-slate-200 bg-white p-6 shadow-xl" data-lead-form>
        <button type="button" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600" data-action="close-modal" aria-label="Fechar modal">
            &times;
        </button>
        <h2 id="lead-modal-title" class="text-lg font-semibold text-slate-900">Lead</h2>
        <p class="mt-1 text-sm text-slate-500">Cadastre ou edite informações completas do lead.</p>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="lead-name" class="block text-sm font-medium text-slate-700">Nome *</label>
                <input type="text" id="lead-name" name="name" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                <p class="mt-1 hidden text-xs text-rose-600" data-error="name"></p>
            </div>
            <div>
                <label for="lead-company" class="block text-sm font-medium text-slate-700">Empresa</label>
                <input type="text" id="lead-company" name="company" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>
            <div>
                <label for="lead-email" class="block text-sm font-medium text-slate-700">E-mail</label>
                <input type="email" id="lead-email" name="email" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                <p class="mt-1 hidden text-xs text-rose-600" data-error="email"></p>
            </div>
            <div>
                <label for="lead-phone" class="block text-sm font-medium text-slate-700">Telefone</label>
                <input type="text" id="lead-phone" name="phone" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>
            <div>
                <label for="lead-value" class="block text-sm font-medium text-slate-700">Valor estimado</label>
                <input type="number" step="0.01" min="0" id="lead-value" name="value" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                <p class="mt-1 hidden text-xs text-rose-600" data-error="value"></p>
            </div>
            <div>
                <label for="lead-stage" class="block text-sm font-medium text-slate-700">Etapa *</label>
                <select id="lead-stage" name="stage_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                    <option value="">Selecione</option>
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= $stage->id ?>"><?= Helpers::e($stage->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 hidden text-xs text-rose-600" data-error="stage_id"></p>
            </div>
            <div class="md:col-span-2">
                <label for="lead-tags" class="block text-sm font-medium text-slate-700">Tags (separe por vírgula)</label>
                <input type="text" id="lead-tags" name="tags" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label for="lead-notes" class="block text-sm font-medium text-slate-700">Notas</label>
                <textarea id="lead-notes" name="notes" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"></textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <button type="button" class="text-sm text-rose-600 hover:text-rose-700" data-action="delete-lead" hidden>Excluir lead</button>
            <div class="ml-auto flex gap-2">
                <button type="button" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" data-action="close-modal">Cancelar</button>
                <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" data-submit>Salvar</button>
            </div>
        </div>
        <input type="hidden" name="id" value="">
    </form>
</dialog>

<dialog id="confirm-lead-delete" class="modal hidden" aria-labelledby="confirm-lead-delete-title">
    <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-xl">
        <h2 id="confirm-lead-delete-title" class="text-lg font-semibold text-slate-900">Excluir lead</h2>
        <p class="mt-3 text-sm text-slate-600">
            Tem certeza que deseja excluir o lead <strong data-lead-name></strong>? Esta ação não pode ser desfeita.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" data-action="close-modal">Cancelar</button>
            <button type="button" class="rounded bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700" data-confirm-delete-lead>Excluir</button>
        </div>
    </div>
</dialog>

<dialog id="stage-modal" class="modal hidden" aria-labelledby="stage-modal-title">
    <form method="dialog" class="relative w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-xl" data-stage-form>
        <button type="button" class="absolute right-4 top-4 text-slate-400 hover:text-slate-600" data-action="close-modal" aria-label="Fechar modal">
            &times;
        </button>
        <h2 id="stage-modal-title" class="text-lg font-semibold text-slate-900">Etapa</h2>
        <p class="mt-1 text-sm text-slate-500">Crie ou renomeie uma etapa do seu funil.</p>
        <div class="mt-6">
            <label for="stage-name" class="block text-sm font-medium text-slate-700">Nome da etapa *</label>
            <input type="text" id="stage-name" name="name" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
            <p class="mt-1 hidden text-xs text-rose-600" data-error="name"></p>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" data-action="close-modal">Cancelar</button>
            <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" data-submit>Salvar</button>
        </div>
        <input type="hidden" name="id" value="">
    </form>
</dialog>

<dialog id="confirm-stage-delete" class="modal hidden" aria-labelledby="confirm-stage-delete-title">
    <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-xl">
        <h2 id="confirm-stage-delete-title" class="text-lg font-semibold text-slate-900">Excluir etapa</h2>
        <p class="mt-3 text-sm text-slate-600">
            Deseja realmente excluir a etapa <strong data-stage-name></strong>? Se existirem leads, você precisará movê-los para outra etapa.
        </p>
        <div class="mt-4 space-y-2" data-stage-transfer hidden>
            <label for="stage-transfer-select" class="block text-sm font-medium text-slate-700">Mover leads para:</label>
            <select id="stage-transfer-select" class="w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= $stage->id ?>"><?= Helpers::e($stage->name) ?></option>
                <?php endforeach; ?>
            </select>
            <p class="text-xs text-rose-600" data-error="target_stage_id" hidden></p>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" data-action="close-modal">Cancelar</button>
            <button type="button" class="rounded bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700" data-confirm-delete-stage>Excluir</button>
        </div>
    </div>
</dialog>
