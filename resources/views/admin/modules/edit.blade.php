@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Edit Module</h1>
        <p class="text-muted mb-0">
            {{ $module->name }}
        </p>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.modules.update', $module) }}">
        @csrf
        @method('PUT')
        @include('admin.modules._form')
    </form>
</div>
@endsection
