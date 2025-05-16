@extends('theme-views.layouts.app')

@section('title', translate('Customer_Login') . ' | ' . $web_config['name']->value . ' ' . translate('ecommerce'))

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-sm-5">
        <div class="container">
            <div class="card">
                <div class="card-body py-5 px-lg-5">
                    <div class="row align-items-center pb-5">
                        <div class="col-lg-12">

                            <h4><strong>Login Account</strong></h4> <br><br>

                            <form action="{{ route('customer.auth.login') }}" class="forget-password-form" method="post">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="recover-email">Email Address</label>
                                    <input class="form-control" type="text" name="email"
                                        placeholder="Enter a valid email address" required>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="recover-email">Password</label>
                                    <input class="form-control" type="password" name="password"
                                        placeholder="Enter your password" placeholder="Enter password">
                                </div>

                                <div class="d-flex justify-content-center gap-3 mt-5">
                                    <button class="btn btn-outline-primary" onclick="location.href='{{ route('home') }}'"
                                        type="button">{{ translate('back_again') }}</button>
                                    <button class="btn btn-primary px-sm-5"
                                        type="submit">{{ translate('proceed_to_login') }}</button>
                                </div>

                            </form>

                            @if ($web_config['social_login_text'])
                                <p class="text-center text-muted m-4">{{ translate('or_continue_with') }}</p>
                            @endif
                            <div class="d-flex justify-content-center gap-3 align-items-center flex-wrap pb-3">
                                @foreach ($web_config['socials_login'] as $socialLoginService)
                                    @if (isset($socialLoginService) && $socialLoginService['status'] == true)
                                        <a
                                            href="{{ route('customer.auth.service-login', $socialLoginService['login_medium']) }}">
                                            <img width="35"
                                                src="{{ theme_asset('assets/img/svg/' . $socialLoginService['login_medium'] . '.svg') }}"
                                                alt="" class="dark-support" />
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

@endsection
