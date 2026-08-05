<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'billing_type',
        'price_monthly',
        'price_yearly',
        'status',
    ];

    /**
     * Websites using this plan.
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }
}
