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
                                        <b>Corporate Merchant</b>. Kindly fill all your KYC form adequately to enable
                                        us approve your submission
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3 mb-3">
                                    <h3><u>Section A : Business Information</u></h3>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Company's Name</strong></label>
                                    <input class="form-control" name="company_name">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Company's Email Address</strong></label>
                                    <input class="form-control" name="company_email">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Business Year of Existence</strong></label>
                                    <input type="number" min="1" value="1" class="form-control"
                                        name="business_year">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Company's Mobile Number</strong></label>
                                    <input class="form-control" name="company_phone">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Company's Address</strong></label>
                                    <textarea class="form-control" rows="5" name="company_address"></textarea>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Name of Companies you've done business with in the past
                                            ?</strong></label>
                                    <textarea class="form-control" rows="5" name="partner_companies"></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Corporate Affairs Commision [CAC] Certificate <em>(Optional)</em></strong></label>
                                    <input type="file" class="form-control" name="cac_certificate"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('cac_certificate', 'cacPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Professional Bodies Certificate (NAFDAC/SON/MAN) <em>(Optional)</em></strong></label>
                                    <input type="file" class="form-control" name="professional_body_certificate"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('professional_body_certificate', 'professionalBodyPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Product Registration Number <em>(Optional)</em></strong></label>
                                    <input type="file" class="form-control" name="product_reg_number"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('product_reg_number', 'productRegNoPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Tax Papers and Return <em>(Optional)</em></strong></label>
                                    <input type="file" class="form-control" name="tax_paper"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('tax_paper', 'taxPaperPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-12">
                                    <img id="cacPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">

                                    <img id="professionalBodyPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">

                                    <img id="productRegNoPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">

                                    <img id="taxPaperPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                </div>

                                <div class="col-md-12 mt-3 mb-3">
                                    <h3><u>Section B : Business Representative Information</u></h3>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Business Manager's Name</strong></label>
                                    <input class="form-control" name="business_manager_name">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Business Manager's Phone Number</strong></label>
                                    <input class="form-control" name="business_manager_phone">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Manager's Contact Address</strong></label>
                                    <textarea class="form-control" rows="5" name="business_manager_contact_address"></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Business Manager Identity (NIN, Driver's Licence/Int'l Passport)
                                            <span class="text-danger" style="font-size: 20px">*</span></strong></label>
                                    <input type="file" class="form-control" name="manager_identity"
                                        accept="image/png, image/jpeg, image/jpg"
                                        onchange="previewFile('manager_identity', 'managerIdentityPreview');">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label><strong>Business Manager's Passport <em>(Optional)</em> </strong></label>
                                    <input type="file" class="form-control" name="manager_passport"
                                        onchange="previewFile('manager_passport', 'managerPassportPreview');"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            5MB</em></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <img id="managerIdentityPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                    <img id="managerPassportPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                </div>
                            </div>

                            <div class="mt-2">
                                <button class="btn btn-primary mb-3">
                                    <b>Submit Business Request</b>
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
