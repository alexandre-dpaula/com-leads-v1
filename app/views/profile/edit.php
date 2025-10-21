<?php

use App\Core\Helpers;

/** @var \App\Models\User $user */
/** @var array $errors */

$title = 'Meu perfil — ' . APP_NAME;
include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/navbar.php';
include __DIR__ . '/../partials/flash.php';
?>

<main class="mx-auto mt-10 max-w-3xl rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-900">Perfil</h1>
    <p class="mt-1 text-sm text-slate-600">Atualize suas informações pessoais e credenciais de acesso.</p>

    <form class="mt-8 space-y-6" method="post" action="/profile" novalidate>
        <?php include __DIR__ . '/../partials/csrf.php'; ?>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nome</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    autocomplete="name"
                    value="<?= Helpers::e($user->name) ?>"
                    class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                <?php if (!empty($errors['name'])): ?>
                    <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['name'][0]) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= Helpers::e($user->email) ?>"
                    class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['email'][0]) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="border-dashed border-slate-200">

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Nova senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    minlength="8"
                    class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                <p class="mt-1 text-xs text-slate-500">Deixe em branco para manter a senha atual.</p>
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
                    autocomplete="new-password"
                    class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                >
                <?php if (!empty($errors['password_confirmation'])): ?>
                    <p class="mt-1 text-xs text-rose-600"><?= Helpers::e($errors['password_confirmation'][0]) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="/dashboard" class="rounded border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Cancelar</a>
            <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Salvar alterações
            </button>
        </div>
    </form>
</main>

<?php
$scripts = [];
include __DIR__ . '/../partials/footer.php';
