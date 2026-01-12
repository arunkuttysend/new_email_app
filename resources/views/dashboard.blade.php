@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="150" text="New Campaigns" icon="fas fa-paper-plane"
                theme="info" url="#" url-text="View all campaigns"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="53%" text="Open Rate" icon="fas fa-chart-bar"
                theme="success" url="#"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="44" text="User Registrations" icon="fas fa-user-plus"
                theme="warning" url="#"/>
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box title="65" text="Unique Visitors" icon="fas fa-chart-pie"
                theme="danger" url="#"/>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Recent Activity" theme="primary" icon="fas fa-history" collapsible>
                <p>Welcome to your new Email Marketing Platform dashboard!</p>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('css')
    {{-- Add extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log("Dashboard loaded!"); </script>
@stop
