<?php

use App\Core\Helpers;

/** @var array $errors */
/** @var array $old */

$title = 'Criar conta — ' . APP_NAME;
$user = null;
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto mt-10 max-w-lg rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-900">Crie sua conta</h1>
    <p class="mt-2 text-sm text-slate-600">
        Comece a organizar seus leads em minutos. É rápido e gratuito.
    </p>

    <form class="mt-8 space-y-6" method="post" action="/register" novalidate>
        <?php include __DIR__ . '/../partials/csrf.php'; ?>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nome completo</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                autocomplete="name"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                value="<?= Helpers::e($old['name'] ?? '') ?>"
            >
            <?php if (!empty($errors['name'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['name'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">E-mail profissional</label>
            <input
                type="email"
                id="email"
                name="email"
                required
                autocomplete="email"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                value="<?= Helpers::e($old['email'] ?? '') ?>"
            >
            <?php if (!empty($errors['email'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['email'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
                minlength="8"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            <p class="mt-1 text-xs text-slate-500">Mínimo 8 caracteres.</p>
            <?php if (!empty($errors['password'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['password'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar senha</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            <?php if (!empty($errors['password_confirmation'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['password_confirmation'][0]) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="w-full rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Criar conta
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Já tem conta? <a href="/login" class="text-indigo-600 hover:text-indigo-700">Entre aqui</a>.
    </p>
</main>

<?php
$scripts = [];
include __DIR__ . '/../partials/footer.php';
