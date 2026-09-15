<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteMailbox extends Model
{
    /*
     * ESUBIZ_CENTRAL_WEBSITE_MAILBOX_MODEL_V1
     */

    protected $fillable = [
        'website_id',
        'provider',
        'connection_type',
        'domain',
        'local_part',
        'email_address',
        'password',
        'status',
        'last_storage_bytes',
        'storage_checked_at',
        'provisioned_at',
        'deleted_at_remote',
        'last_error',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        /*
         * Laravel application encryption.
         *
         * Raw mailbox passwords must never be stored in plaintext.
         */
        'password' => 'encrypted',

        'last_storage_bytes' => 'integer',
        'storage_checked_at' => 'datetime',
        'provisioned_at' => 'datetime',
        'deleted_at_remote' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(
            Website::class
        );
    }
}
