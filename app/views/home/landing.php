<?php

/** @var array|null $flash */

use App\Core\Helpers;

$title = APP_NAME . ' — Organize seus leads com facilidade';
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto flex max-w-6xl flex-col items-center gap-16 px-4 py-16 text-center md:flex-row md:text-left">
    <div class="flex-1">
        <h1 class="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
            Centralize seu funil de vendas com o <?= Helpers::e(APP_NAME) ?>
        </h1>
        <p class="mt-6 text-lg leading-relaxed text-slate-600">
            Gerencie leads, acompanhe negociações e mantenha seu pipeline sempre atualizado com nosso CRM simples e
            eficiente. Ideal para equipes enxutas que precisam de visibilidade e controle.
        </p>
        <div class="mt-8 flex flex-col gap-3 md:flex-row">
            <a href="/register" class="inline-flex items-center justify-center rounded bg-indigo-600 px-6 py-3 text-white shadow hover:bg-indigo-700 transition-colors">
                Criar minha conta
            </a>
            <a href="/login" class="inline-flex items-center justify-center rounded border border-indigo-200 px-6 py-3 text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50 transition-colors">
                Já tenho conta
            </a>
        </div>
    </div>
    <div class="flex-1">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Benefícios principais</h2>
            <ul class="mt-6 space-y-3 text-left text-sm text-slate-600">
                <li class="flex items-start gap-2">
                    <span class="mt-1 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">1</span>
                    <span>Kanban arrastável para visualizar seu funil em tempo real.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">2</span>
                    <span>Leads com dados completos, notas e tags para organizar oportunidades.</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="mt-1 inline-flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">3</span>
                    <span>Multi-usuário isolado: cada conta enxerga apenas seus próprios dados.</span>
                </li>
            </ul>
        </div>
    </div>
</main>

<section class="bg-white py-12">
    <div class="mx-auto max-w-6xl px-4">
        <h2 class="text-center text-2xl font-semibold text-slate-900 md:text-3xl">
            Simplifique sua operação comercial hoje mesmo
        </h2>
        <p class="mt-4 text-center text-base text-slate-600">
            Sem instalações complicadas, pronto para usar em qualquer hospedagem PHP.
        </p>
        <div class="mt-8 flex justify-center">
            <a href="/register" class="rounded bg-indigo-600 px-6 py-3 text-white shadow hover:bg-indigo-700 transition-colors">
                Começar agora
            </a>
        </div>
    </div>
</section>

<?php
$scripts = [];
include __DIR__ . '/../partials/footer.php';
