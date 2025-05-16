@extends('layouts.back-end.app')

@section('title', translate('order_List'))

@push('css_or_js')

@endpush

@section('content')

<div class="content container-fluid">
    <!-- Page Header -->
    <div>
        <!-- Page Title -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{asset('/public/assets/back-end/img/all-orders.png')}}" class="mb-1 mr-1" alt="">
                <span class="page-header-title">
                    @if($status =='processing')
                        {{translate('packaging')}}
                    @elseif($status =='failed')
                        {{translate('failed_to_Deliver')}}
                    @elseif($status == 'all')
                        {{translate('all')}}
                    @else
                        {{translate(str_replace('_',' ',$status))}}
                    @endif
                </span>
                {{translate('orders')}}
            </h2>
            <span class="badge badge-soft-dark radius-50 fz-14">{{$orders->total()}}</span>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <!-- Data Table Top -->
                <div class="px-3 py-4 light-bg">
                    <div class="row g-2 align-items-center flex-grow-1">
                        <div class="col-md-4">
                            <h5 class="text-capitalize d-flex gap-1">
                                {{translate('order_list')}}
                                <span class="badge badge-soft-dark radius-50 fz-12">{{$orders->total()}}</span>
                            </h5>
                        </div>
                        <div class="col-md-8 d-flex gap-3 flex-wrap flex-sm-nowrap justify-content-md-end">
                            <form action="" method="GET">
                                <!-- Search -->
                                <div class="input-group input-group-custom input-group-merge">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{translate('search_by_Order_ID')}}" aria-label="Search by Order ID" value="{{ $search }}">
                                    <button type="submit" class="btn btn--primary input-group-text">{{translate('search')}}</button>
                                </div>
                                <!-- End Search -->
                            </form>
                            <div class="dropdown">
                                <button type="button" class="btn btn-outline--primary" data-toggle="dropdown">
                                    <i class="tio-download-to"></i>
                                    {{translate('export')}}
                                    <i class="tio-chevron-down"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a type="submit" class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.orders.order-bulk-export', ['delivery_man_id' => request('delivery_man_id'), 'status' => $status, 'from' => $from, 'to' => $to, 'filter' => $filter, 'search' => $search,'seller_id'=>$seller_id,'customer_id'=>$customer_id, 'date_type'=>$date_type]) }}">
                                            <img width="14" src="{{asset('/public/assets/back-end/img/excel.png')}}" alt="">
                                            {{translate('excel')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100"
                        style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}}">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>S/No</th>
                                <th>{{translate('order_ID')}}</th>
                                <th>{{translate('order_Date')}}</th>
                                <th>{{translate('customer_Info')}}</th>
                                <th class="text-right">{{translate('total_Amount')}}</th>
                                <th class="text-center">{{translate('order_Status')}} </th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @if ($orders->count() > 0)  
                                @foreach ($orders as $orderIndex => $orderData )
                                    <tr class="status-{{$orderData['order_status']}} class-all">
                                        <td class="">
                                            {{$orderIndex + 1}}
                                        </td>

                                        <td>
                                            {{-- <a class="title-color" href="{{route('admin.orders.details',['id'=>$orderData['id']])}}"> --}}
                                                {{$orderData['order_group_id']}}
                                            {{-- </a> --}}
                                        </td>

                                        <td>
                                            <div>{{date('d M Y',strtotime($orderData['created_at']))}},</div>
                                            <div>{{ date("h:i A",strtotime($orderData['created_at'])) }}</div>
                                        </td>

                                        <td>
                                            @if($orderData->is_guest)
                                                <strong class="title-name">{{translate('guest_customer')}}</strong>
                                            @elseif($orderData->customer_id == 0)
                                                <strong class="title-name">{{translate('walking_customer')}}</strong>
                                            @else
                                                @if($orderData->customer)
                                                    <a class="text-body text-capitalize" href="{{route('admin.orders.details',['id'=>$orderData['id']])}}">
                                                        <strong class="title-name">{{$orderData->customer['f_name'].' '.$orderData->customer['l_name']}}</strong>
                                                    </a>
                                                    <a class="d-block title-color" href="tel:{{ $orderData->customer['phone'] }}">{{ $orderData->customer['phone'] }}</a>
                                                @else
                                                    <label class="badge badge-danger fz-12">{{translate('invalid_customer_data')}}</label>
                                                @endif
                                            @endif
                                        </td>
                                        
                                        <td class="text-right">
                                            <div>
                                                @php($discount = 0)
                                                @if($orderData->order_type == 'default_type' && $orderData->coupon_discount_bearer == 'inhouse' && !in_array($orderData['coupon_code'], [0, NULL]))
                                                    @php($discount = $orderData->discount_amount)
                                                @endif
    
                                                @php($free_shipping = 0)
                                                @if($orderData->is_shipping_free)
                                                    @php($free_shipping = $orderData->shipping_cost)
                                                @endif
    
                                                {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($orderData->total_amount+$discount+$free_shipping))}}
                                            </div>
    
                                            @if($orderData->payment_status=='paid')
                                                <span class="badge text-success fz-12 px-0">
                                                    {{translate('paid')}}
                                                </span>
                                            @else
                                                <span class="badge text-danger fz-12 px-0">
                                                    {{translate('unpaid')}}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        <td class="text-center text-capitalize">
                                            @if($orderData['order_status']=='pending')
                                                <span class="badge badge-soft-info fz-12">
                                                    {{translate($orderData['order_status'])}}
                                                </span>

                                            @elseif($orderData['order_status']=='processing' || $orderData['order_status']=='out_for_delivery')
                                                <span class="badge badge-soft-warning fz-12">
                                                    {{str_replace('_',' ',$orderData['order_status'] == 'processing' ? translate('packaging'):translate($orderData['order_status']))}}
                                                </span>
                                            @elseif($orderData['order_status']=='confirmed')
                                                <span class="badge badge-soft-success fz-12">
                                                    {{translate($orderData['order_status'])}}
                                                </span>
                                            @elseif($orderData['order_status']=='failed')
                                                <span class="badge badge-danger fz-12">
                                                    {{translate('failed_to_deliver')}}
                                                </span>
                                            @elseif($orderData['order_status']=='delivered')
                                                <span class="badge badge-soft-success fz-12">
                                                    {{translate($orderData['order_status'])}}
                                                </span>
                                            @else
                                                <span class="badge badge-soft-danger fz-12">
                                                    {{translate($orderData['order_status'])}}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline--primary square-btn btn-sm mr-1" title="{{translate('view')}}"
                                                    href="{{route('admin.orders.view',['id'=>$orderData['order_group_id']])}}">
                                                    <img src="{{asset('/public/assets/back-end/img/eye.svg')}}" class="svg" alt="">
                                                </a>
                                                <a class="btn btn-outline-success square-btn btn-sm mr-1" target="_blank" title="{{translate('invoice')}}"
                                                    href="{{route('admin.orders.generate-invoice',[$orderData['order_group_id']])}}">
                                                    <i class="tio-download-to"></i>
                                                </a>
                                            </div>
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
@endsection

@push('script_2')
    <script>
        function customer_id_value(id){
            $('#customer_id').empty().val(id);
        }
        $('.js-data-example-ajax').select2({
        // Need Add a initial option
        data: [{ id: '', text: 'Select your option', disabled: true, selected: true }],
        ajax: {
            url: '{{route('admin.orders.customers')}}',
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data) {
                // console.log(data);
                return {
                results: data

                };
            },
            __port: function (params, success, failure) {
                var $request = $.ajax(params);

                $request.then(success);
                $request.fail(failure);

                return $request;
            }

        }
    });
    $(document).ready(function() {
        $('.select2-container--default').addClass('form-control');
        $('.select2-container--default').addClass('p-0');
        $('.select2-selection').addClass('border-0');
    });

    </script>
    <script>
        function filter_order() {
            $.get({
                url: '{{route('admin.orders.inhouse-order-filter')}}',
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').fadeIn();
                },
                success: function (data) {
                    toastr.success('{{translate("order_filter_success")}}');
                    location.reload();
                },
                complete: function () {
                    $('#loading').fadeOut();
                },
            });
        };
    </script>
    <script>
        $('#from_date,#to_date').change(function () {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if(fr != ''){
                $('#to_date').attr('required','required');
            }
            if(to != ''){
                $('#from_date').attr('required','required');
            }
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('{{translate("invalid_date_range")}}!', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }

        })

        $("#date_type").change(function() {
            let val = $(this).val();
            $('#from_div').toggle(val === 'custom_date');
            $('#to_div').toggle(val === 'custom_date');

            if(val === 'custom_date'){
                $('#from_date').attr('required','required');
                $('#to_date').attr('required','required');
                $('.filter-btn').attr('class','filter-btn col-12 text-right');
            }else{
                $('#from_date').val(null).removeAttr('required')
                $('#to_date').val(null).removeAttr('required')
                $('.filter-btn').attr('class','col-sm-6 col-md-3 filter-btn');
            }
        }).change();

        $("#filter").change(function() {
            let val = $(this).val();
            if(val === 'admin'){
                $('#seller_id_area').fadeOut();
            }else{
                $('#seller_id_area').fadeIn();
                $('#seller_id').val('all');
            }
            if(val === 'seller'){
                $('#seller_id_inhouse').fadeOut();
            }else{
                $('#seller_id_inhouse').fadeIn();
            }
        });
    </script>
@endpush
