<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\Core\CoreCrmService;
use Illuminate\Http\Request;
use RuntimeException;

class CoreCrmController extends Controller
{
    public function __construct(
        protected CoreCrmService $crm
    ) {}

    protected function website(): Website
    {
        $website = request()->user()?->websites()->first();

        if (!$website) {
            throw new RuntimeException('No website is available for CRM.');
        }

        return $website;
    }

    public function index()
    {
        $website = $this->website();

        return view('user.crm.index', [
            'website' => $website,
            'contacts' => $this->crm->contacts($website),
            'leads' => $this->crm->leads($website),
            'tasks' => $this->crm->tasks($website),
            'crmFeatures' => collect(
                app(\App\Services\Core\CoreCrmFeatureRegistry::class)
                    ->available()
            ),
        ]);
    }

    public function storeContact(Request $request)
    {
        $this->crm->createContact($this->website(), $request->all());

        return back()->with('success', 'Client/contact created successfully.');
    }

    public function storeLead(Request $request)
    {
        $this->crm->createLead($this->website(), $request->all());

        return back()->with('success', 'Lead created successfully.');
    }

    public function storeTask(Request $request)
    {
        $this->crm->createTask($this->website(), $request->all());

        return back()->with('success', 'Task created successfully.');
    }
}
