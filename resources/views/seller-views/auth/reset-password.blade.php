<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required Meta Tags Always Come First -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Title -->
    <title>{{ translate('forgot_password') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/vendor.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/style.css">
    <link rel="stylesheet" href="{{ asset('public/assets/back-end') }}/css/toastr.css">
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
                <img class="z-index-2 __w-8rem" src="{{ asset('storage/app/public/company/' . $e_commerce_logo) }}"
                    alt="Logo" onerror="this.src='{{ asset('public/assets/back-end/img/400x400/img2.jpg') }}'">
            </a>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <h2 class="h3 mb-4">{{ translate('forgot_password?') }}</h2>
                    <div class="card py-2 mt-4">
                        <form class="card-body needs-validation" action="{{request('seller.auth.reset-password')}}"
                            method="POST">
                            @csrf

                            @if ($errors->any() || session()->has('error'))
                                <div class="alert alert-danger" style="font-size: 18px">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                        @if (session()->has('error'))
                                            <li>{{ session()->get('error') }}</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="si-password">Reset Token</label>
                                <input class="form-control" type="text" value="{{ $token }}" disabled>
                                <input class="form-control" type="hidden" name="reset_token"
                                    value="{{ $token }}">
                            </div>

                            <div class="form-group">
                                <label for="si-password">New Password</label>
                                <input class="form-control" name="password" type="password"
                                    placeholder="Enter new password" required minlength="5">
                                <div class="invalid-feedback">{{ translate('provide_valid_password') }}.</div>
                            </div>

                            <div class="form-group">
                                <label for="si-password">Current Password</label>
                                <input class="form-control" name="confirm_password" type="password"
                                    placeholder="Retype new password" required minlength="5">
                                <div class="invalid-feedback">{{ translate('provide_valid_password') }}.</div>
                            </div>

                            <button class="btn btn-primary" type="submit">Reset Password</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Content -->
    </main>
    <!-- ========== END MAIN CONTENT ========== -->
</body>

</html>
