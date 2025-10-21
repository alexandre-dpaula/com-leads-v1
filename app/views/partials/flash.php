<?php

use App\Core\Helpers;

if (!empty($flash) && is_array($flash)):
    $type = $flash['type'] ?? 'info';
    $baseClasses = 'rounded border px-4 py-3 text-sm';
    $styles = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'error' => 'border-rose-200 bg-rose-50 text-rose-700',
        'info' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
    ];
    $class = $baseClasses . ' ' . ($styles[$type] ?? $styles['info']);
?>
    <div class="mx-auto mt-4 max-w-3xl">
        <div class="<?= $class ?>" role="alert">
            <p class="font-medium"><?= Helpers::e($flash['message'] ?? '') ?></p>
            <?php if (!empty($flash['debug_token'])): ?>
                <p class="mt-2 text-xs text-slate-500">
                    Token (ambiente de testes): <span class="font-mono"><?= Helpers::e($flash['debug_token']) ?></span>
                </p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
