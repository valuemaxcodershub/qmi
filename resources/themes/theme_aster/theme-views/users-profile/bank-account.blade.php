@extends('theme-views.layouts.app')

@php
    $bankCode = $bankDetail !== NULL ? $bankDetail['bank_code'] : '';    
@endphp

@section('title', translate('Personal_Details') . ' | ' . $web_config['name']->value . ' ' . translate('ecommerce'))
@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">

                <!-- Sidebar-->
                @include('theme-views.partials._profile-aside')

                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-header p-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h5>Banking Information</h5>
                            </div>
                        </div>
                        <div class="card-body p-lg-4">

                            <div class="col-md-12">
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
                            </div>

                            <div class="mt-4">
                                <form action="{{ route('user-update-bank-account') }}" method="post">
                                    @csrf @method('PUT')

                                    <div class="row gy-4">

                                        <div class="col-md-12">
                                            <label>Select Your Bank</label>
                                            <select class="form-control" name="bank" required>
                                                <option value="">-- Select desired bank --</option>
                                                @if ($allBanks != null)
                                                    @foreach ($allBanks as $bankInfo)
                                                        <option value="{{ $bankInfo['bank_code'] }}" {{ $bankInfo['bank_code'] == $bankCode ? 'selected' : '' }}>
                                                            {{ $bankInfo['bank_name'] }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label>Account Name <small class="text-danger"><em>(This will be auto-generated
                                                        from your bank)</em></small>
                                            </label>
                                            <input class="form-control" name="account_name" disabled
                                                value="{{ $bankDetail !== NULL ? $bankDetail['account_name'] : '' }}" placeholder="Enter account name">
                                        </div>

                                        <div class="col-md-12">
                                            <label>Account Number</label>
                                            <input class="form-control" name="account_number" maxlength="10"
                                                value="{{ $bankDetail !== NULL ? $bankDetail['account_number'] : '' }}" placeholder="Enter account number">
                                        </div>

                                        <div class="col-md-12">
                                            <label>Security Pin</label>
                                            <input type="password" class="form-control" name="transact_pin" placeholder="Enter support pin"
                                                maxlength="6" minlength="6">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-3">
                                                <button type="reset"
                                                    class="btn btn-secondary">{{ translate('Reset') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary">{{ translate('Update_Profile') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <!-- End Main Content -->
@endsection
