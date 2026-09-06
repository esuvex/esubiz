<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;

class SitePageController extends Controller
{
    public function index()
    {
        $pages = SitePage::query()
            ->orderBy('title')
            ->get();

        return view('admin.site-pages.index', compact('pages'));
    }
}