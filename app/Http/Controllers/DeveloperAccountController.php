<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeveloperAccountController extends Controller
{
    /**
     * Begin the developer account onboarding process.
     */
    public function create(Request $request)
    {
        return view('developer-account.create');
    }
}
