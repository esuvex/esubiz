<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles assigned to the user.
     */
    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Workspaces owned by the user.
     */
    public function ownedWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Workspace memberships.
     */
    public function workspaceMemberships()
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Current workspace.
     */
    public function currentWorkspace()
    {
        return $this->workspaceMemberships()->where('status', 'active')->first();
    }
}
