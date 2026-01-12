@extends('adminlte::page')

@section('title', 'Connect Inbox')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Connect New Inbox</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inboxes.index') }}">Inboxes</a></li>
                <li class="breadcrumb-item active">Connect</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:inboxes.create />
@stop
