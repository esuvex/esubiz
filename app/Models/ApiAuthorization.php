<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiAuthorization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'api_application_id',
        'user_id',
        'uuid',
        'authorization_code',
        'access_token_hash',
        'scopes',
        'approved_at',
        'expires_at',
        'is_revoked',
        'revoked_at',
    ];

    protected $hidden = [
        'authorization_code',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_revoked' => 'boolean',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Application being authorized.
     */
    public function application()
    {
        return $this->belongsTo(
            ApiApplication::class,
            'api_application_id'
        );
    }

    /**
     * Esubiz user authorizing the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
