@extends('layouts.back-end.app')

@section('title', 'Payout Banks')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/3rd-party.png') }}" alt="">
                Payout Banks
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="card">
            <div class="card-header">
                <h4>Payout Banks</h4>
                <div class="d-flex gap-2 justify-content-end mr-3">
                    <a href="{{ route('admin.payment-method.fetch-payout-banks') }}" class="btn btn--primary text-nowrap"
                        onclick="return confirm('This will erase all existing payout banks and fetch all banks again')">
                        <i class="tio-refresh"></i>
                        Generate Banks
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="col-md-12">
                    @if (Session::has('error'))
                        <div class="alert alert-danger">
                            <strong>Error: </strong> {{ Session::get('error') }}
                        </div>
                    @elseif (Session::has('success'))
                        <div class="alert alert-success">
                            <strong>Success: </strong> {{ Session::get('success') }}
                        </div>
                    @endif
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>S/No</th>
                            <th>Bank Name</th>
                            <th>Bank Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($allBanks) > 0)
                            @foreach ($allBanks as $bankIndex => $bankInfo)
                                <tr>
                                    <td>{{ $bankIndex + 1 }}</td>
                                    <td>{{ $bankInfo['bank_name'] }}</td>
                                    <td>{{ $bankInfo['bank_code'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
