@extends('adminlte::page')

@section('title', 'Import Subscribers')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Import Subscribers</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscribers.index') }}">Subscribers</a></li>
                <li class="breadcrumb-item active">Import</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:subscribers.import />
@stop
