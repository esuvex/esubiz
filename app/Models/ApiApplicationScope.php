<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiApplicationScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_application_id',
        'api_scope_id',
        'is_allowed',
    ];

    protected function casts(): array
    {
        return [
            'is_allowed' => 'boolean',
        ];
    }

    /**
     * Application.
     */
    public function application()
    {
        return $this->belongsTo(ApiApplication::class, 'api_application_id');
    }

    /**
     * Scope.
     */
    public function scope()
    {
        return $this->belongsTo(ApiScope::class, 'api_scope_id');
    }
}
