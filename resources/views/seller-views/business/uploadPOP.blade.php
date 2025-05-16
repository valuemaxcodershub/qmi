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

                        <form action="{{ route('seller.business.pop-upload') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">

                                <div class="col-md-12 mb-3">
                                    <label><strong>Business Name:</strong></label><br>
                                    {{ $seller->shop->name }}
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Business Address:</strong></label><br>
                                    {{ $seller->shop->address }}
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Current Merchant Package:</strong></label><br>
                                    {{ $seller->sellertype->name }}
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Upgrading To:</strong></label><br>
                                    {{ $seller->sellertype->name }}
                                </div>

                                <div class="col-md-12 mb-3">
                                    <h4>Payment Information</h4>

                                    <div class=""
                                        style="border: 1px solid #c4c4c4; padding: 10px; background: #f9f9fb">
                                        Hey <strong>{{ $seller->f_name . ' ' . $seller->l_name }}</strong>, To complete you
                                        MERCHANT UPGRADE, you are expected to make a payment of
                                        <strong style="font-size: 18px"
                                            class="text-danger">&#8358;{{ number_format($sellerType->amount, 2) }}</strong>
                                        to any of the following bank information provided below <br> <br>

                                        @if ($offlinePayments != null)
                                            @foreach ($offlinePayments as $offlinePayment)
                                                {{ str_replace('_', ' ', $offlinePayment['method_name']) }} <br>
                                                @foreach ($offlinePayment->method_fields as $key => $item)
                                                    {{ str_replace('_', ' ', $item['input_name']) }} <br>
                                                    {{ str_replace('_', ' ', $item['input_data']) }} <br>
                                                    <hr>
                                                @endforeach
                                            @endforeach
                                        @endif

                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label><strong>Select Proof of Payment</strong></label>
                                    <input type="file" class="form-control" name="popupload"
                                        onchange="previewFile('popupload', 'popUploadPreview');"
                                        accept="image/png, image/jpeg, image/jpg">
                                    <input type="hidden" class="form-control" name="reference"
                                        value="{{ request()->query('reference') }}">
                                    <span class="text-danger" style="font-size: 16px"><em>Maximum file upload is
                                            10MB</em></span>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <img id="popUploadPreview" src="/images/example.png" class="img-fluid d-none"
                                        style="width: 100px; height: 100px;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary mb-3" type="submit">
                                    <b>Submit Upgrade Request</b>
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
