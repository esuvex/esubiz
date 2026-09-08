<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\Installation\CoreInstallationState;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\Crypt;

/**
 * ESUBIZ_CORE_FIRST_RUN_SETUP_CONTROLLER_V3
 *
 * Real OFF-SERVER Core first-run installer.
 *
 * Customer entry:
 *
 *     https://example.com/
 *
 * Wizard:
 *
 * 1. Website & Licence
 * 2. System Check
 * 3. Database
 * 4. Administrator
 * 5. Install Esubiz
 *
 * There is no /install lifecycle.
 */
class CoreSetupController extends Controller
{
    public function __construct(
        protected CoreInstallationState $installation
    ) {
    }

    public function start(
        Request $request
    ): View {
        $this->assertAvailable();

        $instanceUuid = $request->session()->get(
            'core_first_run_instance_uuid'
        );

        if (!$instanceUuid) {
            $instanceUuid = (string) app(
                \App\Services\Core\Installation\CoreInstanceIdentity::class
            )->uuid();

            $request->session()->put(
                'core_first_run_instance_uuid',
                $instanceUuid
            );
        }

        return view(
            'core.installation.setup',
            [
                'detectedDomain' =>
                    $request->getHost(),

                'generatedInstanceUuid' =>
                    $instanceUuid,
            ]
        );
    }

    /**
     * STEP 1:
     * Validate website identity + Esubiz Core licence.
     */
    /**
     * STEP 1:
     * Validate the human-readable Esubiz licence against Central.
     *
     * Product identity comes exclusively from the immutable
     * Core package manifest.
     *
     * Instance identity comes exclusively from private Core
     * storage and cannot be chosen by browser input.
     */
    public function validateLicense(
        Request $request
    ): JsonResponse {
        $this->assertAvailable();

        $validated =
            $request->validate([
                'site_name' => [
                    'required',
                    'string',
                    'max:191',
                ],

                'domain' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'license_key' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ]);

        try {
            $package =
                app(
                    \App\Services\Core\Installation\CorePackageIdentity::class
                )->get();

            $instanceUuid =
                app(
                    \App\Services\Core\Installation\CoreInstanceIdentity::class
                )->uuid();

            $domain =
                strtolower(
                    trim(
                        (string) $validated['domain']
                    )
                );

            $domain =
                preg_replace(
                    '#^https?://#i',
                    '',
                    $domain
                ) ?? $domain;

            $domain =
                trim(
                    $domain,
                    '/'
                );

            if (
                $domain === ''
                || str_contains($domain, '/')
                || str_contains($domain, '?')
                || str_contains($domain, '#')
                || preg_match('/\s/', $domain)
            ) {
                return response()->json(
                    [
                        'ok' => false,
                        'message' =>
                            'Enter a valid website domain.',
                    ],
                    422
                );
            }

            $centralResult =
                $this->validateCentralLicense(
                    trim(
                        (string) $validated[
                            'license_key'
                        ]
                    ),
                    $instanceUuid,
                    $domain,
                    $package
                );

            if (
                !(
                    $centralResult['ok']
                    ?? false
                )
            ) {
                return response()->json(
                    [
                        'ok' => false,

                        'message' =>
                            $centralResult['message']
                            ?? 'The Esubiz licence could not be validated.',
                    ],
                    422
                );
            }

            $website = [
                'site_name' =>
                    trim(
                        (string) $validated[
                            'site_name'
                        ]
                    ),

                'domain' =>
                    $domain,

                'core_instance_uuid' =>
                    $instanceUuid,

                /*
                 * Kept here for compatibility with the current
                 * Step 5 normalizer. This session will be cleared
                 * immediately after successful installation.
                 */
                'license_key' =>
                    trim(
                        (string) $validated[
                            'license_key'
                        ]
                    ),

                'product_type' =>
                    $package[
                        'product_type'
                    ],

                'product_slug' =>
                    $package[
                        'product_slug'
                    ],

                'product_name' =>
                    $package[
                        'product_name'
                    ],

                'product_version' =>
                    $package[
                        'product_version'
                    ],
            ];

            $license = [
                'license_key' =>
                    trim(
                        (string) $validated[
                            'license_key'
                        ]
                    ),

                'product_type' =>
                    $package[
                        'product_type'
                    ],

                'product_slug' =>
                    $package[
                        'product_slug'
                    ],

                'product_name' =>
                    $package[
                        'product_name'
                    ],

                'product_version' =>
                    $package[
                        'product_version'
                    ],

                'validated_at' =>
                    now()->toIso8601String(),
            ];

            $request
                ->session()
                ->put(
                    'core_first_run_website',
                    $this->stripInstallerWebsiteSecrets(
                        $website
                    ));

            $request
                ->session()
                ->put(
                    'core_first_run_license',
                    $this->encryptInstallerLicensePayload(
                        $license
                    ));

            $request
                ->session()
                ->put(
                    'core_first_run_instance_uuid',
                    $instanceUuid
                );

            /*
             * Revalidating Website & Licence invalidates every
             * downstream installer checkpoint.
             */
            $request
                ->session()
                ->forget([
                    'core_first_run_system_check_passed',
                    'core_first_run_database',
                    'core_first_run_database_passed',
                    'core_first_run_administrator',
                    'core_first_run_administrator_passed',
                ]);

            return response()->json([
                'ok' =>
                    true,

                'message' =>
                    'Esubiz licence validated successfully.',

                'instance_uuid' =>
                    $instanceUuid,

                'product' => [
                    'type' =>
                        $package[
                            'product_type'
                        ],

                    'slug' =>
                        $package[
                            'product_slug'
                        ],

                    'name' =>
                        $package[
                            'product_name'
                        ],

                    'version' =>
                        $package[
                            'product_version'
                        ],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(
                [
                    'ok' => false,

                    'message' =>
                        'Esubiz could not validate this licence at the moment.',
                ],
                502
            );
        }
    }


    /**
     * STEP 2:
     * Run genuine server/environment requirements.
     */
    public function systemCheck(): JsonResponse
    {
        $this->assertAvailable();

        $requiredExtensions = [
            'pdo',
            'pdo_mysql',
            'openssl',
            'mbstring',
            'xml',
            'curl',
            'zip',
        ];

        $missingExtensions = array_values(
            array_filter(
                $requiredExtensions,
                fn (string $extension): bool =>
                    !extension_loaded($extension)
            )
        );

        $storageWritable =
            is_writable(storage_path());

        $cacheWritable =
            is_writable(
                base_path('bootstrap/cache')
            );

        $https =
            request()->isSecure()
            || strtolower(
                (string) request()->header(
                    'x-forwarded-proto'
                )
            ) === 'https';

        /*
         * Laravel 12 / modern Esubiz Core baseline.
         * This can later come from the packaged Core manifest.
         */
        $minimumPhp = '8.2.0';

        $phpPass =
            version_compare(
                PHP_VERSION,
                $minimumPhp,
                '>='
            );

        $checks = [
            [
                'key' => 'php',
                'label' => 'PHP Version',
                'detail' =>
                    'Running PHP '
                    . PHP_VERSION
                    . ' — minimum '
                    . $minimumPhp,
                'pass' => $phpPass,
            ],

            [
                'key' => 'pdo_mysql',
                'label' => 'PDO / MySQL',
                'detail' =>
                    extension_loaded('pdo_mysql')
                        ? 'MySQL database driver is available.'
                        : 'PDO MySQL extension is missing.',
                'pass' =>
                    extension_loaded('pdo')
                    && extension_loaded('pdo_mysql'),
            ],

            [
                'key' => 'extensions',
                'label' =>
                    'OpenSSL, Mbstring, XML, cURL, ZIP',
                'detail' =>
                    empty($missingExtensions)
                        ? 'All required PHP extensions are available.'
                        : 'Missing: '
                            . implode(
                                ', ',
                                $missingExtensions
                            ),
                'pass' =>
                    empty($missingExtensions),
            ],

            [
                'key' => 'permissions',
                'label' => 'Storage Permissions',
                'detail' =>
                    (
                        $storageWritable
                        && $cacheWritable
                    )
                        ? 'storage/ and bootstrap/cache/ are writable.'
                        : 'Required application directories are not writable.',
                'pass' =>
                    $storageWritable
                    && $cacheWritable,
            ],

            [
                'key' => 'https',
                'label' => 'HTTPS / Domain',
                'detail' =>
                    $https
                        ? 'Secure HTTPS request detected for '
                            . request()->getHost()
                            . '.'
                        : 'HTTPS was not detected for this request.',
                'pass' =>
                    $https,
            ],
        ];

        $allPassed = collect(
            $checks
        )->every(
            fn (array $check): bool =>
                $check['pass'] === true
        );

        if ($allPassed) {
            request()->session()->put(
                'core_first_run_system_check_passed',
                true
            );
        } else {
            request()->session()->forget(
                'core_first_run_system_check_passed'
            );
        }

        return response()->json([
            'ok' => true,
            'passed' => $allPassed,
            'checks' => $checks,
        ]);
    }

    /**
     * STEP 3:
     * Test the supplied MySQL/MariaDB database connection.
     *
     * This uses an isolated temporary Laravel connection and
     * does NOT modify the application's current/default DB.
     */
    public function testDatabase(
        Request $request
    ): JsonResponse {
        $this->assertAvailable();

        if (
            !$request->session()->has(
                'core_first_run_website'
            )
        ) {
            return response()->json(
                [
                    'ok' => false,
                    'message' =>
                        'Validate the Esubiz licence before configuring the database.',
                ],
                409
            );
        }

        if (
            !$request->session()->get(
                'core_first_run_system_check_passed',
                false
            )
        ) {
            return response()->json(
                [
                    'ok' => false,
                    'message' =>
                        'Complete the system check before configuring the database.',
                ],
                409
            );
        }

        $validated = $request->validate([
            'db_host' => [
                'required',
                'string',
                'max:255',
            ],

            'db_port' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],

            'db_database' => [
                'required',
                'string',
                'max:191',
            ],

            'db_username' => [
                'required',
                'string',
                'max:191',
            ],

            'db_password' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $connectionName =
            'esubiz_core_installer_test';

        config([
            'database.connections.'
                . $connectionName => [
                    'driver' =>
                        'mysql',

                    'host' =>
                        $validated['db_host'],

                    'port' =>
                        (int) $validated['db_port'],

                    'database' =>
                        $validated['db_database'],

                    'username' =>
                        $validated['db_username'],

                    'password' =>
                        $validated['db_password']
                            ?? '',

                    'unix_socket' =>
                        '',

                    'charset' =>
                        'utf8mb4',

                    'collation' =>
                        'utf8mb4_unicode_ci',

                    'prefix' =>
                        '',

                    'prefix_indexes' =>
                        true,

                    'strict' =>
                        true,

                    'engine' =>
                        null,

                    'options' =>
                        extension_loaded(
                            'pdo_mysql'
                        )
                            ? array_filter([
                                \PDO::ATTR_TIMEOUT =>
                                    8,
                            ])
                            : [],
                ],
        ]);

        try {
            DB::purge(
                $connectionName
            );

            $connection =
                DB::connection(
                    $connectionName
                );

            /*
             * Force a genuine network/auth/database connection.
             */
            $connection->getPdo();

            /*
             * Confirm the selected database can execute SQL.
             */
            $connection->select(
                'SELECT 1 AS esubiz_connection_test'
            );

            /*
             * Store the validated settings only for this
             * first-run installation session.
             *
             * They will later be written safely into .env.
             */
            $request->session()->put(
                'core_first_run_database',
                    $this->encryptInstallerDatabasePayload(
                        [
                                            'host' =>
                                                $validated['db_host'],
                        
                                            'port' =>
                                                (int) $validated['db_port'],
                        
                                            'database' =>
                                                $validated['db_database'],
                        
                                            'username' =>
                                                $validated['db_username'],
                        
                                            'password' =>
                                                $validated['db_password']
                                                    ?? '',
                                        ]
                    ));

            $request->session()->put(
                'core_first_run_database_passed',
                true
            );

            return response()->json([
                'ok' => true,

                'message' =>
                    'Database connection successful.',

                'database' =>
                    $validated['db_database'],
            ]);
        } catch (Throwable $e) {
            $request->session()->forget(
                'core_first_run_database_passed'
            );

            $request->session()->forget(
                'core_first_run_database'
            );

            /*
             * Do not expose raw PDO/SQL credentials or server
             * exception details to the browser.
             */
            report($e);

            return response()->json(
                [
                    'ok' => false,

                    'message' =>
                        'Database connection failed. Check the host, port, database name, username and password.',
                ],
                422
            );
        } finally {
            DB::disconnect(
                $connectionName
            );

            DB::purge(
                $connectionName
            );
        }
    }

    /**
     * STEP 4:
     * Validate and stage the first local Core administrator.
     *
     * The administrator is NOT written to the database here.
     * Creation only occurs during the final installation transaction.
     */
    public function validateAdministrator(
        Request $request
    ): JsonResponse {
        $this->assertAvailable();

        if (
            !$request->session()->get(
                'core_first_run_database_passed',
                false
            )
        ) {
            return response()->json(
                [
                    'ok' => false,
                    'message' =>
                        'Complete the database connection test before creating the administrator.',
                ],
                409
            );
        }

        $validated = $request->validate([
            'admin_name' => [
                'required',
                'string',
                'max:191',
            ],

            'admin_email' => [
                'required',
                'email',
                'max:255',
            ],

            'admin_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'admin_password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],
        ]);

        /*
         * Retain only for the current first-run installation session.
         *
         * The password will be hashed during final installation and
         * must never be written into the installed lock metadata.
         */
        $request->session()->put(
            'core_first_run_administrator',
                    $this->encryptInstallerAdministratorPayload(
                        [
                                        'name' =>
                                            $validated['admin_name'],
                        
                                        'email' =>
                                            strtolower(
                                                trim(
                                                    $validated['admin_email']
                                                )
                                            ),
                        
                                        'phone' =>
                                            $validated['admin_phone']
                                                ?? null,
                        
                                        'password' =>
                                            $validated['admin_password'],
                                    ]
                    ));

        $request->session()->put(
            'core_first_run_administrator_passed',
            true
        );

        return response()->json([
            'ok' => true,

            'message' =>
                'Administrator details validated successfully.',

            'administrator' => [
                'name' =>
                    $validated['admin_name'],

                'email' =>
                    strtolower(
                        trim(
                            $validated['admin_email']
                        )
                    ),

                'phone' =>
                    $validated['admin_phone']
                        ?? null,
            ],
        ]);
    }

    /**
     * Final installer orchestration endpoint.
     *
     * Database testing, environment writing, migrations,
     * administrator creation and final installation are being
     * connected in the next stages.
     */
    /**
     * STEP 5:
     * Perform the real OFF-SERVER Core installation.
     *
     * All previous installer stages must have completed
     * successfully before this endpoint can run.
     */
    /**
     * STEP 5:
     * Execute the real off-server Core installation.
     *
     * Sensitive installer values remain encrypted in session
     * storage and are decrypted only immediately before use.
     */
    public function store(
        Request $request,
        \App\Services\Core\Installation\CoreInstallationService $installer
    ): JsonResponse {
        $this->assertAvailable();

        $session =
            $request->session();

        if (
            !$session->has(
                'core_first_run_website'
            )
            || !$session->get(
                'core_first_run_system_check_passed',
                false
            )
            || !$session->get(
                'core_first_run_database_passed',
                false
            )
            || !$session->has(
                'core_first_run_database'
            )
            || !$session->get(
                'core_first_run_administrator_passed',
                false
            )
            || !$session->has(
                'core_first_run_administrator'
            )
            || !$session->has(
                'core_first_run_license'
            )
        ) {
            return response()->json(
                [
                    'ok' => false,

                    'message' =>
                        'Complete every installer step before installing Esubiz.',
                ],
                422
            );
        }

        try {
            $website =
                (array) $session->get(
                    'core_first_run_website',
                    []
                );

            $database =
                (array) $session->get(
                    'core_first_run_database',
                    []
                );

            $administrator =
                (array) $session->get(
                    'core_first_run_administrator',
                    []
                );

            $license =
                (array) $session->get(
                    'core_first_run_license',
                    []
                );

            /*
             * Licence
             */
            if (
                empty(
                    $license[
                        'license_key_encrypted'
                    ]
                )
            ) {
                throw new \RuntimeException(
                    'Encrypted licence state is missing.'
                );
            }

            $license['license_key'] =
                Crypt::decryptString(
                    (string) $license[
                        'license_key_encrypted'
                    ]
                );

            unset(
                $license[
                    'license_key_encrypted'
                ]
            );

            /*
             * Database password.
             *
             * Empty database passwords are valid, so test for
             * the encrypted field rather than truthiness.
             */
            if (
                !array_key_exists(
                    'password_encrypted',
                    $database
                )
            ) {
                throw new \RuntimeException(
                    'Encrypted database password state is missing.'
                );
            }

            $database['password'] =
                Crypt::decryptString(
                    (string) $database[
                        'password_encrypted'
                    ]
                );

            unset(
                $database[
                    'password_encrypted'
                ]
            );

            /*
             * Administrator password
             */
            if (
                empty(
                    $administrator[
                        'password_encrypted'
                    ]
                )
            ) {
                throw new \RuntimeException(
                    'Encrypted administrator password state is missing.'
                );
            }

            $administrator['password'] =
                Crypt::decryptString(
                    (string) $administrator[
                        'password_encrypted'
                    ]
                );

            unset(
                $administrator[
                    'password_encrypted'
                ]
            );

            /*
             * Package and instance identities remain server-owned.
             */
            $package =
                app(
                    \App\Services\Core\Installation\CorePackageIdentity::class
                )->get();

            $license['product_type'] =
                (string) (
                    $license[
                        'product_type'
                    ]
                    ?? $package[
                        'product_type'
                    ]
                );

            $license['product_slug'] =
                (string) (
                    $license[
                        'product_slug'
                    ]
                    ?? $package[
                        'product_slug'
                    ]
                );

            $license['product_name'] =
                (string) (
                    $license[
                        'product_name'
                    ]
                    ?? $package[
                        'product_name'
                    ]
                );

            $license['product_version'] =
                (string) (
                    $license[
                        'product_version'
                    ]
                    ?? $package[
                        'product_version'
                    ]
                );

            $instanceUuid =
                app(
                    \App\Services\Core\Installation\CoreInstanceIdentity::class
                )->uuid();

            $result =
                $installer->install(
                    $website,
                    $database,
                    $administrator,
                    $license,
                    $instanceUuid
                );

            /*
             * Successful installation destroys all staged state,
             * including encrypted secrets.
             */
            $session->forget([
                'core_first_run_website',
                'core_first_run_license',
                'core_first_run_system_check_passed',
                'core_first_run_database',
                'core_first_run_database_passed',
                'core_first_run_administrator',
                'core_first_run_administrator_passed',
                'core_first_run_instance_uuid',
            ]);

            $session->regenerateToken();

            return response()->json([
                'ok' =>
                    true,

                'message' =>
                    'Esubiz installed successfully.',

                'redirect_url' =>
                    url('/'),

                'administrator_email' =>
                    $administrator[
                        'email'
                    ]
                    ?? null,

                'instance_uuid' =>
                    $result[
                        'instance_uuid'
                    ]
                    ?? $instanceUuid,
            ]);
        } catch (\Throwable $e) {
            report(
                $e
            );

            return response()->json(
                [
                    'ok' => false,

                    'message' =>
                        'Esubiz could not complete the installation. Verify the installer details and try again.',
                ],
                500
            );
        }
    }



    /**
     * Validate Core licence with Central Esubiz.
     *
     * @param array<string,mixed> $setup
     * @return array<string,mixed>
     */
    /**
     * Validate this Core package with Central Esubiz.
     *
     * No Central numeric database ID is embedded in the
     * distributed package.
     *
     * @param array<string,mixed> $package
     *
     * @return array<string,mixed>
     */
    protected function validateCentralLicense(
        string $licenseKey,
        string $instanceUuid,
        string $domain,
        array $package
    ): array {
        $centralUrl =
            rtrim(
                (string) config(
                    'services.esubiz.marketplace_url',
                    config('app.url')
                ),
                '/'
            );

        if ($centralUrl === '') {
            throw new \RuntimeException(
                'The Central Esubiz Marketplace URL is not configured.'
            );
        }

        $response =
            \Illuminate\Support\Facades\Http::asJson()
                ->acceptJson()
                ->timeout(15)
                ->post(
                    $centralUrl
                    . '/marketplace/licenses/validate',
                    [
                        'license_key' =>
                            $licenseKey,

                        'instance_uuid' =>
                            $instanceUuid,

                        'product_type' =>
                            (string) $package[
                                'product_type'
                            ],

                        'product_slug' =>
                            (string) $package[
                                'product_slug'
                            ],

                        'product_name' =>
                            (string) $package[
                                'product_name'
                            ],

                        'product_version' =>
                            (string) $package[
                                'product_version'
                            ],

                        'deployment' =>
                            'off_server',

                        'domain' =>
                            $domain,
                    ]
                );

        if (!$response->successful()) {
            return [
                'ok' =>
                    false,

                'message' =>
                    (string) (
                        $response->json(
                            'message'
                        )
                        ?: 'Central Esubiz rejected the licence validation request.'
                    ),
            ];
        }

        $payload =
            $response->json();

        if (!is_array($payload)) {
            return [
                'ok' =>
                    false,

                'message' =>
                    'Central Esubiz returned an invalid licence response.',
            ];
        }

        return $payload;
    }


    protected function assertAvailable(): void
    {
        if (!$this->isOffServer()) {
            abort(404);
        }

        if ($this->installation->isInstalled()) {
            abort(404);
        }
    }

    protected function isOffServer(): bool
    {
        return strtolower(
            trim(
                (string) config(
                    'services.esubiz.deployment',
                    env(
                        'ESUBIZ_DEPLOYMENT',
                        'saas'
                    )
                )
            )
        ) === 'off_server';
    }


    /**
     * ESUBIZ_CORE_INSTALLER_ENCRYPTED_SESSION_V1
     *
     * Website state must never retain a duplicated licence key.
     *
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    protected function stripInstallerWebsiteSecrets(
        array $payload
    ): array {
        unset(
            $payload['license_key'],
            $payload['license_key_encrypted']
        );

        return $payload;
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    protected function encryptInstallerLicensePayload(
        array $payload
    ): array {
        if (
            array_key_exists(
                'license_key',
                $payload
            )
        ) {
            $payload['license_key_encrypted'] =
                Crypt::encryptString(
                    (string) $payload[
                        'license_key'
                    ]
                );

            unset(
                $payload[
                    'license_key'
                ]
            );
        }

        return $payload;
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    protected function encryptInstallerDatabasePayload(
        array $payload
    ): array {
        if (
            array_key_exists(
                'password',
                $payload
            )
        ) {
            $payload['password_encrypted'] =
                Crypt::encryptString(
                    (string) $payload[
                        'password'
                    ]
                );

            unset(
                $payload[
                    'password'
                ]
            );
        }

        return $payload;
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    protected function encryptInstallerAdministratorPayload(
        array $payload
    ): array {
        if (
            array_key_exists(
                'password',
                $payload
            )
        ) {
            $payload['password_encrypted'] =
                Crypt::encryptString(
                    (string) $payload[
                        'password'
                    ]
                );

            unset(
                $payload[
                    'password'
                ]
            );
        }

        /*
         * Confirmation is never needed after Step 4 validation.
         */
        unset(
            $payload[
                'password_confirmation'
            ]
        );

        return $payload;
    }

}
