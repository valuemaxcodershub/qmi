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

@section('content')
    <div class="content container-fluid">

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card" style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                    <!-- Card Header -->
                    <div class="border-bottom d-flex gap-3 flex-wrap justify-content-between align-items-center px-4 py-3">
                        <div class="d-flex gap-2 align-items-center">
                            <h3 class="mb-0">Store Name</h3>
                        </div>
                    </div>
                    <!-- End Card Header -->

                    <!-- Card Body -->
                    <div class="card-body p-30">

                        <div class="alert alert-info">
                            <b>NOTE: </b> Be sure to use a store name that is yours as we won't allow you to modify this anymore. No space in between your store name if you are joining two words together
                        </div>

                        @if (session()->has('error'))
                            <div class="alert alert-danger fs-16"> {{ session()->get('error') }} </div>
                        @elseif (session()->has('success'))
                            <div class="alert alert-success fs-16"> {{ session()->get('success') }} </div>
                        @endif

                        <form action="{{ route('seller.business.submit-shop-name') }}" method="POST">
                            @csrf @method('PUT')

                            <div class="row">
                                <div class="col-md-12">
                                    <label>Store Name</label>
                                    <input type="text" placeholder="Enter your desired store name" name="store_name"
                                        class="form-control" value="{{ $businessShortcode }}"
                                        {{ $businessShortcode != null ? 'disabled' : '' }}>
                                </div>
                            </div>
                            @if ($businessShortcode == null)
                                <div class="mt-2">
                                    <button class="btn btn-primary mb-3" type="submit">
                                        <b>Submit</b>
                                    </button>
                                </div>
                            @endif
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
