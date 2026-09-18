<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_id',
        'uuid',
        'version',
        'release_notes',
        'changes',
        'package_path',
        'package_size',
        'requirements',
        'is_stable',
        'is_active',
    ];

    protected $casts = [
        'changes' => 'array',
        'package_size' => 'integer',
        'requirements' => 'array',
        'is_stable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
