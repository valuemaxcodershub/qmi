@extends('layouts.back-end.app')

@section('title', 'Seller Types MGT')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                Seller Types Management
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="card-header row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Seller Types
                                <span class="badge badge-soft-dark radius-50 fz-12 ml-1">{{ count($sellerTypes) }}</span>
                            </h5>
                            <div class="d-flex gap-2 justify-content-end mr-3">
                                <a href="{{ route('admin.sellers.seller-types-create') }}"
                                    class="btn btn--primary text-nowrap">
                                    <i class="tio-add"></i>
                                    Create A Type
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">

                        @if (Session::has('error'))
                            <div class="alert alert-danger">
                                {{ Session::get('error') }}
                            </div>
                        @elseif (Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="datatable"
                                style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                <thead class="thead-light thead-50 text-capitalize">
                                    <tr>
                                        <th>S/No</th>
                                        <th>Type Name</th>
                                        <th>Upgrade Fee</th>
                                        <th>Boosting Fee</th>
                                        <th>Product Limit</th>
                                        <th>Color Type</th>
                                        <th>Allowed Packages</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sellerTypes as $sellerIndex => $sellerType)
                                        <tr>
                                            <th>{{ $sellerIndex + 1 }}</th>
                                            <th> {{ $sellerType->name }}</th>
                                            <th> &#8358;{{ number_format($sellerType->amount, 2) }}</th>
                                            <th> &#8358;{{ number_format($sellerType->boosting_fee, 2) }}</th>
                                            <th>{{ number_format($sellerType->product_limit) }}</th>
                                            <th><i class="tio-star" style="color: {{ $sellerType->rank_color }}"></i>
                                                ({{ $sellerType->rank_color }})
                                            </th>
                                            <th> {{ $sellerType->allowed_packages }}</th>
                                            <th>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('admin.sellers.seller-types-edit', ['id' => $sellerType->id]) }}"
                                                        class="btn btn-outline-primary btn-sm square-btn">
                                                        <i class="tio-edit"></i></a>
                                                    @if (strtolower($sellerType->name) != 'gold')
                                                        <a href="{{ route('admin.sellers.delete-seller-types', ['id' => $sellerType->id]) }}"
                                                            class="btn btn-outline-danger btn-sm square-btn"
                                                            onclick="return confirm('Want to delete this item ?')">
                                                            <i class="tio-delete"></i></a>
                                                    @endif
                                                </div>
                                            </th>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
