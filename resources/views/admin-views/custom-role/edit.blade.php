@extends('layouts.back-end.app')
@section('title', translate('edit_Role'))
@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('role_Update')}}
            </h2>
        </div>
        <!-- End Page Title -->

        <!-- Content Row -->
        <div class="card">
            <div class="card-body">
                <form id="submit-create-role" action="{{route('admin.custom-role.update',[$role['id']])}}" method="post"
                      style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group mb-4">
                                <label for="name" class="title-color">{{translate('role_name')}}</label>
                                <input type="text" name="name" value="{{$role['name']}}" class="form-control" id="name"
                                       aria-describedby="emailHelp"
                                       placeholder="{{translate('ex')}} : {{translate('store')}}">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-4 flex-wrap">
                        <label for="module" class="title-color mb-0">{{translate('module_permission')}}
                            : </label>
                        <div class="form-group d-flex gap-2">
                            <input type="checkbox" id="select_all" class="cursor-pointer">
                            <label class="title-color mb-0  cursor-pointer"
                                   for="select_all">{{translate('select_All')}}</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="dashboard"
                                       class="form-check-input module-permission"
                                       id="dashboard" {{in_array('dashboard',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="dashboard">{{translate('dashboard')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input module-permission" name="modules[]"
                                       {{in_array('pos_management',(array)json_decode($role['module_access']))?'checked':''}} value="pos_management"
                                       id="pos_management">
                                <label class="title-color mb-0"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="pos_management">{{translate('pos_management')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="order_management"
                                       class="form-check-input module-permission"
                                       id="order" {{in_array('order_management',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="order">{{translate('Order_Management')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="product_management"
                                       class="form-check-input module-permission"
                                       id="product" {{in_array('product_management',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="product">{{translate('Product_Management')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="promotion_management"
                                       class="form-check-input module-permission"
                                       id="promotion_management" {{in_array('promotion_management',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="promotion_management">{{translate('Promotion_Management')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="support_section"
                                       class="form-check-input module-permission"
                                       id="support_section" {{in_array('support_section',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="support_section">{{translate('Help_&_Support_Section')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="report"
                                       class="form-check-input module-permission"
                                       id="report" {{in_array('report',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="report">{{translate('reports_and_analytics')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="user_section"
                                       class="form-check-input module-permission"
                                       id="user_section" {{in_array('user_section',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="user_section">{{translate('user_management')}}</label>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group form-check">
                                <input type="checkbox" name="modules[]" value="system_settings"
                                       class="form-check-input module-permission"
                                       id="system_settings" {{in_array('system_settings',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="form-check-label"
                                       style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                       for="system_settings">{{translate('system_Settings')}}</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group d-flex gap-2">
                                <input type="checkbox" class="module-permission" name="modules[]" value="competition"
                                    id="competition" {{in_array('competition',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="title-color mb-0" style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                        for="competition">{{translate('competition_management')}}</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group d-flex gap-2">
                                <input type="checkbox" class="module-permission" name="modules[]" value="sales_commission" 
                                      id="sales_commission" {{in_array('sales_commission',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="title-color mb-0" style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                        for="sales_commission">{{translate('sales_commission_setup')}}</label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group d-flex gap-2">
                                <input type="checkbox" class="module-permission" name="modules[]" value="system_settings"
                                    id="system_settings" {{in_array('system_settings',(array)json_decode($role['module_access']))?'checked':''}}>
                                <label class="title-color mb-0" style="{{Session::get('direction') === "rtl" ? 'margin-right: 1.25rem;' : ''}};"
                                        for="system_settings">KYC Verification</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn--primary">{{translate('update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>

        $('#submit-create-role').on('submit', function (e) {

            var fields = $("input[name='modules[]']").serializeArray();
            if (fields.length === 0) {
                toastr.warning("{{ translate('select_minimum_one_selection_box') }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
                return false;
            } else {
                $('#submit-create-role').submit();
            }
        });
    </script>

    <script>
        $("#select_all").on('change', function () {
            if ($("#select_all").is(":checked") === true) {
                console.log($("#select_all").is(":checked"));
                $(".module-permission").prop("checked", true);
            } else {
                $(".module-permission").removeAttr("checked");
            }
        });

        $(document).ready(function(){
            checkbox_selection_check();
        })

        function checkbox_selection_check() {
            let nonEmptyCount = 0;
            $(".module-permission").each(function() {
                if ($(this).is(":checked") !== true) {
                    nonEmptyCount++;
                }
            });
            if (nonEmptyCount == 0) {
                $("#select_all").prop("checked", true);
            }else{
                $("#select_all").removeAttr("checked");
            }
        }

        $('.module-permission').click(function(){
            checkbox_selection_check();
        });
    </script>
@endpush
