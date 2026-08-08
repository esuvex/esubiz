<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        session(['account_mode' => 'developer']);

        return view('developer.index');
    }
}
