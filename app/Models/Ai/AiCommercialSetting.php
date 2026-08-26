<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiCommercialSetting extends Model
{
    protected $connection = 'mysql';

    protected $table =
        'ai_commercial_settings';


    protected $fillable = [
        'key',
        'label',
        'value',
        'unit',
        'is_active',
        'metadata',
    ];


    protected $casts = [
        'value' =>
            'decimal:8',

        'is_active' =>
            'boolean',

        'metadata' =>
            'array',
    ];


    public static function number(
        string $key,
        float $default = 0
    ): float {

        $value =
            static::query()
                ->where(
                    'key',
                    $key
                )
                ->where(
                    'is_active',
                    true
                )
                ->value(
                    'value'
                );


        if ($value === null) {
            return $default;
        }


        return (float) $value;
    }
}
