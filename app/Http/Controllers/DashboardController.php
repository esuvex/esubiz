<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Esubiz Dashboard.
     */
    public function index()
    {
        return view('dashboard.index');
    }
}
