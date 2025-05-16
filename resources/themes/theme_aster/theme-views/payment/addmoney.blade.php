@extends('theme-views.layouts.app')

@section('title', 'Fund Wallet | ' . $web_config['name']->value . ' ' . translate('ecommerce'))

@section('content')
    <style>
        .btn-outline-secondary {
            background: #fff;
            font-size: 16px;
        }

        .btn-outline-secondary:hover {
            background: #fff;
            font-size: 16px;
            color: var(--title-color)
        }
    </style>
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-4">
        <div class="container">
            <div class="row g-3">

                <!-- Sidebar-->
                @include('theme-views.partials._profile-aside')

                <div class="col-lg-9">


                    <div class="card">
                        <div class="card-header" style="padding: 20px">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h5>Fund Wallet</h5>
                                <span class="btn btn-outline-secondary rounded-pill px-3 px-sm-4">
                                    <span class="d-none d-sm-inline-block">
                                        <b>
                                            {{ \App\CPU\Helpers::currency_converter($total_wallet_balance) }}
                                        </b>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger fs-16">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }} <br>
                                    @endforeach
                                </div>
                            @elseif (session()->has('error'))
                                <div class="alert alert-danger fs-16"> {{ session()->get('error') }} </div>
                            @elseif (session()->has('success'))
                                <div class="alert alert-success fs-16"> {{ session()->get('success') }} </div>
                            @endif

                            @if (session()->has('payment_provider_error'))
                                <div class="alert alert-danger fs-16"> {{ session('payment_provider_error') }} </div>
                            @else
                                <div class="mt-3">
                                    <div class="row gy-3 text-dark">

                                        <form action="{{ route('fundwallet') }}" method="POST">
                                            @csrf
                                            <label>Enter Amount </label>
                                            <input type="text" class="form-control mb-3" name="amount"
                                                placeholder="Enter amount you want to fund">

                                            <label>Select Gateway </label>
                                            <select class="form-control mb-3" name="payment_gateway">
                                                <option value="" disabled>-- Select Payment Channel --</option>
                                                @foreach ($paymentGateways as $gatewayIndex => $gateway)
                                                    <option value="{{ $gatewayIndex }}">{{ $gateway }}</option>
                                                @endforeach
                                            </select>

                                            <div class="col-12">
                                                <div
                                                    class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                                    <label class="custom-checkbox"></label>
                                                    <div class="d-flex justify-content-end gap-3">
                                                        <button type="submit" class="btn btn-primary">Fund
                                                            Wallet</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- End Main Content -->
@endsection
