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

                        <form method="POST" action="{{ route('admin.sellers.seller-types-settingsupdate') }}">
                            @csrf @method('PUT')

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label>Set Default Type </label> <br />
                                    <select name="defaultTypeId" id="" class="form-control" required>
                                        <option value=""> -- Please select a default type --</option>
                                        @foreach ($sellerTypes as $sellertype)
                                            <option value="{{ $sellertype->id }}" {{ $sellertype->id == $defaultSettings->value ? 'selected' : '' }}>
                                                {{ $sellertype->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

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
