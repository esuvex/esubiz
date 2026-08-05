<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'plan_id',
        'workspace_id',
        'name',
        'type',
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
     * Website plan.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Developer workspace (optional).
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
