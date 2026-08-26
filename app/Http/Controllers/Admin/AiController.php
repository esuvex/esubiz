<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiPersona;
use App\Services\SiteAi\SiteAiRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function index(
        SiteAiRegistry $registry
    ): View {

        $personas =
            AiPersona::query()
                ->orderByDesc(
                    'is_default'
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get();


        $capabilities =
            collect(
                $registry->all()
            )
            ->map(
                function (
                    $capability,
                    $key
                ) {

                    $definition =
                        method_exists(
                            $capability,
                            'definition'
                        )
                            ? $capability
                                ->definition()
                            : $capability
                                ->manifest();

                    return [
                        'key' =>
                            $key,

                        'label' =>
                            $definition[
                                'label'
                            ] ?? $key,

                        'version' =>
                            $definition[
                                'version'
                            ] ?? null,

                        'functions' =>
                            collect(
                                $definition[
                                    'functions'
                                ] ?? []
                            )
                            ->pluck(
                                'label'
                            )
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                }
            )
            ->values();


        $services = [
            [
                'key' => 'site',
                'label' => 'Site AI',
                'description' =>
                    'Theme, Page Builder, products, content and other internal website AI tools.',
            ],

            [
                'key' => 'live_chat',
                'label' => 'Live Chat AI',
                'description' =>
                    'Customer-facing AI conversations through website live chat.',
            ],

            [
                'key' => 'whatsapp',
                'label' => 'WhatsApp AI',
                'description' =>
                    'AI-powered WhatsApp support, enquiries and automated replies.',
            ],

            [
                'key' => 'email',
                'label' => 'Email AI',
                'description' =>
                    'Drafts, replies, campaigns, follow-ups and subject lines.',
            ],

            [
                'key' => 'sms',
                'label' => 'SMS AI',
                'description' =>
                    'SMS campaigns, reminders, alerts and concise generated messages.',
            ],

            [
                'key' => 'social_media',
                'label' => 'Social Media AI',
                'description' =>
                    'Posts, captions, images, scheduling and publishing assistance.',
            ],

            [
                'key' => 'ads',
                'label' => 'Ads AI',
                'description' =>
                    'Ad concepts, copy, creatives, CTAs and campaign variations.',
            ],

            [
                'key' => 'app_builder',
                'label' => 'App Builder AI',
                'description' =>
                    'Build and assist with Esubiz applications using registered app architecture, components and development capabilities.',
            ],

            [
                'key' => 'module_builder',
                'label' => 'Module Builder AI',
                'description' =>
                    'Build and assist with Esubiz modules using the central module architecture and registered module capabilities.',
            ],

            [
                'key' => 'addon_builder',
                'label' => 'Addon Builder AI',
                'description' =>
                    'Build and assist with Esubiz addons using the shared addon architecture and registered addon capabilities.',
            ],
        ];


        return view(
            'admin.ai.index',
            compact(
                'personas',
                'capabilities',
                'services'
            )
        );
    }
}
