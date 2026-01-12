@extends('adminlte::page')

@section('title', 'Mailing Lists')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Mailing Lists</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Mailing Lists</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <livewire:mailing-lists.index />
@stop

@section('js')
    <script>
        console.log('Mailing Lists Wrapper Loaded');
    </script>
@stop
