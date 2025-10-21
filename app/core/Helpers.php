<?php

declare(strict_types=1);

namespace App\Core;

final class Helpers
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function formatCurrency(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    public static function formatDate(?string $datetime): string
    {
        if (!$datetime) {
            return '';
        }

        $date = new \DateTime($datetime);
        return $date->format('d/m/Y H:i');
    }
}

