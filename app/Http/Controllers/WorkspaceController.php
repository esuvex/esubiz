<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    /**
     * List all workspaces owned by the authenticated user.
     */
    public function index()
    {
        $workspaces = Workspace::where('owner_id', Auth::id())
            ->orderBy('name')
            ->get();

        return view('workspaces.index', compact('workspaces'));
    }

    /**
     * Store a new workspace.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $workspace = Workspace::create([
            'owner_id'    => Auth::id(),
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']) . '-' . Str::lower(Str::random(6)),
            'status'      => 'active',
            'timezone'    => config('app.timezone'),
            'country'     => null,
            'currency'    => null,
            'description' => null,
            'logo'        => null,
        ]);

        $workspace->members()->create([
            'user_id'   => Auth::id(),
            'role'      => 'Owner',
            'status'    => 'active',
            'joined_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Workspace created successfully.');
    }
}
