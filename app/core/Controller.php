<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new \RuntimeException('View não encontrada: ' . $view);
        }

        include $viewPath;
    }

    protected function redirect(string $path): void
    {
        $base = \APP_BASE_URL;
        $location = $base ? rtrim($base, '/') . $path : $path;
        header('Location: ' . $location);
        exit;
    }

    protected function currentUser(): ?User
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }

        static $user;
        if (!$user) {
            $user = User::findById((int) $userId);
        }

        return $user;
    }

    protected function requireAuth(): User
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->redirect('/login');
        }

        return $user;
    }
}

