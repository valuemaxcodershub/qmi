@extends('theme-views.layouts.app')

@section('title', 'Withdraw Money | ' . $web_config['name']->value . ' ' . translate('ecommerce'))

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
                                <h5>Withdraw Money</h5>
                                <span class="btn btn-outline-light rounded-pill px-3 px-sm-4">
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
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li> {{ $error }} </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif (Session::has('error'))
                                <div class="alert alert-danger">
                                    <strong>Error: </strong> {{ Session::get('error') }}
                                </div>
                            @elseif (Session::has('success'))
                                <div class="alert alert-success">
                                    <strong>Success: </strong> {{ Session::get('success') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('initiate-withdrawal') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Amount:</label>
                                        <input type="number" value="10" min="1" class="form-control" name="amount" value="{{ old('amount') }}"
                                            placeholder="Maximum amount withdrawable : {{ number_format($total_wallet_balance, 2) }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Support Pin:</label>
                                        <input type="password" class="form-control" name="transact_pin" maxlength="6" minlength="6" placeholder="Enter security pin">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Bank Name</label>
                                        <input type="text" class="form-control" disabled
                                            value="{{ $bankDetail['bank'] }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label>Account Name</label>
                                        <input type="text" class="form-control" disabled
                                            value="{{ $bankDetail['account_name'] }}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Account Number</label>
                                        <input type="text" class="form-control" disabled
                                            value="{{ $bankDetail['account_number'] }}">
                                    </div>
                                </div>


                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
                                        <button type="submit" class="btn btn-primary"><b>Initiate Withdrawal</b></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- End Main Content -->
@endsection
