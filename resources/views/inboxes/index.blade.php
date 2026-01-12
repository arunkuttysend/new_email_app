@extends('adminlte::page')

@section('title', 'Inboxes')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Inboxes</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Inboxes</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:inboxes.index />
@stop
