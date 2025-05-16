@extends('theme-views.layouts.app')

@section('title', translate('My_Order_List').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-4">
        <div class="container">
            <div class="row g-3">
                <!-- Sidebar-->
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h5>{{translate('My_Order_List')}}</h5>
                                <div class="border rounded  custom-ps-3 py-2">
                                    <div class="d-flex gap-2">
                                        <div class="flex-middle gap-2">
                                            <i class="bi bi-sort-up-alt"></i>
                                            <span class="d-none d-sm-inline-block">{{translate('Show_Order')}} : </span>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button" class="border-0 bg-transparent dropdown-toggle text-dark p-0 custom-pe-3" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{translate($order_by=='asc'?'old':'latest')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">

                                                <li >
                                                    <a class="d-flex" href="{{route('account-oder')}}/?order_by=desc">
                                                        {{translate('latest')}}
                                                    </a>
                                                </li>
                                                <li >
                                                    <a class="d-flex" href="{{route('account-oder')}}/?order_by=asc">
                                                        {{translate('old')}}
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="table-responsive d-none d-sm-block">
                                    <table class="table align-middle table-striped">
                                        <thead class="text-primary">
                                            <tr>
                                                <th>S/No</th>
                                                <th>Order Reference</th>
                                                <th>Amount Paid</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($orders as $orderIndex => $orderData)
                                                <tr>
                                                    <td> {{ $orderIndex + 1 }} </td>
                                                    <td>
                                                        <strong>{{ $orderData->order_group_id }}</strong>
                                                        <span class="d-flex"> 
                                                            {{date('d M Y',strtotime($orderData['created_at']))}},
                                                            {{ date("h:i A",strtotime($orderData['created_at'])) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php($discount = 0)
                                                        @if($orderData->order_type == 'default_type' && $orderData->coupon_discount_bearer == 'inhouse' && !in_array($orderData['coupon_code'], [0, NULL]))
                                                            @php($discount = $orderData->discount_amount)
                                                        @endif
            
                                                        @php($free_shipping = 0)
                                                        @if($orderData->is_shipping_free)
                                                            @php($free_shipping = $orderData->shipping_cost)
                                                        @endif
            
                                                        {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($orderData->total_amount+$discount+$free_shipping))}}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                                            <a href="{{ route('account-order-details', ['id'=>$orderData->order_group_id]) }}" class="btn btn-outline-info btn-action">
                                                                <i class="bi bi-eye-fill"></i>
                                                            </a>
                                                        </div>
                                                    </td>
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
        </div>
    </main>


    <!-- End Main Content -->
@endsection
@push('script')

    <script>
        function cancel_message() {
            toastr.info('{{translate("order_can_be_canceled_only_when_pending.")}}', {
                CloseButton: true,
                ProgressBar: true
            });
        }
    </script>


@endpush
