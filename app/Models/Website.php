<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{

    /*
     * ============================================================
     * ESUBIZ_UNIFIED_WEBSITE_IDENTITY
     * ============================================================
     *
     * Every website known to Esubiz has one permanent Central
     * identity regardless of where checkout originates.
     *
     * Deployment:
     * - saas
     * - off_server
     *
     * "central" is not a deployment type. It is a checkout origin.
     */

    public const DEPLOYMENT_SAAS =
        'saas';

    public const DEPLOYMENT_OFF_SERVER =
        'off_server';

    public const REGISTRY_ACTIVE =
        'active';

    public const REGISTRY_SUSPENDED =
        'suspended';

    public const REGISTRY_REVOKED =
        'revoked';


    public function isSaas(): bool
    {
        return $this->deployment_type
            === static::DEPLOYMENT_SAAS;
    }


    public function isOffServer(): bool
    {
        return $this->deployment_type
            === static::DEPLOYMENT_OFF_SERVER;
    }


    public function isRegistryActive(): bool
    {
        return $this->registry_status
            === static::REGISTRY_ACTIVE;
    }


    /**
     * Public-safe Central website identity for APIs,
     * installations, licensing and Marketplace handoffs.
     */
    public function centralWebsiteIdentity(): string
    {
        return (string) (
            $this->website_uuid
            ?: $this->id
        );
    }


    /**
     * Current canonical registered host.
     */
    public function registeredHost(): ?string
    {
        $domain =
            strtolower(
                trim(
                    (string) $this->registered_domain
                )
            );

        return $domain !== ''
            ? $domain
            : null;
    }


    use HasFactory;

    protected static function booted(): void
    {

        /*
         * ESUBIZ_CENTRAL_WEBSITE_IDENTITY_CREATION
         */
        static::creating(
            function (Website $website): void {

                if (
                    empty(
                        $website->website_uuid
                    )
                ) {
                    $website->website_uuid =
                        (string)
                        \Illuminate\Support\Str::uuid();
                }


                if (
                    empty(
                        $website->deployment_type
                    )
                ) {
                    $website->deployment_type =
                        static::DEPLOYMENT_SAAS;
                }


                if (
                    empty(
                        $website->registry_status
                    )
                ) {
                    $website->registry_status =
                        static::REGISTRY_ACTIVE;
                }


                if (
                    empty(
                        $website->registered_at
                    )
                ) {
                    $website->registered_at =
                        now();
                }


                /*
                 * SaaS default domain.
                 *
                 * Off-server registration will explicitly supply its
                 * licensed domain during Central licence activation.
                 */
                if (
                    $website->deployment_type
                        === static::DEPLOYMENT_SAAS
                    && empty(
                        $website->registered_domain
                    )
                    && !empty(
                        $website->subdomain
                    )
                ) {
                    $website->registered_domain =
                        strtolower(
                            trim(
                                (string)
                                $website->subdomain
                            )
                        )
                        . '.esubiz.com';
                }
            }
        );


        static::deleting(function (Website $website) {
            $website->apiApplication()->delete();
        });
    }

    protected $fillable = [
        'website_uuid',
        'deployment_type',
        'registered_domain',
        'registry_status',
        'registered_at',
        'last_central_seen_at',

        /*
        |--------------------------------------------------------------------------
        | Ownership
        |--------------------------------------------------------------------------
        */

        'owner_id',
        'developer_id',
        'plan_id',
        'workspace_id',

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'uuid',
        'website_code',

        'name',
        'type',
        'edition',
        'owner_type',

        'slug',
        'domain',
        'subdomain',

        /*
        |--------------------------------------------------------------------------
        | Website
        |--------------------------------------------------------------------------
        */

        'industry',
        'theme',
        'template',

        'status',
        'user_enabled',
        'current_step',

        'wizard_data',

        /*
        |--------------------------------------------------------------------------
        | Website Administrator
        |--------------------------------------------------------------------------
        */

        'admin_name',
        'admin_email',
        'admin_password',

        /*
         * ESUBIZ_SAAS_MAILBOX_INITIAL_CREDENTIAL_V3
         *
         * Temporary encrypted credential used only to initialize the
         * automatically managed SaaS mailbox.
         */
        'mailbox_provisioning_password',

        /*
        |--------------------------------------------------------------------------
        | Deployment
        |--------------------------------------------------------------------------
        */

        'deployment_progress',
        'estimated_finish_at',
        'deployment_started_at',
        'deployment_completed_at',

        'last_saved_at',

        /*
        |--------------------------------------------------------------------------
        | Limits
        |--------------------------------------------------------------------------
        */

        'multi_branch',
        'branch_limit',

        'ai_credits',
        'sms_credits',

        'storage_mb',
        'bandwidth_mb',

        /*
        |--------------------------------------------------------------------------
        | Features
        |--------------------------------------------------------------------------
        */

        'enabled_modules',
        'enabled_features',

        'settings',

        'is_default',
        'is_homepage',
        'is_pwa',
        'is_native_app',

        'published_at',
    ];

    protected $casts = [

        'wizard_data' => 'array',

        /*
         * ESUBIZ_SAAS_MAILBOX_INITIAL_CREDENTIAL_CAST_V3
         *
         * Never store the temporary mailbox provisioning password
         * as plaintext in the Central database.
         */
        'mailbox_provisioning_password' => 'encrypted',

        'enabled_modules' => 'array',
        'enabled_features' => 'array',
        'settings' => 'array',

        'multi_branch' => 'boolean',
        'user_enabled' => 'boolean',
        'is_default' => 'boolean',
        'is_homepage' => 'boolean',
        'is_pwa' => 'boolean',
        'is_native_app' => 'boolean',

        'published_at' => 'datetime',

        'last_saved_at' => 'datetime',

        'estimated_finish_at' => 'datetime',
        'deployment_started_at' => 'datetime',
        'deployment_completed_at' => 'datetime',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * SSO application registered for this website.
     */
    public function apiApplication()
    {
        return $this->hasOne(ApiApplication::class);
    }

    /**
     * Database connection used by this website.
     */
    public function databaseConnection()
    {
        return $this->hasOne(WebsiteDatabaseConnection::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function saveWizard(array $data, int $step): void
    {
        $this->update([

            'wizard_data' => array_merge(
                $this->wizard_data ?? [],
                $data
            ),

            'current_step' => $step,

            'last_saved_at' => now(),

        ]);
    }

    public function markProvisioning(): void
    {
        $this->update([

            'status' => 'provisioning',

            'deployment_progress' => 0,

            'deployment_started_at' => now(),

        ]);
    }
}
