<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiSettingsController extends Controller
{
    public function index()
    {
        $chat =
            DB::table(
                'central_ai_chat_settings'
            )
                ->orderBy('id')
                ->first();

        return view(
            'admin.ai.settings',
            [
                'chat' => $chat,
            ]
        );
    }


    public function updateChat(
        Request $request
    ) {
        $data =
            $request->validate([
                'ai_bubble_color' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'ai_text_color' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'user_bubble_color' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                'user_text_color' => [
                    'required',
                    'regex:/^#[0-9A-Fa-f]{6}$/',
                ],

                /*
                 * Global Esubiz AI chat limits.
                 */
                'max_message_characters' => [
                    'required',
                    'integer',
                    'min:100',
                    'max:100000',
                ],

                'max_conversation_messages' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:1000',
                ],

                'max_photos_per_message' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:20',
                ],

                'max_photo_size_mb' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:50',
                ],
            ]);

        $existing =
            DB::table(
                'central_ai_chat_settings'
            )
                ->orderBy('id')
                ->first();

        if ($existing) {
            DB::table(
                'central_ai_chat_settings'
            )
                ->where(
                    'id',
                    $existing->id
                )
                ->update([
                    ...$data,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table(
                'central_ai_chat_settings'
            )
                ->insert([
                    ...$data,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        return back()->with(
            'success',
            'Global AI chat settings updated successfully.'
        );
    }
}
