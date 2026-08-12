<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCustomPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'scope_type',
        'owner_id',
        'user_id',
        'workspace_id',
        'plan_id',
        'name',
        'price',
        'currency',
        'billing_period',
        'features',
        'starts_at',
        'expires_at',
        'is_complimentary',
        'complimentary_days',
        'complimentary_reason',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_complimentary' => 'boolean',
            'complimentary_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
