@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <form action="{{ route('two-factor.login') }}" method="post">
        @csrf

        <p class="text-muted">
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        </p>

        <div class="input-group mb-3">
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                   placeholder="{{ __('Authentication Code') }}" autofocus autocomplete="one-time-code">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
            @error('code')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    {{ __('Login') }}
                </button>
            </div>
        </div>
    </form>

    <div class="mt-3 text-center">
        <p class="text-muted">
            {{ __('Or, confirm access to your account by entering one of your emergency recovery codes.') }}
        </p>

        <form action="{{ route('two-factor.login') }}" method="post">
            @csrf
            
            <div class="input-group mb-3">
                <input type="text" name="recovery_code" class="form-control @error('recovery_code') is-invalid @enderror"
                       placeholder="{{ __('Recovery Code') }}" autocomplete="one-time-code">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-key"></span>
                    </div>
                </div>
                @error('recovery_code')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-secondary btn-block btn-flat">
                        {{ __('Use Recovery Code') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop
