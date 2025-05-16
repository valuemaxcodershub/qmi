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
                                <h5>Set Security Pin</h5>
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

                            @if ($customerDetail->transact_pin == '000000')
                                <div class="alert alert-info">
                                    <span class="fs-18">
                                        <strong class="text-danger">NOTE: </strong> Your default transact pin is
                                        <strong class="text-dark">{{ $customerDetail->transact_pin }}</strong>. Please
                                        ensure to use a secure code as this will be requested whenever you are transacting on our platform
                                    </span>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <span class="fs-18">
                                        <strong class="text-danger">NOTE: </strong> Please ensure to use a secure code as
                                        this will be
                                        requested whenever you are transacting on our platform
                                    </span>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('user-update-securitypin') }}">
                                @csrf @method('PUT')
                                <div class="col-md-12 mb-3 mt-3">
                                    <label>Current Pin</label>
                                    <input type="password" class="form-control" minlength="6" maxlength="6"
                                        name="current_pin" id="current_pin" placeholder="Enter your current pin">
                                </div>

                                <div class="col-md-12 mb-3 mt-3">
                                    <label>New Pin</label>
                                    <input type="password" class="form-control" minlength="6" maxlength="6" name="new_pin"
                                        id="new_pin" placeholder="Enter your new pin">
                                </div>

                                <div class="col-md-12 mb-3 mt-3">
                                    <label>Re-type Pin</label>
                                    <input type="password" class="form-control" minlength="6" maxlength="6"
                                        name="retype_pin" id="retype_pin" placeholder="Retype your new pin">
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-3">
                                        <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
                                        <button type="submit" class="btn btn-primary"><b>Update</b></button>
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
