<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'website_id',
        'user_id',
        'uuid',
        'name',
        'slug',
        'description',
        'application_type',
        'client_id',
        'client_secret',
        'redirect_urls',
        'scopes',
        'is_verified',
        'is_active',
    ];

    protected $hidden = [
        'client_secret',
    ];

    protected function casts(): array
    {
        return [
            'redirect_urls' => 'array',
            'scopes' => 'array',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function applicationScopes()
    {
        return $this->hasMany(ApiApplicationScope::class);
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function authorizations()
    {
        return $this->hasMany(ApiAuthorization::class);
    }
}
