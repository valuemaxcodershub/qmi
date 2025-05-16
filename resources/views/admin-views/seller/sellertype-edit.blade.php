@extends('layouts.back-end.app')

@section('title', 'Update Seller Type')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                Update Seller Type
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="card-header row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Update Seller Type
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">

                        @if (Session::has('error') or $errors->any())

                            <div class="alert alert-danger" style="font-size: 18px">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    @if (session()->has('error'))
                                        <li>{{ session()->get('error') }}</li>
                                    @endif
                                </ul>
                            </div>
                        @elseif (Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.sellers.seller-types-update') }}">
                            @csrf @method('PUT')
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Type Name</label>
                                    <input class="form-control" name="seller_type" value="{{ $typeInfo->name }}"
                                        placeholder="Enter seller type name">
                                    <input type="hidden" name="type_id" value="{{ $typeInfo->id }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Product Limit <span style="color: red; font-size: 12px"><em>No of Product to
                                                display per
                                                store</em></span></label>
                                    <input class="form-control" name="seller_product_limit"
                                        value="{{ $typeInfo->product_limit }}" placeholder="Enter Product Limit: 5"
                                        value="1" type="number" min="1">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Rank Color</label>
                                    <input class="form-control" name="seller_rank_color" value="{{ $typeInfo->rank_color }}"
                                        placeholder="Enter rank color">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Amount</label>
                                    <input class="form-control" name="amount" value="{{ $typeInfo->amount }}"
                                        placeholder="Enter amount">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Boosting Fee</label>
                                    <input class="form-control" name="boosting_fee" value="{{ $typeInfo->boosting_fee }}"
                                        placeholder="Enter boosting fee">
                                </div>
                            </div>

                            @if (strtolower($typeInfo->name) != 'gold')
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label>Allowed Packages <small><em>(Optional)</em></small></label> <br />
                                        @foreach ($sellerTypes as $sellertype)
                                            @if ($sellertype->name != $typeInfo->name)
                                                <label>
                                                    <input type="checkbox" name="allowedPackages[]"
                                                        value="{{ $sellertype->name }}"
                                                        {{ in_array(strtolower($sellertype->name), explode(',', $typeInfo->allowed_packages)) ? 'checked' : '' }}>
                                                    {{ $sellertype->name }}
                                                </label> <br />
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <hr />
                            <div class="row">
                                <button type="submit" class="btn btn-primary btn-outline">Update Types</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
