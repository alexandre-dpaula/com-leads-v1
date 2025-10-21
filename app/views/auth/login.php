<?php

use App\Core\Helpers;

/** @var array $errors */
/** @var array $old */

$title = 'Entrar — ' . APP_NAME;
$user = null;
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto mt-10 max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-900">Bem-vindo de volta</h1>
    <p class="mt-2 text-sm text-slate-600">Acesse sua conta para continuar gerenciando seus leads.</p>

    <form class="mt-8 space-y-6" method="post" action="/login" novalidate>
        <?php include __DIR__ . '/../partials/csrf.php'; ?>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
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
            <div class="flex items-center justify-between">
                <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
                <a href="/password/forgot" class="text-xs text-indigo-600 hover:text-indigo-700">Esqueceu a senha?</a>
            </div>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            <?php if (!empty($errors['password'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['password'][0]) ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="w-full rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Entrar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Não tem conta? <a href="/register" class="text-indigo-600 hover:text-indigo-700">Cadastre-se agora</a>.
    </p>
</main>

<?php
$scripts = [];
include __DIR__ . '/../partials/footer.php';
