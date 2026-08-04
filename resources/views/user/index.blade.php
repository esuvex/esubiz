@extends('admin.layouts.app')

@section('title', 'User Dashboard')


@section('content')

<div>

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            Welcome Back
        </h1>

        <p class="text-gray-500">
            Manage your Esubiz account and services
        </p>

    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Active Subscription
            </p>

            <h2 class="text-2xl font-bold mt-2">
                Pro Plan
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Websites
            </p>

            <h2 class="text-3xl font-bold mt-2">
                5
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                API Usage
            </p>

            <h2 class="text-3xl font-bold mt-2">
                78%
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Wallet Balance
            </p>

            <h2 class="text-3xl font-bold mt-2">
                ₦25,000
            </h2>

        </div>


    </div>



    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                My Services
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>Websites</p>

                <p>CRM</p>

                <p>API Applications</p>

                <p>Subscriptions</p>

                <p>Billing</p>

            </div>


        </div>



        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                Account Activity
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>Recent Payments</p>

                <p>Invoices</p>

                <p>Transactions</p>

                <p>Notifications</p>

            </div>


        </div>


    </div>


</div>

@endsection
