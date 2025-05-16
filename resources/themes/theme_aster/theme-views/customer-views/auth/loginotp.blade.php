@extends('theme-views.layouts.app')

@section('title', translate('Verify OTP') . ' | ' . $web_config['name']->value . ' ' . translate('ecommerce'))

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-sm-5">
        <div class="container">
            <div class="card">
                <div class="card-body py-5 px-lg-5">
                    <div class="row align-items-center pb-5">
                        <div class="col-lg-12">

                            <h4><strong>Login Account</strong></h4> <br><br>

                            <div class="alert alert-info">
                                Please enter the OTP Code that was sent to your email address
                            </div>

                            <form action="{{ route('customer.auth.submitOtp') }}" class="forget-password-form" method="post">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="recover-email">Client Name</label>
                                    <input class="form-control" type="text" value="{{ $name }}" disabled>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="recover-email">Email Address</label>
                                    <input class="form-control" type="text" value="{{ $email }}" disabled>
                                    <input type="hidden" name="email" value="{{ $email }}">
                                </div>

                                <div class="form-group mb-2">
                                    <label for="recover-email">OTP Code</label>
                                    <input class="form-control" type="text" name="otpcode"
                                        placeholder="Enter your otp code">
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-5">
                                    <button class="btn btn-outline-primary" onclick="location.href='{{ route('home') }}'"
                                        type="button">{{ translate('resend_otp') }}</button>
                                    <button class="btn btn-primary px-sm-5"
                                        type="submit">{{ translate('Verify_Otp') }}</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

@endsection
