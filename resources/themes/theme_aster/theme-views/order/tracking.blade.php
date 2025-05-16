@extends('theme-views.layouts.app')

@section('title', translate('Track_Order_Result ').' | '.$web_config['name']->value)

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-4">
        <div class="container">
            <div class="card h-100">
                <div class="card-body py-4 px-sm-4">
                    <div class="mt-4">
                        <h4 class="text-center text-uppercase mb-5">{{ translate('Your_order') }} #{{ $orderId }}
                            {{-- {{ $utilityService->getOrderStatus($order) }} --}}
                        </h4>
                        <div class="row justify-content-center">
                            <div class="col-xl-10">
                                <div id="timeline">
                                    <div
                                        @if($orderInfo['order_status']=='processing')
                                            class="bar progress two"
                                        @elseif($orderInfo['order_status']=='out_for_delivery')
                                            class="bar progress three"
                                        @elseif($orderInfo['order_status']=='delivered')
                                            class="bar progress four"
                                        @else
                                            class="bar progress one"
                                        @endif
                                    ></div>
                                    <div class="state">
                                        <ul>
                                            <li>
                                                <div class="state-img">
                                                    <img width="30" src="{{theme_asset('assets/img/icons/track1.png')}}" class="dark-support" alt="">
                                                </div>
                                                <div class="badge active">
                                                    <span>1</span>
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <div>
                                                    <div class="state-text">{{translate('Order_placed')}}</div>
                                                    <div class="mt-2 fs-12">{{date('d M, Y h:i A',strtotime($orderInfo->created_at))}}</div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="state-img">
                                                    <img width="30" src="{{theme_asset('assets/img/icons/track2.png')}}" class="dark-support" alt="">
                                                </div>
                                                <div class="{{($orderInfo['order_status']=='processing') || ($orderInfo['order_status']=='processed') || ($orderInfo['order_status']=='out_for_delivery') || ($orderInfo['order_status']=='delivered')?'badge active' : 'badge'}}">
                                                    <span>2</span>
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <div>
                                                    <div class="state-text">{{translate('Packaging_order')}}</div>
                                                    @if(($orderInfo['order_status']=='processing') || ($orderInfo['order_status']=='processed') || ($orderInfo['order_status']=='out_for_delivery') || ($orderInfo['order_status']=='delivered'))
                                                        <div class="mt-2 fs-12">
                                                            @if(\App\CPU\order_status_history($orderInfo['id'],'processing'))
                                                            {{date('d M, Y h:i A',strtotime(\App\CPU\order_status_history($orderInfo['id'],'processing')))}}
                                                            @endif
                                                        </div>
                                                    @endif

                                                </div>
                                            </li>

                                            <li>
                                                <div class="state-img">
                                                    <img width="30" src="{{theme_asset('assets/img/icons/track4.png')}}" class="dark-support" alt="">
                                                </div>
                                                <div class="{{($orderInfo['order_status']=='out_for_delivery') || ($orderInfo['order_status']=='delivered')?'badge active' : 'badge'}}">
                                                    <span>3</span>
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <div class="state-text">{{translate('Order_is_on_the_way')}}</div>
                                                @if(($orderInfo['order_status']=='out_for_delivery') || ($orderInfo['order_status']=='delivered'))
                                                    <div class="mt-2 fs-12">
                                                        @if(\App\CPU\order_status_history($orderInfo['id'],'out_for_delivery'))
                                                            {{date('d M, Y h:i A',strtotime(\App\CPU\order_status_history($orderInfo['id'],'out_for_delivery')))}}
                                                        @endif
                                                    </div>
                                                @endif
                                            </li>
                                            <li>
                                                <div class="state-img">
                                                    <img width="30" src="{{theme_asset('assets/img/icons/track5.png')}}" class="dark-support" alt="">
                                                </div>
                                                <div class="{{($orderInfo['order_status']=='delivered')?'badge active' : 'badge'}}">
                                                    <span>4</span>
                                                    <i class="bi bi-check"></i>
                                                </div>
                                                <div class="state-text">{{translate('Order_Delivered')}}</div>
                                                @if($orderInfo['order_status']=='delivered')
                                                    <div class="mt-2 fs-12">
                                                        @if(\App\CPU\order_status_history($orderInfo['id'], 'delivered'))
                                                        {{date('d M, Y h:i A',strtotime(\App\CPU\order_status_history($orderInfo['id'], 'delivered')))}}
                                                        @endif
                                                    </div>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 bg-light p-3 p-sm-4">

                            <div class="d-flex justify-content-between">
                                <h5 class="mb-4">{{ translate('order_details') }}</h5>
                                <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#order_details">
                                    <span class="media-body hover-primary text-nowrap">{{translate('view_order_details')}}</span>
                                </button>
                            </div>
                            <div class="row gy-3 text-dark track-order-details-info">
                                <div class="col-lg-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="column-2">
                                            <div>{{ translate('Order_Created_At') }}</div>
                                            <div class="fw-bold">{{date('D, d M, Y ',strtotime($orderInfo['created_at']))}}</div>
                                        </div>
                                        @if($orderInfo->delivery_man_id && $orderInfo['order_status'] !="delivered")
                                            <div class="column-2">
                                                <div>{{ translate('Estimated_Delivery_Date') }}</div>
                                                <div class="fw-bold">
                                                    @if($orderInfo['expected_delivery_date'])
                                                        {{ date('D, d M, Y ',strtotime($orderInfo['expected_delivery_date'])) }}
                                                    @else
                                                        Not Set
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="column-2">
                                            <div>{{ translate('Order_Status') }}</div>
                                            <div class="fw-bold">
                                                {{$utilityService->translateOrderStatus($orderInfo['order_status']) }}
                                            </div>
                                        </div>
                                        <div class="column-2">
                                            <div>{{ translate('Payment_Status') }}</div>
                                            @if($orderInfo['payment_status']=="paid")
                                            <div class="fw-bold">
                                                <span class="badge bg-success text-white">Paid</span>
                                            </div>
                                            @else
                                                <span class="badge bg-info text-white">Unpaid</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                        </div>

                        <div class="table-responsive">
                            <table class="table text-capitalize text-start align-middle">
                                <thead class="mb-3">
                                    <tr>
                                        <th class="min-w-300 text-nowrap">{{translate('product_details')}}</th>
                                        <th>{{translate('QTY')}}</th>
                                        <th class="text-end text-nowrap">{{translate('sub_total')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderDetail as $key=>$detail)
                                        @php($productDetails = App\Model\Product::where('id', $detail->product_id)->first())
                                        <tr>
                                            <td>
                                                <div class="media align-items-center gap-3">
                                                    <img class="rounded border"
                                                        src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$productDetails['thumbnail']}}"
                                                        onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'" width="100px"                                                src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$productDetails['thumbnail']}}"
                                                        alt="Image Description">
                                                    <div >
                                                        <h6 class="title-color mb-2">{{Str::limit($productDetails['name'],30)}}</h6>
                                                        <div class="d-flex flex-column">
                                                            <small>
                                                                <strong>{{translate('unit_price')}} :</strong>
                                                                {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['price']))}}
                                                                @if ($detail->tax_model =='include')
                                                                    ({{translate('tax_incl.')}})
                                                                @else
                                                                    ({{translate('tax').":".($productDetails->tax)}}{{$productDetails->tax_type ==="percent" ? '%' :''}})
                                                                @endif
                                                            </small>
                                                            @if ($detail->variant)
                                                                <small><strong>{{translate('variation')}} :</strong> {{$detail['variant']}}</small>
                                                            @endif
                                                            <div>{!! $utilityService->translateOrderStatus($detail['delivery_status'], true) !!}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{$detail['qty']}}</td>
                                            @php($subtotal=$detail['price']*$detail['qty']+$detail['tax']-$detail['discount'])
                                            <td>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($subtotal))}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
    </script>
@endpush

