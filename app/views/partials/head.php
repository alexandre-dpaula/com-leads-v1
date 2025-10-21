<?php

use App\Core\Csrf;
use App\Core\Helpers;

$pageTitle = $title ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Helpers::e($pageTitle) ?></title>
    <meta name="csrf-token" content="<?= Helpers::e(Csrf::token()) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="/assets/logo.svg">
    <script>
        window.APP = {
            baseUrl: <?= json_encode(APP_BASE_URL ?: '') ?>,
            csrfToken: <?= json_encode(Csrf::token()) ?>
        };
    </script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
