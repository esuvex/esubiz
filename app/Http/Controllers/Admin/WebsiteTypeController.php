<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteType;
use Illuminate\View\View;

class WebsiteTypeController extends Controller
{
    public function index(): View
    {
        $websiteTypes = WebsiteType::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.website-types.index', [
            'websiteTypes' => $websiteTypes,
        ]);
    }
}
