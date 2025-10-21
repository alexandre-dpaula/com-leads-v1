<?php

use App\Core\Helpers;

$isAuthenticated = isset($user) && $user !== null;
?>
<header class="bg-white shadow-sm">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
        <a class="flex items-center gap-2 text-lg font-semibold text-slate-800" href="<?= $isAuthenticated ? '/dashboard' : '/' ?>">
            <img src="/assets/logo.svg" alt="Logo" class="h-8 w-8">
            <span><?= Helpers::e(APP_NAME) ?></span>
        </a>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
            <?php if ($isAuthenticated): ?>
                <a class="hover:text-indigo-600 transition-colors" href="/dashboard">Dashboard</a>
                <a class="hover:text-indigo-600 transition-colors" href="/profile">Perfil</a>
                <form class="inline" method="post" action="/logout">
                    <input type="hidden" name="_csrf" value="<?= Helpers::e(\App\Core\Csrf::token()) ?>">
                    <button type="submit" class="rounded border border-slate-200 px-3 py-1 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                        Sair
                    </button>
                </form>
            <?php else: ?>
                <a class="hover:text-indigo-600 transition-colors" href="/login">Entrar</a>
                <a class="rounded bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700 transition-colors" href="/register">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
