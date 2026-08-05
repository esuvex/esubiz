<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    /**
     * Display all websites owned by the authenticated user.
     */
    public function index()
    {
        $websites = Website::where('owner_id', Auth::id())
            ->latest()
            ->get();

        return view('websites.index', compact('websites'));
    }

    /**
     * Store a new website.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        Website::create([
            'owner_id'     => Auth::id(),
            'workspace_id' => null,
            'name'         => $validated['name'],
            'slug'         => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'status'       => 'draft',
        ]);

        return redirect()
            ->back()
            ->with('success', 'Website created successfully.');
    }

    /**
     * Display a website dashboard.
     */
    public function show(Website $website)
    {
        abort_unless($website->owner_id === Auth::id(), 403);

        return view('websites.show', compact('website'));
    }
}
