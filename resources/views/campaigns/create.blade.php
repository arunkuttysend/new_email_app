@extends('adminlte::page')

@section('title', 'New Campaign')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>New Campaign</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('campaigns.index') }}">Campaigns</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:campaigns.create />
@stop
