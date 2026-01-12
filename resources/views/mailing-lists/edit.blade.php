@extends('adminlte::page')

@section('title', 'Edit Mailing List')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Edit Mailing List</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lists.index') }}">Mailing Lists</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Pass the ID from the route parameter to the Livewire component -->
    <livewire:mailing-lists.edit :mailing-list="$mailingList" />
@stop
