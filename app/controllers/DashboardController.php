<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Lead;
use App\Models\Stage;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();

        $search = trim((string) ($_GET['search'] ?? ''));
        $tag = trim((string) ($_GET['tag'] ?? ''));

        $stages = Stage::allForUser($user->id);
        $leads = Lead::allForUserGroupedByStage($user->id, $search !== '' ? $search : null, $tag !== '' ? $tag : null);

        $this->render('dashboard/kanban', [
            'user' => $user,
            'stages' => $stages,
            'leads' => $leads,
            'search' => $search,
            'tag' => $tag,
            'flash' => Session::getFlash('flash'),
        ]);
    }
}

