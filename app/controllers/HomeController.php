<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

final class HomeController extends Controller
{
    public function index(): void
    {
        if ($this->currentUser()) {
            $this->redirect('/dashboard');
        }

        $this->render('home/landing', [
            'flash' => Session::getFlash('flash'),
        ]);
    }
}

