@extends('layouts.back-end.app-seller')
@section('title', translate('business_profile_View'))
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

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ asset('/public/assets/back-end/img/my-bank-info.png') }}" alt="">
                {{ translate('my_business') }}
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
                            <h3 class="mb-0">{{ translate('my_business') }}</h3>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('seller.business.upgrade-business') }}" class="btn btn-primary">
                                Upgrade Business
                            </a>
                        </div>
                    </div>
                    <!-- End Card Header -->

                    <!-- Card Body -->
                    <div class="card-body p-30">

                        @if (Session::has('success'))
                            <div class="alert alert-success" style="font-size: 18px">
                                {{ Session::get('success') }}
                            </div>
                        @elseif (Session::has('error'))
                            <div class="alert alert-danger" style="font-size: 18px">
                                {{ Session::get('error') }}
                            </div>
                        @endif

                        <div class="row">

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Business Name: </strong> {{ $seller_data->shop->name }}
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Business Address: </strong> {{ $seller_data->shop->address }}
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Seller Type: </strong> {{ $seller_data->sellertype->name }} Merchant
                                    <i class="tio-star" style="color: {{ $seller_data->sellertype->rank_color }}"></i>
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Total Product Added: </strong>
                                    {{ number_format($seller_data->total_product_added) }}
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Product Limit Creation: </strong>
                                    {{ number_format($seller_data->sellertype->product_limit) }}
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="borderline">
                                    <strong>Product Creation Status: </strong> {!! $seller_data->product_status !!}
                                </div>
                            </div>

                        </div>
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
