@extends('layouts.back-end.app-seller')

@section('title', translate('bank_Info'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/back-end')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{asset('/public/assets/back-end/img/my-bank-info.png')}}" alt="">
                {{translate('edit_Bank_info')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0 ">{{translate('edit_Bank_Info')}}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{route('seller.profile.bank_update',[$data->id])}}" method="post">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="name" class="title-color">{{translate('bank_Name')}} <span class="text-danger">*</span></label>
                                        <select name="bank_name" id="" class="form-control">
                                            @if (count($banks) > 0)
                                                @foreach ($banks as $bank)
                                                    <option value="{{ $bank->bank_name }}" {{ $bank->bank_name == $data->bank_name ? "selected" : "" }}>
                                                        {{ $bank->bank_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="account_no" class="title-color">{{translate('holder_Name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="holder_name" value="{{$data->holder_name}}"
                                               class="form-control" id="account_no"
                                               required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="account_no" class="title-color">{{translate('account_No')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="account_no" value="{{$data->account_no}}" maxlength="10"
                                               class="form-control" id="account_no"
                                               required>
                                    </div>

                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-danger" href="{{route('seller.profile.view')}}">{{translate('cancel')}}</a>
                                <button type="submit" class="btn btn--primary" id="btn_update">{{translate('update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')

@endpush
