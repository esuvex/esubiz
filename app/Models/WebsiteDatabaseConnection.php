<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteDatabaseConnection extends Model
{
    protected $fillable = [
        'website_id',
        'host',
        'port',
        'database',
        'status',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    public static function databaseNameFor(Website $website): string
    {
        return 'esubiz_' . strtolower($website->website_code);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
