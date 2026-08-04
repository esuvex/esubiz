<?php

namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('saas.index');
    }
}
