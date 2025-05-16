@extends('layouts.back-end.app')

@section('title', 'View Order')

@php
    $order = $orders[0];
@endphp

@push('css_or_js')

@endpush
@inject('utilityService', 'App\Services\UtilityService')
@section('content')

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{asset('/public/assets/back-end/img/all-orders.png')}}" alt="">
            {{translate('order_Details')}}
        </h2>
    </div>
    
    <div class="row gy-3" id="printableArea">
        <div class="col-lg-8">
            <div class="card h-80">
                <!-- Body -->
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-10 justify-content-between mb-4">
                        <div class="d-flex flex-column gap-10">
                            <h4 class="text-capitalize">{{translate('Order_ID')}}  #{{$reference}}</h4>
                            <div class="">
                                {{date('d M, Y , h:i A',strtotime($order['created_at']))}}
                            </div>
                            {{-- @if ($linked_orders->count() >0)
                                <div class="d-flex flex-wrap gap-10">
                                    <div class="color-caribbean-green-soft font-weight-bold d-flex align-items-center rounded py-1 px-2"> {{translate('linked_orders')}} ({{$linked_orders->count()}}) : </div>
                                    @foreach($linked_orders as $linked)
                                        <a href="{{route('admin.orders.details',[$linked['id']])}}"
                                        class="btn color-caribbean-green text-white rounded py-1 px-2">{{$linked['id']}}</a>
                                    @endforeach
                                </div>
                            @endif --}}
                        </div>
                    </div>
                    <div class="text-sm-right">
                        <div class="d-flex flex-wrap gap-10 justify-content-end">
                            <!-- order verificaiton button-->
                            {{-- @if (count($order->verification_images)>0 && $order->verification_status ==1)
                                <div>
                                    <button class="btn btn--primary px-4" data-toggle="modal" data-target="#order_verification_modal"><i
                                        class="tio-verified"></i> {{translate('order_verification')}}
                                    </button>
                                </div>
                            @endif
                            <!-- order verificaiton button-->
                            @if (isset($shipping_address['latitude']) && isset($shipping_address['longitude']))
                            <div class="">
                                    <button class="btn btn--primary px-4" data-toggle="modal" data-target="#locationModal"><i
                                            class="tio-map"></i> {{translate('show_locations_on_map')}}</button>
                            </div>
                            @endif --}}

                            <a class="btn btn--primary px-4" target="_blank"
                                href={{route('admin.orders.generate-invoice',[$reference])}}>
                                <img src="{{ asset('public/assets/back-end/img/icons/uil_invoice.svg') }}" alt="" class="mr-1">
                                {{translate('print_Invoice')}}
                            </a>
                        </div>
                        <div class="d-flex flex-column gap-2 mt-3">
                            <!-- Payment Method -->
                            <div class="payment-method d-flex justify-content-sm-end gap-10 text-capitalize">
                                <span class="title-color">{{translate('payment_Method')}} :</span>
                                <strong>{{translate($order['payment_method'])}}</strong>
                            </div>

                            @if($order->payment_method != 'cash_on_delivery' && $order->payment_method != 'pay_by_wallet' && !isset($order->offline_payments))
                                <div class="reference-code d-flex justify-content-sm-end gap-10 text-capitalize">
                                    <span class="title-color">{{translate('reference_Code')}} :</span>
                                    <strong>{{str_replace('_',' ',$order['transaction_ref'])}} {{ $order->payment_method == 'offline_payment' ? '('.$order->payment_by.')':'' }}</strong>
                                </div>
                            @endif

                            <div class="payment-status d-flex justify-content-sm-end gap-10">
                                <span class="title-color">{{translate('payment_Status')}}:</span>
                                @if($order['payment_status']=='paid')
                                    <span class="text-success payment-status-span font-weight-bold">
                                        {{translate('paid')}}
                                    </span>
                                @else
                                    <span class="text-danger payment-status-span font-weight-bold">
                                        {{translate('unpaid')}}
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    @if ($order->order_note !=null)
                        <div class="mt-2 mb-5 w-100 d-block">
                            <div class="gap-10">
                                <h4>{{translate('order_Note')}}:</h4>
                                <div class="text-justify">{{$order->order_note}}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table fz-12 table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>S/No</th>
                            <th>{{translate('item_details')}}</th>
                            <th>{{translate('item_price')}}</th>
                            <th>{{translate('item_discount')}}</th>
                            <th>{{translate('total_price')}}</th>
                        </tr>
                        </thead>

                        <tbody>
                            @php($item_price=0)
                            @php($total_price=0)
                            @php($subtotal=0)
                            @php($total=0)
                            @php($shipping=0)
                            @php($discount=0)
                            @php($tax=0)
                            @php($row=0)

                            @if ($orders->count() > 0)
                                @foreach ($orders as $orderIndex => $orderInfo)
                                    @foreach ($orderInfo->details as $detail)
                                        {{-- @php(print_r($detail['seller']))
                                        @php(print_r($detail['added_by'])) --}}
                                        <tr>
                                            <td>{{ ++$row }}</td>
                                            <td>
                                                <div class="media align-items-center gap-10">
                                                    <img class="avatar avatar-60 rounded"
                                                         onerror="this.src='{{asset('public/assets/back-end/img/160x160/img2.jpg')}}'"
                                                         src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$detail->product_all_status['thumbnail']}}"
                                                         alt="Image Description">

                                                        <div>
                                                            <h6 class="title-color">{{substr($detail->product_all_status['name'],0,30)}}{{strlen($detail->product_all_status['name'])>10?'...':''}}</h6>
                                                            <div><strong>{{translate('qty')}} :</strong> {{$detail['qty']}}</div>
                                                            <div>
                                                                <strong>{{translate('unit_price')}} :</strong>
                                                                {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['price']+($detail->tax_model =='include' ? $detail['tax']:0)))}}
                                                                @if ($detail->tax_model =='include')
                                                                    ({{translate('tax_incl.')}})
                                                                @else
                                                                    ({{translate('tax').":".($detail->product_all_status->tax)}}{{$detail->product_all_status->tax_type ==="percent" ? '%' :''}})
                                                                @endif
    
                                                            </div>
                                                            @if ($detail->variant)
                                                                <div><strong>{{translate('variation')}} :</strong> {{$detail['variant']}}</div>
                                                            @endif
                                                            <div><strong>Seller Details</strong></div>
                                                            <div><strong>{{ $detail['seller']['f_name'] . " ".$detail['seller']['l_name']}}</strong></div>
                                                            <div>Shop: {{ Str::ucfirst($detail['seller']['business_shortcode']) }}</div>
                                                            <div>Phone: {{ $detail['seller']['phone'] }}</div>
                                                            <div>{!! $utilityService->translateOrderStatus($detail['delivery_status'], true) !!}</div>

                                                        </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ \App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['price']*$detail['qty'])) }}
                                            </td>

                                            <td>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['discount']))}}</td>

                                            @php($subtotal=$detail['price']*$detail['qty']+$detail['tax']-$detail['discount'])
                                            <td>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($subtotal))}}</td>

                                        </tr>
                                        @php($item_price+=$detail['price']*$detail['qty'])
                                        @php($discount+=$detail['discount'])
                                        @php($tax+=$detail['tax'])
                                        @php($total+=$subtotal)
                                    @endforeach
                                    {{-- @php($shipping=$detail['shipping_cost'])
                                    @php($order_amount=$detail['order_amount']) --}}
                                @endforeach
                            @endif
                            
                            
                        </tbody>
                    </table>
                </div>


                <div class="row justify-content-md-end mb-3">
                    <div class="col-md-9 col-lg-8">
                        <dl class="row gy-1 text-sm-right">
                            <dt class="col-5">{{translate('item_price')}}</dt>
                            <dd class="col-6 title-color">
                                <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($item_price))}}</strong>
                            </dd>
                            <dt class="col-5 text-capitalize">{{translate('item_discount')}}</dt>
                            <dd class="col-6 title-color">
                                - <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($discount))}}</strong>
                            </dd>
                            <dt class="col-5 text-capitalize">{{translate('sub_total')}}</dt>
                            <dd class="col-6 title-color">
                                <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($item_price-$discount))}}</strong>
                            </dd>
                            <dt class="col-5 text-uppercase">{{translate('vat')}}/{{translate('tax')}}</dt>
                            <dd class="col-6 title-color">
                                <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($tax))}}</strong>
                            </dd>
                            <dt class="col-5 text-capitalize">{{translate('delivery_fee')}}</dt>
                            <dd class="col-6 title-color">
                                <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($shipping))}}</strong>
                            </dd>

                            @if($order['coupon_discount_bearer'] == 'inhouse' && !in_array($order['coupon_code'], [0, NULL]))
                                <dt class="col-5">{{translate('coupon_discount')}} ({{translate('admin_bearer')}})</dt>
                                <dd class="col-6 title-color">
                                    + {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($coupon_discount))}}
                                </dd>
                                @php($total += $coupon_discount)
                            @endif

                            <dt class="col-5"><strong>{{translate('total')}}</strong></dt>
                            <dd class="col-6 title-color">
                                <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($total+$shipping))}}</strong>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>       

        <div class="col-lg-4 d-flex flex-column gap-3">
            {{-- Payment Information --}}
            @if($order->payment_method == 'offline_payment' && isset($order->offline_payments))
                <div class="card">
                    <!-- Body -->
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center justify-content-between mb-4">
                            <h4 class="d-flex gap-2">
                                <img src="{{asset('/public/assets/back-end/img/product_setup.png')}}" alt="" width="20">
                                {{translate('Payment_Information')}}
                            </h4>
                        </div>

                        <div>
                            <table>
                                <tbody>
                                    <tr>
                                        <td>{{translate('payment_Method')}}</td>
                                        <td class="py-1 px-2">:</td>
                                        <td><strong>{{ translate($order['payment_method']) }}</strong></td>
                                    </tr>
                                    @foreach (json_decode($order->offline_payments->payment_info) as $key=>$item)
                                        @if (isset($item) && $key != 'method_id')
                                            <tr>
                                                <td>{{translate($key)}}</td>
                                                <td class="py-1 px-2">:</td>
                                                <td><strong>{{ $item }}</strong></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(isset($order->payment_note) && $order->payment_method == 'offline_payment')
                            <div class="payment-status mt-3">
                                <h4>{{translate('payment_Note')}}:</h4>
                                <p class="text-justify">
                                    {{ $order->payment_note }}
                                </p>
                            </div>
                        @endif
                    </div>
                    <!-- End Body -->
                </div>
            @endif
            
            <!-- Order & Shipping Info Card -->
            <div class="card">
                <div class="card-body text-capitalize d-flex flex-column gap-4">
                    <div class="d-flex flex-column align-items-center gap-2">
                        <h4 class="mb-0 text-center">{{translate('order_&_Shipping_Info')}}</h4>
                    </div>

                    <div class="">
                        <label class="font-weight-bold title-color fz-14">{{translate('change_order_status')}}</label>
                        <select name="order_status" onchange="order_status(this.value)" id="order_status_select"
                                class="status form-control" data-id="{{$order['id']}}" data-order-reference="{{$reference}}">

                            <option
                                value="pending" {{$order->order_status == 'pending'?'selected':''}} > {{translate('pending')}}</option>
                            <option
                                value="confirmed" {{$order->order_status == 'confirmed'?'selected':''}} > {{translate('confirmed')}}</option>
                            <option
                                value="processing" {{$order->order_status == 'processing'?'selected':''}} >{{translate('packaging')}} </option>
                            <option class="text-capitalize"
                                    value="out_for_delivery" {{$order->order_status == 'out_for_delivery'?'selected':''}} >{{translate('out_for_delivery')}} </option>
                            <option
                                value="delivered" {{$order->order_status == 'delivered'?'selected':''}} >{{translate('delivered')}} </option>
                            <option
                                value="returned" {{$order->order_status == 'returned'?'selected':''}} > {{translate('returned')}}</option>
                            <option
                                value="failed" {{$order->order_status == 'failed'?'selected':''}} >{{translate('failed_to_Deliver')}} </option>
                            <option
                                value="canceled" {{$order->order_status == 'canceled'?'selected':''}} >{{translate('canceled')}} </option>
                        </select>
                    </div>

                    <!-- Payment Status -->
                    <div class="d-flex justify-content-between align-items-center gap-10 form-control h-auto flex-wrap">
                        <span class="title-color">
                            {{translate('payment_status')}}
                        </span>
                        <div class="d-flex justify-content-end min-w-100 align-items-center gap-2">
                            <span class="text--primary font-weight-bold">{{ $order->payment_status=='paid' ? translate('paid'):translate('unpaid')}}</span>
                            <label class="switcher payment-status-text">
                                <input class="switcher_input payment_status"  type="checkbox" name="status" value="{{$order->payment_status}}" 
                                    {{ $order->payment_status=='paid' ? 'checked disabled':''}} >
                                <span class="switcher_control switcher_control_add"></span>
                            </label>
                        </div>
                    </div>
                    

                    @if($physical_product)
                        <ul class="list-unstyled list-unstyled-py-4">
                            <li>
                                @if ($order->shipping_type == 'order_wise')
                                    <label class="font-weight-bold title-color fz-14">
                                        {{translate('shipping_Method')}}
                                        ({{$order->shipping ? translate(str_replace('_',' ',$order->shipping->title)) :translate('no_shipping_method_selected')}})
                                    </label>
                                @endif

                                <select class="form-control text-capitalize" name="delivery_type" onchange="choose_delivery_type(this.value)">
                                    <option value="self_delivery" selected='selected'>
                                        {{translate('by_self_delivery_man')}}
                                    </option>

                                    {{-- <option value="0">
                                        {{translate('choose_delivery_type')}}
                                    </option>

                                    <option value="self_delivery" {{$order->delivery_type=='self_delivery'?'selected':''}}>
                                        {{translate('by_self_delivery_man')}}
                                    </option>
                                    <option value="third_party_delivery" {{$order->delivery_type=='third_party_delivery'?'selected':''}} >
                                        {{translate('by_third_party_delivery_service')}}
                                    </option> --}}
                                </select>
                            </li>

                            <li class="choose_delivery_man">
                                <label class="font-weight-bold title-color fz-14">
                                    {{translate('delivery_man')}}
                                </label>
                                <select class="form-control text-capitalize js-select2-custom" name="delivery_man_id" onchange="addDeliveryMan(this.value)">
                                    <option
                                        value="0">{{translate('select')}}</option>
                                    @foreach($delivery_men as $deliveryMan)
                                        <option
                                            value="{{$deliveryMan['id']}}" {{$order['delivery_man_id']==$deliveryMan['id']?'selected':''}}>
                                            {{$deliveryMan['f_name'].' '.$deliveryMan['l_name'].' ('.$deliveryMan['phone'].' )'}}
                                        </option>
                                    @endforeach
                                </select>
    
                                @if (isset($order->delivery_man))
                                    <div class="p-2 bg-light rounded mt-4">
                                        <div class="media m-1 gap-3">
                                            <img class="avatar rounded-circle"
                                                onerror="this.src='{{asset('public/assets/back-end/img/image-place-holder.png')}}'"
                                                src="{{asset('storage/app/public/profile/'.isset($order->delivery_man->image) ?? '')}}"
                                                alt="Image">
                                            <div class="media-body">
                                                <h5 class="mb-1">{{ isset($order->delivery_man) ? $order->delivery_man->f_name.' '.$order->delivery_man->l_name :''}}</h5>
                                                <a href="tel:{{isset($order->delivery_man) ? $order->delivery_man->phone : ''}}" class="fz-12 title-color">{{isset($order->delivery_man) ? $order->delivery_man->phone :''}}</a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-2 bg-light rounded mt-4">
                                        <div class="media m-1 gap-3">
                                            <img class="avatar rounded-circle"
                                                onerror="this.src='{{asset('public/assets/back-end/img/image-place-holder.png')}}'"
                                                src="{{asset('public/assets/back-end/img/delivery-man.png')}}"
                                                alt="Image">
                                            <div class="media-body">
                                                <h5 class="mt-3">{{translate('no_delivery_man_assigned')}}</h5>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </li>
                            
                            @if (isset($order->delivery_man))
                                {{-- <li class="choose_delivery_man">
                                    <label class="font-weight-bold title-color fz-14">
                                        {{translate('deliveryman_will_get')}} ({{ session('currency_symbol') }})
                                    </label>
                                    <input type="number" id="deliveryman_charge" onkeyup="amountDateUpdate(this, event)" value="{{ $order->deliveryman_charge }}" name="deliveryman_charge" class="form-control" placeholder="Ex: 20" required>
                                </li> --}}
                                <li class="choose_delivery_man">
                                    <label class="font-weight-bold title-color fz-14">
                                        {{translate('expected_delivery_date')}}
                                    </label>
                                    <input type="date" onchange="deliveryDateUpdate(this, event)" value="{{ $order->expected_delivery_date }}" name="expected_delivery_date" id="expected_delivery_date" class="form-control" required>
                                </li>
                            @endif
                            

                            {{-- <li class="mt-1" id="by_third_party_delivery_service_info">
                                <div class="p-2 bg-light rounded">
                                    <div class="media m-1 gap-3">
                                        <img class="avatar rounded-circle"
                                            onerror="this.src='{{asset('public/assets/back-end/img/image-place-holder.png')}}'"
                                            src="{{asset('public/assets/back-end/img/third-party-delivery.png')}}"
                                            alt="Image">
                                        <div class="media-body">
                                            <h5 class="">{{isset($order->delivery_service_name) ? $order->delivery_service_name :translate('not_assign_yet')}}</h5>
                                            <span class="fz-12 title-color">{{translate('track_ID')}} :  {{$order->third_party_delivery_tracking_id}}</span>
                                        </div>
                                    </div>
                                </div>
                            </li> --}}
                        </ul>

                    @endif
                </div>
            </div>
            
            <!-- Customer Info Card -->
            @if(!$order->is_guest && $order->customer)
                <div class="card">
                    <!-- Body -->
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center justify-content-between mb-4">
                            <h4 class="d-flex gap-2">
                                <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                {{translate('customer_information')}}
                            </h4>
                        </div>
                        <div class="media flex-wrap gap-3">
                            <div class="">
                                <img class="avatar rounded-circle avatar-70"
                                        onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                        src="{{asset('storage/app/public/profile/'.$order->customer->image)}}"
                                        alt="Image">
                            </div>
                            <div class="media-body d-flex flex-column gap-1">
                                <span class="title-color"><strong>{{$order->customer['f_name'].' '.$order->customer['l_name']}} </strong></span>
                                <span class="title-color"> <strong>{{\App\Model\Order::where('customer_id',$order['customer_id'])->count()}}</strong> {{translate('orders')}}</span>
                                <span class="title-color break-all"><strong>{{$order->customer['phone']}}</strong></span>
                                <span class="title-color break-all">{{$order->customer['email']}}</span>
                            </div>
                        </div>
                    </div>
                    <!-- End Body -->
                </div>
            @endif

            <!-- Shipping Address Card -->
            @if($physical_product)
                <div class="card">
                    <!-- Body -->
                    @php($shipping_address=json_decode($order['shipping_address_data']))
                    @if($shipping_address)
                        <div class="card-body">
                            <div class="d-flex gap-2 align-items-center justify-content-between mb-4">
                                <h4 class="d-flex gap-2">
                                    <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                    {{translate('shipping_address')}}
                                </h4>

                                <button class="btn btn-outline-primary btn-sm square-btn" title="Edit" data-toggle="modal" data-target="#shippingAddressUpdateModal">
                                    <i class="tio-edit"></i>
                                </button>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <div>
                                    <span>{{translate('name')}} :</span>
                                    <strong>{{$shipping_address->contact_person_name}}</strong> {{ $order->is_guest ? '('. translate('guest_customer') .')':''}}
                                </div>
                                <div>
                                    <span>{{translate('contact')}} :</span>
                                    <strong>{{$shipping_address->phone}}</strong>
                                </div>
                                @if ($order->is_guest && $shipping_address->email)
                                <div>
                                    <span>{{translate('email')}} :</span>
                                    <strong>{{$shipping_address->email}}</strong>
                                </div>
                                @endif
                                <div>
                                    <span>{{translate('city')}} :</span>
                                    <strong>{{$shipping_address->city}}</strong>
                                </div>
                                {{-- <div>
                                    <span>{{translate('zip_code')}} :</span>
                                    <strong>{{$shipping_address->zip}}</strong>
                                </div> --}}
                                <div class="d-flex align-items-start gap-2">
                                    <!-- <span>{{translate('address')}} :</span> -->
                                    <img src="{{asset('/public/assets/back-end/img/location.png')}}" alt="">
                                    {{$shipping_address->address  ?? translate('empty')}}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="media align-items-center">
                                <span>{{translate('no_shipping_address_found')}}</span>
                            </div>
                        </div>
                    @endif
                    <!-- End Body -->
                </div>
            @endif

            <!-- Billing Address Card -->
            <div class="card">
                <!-- Body -->
                @php($billing=json_decode($order['billing_address_data']))
                @if($billing)
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center justify-content-between mb-4">
                            <h4 class="d-flex gap-2">
                                <img src="{{asset('/public/assets/back-end/img/seller-information.png')}}" alt="">
                                {{translate('billing_address')}}
                            </h4>

                            <button class="btn btn-outline-primary btn-sm square-btn" title="Edit" data-toggle="modal" data-target="#billingAddressUpdateModal">
                                <i class="tio-edit"></i>
                            </button>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <span>{{translate('name')}} :</span>
                                <strong>{{$billing->contact_person_name}}</strong> {{ $order->is_guest ? '('. translate('guest_customer') .')':''}}
                            </div>
                            <div>
                                <span>{{translate('contact')}} :</span>
                                <strong>{{$billing->phone}}</strong>
                            </div>
                            @if ($order->is_guest && $billing->email)
                            <div>
                                <span>{{translate('email')}} :</span>
                                <strong>{{$billing->email}}</strong>
                            </div>
                            @endif
                            <div>
                                <span>{{translate('city')}} :</span>
                                <strong>{{$billing->city}}</strong>
                            </div>
                            <div>
                                <span>{{translate('zip_code')}} :</span>
                                <strong>{{$billing->zip}}</strong>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <!-- <span>{{translate('address')}} :</span> -->
                                <img src="{{asset('/public/assets/back-end/img/location.png')}}" alt="">
                                {{$billing->address}}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="media align-items-center">
                            <span>{{translate('no_billing_address_found')}}</span>
                        </div>
                    </div>
                @endif
                <!-- End Body -->
            </div>
            <!-- End Card -->
            
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script>
        // document.addEventListener('DOMContentLoaded', function () {
        //     var orderStatusSelect = document.getElementById('order_status_select');

        //     function updateOrderStatusOptions(status) {
        //         var options = orderStatusSelect.options;

        //         // Enable all options first
        //         for (var i = 0; i < options.length; i++) {
        //             options[i].disabled = false;
        //         }

        //         // Disable options based on the current status
        //         switch (status) {
        //             case 'pending':
        //                 // All options should be enabled for pending
        //                 break;
        //             case 'confirmed':
        //                 disableOption('pending');
        //                 break;
        //             case 'processing':
        //                 disableOption('pending');
        //                 disableOption('confirmed');
        //                 break;
        //             case 'out_for_delivery':
        //                 disableOption('pending');
        //                 disableOption('confirmed');
        //                 disableOption('processing');
        //                 break;
        //             case 'delivered':
        //                 disableOption('pending');
        //                 disableOption('confirmed');
        //                 disableOption('processing');
        //                 disableOption('out_for_delivery');
        //                 break;
        //             case 'returned':
        //             case 'failed':
        //             case 'canceled':
        //                 disableOption('pending');
        //                 disableOption('confirmed');
        //                 disableOption('processing');
        //                 disableOption('out_for_delivery');
        //                 disableOption('delivered');
        //                 break;
        //         }
        //     }

        //     function disableOption(value) {
        //         var options = orderStatusSelect.options;
        //         for (var i = 0; i < options.length; i++) {
        //             if (options[i].value === value) {
        //                 options[i].disabled = true;
        //                 break;
        //             }
        //         }
        //     }

        //     orderStatusSelect.addEventListener('change', function () {
        //         updateOrderStatusOptions(this.value);
        //     });

        //     // Initialize the select options on page load based on current status
        //     updateOrderStatusOptions(orderStatusSelect.value);
        // });

        function order_status(status) {
            let orderStatusSelect = document.getElementById('order_status_select');
            let initialStatus = "{{$order->order_status}}";

            @if($order['order_status']=='delivered')
                Swal.fire({
                    title: '{{translate("Order_is_already_delivered_and_transaction_amount_has_been_disbursed_changing_status_can_be_the_reason_of_miscalculation")}}!',
                    text: "{{translate('think_before_you_proceed')}}.",
                    showCancelButton: true,
                    confirmButtonColor: '#377dff',
                    cancelButtonColor: 'secondary',
                    confirmButtonText: '{{translate("yes_change_it")}}!',
                    cancelButtonText: '{{ translate("cancel") }}',
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{route('admin.orders.status')}}",
                            method: 'POST',
                            data: {
                                "id": '{{$order['id']}}',
                                "reference": '{{$reference}}',
                                "order_status": status,
                                'tx_id_type': 'reference'
                            },
                            success: function (response) {
                                Swal.fire({
                                    title: 'Success!',
                                    icon: 'success',
                                    text: response.message,
                                    showCancelButton: true,
                                    showConfirmButton: false,
                                    cancelButtonColor: 'secondary',
                                    cancelButtonText: '{{ translate("Ok") }}',
                                }).then((result) => {
                                    if (result) {
                                        location.reload();
                                    }
                                })
                            },
                            error: function (jxhr, ajaxOptions, thrownError) {
                                let decodeResponse = JSON.parse(jxhr.responseText);
                                Swal.fire({
                                    title: 'Error!',
                                    text: decodeResponse.message,
                                    showCancelButton: true,
                                    showConfirmButton: false,
                                    cancelButtonColor: 'secondary',
                                    cancelButtonText: '{{ translate("Ok") }}',
                                })
                                orderStatusSelect.value = initialStatus;
                            }
                        });
                    } else {
                        orderStatusSelect.value = initialStatus;
                    }
                })
            @else
                Swal.fire({
                    title: '{{translate("are_you_sure_change_this")}}?',
                    text: "{{translate('you_will_not_be_able_to_revert_this')}}!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#377dff',
                    cancelButtonColor: 'secondary',
                    confirmButtonText: '{{translate("yes_change_it")}}!',
                    cancelButtonText: '{{ translate("cancel") }}',
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{route('admin.orders.status')}}",
                            method: 'POST',
                            data: {
                                "id": '{{$order['id']}}',
                                "reference": '{{$reference}}',
                                "order_status": status,
                                'tx_id_type': 'reference'
                            },
                            success: function (response) {
                                Swal.fire({
                                    title: 'Success!',
                                    icon: 'success',
                                    text: response.message,
                                    showCancelButton: true,
                                    showConfirmButton: false,
                                    cancelButtonColor: 'secondary',
                                    cancelButtonText: '{{ translate("Ok") }}',
                                }).then((result) => {
                                    if (result) {
                                        location.reload();
                                    }
                                })
                            },
                            error: function (jxhr, ajaxOptions, thrownError) {
                                let decodeResponse = JSON.parse(jxhr.responseText);
                                Swal.fire({
                                    title: 'Error!',
                                    text: decodeResponse.message,
                                    showCancelButton: true,
                                    showConfirmButton: false,
                                    cancelButtonColor: 'secondary',
                                    cancelButtonText: '{{ translate("Ok") }}',
                                })
                                orderStatusSelect.value = initialStatus;
                            }
                        });
                    } else {
                        orderStatusSelect.value = initialStatus;
                    }
                })
            @endif
        }

        $(document).on('click','.payment_status', function (e) {
            e.preventDefault();
            var id = "{{$order->order_group_id}}";
            var value = $(this).val();

            Swal.fire({
                title: '{{translate("are_you_sure_change_this")}}?',
                text: "{{translate('you_will_not_be_able_to_revert_this')}}!",
                showCancelButton: true,
                confirmButtonColor: '#377dff',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{translate("yes_change_it")}}!',
                cancelButtonText: '{{ translate("cancel") }}',
            }).then((result) => {
                if(value == 'paid'){
                    value = 'unpaid'
                }else{
                    value = 'paid'
                }
                if (result.value) {
                    
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.orders.payment-status')}}",
                        method: 'POST',
                        data: {
                            "id": id,
                            "payment_status": value
                        },
                        success: function (data) {
                            clearCookies()
                            toastr.success('{{translate("status_change_successfully")}}');
                            window.location.href=data.route;
                        }
                    });
                }
            })
        });

        function clearCookies() {
            var cookies = document.cookie.split(";");

            for (var i = 0; i < cookies.length; i++) {
                var cookie = cookies[i];
                var eqPos = cookie.indexOf("=");
                var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
            }
        }

        function addDeliveryMan(id) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/admin/orders/add-delivery-man/{{$order['order_group_id']}}/' + id,
                data: {
                    'order_id': '{{$order['id']}}',
                    'delivery_man_id': id
                },
                success: function (data) {
                    // console.log(data);
                    if (data.status == true) {
                        toastr.success('{{ translate("delivery_man_successfully_assigned_or_changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        location.reload();
                    } else {
                        toastr.error('{{ translate("deliveryman_man_can_not_assign_or_change_in_that_status") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('{{ translate("add_valid_data") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function deliveryDateUpdate(t, event) {
            let field_name = $(t).attr('name');
            let field_val = $(t).val();
            let initDeliveryDate = "{{ $order->expected_delivery_date }}"
            console.log(field_name);
            console.log(event);
           
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.orders.delivery-date-update')}}",
                method: 'POST',
                data: {
                    'order_id': '{{$order['order_group_id']}}',
                    'field_name': field_name,
                    'delivery_date': field_val
                },
                success: function (response) {
                    console.log(response);
                    Swal.fire({
                        title: 'Success!',
                        icon: 'success',
                        text: response.message,
                        showCancelButton: true,
                        showConfirmButton: false,
                        cancelButtonColor: 'secondary',
                        cancelButtonText: '{{ translate("Ok") }}',
                    }).then((result) => {
                        if (result) {
                            location.reload();
                        }
                    })
                },
                error: function (jqxhr, errorThrown, resp) {
                    console.log(jqxhr.responseText);
                    let decodeResponse = JSON.parse(jqxhr.responseText)
                    Swal.fire({
                        title: 'Error!',
                        text: decodeResponse.message,
                        showCancelButton: true,
                        showConfirmButton: false,
                        cancelButtonColor: 'secondary',
                        cancelButtonText: '{{ translate("Ok") }}',
                    })
                    $("#expected_delivery_date").val(initDeliveryDate)
                }
            });

        }

        function amountDateUpdate(t, e){
            let field_name = $(t).attr('name');
            let field_val = $(t).val();
            
            console.log(field_name);
            console.log(field_val);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.orders.delivery-date-update')}}",
                method: 'POST',
                data: {
                    'order_id': '{{$order['order_group_id']}}',
                    'field_name': field_name,
                    'field_val': field_val
                },
                success: function (data) {
                    if (data.status == true) {
                        toastr.success('{{ translate("expected_delivery_date_added_successfully") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else {
                        toastr.error('{{ translate("failed_to_add_expected_delivery_date") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function () {
                    toastr.error('Add valid data', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });

        }
    </script>
@endpush
