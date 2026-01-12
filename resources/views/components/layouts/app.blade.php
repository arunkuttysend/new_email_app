@extends('adminlte::page')

@section('title', 'Campaigns')

@section('content_header')
    @yield('content_header')
@stop

@section('content')
    {{ $slot }}
@stop

@section('css')
    {{-- Livewire styles handled by AdminLTE config --}}
@stop

@section('js')
    {{-- Livewire scripts handled by AdminLTE config --}}
@stop
