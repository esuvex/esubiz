<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * User assigned to this role.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Role assigned to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
