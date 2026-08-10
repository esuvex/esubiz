<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
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

    protected function casts(): array
    {
        return [
            'developer_managed' => 'boolean',
            'is_demo' => 'boolean',
            'trial_ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Owner of the workspace.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Websites grouped inside this workspace.
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    /**
     * Workspace members.
     */
    public function members()
    {
        return $this->hasMany(WorkspaceMember::class);
    }
}
