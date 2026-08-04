<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * --------------------------------------------------------------------------
     * Mass Assignable
     * --------------------------------------------------------------------------
     */

    protected $fillable = [

        'owner_id',

        'uuid',

        'name',

        'slug',

        'business_type',

        'country',

        'state',

        'city',

        'currency',

        'timezone',

        'language',

        'status',

        'developer_managed',

        'is_demo',

        'trial_ends_at',

        'published_at',

    ];

    /**
     * --------------------------------------------------------------------------
     * Casts
     * --------------------------------------------------------------------------
     */

    protected $casts = [

        'developer_managed' => 'boolean',

        'is_demo' => 'boolean',

        'trial_ends_at' => 'datetime',

        'published_at' => 'datetime',

    ];

    /**
     * --------------------------------------------------------------------------
     * Boot
     * --------------------------------------------------------------------------
     */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {

            if (empty($workspace->uuid)) {

                $workspace->uuid = (string) Str::uuid();

            }

            if (empty($workspace->slug)) {

                $workspace->slug = Str::slug($workspace->name);

            }

        });
    }

    /**
     * --------------------------------------------------------------------------
     * Route Model Binding
     * --------------------------------------------------------------------------
     */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * --------------------------------------------------------------------------
     * Relationships
     * --------------------------------------------------------------------------
     */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
