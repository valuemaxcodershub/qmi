<div class="col-lg-4">
    <!-- Card -->
    <div class="card h-100 d-flex justify-content-center align-items-center">
        <div class="card-body d-flex flex-column gap-10 align-items-center justify-content-center">
            <img width="48" class="mb-2" src="{{asset('/public/assets/back-end/img/inhouse-earning.png')}}" alt="">
            <h3 class="for-card-count mb-0 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['total_earning']))}}</h3>
            <div class="text-capitalize mb-30">
                {{translate('total_balance')}}
            </div>
        </div>
    </div>
    
    {{-- <div class="card h-100 d-flex justify-content-center align-items-center">
        <div class="card-body d-flex flex-column gap-10 align-items-center justify-content-center">
            <img width="48" class="mb-2" src="{{asset('/public/assets/back-end/img/inhouse-earning.png')}}" alt="">
            <h3 class="for-card-count mb-0 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['inhouse_earning']))}}</h3>
            <div class="text-capitalize mb-30">
                {{translate('in-house_earning')}}
            </div>
        </div>
    </div> --}}
    <!-- End Card -->
</div>
<div class="col-lg-8">
    <div class="row g-2">
        
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['commission_earned']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('Total_commission')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/ce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['commission_earned']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('available_commission')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/ce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['commission_escrow']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('Pending_commission')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/ce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['delivery_charge_earned']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('total_delivery_charge')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/dce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['delivery_charge_earned']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('available_delivery_charge')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/dce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['delivery_charge_escrow']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('pending_delivery_charge')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/dce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['pending_amount']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('pending_inhouse_sales')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/dce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['withdrawn']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('total_withdrawn')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/dce.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['total_tax_collected']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('total_tax_collected')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/ttc.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body h-100 justify-content-center">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-column align-items-start">
                        <h3 class="mb-1 fz-24">{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($data['pending_amount']))}}</h3>
                        <div class="text-capitalize mb-0">{{translate('pending_amount')}}</div>
                    </div>
                    <div>
                        <img width="40" class="mb-2" src="{{asset('/public/assets/back-end/img/pa.png')}}" alt="">
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>
