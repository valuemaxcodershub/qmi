@extends('layouts.back-end.app-seller')
@section('title', translate('upgrade_business_profile'))
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ asset('public/assets/back-end') }}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        .borderline {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #e7eaf3;
        }
    </style>
@endpush

@php
    $sellerCurrentPackage = $seller->sellertype->name;
@endphp

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ asset('/public/assets/back-end/img/my-bank-info.png') }}" alt="">
                {{ translate('upgrade_business_profile') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card" style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                    <!-- Card Header -->
                    <div class="border-bottom d-flex gap-3 flex-wrap justify-content-between align-items-center px-4 py-3">
                        <div class="d-flex gap-2 align-items-center">
                            <img width="20" src="{{ asset('/public/assets/back-end/img/bank.png') }}" alt="" />
                            <h3 class="mb-0">{{ translate('upgrade_business_profile') }}</h3>
                        </div>
                    </div>
                    <!-- End Card Header -->

                    <!-- Card Body -->
                    <div class="card-body p-30">

                        <form action="{{ route('seller.business.next-stage') }}" method="GET">

                            <div class="row">

                                <div class="col-md-12 mt-3">
                                    <label><strong>Business Name:</strong></label><br>
                                    {{ $seller->shop->name }}
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label><strong>Business Address:</strong></label><br>
                                    {{ $seller->shop->address }}
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label><strong>Current Merchant Package</strong></label>
                                    <input class="form-control" value="{{ $seller->sellertype->name }}" disabled>
                                </div>

                                <div class="col-md-6 mt-3">
                                    <label><strong>New Merchant Package</strong></label>
                                    <select class="form-control" name="merchant_package">
                                        <option value="">-- Select Package -- </option>
                                        @if ($sellersPackages != null)
                                            @foreach ($sellersPackages as $sellerPackage)
                                                @if (strtolower($seller->sellertype->name) == 'free')
                                                    <option value="{{ strtolower($sellerPackage['name']) }}"
                                                        {{ strtolower($sellerPackage['name']) == 'super' ? 'disabled' : '' }}>
                                                        {{ $sellerPackage['name'] }} Merchant
                                                        (Fee: &#8358;{{ number_format($sellerPackage['amount'], 2) }})
                                                    </option>
                                                @elseif (strtolower($sellerPackage['allowed_packages']) == strtolower($sellerCurrentPackage))
                                                    <option value="{{ strtolower($sellerPackage['name']) }}">
                                                        {{ $sellerPackage['name'] }} Merchant
                                                        (Fee: &#8358;{{ number_format($sellerPackage['amount'], 2) }})
                                                    </option>
                                                @else
                                                    <option value="" disabled>{{ $sellerPackage['name'] }} Merchant
                                                        (Fee: &#8358;{{ number_format($sellerPackage['amount'], 2) }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="mt-2">
                                <button class="btn btn-primary mb-3" type="submit">
                                    <b>Proceed</b>
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- End Card Body -->
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <!-- Page level plugins -->
@endpush
