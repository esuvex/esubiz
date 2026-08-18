@extends('user.layouts.app')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">CRM</h1>
        <p class="text-sm text-slate-500 mt-1">
            Manage clients, leads and tasks from your Core CRM.
        </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-4">Clients</h2>

            <form method="POST" action="{{ route('user.crm.contacts.store') }}" class="space-y-3">
                @csrf
                <input name="first_name" required placeholder="First name"
                    class="w-full rounded-xl border-slate-300">
                <input name="last_name" placeholder="Last name"
                    class="w-full rounded-xl border-slate-300">
                <input name="email" type="email" placeholder="Email"
                    class="w-full rounded-xl border-slate-300">
                <input name="phone" placeholder="Phone"
                    class="w-full rounded-xl border-slate-300">
                <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 font-semibold text-white">
                    Add Client
                </button>
            </form>

            <div class="mt-5 text-sm text-slate-500">
                {{ $contacts->count() }} clients currently recorded.
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-4">Leads</h2>

            <form method="POST" action="{{ route('user.crm.leads.store') }}" class="space-y-3">
                @csrf
                <input name="name" required placeholder="Lead name"
                    class="w-full rounded-xl border-slate-300">
                <input name="email" type="email" placeholder="Email"
                    class="w-full rounded-xl border-slate-300">
                <input name="phone" placeholder="Phone"
                    class="w-full rounded-xl border-slate-300">
                <input name="source" placeholder="Source"
                    class="w-full rounded-xl border-slate-300">
                <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 font-semibold text-white">
                    Add Lead
                </button>
            </form>

            <div class="mt-5 text-sm text-slate-500">
                {{ $leads->count() }} leads currently recorded.
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold text-slate-900 mb-4">Tasks</h2>

            <form method="POST" action="{{ route('user.crm.tasks.store') }}" class="space-y-3">
                @csrf
                <input name="title" required placeholder="Task title"
                    class="w-full rounded-xl border-slate-300">
                <textarea name="description" placeholder="Description"
                    class="w-full rounded-xl border-slate-300"></textarea>
                <select name="priority" class="w-full rounded-xl border-slate-300">
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                    <option value="high">High</option>
                </select>
                <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 font-semibold text-white">
                    Add Task
                </button>
            </form>

            <div class="mt-5 text-sm text-slate-500">
                {{ $tasks->count() }} tasks currently recorded.
            </div>
        </div>

    </div>
</div>
@endsection
