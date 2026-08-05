<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteRequest;
use App\Services\WebsiteService;
use Illuminate\Http\JsonResponse;

class WebsiteController extends Controller
{
    public function __construct(
        protected WebsiteService $websiteService
    ) {
    }

    /**
     * Store a newly created website.
     */
    public function store(StoreWebsiteRequest $request): JsonResponse
    {
        $website = $this->websiteService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Website created successfully.',
            'website' => $website,
        ], 201);
    }
}
