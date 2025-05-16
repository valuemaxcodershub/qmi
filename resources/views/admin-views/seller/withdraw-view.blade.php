@extends('layouts.back-end.app')
@section('title', translate('Withdraw information View'))
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ asset('public/assets/back-end') }}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="{{ asset('public/assets/back-end/css/croppie.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">

        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ asset('/public/assets/back-end/img/withdraw-icon.png') }}" alt="">
                {{ translate('withdraw') }}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Page Heading -->
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-body"
                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                        <div
                            class="text-capitalize d-flex align-items-center justify-content-between gap-2 border-bottom pb-2 mb-4">
                            <h3 class="text-capitalize">
                                {{ translate('Withdraw_Information') }}
                            </h3>

                            <i class="tio-wallet-outlined fz-30"></i>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="flex-start flex-wrap">
                                    <div>
                                        <h5 class="text-capitalize">{{ translate('amount') }} : </h5>
                                    </div>
                                    <div class="mx-1">
                                        <h5>{{ \App\CPU\BackEndHelper::set_symbol(\App\CPU\Convert::default($withdraw_request->amount)) }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="flex-start flex-wrap">
                                    <div>
                                        <h5>{{ translate('request_time') }} : </h5>
                                    </div>
                                    <h5>{{ $withdraw_request->created_at }}</h5>
                                </div>
                                <div class="flex-start flex-wrap">
                                    <div>
                                        <h5>{{ translate('request_time') }} : </h5>
                                    </div>
                                    <h5>{!! $withdraw_request->statusHtml !!}</h5>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0" style="visibility: hidden">
                                <div class="flex-start">
                                    <div class="title-color">{{ translate('status') }} :</div>
                                    <h5>{{ $withdraw_request->approved }}</h5>
                                </div>
                            </div>
                            <div class="col-md-4">
                                @if ($withdraw_request->approved == 0)
                                    <button type="button"
                                        class="btn btn-success float-{{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}"
                                        data-toggle="modal" data-target="#exampleModal">{{ translate('proceed') }}
                                        <i class="tio-arrow-forward"></i>
                                    </button>
                                @else
                                    <div
                                        class="text-center float-{{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}">
                                        @if ($withdraw_request->approved == 1)
                                            <label class="badge badge-success p-2 rounded-bottom">
                                                {{ translate('approved') }}
                                            </label>
                                        @else
                                            <label class="badge badge-danger p-2 rounded-bottom">
                                                {{ translate('denied') }}
                                            </label>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body"
                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">

                        <div
                            class="text-capitalize d-flex align-items-center justify-content-between gap-2 border-bottom pb-3 mb-4">
                            <h3 class="h3 mb-0">{{ translate('my_bank_info') }} </h3>
                            <i class="tio tio-dollar-outlined"></i>
                        </div>

                        <div class="mt-2">
                            <div class="flex-start">
                                <div>
                                    <h4>{{ translate('bank_name') }} : </h4>
                                </div>
                                <div class="mx-1">
                                    <h6> {{ $withdrawal_method->bank }}</h6>
                                </div>
                            </div>
                            <div class="flex-start">
                                <div>
                                    <h6>{{ translate('holder_name') }} : </h6>
                                </div>
                                <div class="mx-1">
                                    <h6> {{ $withdrawal_method->account_name }}</h6>
                                </div>
                            </div>
                            <div class="flex-start">
                                <div>
                                    <h6>{{ translate('account_no') }} : </h6>
                                </div>
                                <div class="mx-1">
                                    <h6> {{ $withdrawal_method->account_number }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($withdraw_request->seller->shop))
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body"
                            style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">

                            <div
                                class="text-capitalize d-flex align-items-center justify-content-between gap-2 border-bottom pb-3 mb-4">
                                <h3 class="h3 mb-0">{{ translate('shop_info') }} </h3>
                                <i class="tio tio-shop-outlined"></i>
                            </div>

                            <div class="flex-start">
                                <div>
                                    <h5>{{ translate('seller') }} : </h5>
                                </div>
                                <div class="mx-1">
                                    <h5>{{ $withdraw_request->seller->shop->name }}</h5>
                                </div>
                            </div>
                            <div class="flex-start">
                                <div>
                                    <h5>{{ translate('phone') }} : </h5>
                                </div>
                                <div class="mx-1">
                                    <h5>{{ $withdraw_request->seller->shop->contact }}</h5>
                                </div>
                            </div>
                            <div class="flex-start">
                                <div>
                                    <h5>{{ translate('address') }} : </h5>
                                </div>
                                <div class="mx-1">
                                    <h5>{{ $withdraw_request->seller->shop->address }}</h5>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body"
                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                        <div
                            class="text-capitalize d-flex align-items-center justify-content-between gap-2 border-bottom pb-3 mb-4">
                            <h3 class="h3 mb-0">{{ translate('account_info') }} </h3>
                            <i class="tio tio-user-big-outlined"></i>
                        </div>
                        <div class="flex-start">
                            <div>
                                <h5>{{ translate('name') }} : </h5>
                            </div>
                            <div class="mx-1">
                                <h5>{{ isset($withdraw_request->seller)
                                    ? $withdraw_request->seller->f_name . ' ' . $withdraw_request->seller->l_name
                                    : $withdraw_request->user->f_name . ' ' . $withdraw_request->user->l_name }}
                                </h5>
                            </div>
                        </div>
                        <div class="flex-start">
                            <div>
                                <h5>{{ translate('email') }} : </h5>
                            </div>
                            <div class="mx-1">
                                <h5>{{ isset($withdraw_request->seller) ? $withdraw_request->seller->email : $withdraw_request->user->email }}
                                </h5>
                            </div>
                        </div>
                        <div class="flex-start">
                            <div>
                                <h5>{{ translate('phone') }} : </h5>
                            </div>
                            <div class="mx-1">
                                <h5>{{ isset($withdraw_request->seller) ? $withdraw_request->seller->phone : $withdraw_request->user->phone }}
                                </h5>
                            </div>
                        </div>
                        <div class="flex-start">
                            <div>
                                <h5>{{ translate('account_type') }} : </h5>
                            </div>
                            <div class="mx-1">
                                <h5>{{ isset($withdraw_request->seller) ? 'Seller' : 'User' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{ translate('withdraw_request_process') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('admin.sellers.withdraw_status', [$withdraw_request['id']]) }}"
                            method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name"
                                        class="col-form-label">{{ translate('request') }}:</label>
                                    <select name="approved" class="custom-select" id="inputGroupSelect02">
                                        <option value="1">{{ translate('approve') }}</option>
                                        <option value="2">{{ translate('deny') }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="message-text"
                                        class="col-form-label">{{ translate('note_about_transaction_or_request') }}:</label>
                                    <textarea class="form-control" name="note" id="message-text"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-dismiss="modal">{{ translate('close') }}</button>
                                <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
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
