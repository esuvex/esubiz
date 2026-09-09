<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantFormsController extends Controller
{
    protected function currentWebsite(): Website
    {
        $tenant = WebsiteTenant::current();

        abort_unless(
            $tenant,
            404,
            'Website tenant not found.'
        );

        return Website::query()
            ->where('id', $tenant->website_id)
            ->where('status', 'active')
            ->where('user_enabled', true)
            ->firstOrFail();
    }

    protected function authorizeCms(): Website
    {
        $website = $this->currentWebsite();

        $websiteId = (int) $website->id;

        $tenantAuthenticated =
            session()->get(
                "tenant_cms_sites.{$websiteId}.authenticated"
            ) === true
            || (
                session()->get('tenant_cms_authenticated') === true
                && (int) session()->get('tenant_cms_website_id')
                    === $websiteId
            );

        if (!$tenantAuthenticated) {
            session()->put(
                'url.intended',
                request()->fullUrl()
            );

            redirect()
                ->to('/login')
                ->send();

            exit;
        }

        return $website;
    }

    protected function db()
    {
        return DB::connection('tenant');
    }

    protected function ensureFormsSchema(): void
    {
        $schema = Schema::connection('tenant');

        abort_unless(
            $schema->hasTable('forms')
            && $schema->hasTable('form_fields'),
            500,
            'Core Forms schema is unavailable.'
        );
    }

    public function index()
    {
        $website = $this->authorizeCms();

        $this->ensureFormsSchema();

        $db = $this->db();

        $settings = $db->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        $forms = $db->table('forms')
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'settings',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($form) use ($db) {
                $form->field_count = $db
                    ->table('form_fields')
                    ->where('form_id', $form->id)
                    ->count();

                $formSettings = json_decode(
                    (string) ($form->settings ?? ''),
                    true
                );

                $form->is_system =
                    is_array($formSettings)
                    && (($formSettings['system'] ?? false) === true);

                return $form;
            });

        return view(
            'tenant.admin.forms.index',
            compact(
                'website',
                'settings',
                'forms'
            )
        );
    }

    public function create()
    {
        $website = $this->authorizeCms();

        $this->ensureFormsSchema();

        $settings = $this->db()
            ->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        return view(
            'tenant.admin.forms.form',
            [
                'website' => $website,
                'settings' => $settings,
                'form' => null,
                'fields' => collect(),
                'isSystemForm' => false,
            ]
        );
    }

    public function store(Request $request)
    {
        $this->authorizeCms();

        $this->ensureFormsSchema();

        $data = $this->validatedFormData($request);

        $db = $this->db();

        $slug = $this->uniqueSlug(
            $data['slug'] ?: $data['name']
        );

        $formId = null;

        $db->transaction(function () use (
            $db,
            $data,
            $slug,
            &$formId
        ) {
            $now = now();

            $formId = $db->table('forms')
                ->insertGetId([
                    'name' => trim($data['name']),
                    'slug' => $slug,
                    'description' =>
                        $this->nullableString(
                            $data['description'] ?? null
                        ),
                    'settings' => json_encode([
                        'system' => false,
                        'source' => 'core_forms',
                    ]),
                    'is_active' =>
                        (bool) ($data['is_active'] ?? false),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $this->replaceFields(
                $formId,
                $data['fields'] ?? []
            );
        });

        return redirect()
            ->route(
                'tenant.cms.forms.edit',
                [
                    'subdomain' =>
                        request()->route('subdomain'),
                    'form' => $formId,
                ]
            )
            ->with(
                'success',
                'Form created successfully.'
            );
    }

    public function edit(
        string $subdomain,
        int $form
    ) {
        $website = $this->authorizeCms();

        $this->ensureFormsSchema();

        $db = $this->db();

        $settings = $db->table('site_settings')
            ->pluck('value', 'key')
            ->all();

        $formRecord = $db->table('forms')
            ->where('id', $form)
            ->first();

        abort_unless($formRecord, 404);

        $fields = $db->table('form_fields')
            ->where('form_id', $form)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $formSettings = json_decode(
            (string) ($formRecord->settings ?? ''),
            true
        );

        $isSystemForm =
            is_array($formSettings)
            && (($formSettings['system'] ?? false) === true);

        return view(
            'tenant.admin.forms.form',
            [
                'website' => $website,
                'settings' => $settings,
                'form' => $formRecord,
                'fields' => $fields,
                'isSystemForm' => $isSystemForm,
            ]
        );
    }

    public function update(
        Request $request,
        string $subdomain,
        int $form
    ) {
        $this->authorizeCms();

        $this->ensureFormsSchema();

        $db = $this->db();

        $formRecord = $db->table('forms')
            ->where('id', $form)
            ->first();

        abort_unless($formRecord, 404);

        $existingSettings = json_decode(
            (string) ($formRecord->settings ?? ''),
            true
        );

        $isSystemForm =
            is_array($existingSettings)
            && (($existingSettings['system'] ?? false) === true);

        $data = $this->validatedFormData(
            $request,
            $form
        );

        $slug = $this->uniqueSlug(
            $data['slug'] ?: $data['name'],
            $form
        );

        $db->transaction(function () use (
            $db,
            $form,
            $formRecord,
            $existingSettings,
            $isSystemForm,
            $data,
            $slug
        ) {
            $settings = is_array($existingSettings)
                ? $existingSettings
                : [];

            $settings['source'] =
                $settings['source'] ?? 'core_forms';

            $settings['system'] = $isSystemForm;

            $db->table('forms')
                ->where('id', $form)
                ->update([
                    'name' =>
                        $isSystemForm
                            ? $formRecord->name
                            : trim($data['name']),
                    'slug' =>
                        $isSystemForm
                            ? $formRecord->slug
                            : $slug,
                    'description' =>
                        $this->nullableString(
                            $data['description'] ?? null
                        ),
                    'settings' => json_encode($settings),
                    'is_active' =>
                        (bool) ($data['is_active'] ?? false),
                    'updated_at' => now(),
                ]);

            $this->replaceFields(
                $form,
                $data['fields'] ?? []
            );
        });

        return back()->with(
            'success',
            'Form updated successfully.'
        );
    }

    public function destroy(
        string $subdomain,
        int $form
    ) {
        $this->authorizeCms();

        $this->ensureFormsSchema();

        $db = $this->db();

        $formRecord = $db->table('forms')
            ->where('id', $form)
            ->first();

        abort_unless($formRecord, 404);

        $formSettings = json_decode(
            (string) ($formRecord->settings ?? ''),
            true
        );

        $isSystemForm =
            is_array($formSettings)
            && (($formSettings['system'] ?? false) === true);

        if ($isSystemForm) {
            return back()->with(
                'error',
                'System forms cannot be deleted.'
            );
        }

        $db->table('forms')
            ->where('id', $form)
            ->delete();

        return redirect()
            ->route(
                'tenant.cms.forms.index',
                [
                    'subdomain' =>
                        request()->route('subdomain'),
                ]
            )
            ->with(
                'success',
                'Form deleted successfully.'
            );
    }

    protected function validatedFormData(
        Request $request,
        ?int $formId = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:160',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:160',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'fields' => [
                'nullable',
                'array',
            ],

            'fields.*.name' => [
                'required_with:fields',
                'string',
                'max:160',
                'regex:/^[A-Za-z0-9_-]+$/',
            ],

            'fields.*.label' => [
                'required_with:fields',
                'string',
                'max:160',
            ],

            'fields.*.type' => [
                'required_with:fields',
                Rule::in([
                    'text',
                    'email',
                    'tel',
                    'number',
                    'textarea',
                    'select',
                    'checkbox',
                    'radio',
                    'password',
                    'date',
                ]),
            ],

            'fields.*.required' => [
                'nullable',
                'boolean',
            ],

            'fields.*.options' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);
    }

    protected function replaceFields(
        int $formId,
        array $fields
    ): void {
        $db = $this->db();

        $db->table('form_fields')
            ->where('form_id', $formId)
            ->delete();

        foreach (
            array_values($fields)
            as $index => $field
        ) {
            $name = trim(
                (string) ($field['name'] ?? '')
            );

            $label = trim(
                (string) ($field['label'] ?? '')
            );

            if ($name === '' || $label === '') {
                continue;
            }

            $options = $this->parseOptions(
                $field['options'] ?? null
            );

            $db->table('form_fields')
                ->insert([
                    'form_id' => $formId,
                    'name' => $name,
                    'label' => $label,
                    'type' =>
                        (string) (
                            $field['type']
                            ?? 'text'
                        ),
                    'required' =>
                        (bool) (
                            $field['required']
                            ?? false
                        ),
                    'options' =>
                        $options === null
                            ? null
                            : json_encode($options),
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    protected function parseOptions(
        ?string $value
    ): ?array {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $items = collect(
            preg_split(
                '/[\r\n,]+/',
                $value
            )
        )
            ->map(
                fn ($item) => trim($item)
            )
            ->filter()
            ->values()
            ->all();

        return $items ?: null;
    }

    protected function uniqueSlug(
        string $value,
        ?int $ignoreFormId = null
    ): string {
        $base = Str::slug(
            trim($value)
        );

        if ($base === '') {
            $base = 'form';
        }

        $slug = $base;
        $suffix = 2;

        while (true) {
            $query = $this->db()
                ->table('forms')
                ->where('slug', $slug);

            if ($ignoreFormId !== null) {
                $query->where(
                    'id',
                    '!=',
                    $ignoreFormId
                );
            }

            if (!$query->exists()) {
                return $slug;
            }

            $slug =
                $base
                . '-'
                . $suffix;

            $suffix++;
        }
    }

    protected function nullableString(
        $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        return $value === ''
            ? null
            : $value;
    }
}
