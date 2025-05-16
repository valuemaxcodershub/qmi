<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title>{{ translate('seller_login') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/vendor.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/toastr.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/style.css">
</head>

<body>
    <!-- ========== MAIN CONTENT ========== -->
    <main id="content" role="main" class="main">
        <div class="position-fixed top-0 right-0 left-0 bg-img-hero __h-32rem"
            style="background-image: url({{ asset('public/assets/admin') }}/svg/components/abstract-bg-4.svg);">
            <!-- SVG Bottom Shape -->
            <figure class="position-absolute right-0 bottom-0 left-0">
                <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                    viewBox="0 0 1921 273">
                    <polygon fill="#fff" points="0,273 1921,273 1921,0 " />
                </svg>
            </figure>
            <!-- End SVG Bottom Shape -->
        </div>

        <!-- Content -->
        <div class="container py-5 py-sm-7">
            @php($e_commerce_logo = \App\Model\BusinessSetting::where(['type' => 'company_web_logo'])->first()->value)
            <a class="d-flex justify-content-center mb-5" href="javascript:">
                <img class="z-index-2" height="40"
                    src="{{ asset('storage/app/public/company/' . $e_commerce_logo) }}" alt="Logo"
                    onerror="this.src='{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}'">
            </a>

            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">
                    <!-- Card -->
                    <div class="card card-lg mb-5">
                        <div class="card-body">
                            <!-- Form -->
                            <form id="form-id" action="{{ route('seller.auth.submitotp') }}" method="post">
                                @csrf

                                <div class="text-center">
                                    <div class="mb-5">
                                        <h1 class="display-4">{{ translate('verify_your_sign_in') }}</h1>
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">
                                                {{ translate('enter_your_otp_code') }}</h1>
                                        </div>
                                    </div>

                                </div>

                                <div class="js-form-message form-group">
                                    <label class="input-label"
                                        for="signinSrEmail">{{ translate('your_email') }}</label>

                                    <input type="email" class="form-control form-control-lg"
                                        value="{{ $email }}" disabled>
                                    <input type="hidden" name="email" class="form-control form-control-lg"
                                        value="{{ $email }}">
                                </div>

                                <div class="js-form-message form-group">
                                    <label class="input-label" for="signinSrEmail">{{ translate('your_name') }}</label>
                                    <input class="form-control form-control-lg" value="{{ $name }}" disabled>
                                </div>

                                <div class="js-form-message form-group">
                                    <label class="input-label" for="signinSrEmail">OTP Code</label>
                                    <input class="form-control form-control-lg" name="otptoken"
                                        placeholder="Enter otp code">
                                </div>
                                <!-- End Form Group -->

                                <button type="submit"
                                    class="btn btn-lg btn-block btn--primary">{{ translate('sign_in') }}</button>
                            </form>
                            <!-- End Form -->
                        </div>
                        @if (env('APP_MODE') == 'demo')
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-10">
                                        <span>{{ translate('email') }} : test.seller@gmail.com</span><br>
                                        <span>{{ translate('password') }} : 12345678</span>
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn--primary" onclick="copy_cred()"><i class="tio-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- End Card -->
                </div>
            </div>
        </div>
        <!-- End Content -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->


    <!-- JS Implementing Plugins -->
    <script src="{{ asset('public/assets/back-end') }}/js/vendor.min.js"></script>

    <!-- JS Front -->
    <script src="{{ asset('public/assets/back-end') }}/js/theme.min.js"></script>
    <script src="{{ asset('public/assets/back-end') }}/js/toastr.js"></script>
    {!! Toastr::message() !!}

    @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}', Error, {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endforeach
        </script>
    @endif

    <!-- JS Plugins Init. -->
    <script>
        $(document).on('ready', function() {
            // INITIALIZATION OF SHOW PASSWORD
            // =======================================================
            $('.js-toggle-password').each(function() {
                new HSTogglePassword(this).init()
            });

            // INITIALIZATION OF FORM VALIDATION
            // =======================================================
            $('.js-validate').each(function() {
                $.HSCore.components.HSValidation.init($(this));
            });
        });
    </script>

    <!-- IE Support -->
    <script>
        if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write(
            '<script src="{{ asset('public/assets/admin') }}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
    </script>
</body>

</html>
