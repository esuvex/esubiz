@extends('admin.layouts.app')

@section('title', 'Developer Dashboard')


@section('content')

<div>

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            Developer Dashboard
        </h1>

        <p class="text-gray-500">
            Manage your applications, APIs and developer revenue
        </p>

    </div>



    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Applications
            </p>

            <h2 class="text-3xl font-bold mt-2">
                12
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                API Requests
            </p>

            <h2 class="text-3xl font-bold mt-2">
                250K
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                API Revenue
            </p>

            <h2 class="text-3xl font-bold mt-2">
                ₦850K
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Payout Balance
            </p>

            <h2 class="text-3xl font-bold mt-2">
                ₦120K
            </h2>

        </div>


    </div>



    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                Developer Tools
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>API Keys</p>

                <p>Applications</p>

                <p>API Documentation</p>

                <p>Usage Analytics</p>

                <p>Webhooks</p>

            </div>


        </div>



        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                Revenue & Payments
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>API Billing</p>

                <p>Transactions</p>

                <p>Commissions</p>

                <p>Payout Requests</p>

                <p>Settlement History</p>

            </div>


        </div>


    </div>


</div>

@endsection
