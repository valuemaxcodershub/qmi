@extends('layouts.back-end.app')

@section('title', "Seller's Upgrade Request")

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                Seller's Upgrade Request
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="card-header row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Upgrade Request
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="col-md-12">
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
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>S/No</th>
                                        <th>Seller Name</th>
                                        <th>Previous Package</th>
                                        <th>New Package</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($sellerUpgrades != null)
                                        @foreach ($sellerUpgrades as $upgradeIndex => $upgradeData)
                                            <tr>
                                                <td>{{ $upgradeIndex + 1 }}</td>
                                                <td>{{ $upgradeData->seller->f_name . ' ' . $upgradeData->seller->l_name }}
                                                </td>
                                                <td>{{ $upgradeData->current_seller_type_name }}</td>
                                                <td>{{ $upgradeData->new_seller_type_name }}</td>
                                                <td>{!! $upgradeData->statusHtml !!}</td>
                                                <td>
                                                    <a title="View" class="btn btn-outline-info btn-sm square-btn"
                                                        href="{{ route('admin.sellers.view-upgrade-request', ['reference' => $upgradeData->reference]) }}">
                                                        <i class="tio-invisible"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection
