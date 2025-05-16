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

                        @if ($errors->any())
                            <div class="alert alert-danger" style="font-size: 18px">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (Session::has('error'))
                            <div class="alert alert-danger" style="font-size: 18px">
                                {{ Session::get('error') }}
                            </div>
                        @endif

                        <form action="{{ route('seller.business.submit-upgrade') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

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
                                    <label><strong>Current Merchant Package</strong></label><br>
                                    {{ $seller->sellertype->name }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3 mt-3">
                                    <div class="alert alert-danger">
                                        <i class="fa fa-exclamation-circle"></i> You are upgrading your account to an
                                        <b>Individual Merchant</b>. Kindly fill all your KYC form adequately to enable
                                        us approve your submission
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Contact Address</strong></label>
                                    <textarea class="form-control" rows="5" name="individual_contact_address"></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>City</strong></label>
                                    <input class="form-control" name="individual_city">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Local Goverment Area</strong></label>
                                    <input class="form-control" name="individual_lga">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Upload Identity (Driver's License / Int'l
                                            Passport)</strong></label>
                                    <input type="file" class="form-control" name="individual_identity"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('individual_identity', 'identityPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Upload Passport</strong></label>
                                    <input type="file" class="form-control" name="individual_passport"
                                        onchange="previewFile('individual_passport', 'passportPreview');"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            5MB</em></span>
                                </div>

                                @if ($seller->is_nin_verified == '0')
                                    <div class="col-md-6 mb-3">
                                        <label><strong>Upload NIN Slip</strong>
                                            <span>
                                                <em>(Upload a clearer picture of your NIN Slip)</em>
                                            </span>
                                        </label>
                                        <input type="file" class="form-control" name="ninSlip"
                                            onchange="previewFile('ninSlip', 'ninSlipPreview');"
                                            accept="image/png, image/jpeg, image/jpg">
                                        <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                                5MB</em></span>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label><strong>Provide NIN Number</strong></label>
                                        <input type="text" class="form-control" name="ninNumber">
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <img id="identityPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                    <img id="passportPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                    <img id="ninSlipPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                </div>
                            </div>

                            @if ($seller->is_nin_verified == '0')
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong class="text-danger">NOTE: </strong> <br />
                                    </div>
                                    <ol>
                                        <li>Upload a clearer picture of your NIN Slip</li>
                                        <li>Ensure that your Registered name on <strong>PAVI NG</strong> is same as it appears
                                            on your NIN Slip </li>
                                        <li>Should there be any irregularities, your application will be cancelled</li>
                                    </ol>
                                </div>
                            @endif

                            <div class="mt-2">
                                <button class="btn btn-primary mb-3">
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
    <script>
        function previewFile(input, previewField) {
            var file = $(`input[name=${input}]`).get(0).files[0];

            if (file) {
                var reader = new FileReader();

                reader.onload = function() {
                    $(`#${previewField}`).attr("src", reader.result).removeClass('d-none');
                }

                reader.readAsDataURL(file);
            }
        }
    </script>
@endpush
