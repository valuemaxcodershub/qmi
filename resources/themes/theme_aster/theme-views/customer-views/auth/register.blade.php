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

                            <h4><strong>Create An Account</strong></h4> <br><br>

                            <form action="{{ route('customer.auth.sign-up') }}" class="forget-password-form" method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="f_name"> {{ translate('First_Name') }}</label>
                                            <input type="text" id="f_name" name="f_name" class="form-control"
                                                placeholder="{{ translate('Ex') }}: Jhone" value="{{ old('f_name') }}"
                                                required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="l_name">{{ translate('Last_Name') }}</label>
                                            <input type="text" id="l_name" name="l_name" class="form-control"
                                                placeholder="{{ translate('Ex') }}: Jhone" value="{{ old('l_name') }}"
                                                required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="email"> {{ translate('email') }}</label>
                                            <input type="text" id="email" value="{{ old('email') }}" name="email"
                                                class="form-control" placeholder="{{ translate('enter_email') }}"
                                                autocomplete="off" required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="phone">{{ translate('phone') }}</label>
                                            <input type="text" id="phone" name="phone" class="form-control"
                                                placeholder="{{ translate('Phone') }}: Jhone"
                                                value="{{ old('enter_phone_number') }}" required />
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="email"> {{ translate('password') }}</label>
                                            <div class="input-inner-end-ele">
                                                <input type="password" id="password" name="password" class="form-control"
                                                    placeholder="{{ translate('minimum_5_characters_long') }}"
                                                    autocomplete="off" required />
                                                <i class="bi bi-eye-slash-fill togglePassword"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 mb-2">
                                        <div class="form-group mb-2">
                                            <label for="confirm_password">{{ translate('Confirm_Password') }}</label>
                                            <div class="input-inner-end-ele">
                                                <input type="password" id="confirm_password" class="form-control"
                                                    name="con_password"
                                                    placeholder="{{ translate('minimum_5_characters_long') }}"
                                                    autocomplete="off" required />
                                                <i class="bi bi-eye-slash-fill togglePassword"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="d-flex justify-content-center gap-3 mt-5">
                                    <button class="btn btn-outline-primary" onclick="location.href='{{ route('home') }}'"
                                        type="button">{{ translate('back_again') }}</button>

                                    <button type="submit" id="sign-up"
                                        class="btn btn-primary px-5">{{ translate('Sign_Up') }}</button>
                                </div>

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
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

@endsection
