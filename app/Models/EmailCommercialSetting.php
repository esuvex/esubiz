<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCommercialSetting extends Model
{
    protected $table = 'email_commercial_settings';

    protected $fillable = [
        'key',
        'value',
        'value_type',
        'description',
    ];

    public static function value(string $key, mixed $default = null): mixed
    {
        $value = static::query()
            ->where('key', $key)
            ->value('value');

        return $value ?? $default;
    }

    public static function number(string $key, float $default = 0): float
    {
        return (float) static::value($key, $default);
    }

    public static function string(string $key, string $default = ''): string
    {
        return (string) static::value($key, $default);
    }
}
