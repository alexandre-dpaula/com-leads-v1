<?php

use App\Core\Helpers;

/** @var array $errors */

$title = 'Redefinir senha — ' . APP_NAME;
$user = null;
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto mt-10 max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-900">Defina uma nova senha</h1>
    <p class="mt-2 text-sm text-slate-600">
        Escolha uma senha forte para proteger sua conta.
    </p>

    <form class="mt-8 space-y-6" method="post" action="/password/reset" novalidate>
        <?php include __DIR__ . '/../partials/csrf.php'; ?>
        <input type="hidden" name="token" value="<?= Helpers::e($token ?? '') ?>">

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Nova senha</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="new-password"
                minlength="8"
                class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
            >
            <?php if (!empty($errors['password'])): ?>
                <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['password'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar nova senha</label>
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
            Atualizar senha
        </button>
    </form>
</main>

<?php
$scripts = [];
include __DIR__ . '/../partials/footer.php';
