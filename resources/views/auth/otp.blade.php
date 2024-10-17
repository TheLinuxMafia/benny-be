@extends('layouts.app')

@section('title', 'OTP')

@section('content')
<div class="container">
    <form method="POST" action="{{ action('Auth\OTPController@check') }}">
        @csrf

        <div class="form-group row">
            <label for="otp" class="col-sm-4 col-form-label text-md-right">
                OTP
            </label>

            <div class="col-md-6">
                <input id="otp"
                       type="number" min="0" max="999999" step="1"
                       class="form-control{{ $errors->has('otp') ? ' is-invalid' : '' }}"
                       autocomplete="off"
                       name="otp" value="" required autofocus>

                @if ($errors->has('otp'))
                    <span class="invalid-feedback">
                        <strong>{{ $errors->first('otp') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        <div class="form-group row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
            </div>
        </div>
    </form>
</div>
@endsection