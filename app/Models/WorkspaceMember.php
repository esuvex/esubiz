<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'member_type',
        'invite_email',
        'joined_at',
        'status',
        'is_primary',
        'is_billable',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'is_primary' => 'boolean',
            'is_billable' => 'boolean',
        ];
    }

    /**
     * Workspace.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
