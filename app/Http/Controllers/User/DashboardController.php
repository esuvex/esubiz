<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\WebsiteDraftService;

class DashboardController extends Controller
{
    public function index()
    {
        $draftService = app(WebsiteDraftService::class);

        $draft = $draftService->latestDraft();

        return view('user.index', [
            'draft' => $draft,
        ]);
    }
}
