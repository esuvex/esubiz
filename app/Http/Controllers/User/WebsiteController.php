<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Display the user's websites.
     */
    public function index(): View
    {
        $websites = auth()->user()
            ->websites()
            ->latest()
            ->get();

        return view('user.websites.index', [
            'websites' => $websites,
        ]);
    }
}
