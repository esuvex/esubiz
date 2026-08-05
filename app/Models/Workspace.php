<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    /**
     * Developer that owns this workspace.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Websites grouped inside this workspace.
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    /**
     * Developers collaborating in this workspace.
     */
    public function members()
    {
        return $this->hasMany(WorkspaceMember::class);
    }
}
