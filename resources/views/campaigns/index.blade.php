@extends('adminlte::page')

@section('title', 'Campaigns')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Campaigns</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Campaigns</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:campaigns.index />
@stop
