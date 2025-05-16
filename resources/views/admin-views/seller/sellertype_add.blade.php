@extends('layouts.back-end.app')

@section('title', 'Create Seller Types')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                Create Seller Types
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Create Seller Types
                            </h5>
                        </div>
                    </div>
                    <div class="p-3">

                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.sellers.submit-seller-types-create') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Type Name</label>
                                        <input class="form-control" name="seller_type" value="{{ old('seller_type') }}"
                                            placeholder="Enter seller type name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Product Limit <span style="color: red; font-size: 12px"><em>No of Product to
                                                    display per store</em></span></label>
                                        <input class="form-control" name="seller_product_limit"
                                            value="{{ old('seller_product_limit') }}" placeholder="Enter Product Limit: 5"
                                            value="1" type="number" min="1">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Amount</label>
                                        <input class="form-control" name="amount" value="{{ old('amount') }}"
                                            placeholder="Enter amount">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label>Boosting Fee</label>
                                        <input class="form-control" name="boosting_fee"
                                            value="{{ old('boosting_fee') }}" placeholder="Enter boosting fee">
                                    </div>
                                </div>

                                <div class="row">                                    
                                    <div class="col-md-6 mb-3">
                                        <label>Rank Color</label>
                                        <input class="form-control" name="seller_rank_color"
                                            value="{{ old('seller_rank_color') }}" placeholder="Enter rank color">
                                    </div>
                                    
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Allowed Packages <small><em>(Optional)</em></small></label> <br />
                                        @foreach ($sellerTypes as $sellertype)
                                            <label>
                                                <input type="checkbox" name="allowedPackages[]"
                                                    value="{{ $sellertype->name }}"> {{ $sellertype->name }}
                                            </label> <br />
                                        @endforeach
                                    </div>
                                </div>

                                <hr />
                                <div class="row">
                                    <button type="submit" class="btn btn-primary btn-outline">Create Types</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
