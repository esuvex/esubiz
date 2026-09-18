@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Create Module</h1>
        <p class="text-muted mb-0">
            Configure a Module for the Esubiz Marketplace and Website Types.
        </p>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.modules.store') }}">
        @csrf
        @include('admin.modules._form')
    </form>
</div>
@endsection
