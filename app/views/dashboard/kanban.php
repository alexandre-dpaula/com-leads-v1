<?php

use App\Core\Helpers;

/** @var \App\Models\User $user */
/** @var \App\Models\Stage[] $stages */
/** @var array<int, \App\Models\Lead[]> $leads */

$title = 'Dashboard — ' . APP_NAME;
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <section class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Olá, <?= Helpers::e($user->name) ?> 👋</h1>
            <p class="mt-2 text-sm text-slate-600">
                Acompanhe seus leads arrastando-os entre as etapas do funil.
            </p>
        </div>
        <div class="flex gap-3">
            <button type="button" data-action="open-new-lead-modal" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Novo lead
            </button>
            <button type="button" data-action="open-new-stage-modal" class="rounded border border-indigo-200 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Nova etapa
            </button>
        </div>
    </section>

    <section class="mb-6">
        <form method="get" action="/dashboard" class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center">
            <div class="flex-1">
                <label for="search" class="sr-only">Buscar leads</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    placeholder="Buscar por nome ou e-mail..."
                    value="<?= Helpers::e($search ?? '') ?>"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
            </div>
            <div class="flex-1">
                <label for="tag" class="sr-only">Filtrar por tag</label>
                <input
                    type="text"
                    id="tag"
                    name="tag"
                    placeholder="Filtrar por tag"
                    value="<?= Helpers::e($tag ?? '') ?>"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Aplicar
                </button>
                <a href="/dashboard" class="rounded border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                    Limpar
                </a>
            </div>
        </form>
        <p class="mt-2 text-xs text-slate-500">
            Dica: arraste o cabeçalho de uma etapa para reordenar as colunas do funil.
        </p>
    </section>

    <section aria-label="Kanban" class="flex overflow-x-auto pb-4" data-kanban-board>
        <div class="flex min-w-full gap-4">
            <?php foreach ($stages as $stage): ?>
                <article
                    class="flex h-full min-w-[18rem] flex-col rounded-lg border border-slate-200 bg-white shadow-sm"
                    draggable="true"
                    data-stage="<?= $stage->id ?>"
                    data-stage-position="<?= $stage->position ?>"
                >
                    <header class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <div>
                            <h2 class="text-base font-semibold text-slate-800"><?= Helpers::e($stage->name) ?></h2>
                            <p class="text-xs text-slate-500">
                                <?= count($leads[$stage->id] ?? []) ?> lead(s)
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="text-xs text-indigo-600 hover:text-indigo-700"
                                data-action="edit-stage"
                                data-stage-id="<?= $stage->id ?>"
                                data-stage-name="<?= Helpers::e($stage->name) ?>"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                class="text-xs text-rose-600 hover:text-rose-700"
                                data-action="delete-stage"
                                data-stage-id="<?= $stage->id ?>"
                                data-stage-name="<?= Helpers::e($stage->name) ?>"
                            >
                                Excluir
                            </button>
                        </div>
                    </header>
                    <div class="grow space-y-3 overflow-y-auto px-4 py-4" data-stage-items role="list" aria-label="<?= Helpers::e($stage->name) ?>">
                        <?php foreach ($leads[$stage->id] ?? [] as $lead): ?>
                            <div
                                class="cursor-grab rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-200 hover:shadow"
                                draggable="true"
                                data-lead-id="<?= $lead->id ?>"
                                data-stage-id="<?= $lead->stage_id ?>"
                                data-position="<?= $lead->position ?>"
                                role="listitem"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?= Helpers::e($lead->name) ?></p>
                                        <?php if ($lead->company): ?>
                                            <p class="text-xs text-slate-500"><?= Helpers::e($lead->company) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-xs text-indigo-600 hover:text-indigo-700"
                                        data-action="edit-lead"
                                        data-lead='<?= Helpers::e(json_encode([
                                            'id' => $lead->id,
                                            'name' => $lead->name,
                                            'company' => $lead->company,
                                            'email' => $lead->email,
                                            'phone' => $lead->phone,
                                            'value' => $lead->value,
                                            'tags' => $lead->tags,
                                            'notes' => $lead->notes,
                                            'stage_id' => $lead->stage_id,
                                        ])) ?>'
                                    >
                                        Detalhes
                                    </button>
                                </div>
                                <div class="mt-3 space-y-2 text-xs text-slate-600">
                                    <?php if ($lead->email): ?>
                                        <p class="flex items-center gap-2">
                                            <span class="font-medium">E-mail:</span>
                                            <a href="mailto:<?= Helpers::e($lead->email) ?>" class="text-indigo-600 hover:underline"><?= Helpers::e($lead->email) ?></a>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($lead->phone): ?>
                                        <p class="flex items-center gap-2">
                                            <span class="font-medium">Telefone:</span>
                                            <a href="tel:<?= Helpers::e($lead->phone) ?>" class="text-indigo-600 hover:underline"><?= Helpers::e($lead->phone) ?></a>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($lead->value !== null && $lead->value !== ''): ?>
                                        <p><span class="font-medium">Valor:</span> <?= Helpers::formatCurrency($lead->value) ?></p>
                                    <?php endif; ?>
                                    <?php if ($lead->tags): ?>
                                        <p class="flex flex-wrap gap-1">
                                            <?php foreach (explode(',', $lead->tags) as $tagValue): ?>
                                                <span class="rounded bg-indigo-50 px-2 py-1 text-[10px] font-medium uppercase text-indigo-600">
                                                    <?= Helpers::e(trim($tagValue)) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-[11px] text-slate-400">
                                        Atualizado em <?= Helpers::formatDate($lead->updated_at) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <footer class="border-t border-slate-200 px-4 py-3">
                        <button
                            type="button"
                            class="w-full rounded border border-dashed border-slate-300 px-3 py-2 text-sm text-slate-500 hover:border-indigo-400 hover:text-indigo-600"
                            data-action="open-lead-modal"
                            data-stage-id="<?= $stage->id ?>"
                        >
                            + Adicionar lead
                        </button>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/modals.php'; ?>

<?php
$scripts = ['/js/kanban.js'];
include __DIR__ . '/../partials/footer.php';
