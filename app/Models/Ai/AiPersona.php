<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiPersona extends Model
{
    protected $table =
        'ai_personas';


    protected $fillable = [
        'name',
        'avatar_path',
        'gender',
        'description',
        'persona_prompt',
        'is_active',
        'is_default',
        'sort_order',
    ];


    protected $casts = [
        'is_active' =>
            'boolean',

        'is_default' =>
            'boolean',

        'sort_order' =>
            'integer',
    ];


    public function avatarUrl(): ?string
    {
        $path =
            trim(
                (string) (
                    $this->avatar_path
                    ?? ''
                )
            );

        if ($path === '') {
            return null;
        }

        if (
            str_starts_with(
                $path,
                'http://'
            )
            || str_starts_with(
                $path,
                'https://'
            )
        ) {
            return $path;
        }

        return asset(
            ltrim(
                $path,
                '/'
            )
        );
    }
}
