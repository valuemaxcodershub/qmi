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
                    <div class="card-body">


                        <div class="p-3">
                            <div class="card-header row justify-content-between align-items-center">
                                <h5 class="ml-3">
                                    States
                                    <span class="badge badge-soft-dark radius-50 fz-12 ml-1"> {{ count($allStates) }}</span>
                                </h5>
                                <div class="d-flex gap-2 justify-content-end mr-3">
                                    <a href="{{ route('admin.state.add') }}" class="btn btn--primary text-nowrap">
                                        <i class="tio-add"></i>
                                        Create State
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if (Session::has('success'))
                                    <div class="alert alert-success">
                                        <strong>Success: </strong> {{ Session::get('success') }}
                                    </div>
                                @elseif (Session::has('error'))
                                    <div class="alert alert-danger">
                                        <strong>Error: </strong> {{ Session::get('error') }}
                                    </div>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <th>S/No.</th>
                                        <th>State</th>
                                        <th>Action</th>
                                    </tr>
                                    @if ($allStates != null)
                                        @foreach ($allStates as $index => $state)
                                            <tr>
                                                <td> {{ $index + 1 }}</td>
                                                <td> {{ $state['state_name'] }}</td>
                                                <td>
                                                    <a href="{{ route('admin.state.delete', ['id' => $state['id']]) }}">
                                                        <i class="tio-trash"></i> Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
