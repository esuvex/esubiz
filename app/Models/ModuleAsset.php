<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_id',
        'uuid',
        'name',
        'type',
        'disk',
        'path',
        'mime_type',
        'size',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
