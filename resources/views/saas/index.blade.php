@extends('admin.layouts.app')

@section('title', 'SaaS Owner Dashboard')


@section('content')

<div>

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-gray-800">
            SaaS Owner Dashboard
        </h1>

        <p class="text-gray-500">
            Manage your SaaS business, customers and subscriptions
        </p>

    </div>



    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                SaaS Products
            </p>

            <h2 class="text-3xl font-bold mt-2">
                8
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Customers
            </p>

            <h2 class="text-3xl font-bold mt-2">
                2,450
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Monthly Revenue
            </p>

            <h2 class="text-3xl font-bold mt-2">
                ₦5.8M
            </h2>

        </div>


        <div class="bg-white rounded-xl shadow p-6">

            <p class="text-gray-500">
                Active Subscriptions
            </p>

            <h2 class="text-3xl font-bold mt-2">
                1,820
            </h2>

        </div>


    </div>



    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">


        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                SaaS Management
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>Products</p>

                <p>Plans & Pricing</p>

                <p>Customers</p>

                <p>Subscriptions</p>

                <p>Usage Analytics</p>

            </div>


        </div>



        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-xl font-bold mb-4">
                Business Analytics
            </h2>


            <div class="space-y-3 text-gray-700">

                <p>Revenue Reports</p>

                <p>Invoices</p>

                <p>Payments</p>

                <p>Churn Analytics</p>

                <p>Customer Growth</p>

            </div>


        </div>


    </div>


</div>

@endsection
