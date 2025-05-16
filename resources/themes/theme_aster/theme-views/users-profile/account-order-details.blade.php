@extends('theme-views.layouts.app')

@section('title', translate('Order_Details').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@php
    $order = $orders[0];
@endphp

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                <!-- Sidebar-->
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between flex-grow-1 d-lg-block">
                                    <h5 class="mb-2"><strong>Order Details</strong> 
                                        <span class="d-flex">#{{ $ordergroupid }}</span>
                                    </h5>
                                    @if (!empty($order->expected_delivery_date))
                                        <span class="text-dark mb-2">
                                            <strong class="fs-14 text-danger">Expected Delivery Date: </strong> &nbsp;
                                                <span class="d-flex">{{ date('D, d M, Y ', strtotime($order->expected_delivery_date)) }}</span>
                                        </span>
                                    @endif
                                </div>

                                <div class="">

                                    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between flex-grow-1 d-lg-block">
                                        <h6>{{translate('Order_Status')}}</h6>

                                        @if($order['order_status']=='failed' || $order['order_status']=='canceled')
                                            <span class="badge bg-danger rounded-pill">
                                                {{translate($order['order_status'] =='failed' ? 'Failed To Deliver' : $order['order_status'])}}
                                            </span>
                                        @elseif($order['order_status']=='confirmed' || $order['order_status']=='processing' || $order['order_status']=='delivered')
                                            <span class="badge bg-success rounded-pill">
                                                {{translate($order['order_status']=='processing' ? 'packaging' : $order['order_status'])}}
                                            </span>
                                        @else
                                            <span class="badge bg-info rounded-pill">
                                                {{translate($order['order_status'])}}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="d-none d-lg-flex gap-3 align-items-center mt-2 justify-content-between">
                                        <h6>{{translate('Payment_Status')}}</h6>
                                        <div class="{{ $order['payment_status']=='unpaid' ? 'text-danger':'text-dark' }}"> {{ translate($order['payment_status']) }}</div>
                                    </div>
                                    <a href="{{ route('track-order.index', ['id' => $ordergroupid]); }}" class="btn btn-sm btn-primary"><i class="tio-track"></i> Track Order</a>
                                </div>
                            </div>

                            {{-- @include('theme-views.partials._order-details-head',['order'=>$order]) --}}
                            
                            <div class="table-responsive mt-4">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0"><input type="checkbox" id="select_all"></th>
                                            <th class="border-0">{{translate('Product_Details')}}</th>
                                            <th class="border-0 text-center">{{translate('Qty')}}</th>
                                            <th class="border-0 text-end">{{translate('Unit_Price')}}</th>
                                            <th class="border-0 text-end">{{translate('Discount')}}</th>
                                            <th class="border-0 text-end" {{ ($order->order_type == 'default_type' && $order->order_status=='delivered') ? 'colspan="2"':'' }}>{{translate('Total')}}</th>
                                            {{-- @if($order->order_type == 'default_type' && ($order->order_status=='delivered' || ($order->payment_status == 'paid' && $digital_product)))
                                                <th class="border-0 text-center">{{translate('Action')}}</th>
                                            @elseif($order->order_type != 'default_type' && $order->order_status=='delivered')
                                                <th class="border-0 text-center"></th>
                                            @endif --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <form method="POST" action="{{ route('customer-update-order') }}">
                                            @csrf
                                            
                                        @php($allowConfBtn=$allowCancBtn=$amountPaid=0)
                                            @foreach ($orders as $orderInfo)
                                                @foreach ($orderInfo->details as $key=>$detail)
                                                    @php($product=json_decode($detail->product_details,true))
                                                    @php($orderStatus = strtolower($detail->delivery_status))
                                                    @php($subTotal=($detail->qty*$detail->price)-$detail->discount)
                                                    @if($product)
                                                        @if (in_array($orderStatus, ['pending', 'confirmed', 'processing']))
                                                            @php($allowCancBtn++)
                                                        @elseif (str_contains("out_for_delivery", $orderStatus))
                                                            @php($allowConfBtn++)
                                                        @endif
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="order_id[]" class="myproduct" value="{{ $detail->id }}" {{ in_array($orderStatus, ['pending', 'confirmed', 'processing', 'out_for_delivery']) ? '' : 'disabled' }}>
                                                            </td>
                                                            <td>
                                                                <div class="media gap-3">
                                                                    <div class="avatar avatar-xxl rounded border overflow-hidden">

                                                                        @if($detail->product_all_status)
                                                                            <img class="d-block img-fit" onerror="this.src='{{ theme_asset('assets/img/image-place-holder.png') }}'"
                                                                            src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$detail->product_all_status['thumbnail']}}" alt="VR Collection" width="60">
                                                                        @else
                                                                            <img class="d-block img-fit"
                                                                            onerror="this.src='{{ theme_asset('assets/img/image-place-holder.png') }}'"
                                                                            src="{{\App\CPU\ProductManager::product_image_path('thumbnail')}}/{{$product['thumbnail']}}"
                                                                            alt="VR Collection" width="60">
                                                                        @endif

                                                                    </div>
                                                                    <div class="media-body d-flex gap-1 flex-column">
                                                                        <h6>
                                                                            <a href="{{route('product',[$product['slug']])}}">
                                                                                {{isset($product['name']) ? Str::limit($product['name'],40) : ''}}
                                                                            </a>
                                                                            @if($detail->refund_request == 1)
                                                                                <small> ({{translate('refund_pending')}}
                                                                                    ) </small> <br>
                                                                            @elseif($detail->refund_request == 2)
                                                                                <small> ({{translate('refund_approved')}}
                                                                                    ) </small> <br>
                                                                            @elseif($detail->refund_request == 3)
                                                                                <small> ({{translate('refund_rejected')}}
                                                                                    ) </small> <br>
                                                                            @elseif($detail->refund_request == 4)
                                                                                <small> ({{translate('refund_refunded')}}
                                                                                    ) </small> <br>
                                                                            @endif<br>
                                                                        </h6>
                                                                        @if($detail->variant)
                                                                            <small>{{translate('variant')}}
                                                                                :{{$detail->variant}} </small>
                                                                        @endif

                                                                        <span class="d-flex">
                                                                            {!! $utilityService->translateOrderStatus($orderStatus, true) !!}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">{{$detail->qty}}</td>
                                                            <td class="text-end">{{\App\CPU\Helpers::currency_converter($detail->price)}} </td>
                                                            <td class="text-end">{{\App\CPU\Helpers::currency_converter($detail->discount)}}</td>
                                                            
                                                            <td class="text-end">{{\App\CPU\Helpers::currency_converter($subTotal)}}</td>
                                                        </tr>
                                                        @php($amountPaid += $subTotal)
                                                    @endif
                                                @endforeach
                                            @endforeach
                                            
                                            <tr>
                                                <td colspan="6">Amount Paid: <strong>&#8358;{{ number_format($amountPaid, 2) }}</strong></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                    
                                    @if ($allowCancBtn > 0)
                                        <div class="form-group text-center">
                                            <button class="btn btn-danger mb-2" name="action" value="cancel_order" type="submit" onclick="return confirm('Are you sure?')">
                                                Cancel My Order
                                            </button>
                                        </div>
                                    @elseif ($allowConfBtn > 0)
                                        <div class="form-group">
                                            <button class="btn btn-danger mb-2" name="action" value="lodge_complaint" type="submit" onclick="return confirm('Are you sure?')">
                                                Lodge Complaint</button>
                                            <button class="btn btn-dark mb-2" name="action" value="confirm_delivery" type="submit" onclick="return confirm('Are you sure?')">
                                                Confirmed Delivery</button>
                                            {{-- <button class="btn btn-danger mb-2" name="action" value="reject_delivery" type="submit" onclick="return confirm('Are you sure?')">
                                                Reject Delivery</button> --}}
                                        </div>
                                    @endif
                                    
                                </form>
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
    <script src="{{ theme_asset('assets/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $(".coba").spartanMultiImagePicker({
                fieldName: 'fileUpload[]',
                maxCount: 5,
                rowHeight: '150px',
                groupClassName: 'col-md-4',
                placeholderImage: {
                    image: '{{ theme_asset('assets/img/image-place-holder.png') }}',
                    width: '100%'
                },
                dropFileLabel: "{{ translate('drop_here') }}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate("input_png_or_jpg")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate("file_size_too_big")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $("#select_all").change(function(){  //"select all" change
			var status = this.checked; // "select all" checked status
			$(".myproduct").each(function(){ //iterate all listed checkbox items
				this.checked = status; //change ".checkbox" checked status
			});
		});
    </script>

    <script type="text/javascript">
        $(function () {
            $(".coba_refund").spartanMultiImagePicker({
                fieldName: 'images[]',
                maxCount: 5,
                rowHeight: '150px',
                groupClassName: 'col-md-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{ theme_asset('assets/img/image-place-holder.png') }}',
                    width: '100%'
                },
                dropFileLabel: "{{translate('drop_here')}}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('{{translate("input_png_or_jpg")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('{{translate("file_size_too_big")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function digital_product_download(link)
        {
            $.ajax({
                type: "GET",
                url: link,
                responseType: 'blob',
                beforeSend: function () {
                    $("#loading").addClass("d-grid");
                },
                success: function (data) {
                    if (data.status == 1 && data.file_path) {
                        const a = document.createElement('a');
                        a.href = data.file_path;
                        a.download = data.file_name;
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(data.file_path);
                    }else if(data.status == 0) {
                        toastr.error(data.message);
                    }
                },
                error: function () {

                },
                complete: function () {
                    $("#loading").removeClass("d-grid");
                },
            });
        }
    </script>
@endpush
