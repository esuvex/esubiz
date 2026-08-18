<?php

namespace App\Services\Core;

use App\Models\Website;
use App\Services\Website\WebsiteTenantDatabaseService;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class CoreCrmService
{
    public function __construct(
        protected CoreCrmEntitlementService $entitlements,
        protected WebsiteTenantDatabaseService $tenantDatabase
    ) {}

    public function contacts(Website $website)
    {
        return $this->table($website, 'crm_contacts')
            ->latest()
            ->get();
    }

    public function leads(Website $website)
    {
        return $this->table($website, 'crm_leads')
            ->latest()
            ->get();
    }

    public function tasks(Website $website)
    {
        return $this->table($website, 'crm_tasks')
            ->latest()
            ->get();
    }

    public function createContact(Website $website, array $data)
    {
        $this->entitlements->enforce($website, 'clients', 'crm_contacts');

        Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
        ])->validate();

        return $this->table($website, 'crm_contacts')->insertGetId([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'job_title' => $data['job_title'] ?? null,
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
            'custom_fields' => isset($data['custom_fields'])
                ? json_encode($data['custom_fields'])
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function createLead(Website $website, array $data)
    {
        $this->entitlements->enforce($website, 'leads', 'crm_leads');

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'lead_value' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
        ])->validate();

        return $this->table($website, 'crm_leads')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'source' => $data['source'] ?? null,
            'status' => $data['status'] ?? 'new',
            'lead_value' => $data['lead_value'] ?? null,
            'notes' => $data['notes'] ?? null,
            'custom_fields' => isset($data['custom_fields'])
                ? json_encode($data['custom_fields'])
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function createTask(Website $website, array $data)
    {
        $this->entitlements->enforce($website, 'tasks', 'crm_tasks');

        Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact_id' => ['nullable', 'integer'],
            'deal_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
        ])->validate();

        return $this->table($website, 'crm_tasks')->insertGetId([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'deal_id' => $data['deal_id'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'priority' => $data['priority'] ?? 'normal',
            'due_at' => $data['due_at'] ?? null,
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function table(Website $website, string $table)
    {
        $this->tenantDatabase->connect($website);

        return new class($this->tenantDatabase, $table) {
            public function __construct(
                protected WebsiteTenantDatabaseService $service,
                protected string $table
            ) {}

            public function __call($method, $arguments)
            {
                try {
                    return $this->service->connection()
                        ->table($this->table)
                        ->{$method}(...$arguments);
                } finally {
                    if ($method !== 'insertGetId') {
                        $this->service->disconnect();
                    }
                }
            }
        };
    }
}
