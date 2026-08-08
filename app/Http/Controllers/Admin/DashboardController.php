<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        session(['account_mode' => 'admin']);

        return view('admin.index');
    }
}
