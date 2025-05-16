@extends('layouts.back-end.app')

@section('title', 'State Management')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ asset('/public/assets/back-end/img/add-new-delivery-man.png') }}" alt="">
                State Management
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- End Page Header -->
                    <div class="p-3">
                        <div class="card-header row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Create States
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">

                        @if (Session::has('error'))
                            <div class="alert alert-danger">
                                <strong>Success: </strong> {{ Session::get('error') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.state.add') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="title-color d-flex" for="exampleFormControlInput1"><strong>State
                                                Name</strong></label>
                                        <input type="text" name="state_name" value="{{old('state_name')}}" class="form-control form-control-lg"
                                            placeholder="Enter State Name" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-content-end">
                                <button type="reset" id="reset"
                                    class="btn btn-secondary px-4">{{ translate('reset') }}</button>
                                <button type="submit"
                                    class="btn btn--primary px-4">{{ translate('Create_state') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
