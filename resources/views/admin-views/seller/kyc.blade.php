@extends('layouts.back-end.app')

@section('title', 'Seller KYC')

@push('css_or_js')
    <style>
        .pair-list>div {
            flex-wrap: wrap;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/add-new-seller.png') }}" alt="">
                {{ translate('KYC_verification') }}
            </h2>
        </div>
        <!-- End Page Title -->


        <div class="card card-top-bg-element mb-5">
            <div class="card-body">

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @elseif (session()->has('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.sellers.verify-nin') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>NIN Number</label>
                        <input type="text" class="form-control form-control-lg" name="ninNumber"
                            value="{{ old('ninNumber') }}" placeholder="Enter NIN Number">
                    </div>

                    <div class="form-group">
                        <label>Client Email</label>
                        <input type="email" class="form-control form-control-lg" name="email"
                            value="{{ old('email') }}" placeholder="Enter client email address">
                    </div>

                    <div class="text-center">
                        <button class="btn btn-primary" type="submit">
                            <strong>Verify NIN</strong>
                        </button>
                    </div>
                </form>

                <hr>
                {{-- @if (session()->has('info'))
                    @php
                        $sellerData = session()->has('info')['user'];
                    @endphp
                    {{ $sellerData }}
                    {{ session()->has('info') }}
                @endif --}}

            </div>
        </div>

    </div>
@endsection

@push('script')
@endpush
