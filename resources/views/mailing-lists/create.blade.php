@extends('adminlte::page')

@section('title', 'Create Mailing List')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Create Mailing List</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('lists.index') }}">Mailing Lists</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:mailing-lists.create />
@stop
