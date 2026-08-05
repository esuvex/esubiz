<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'workspace_id',
        'name',
        'slug',
        'domain',
        'subdomain',
        'status',
    ];

    /**
     * Website owner.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Developer workspace (optional).
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
