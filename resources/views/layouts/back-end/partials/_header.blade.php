<div id="headerMain" class="d-none">
    <header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container shadow" style="background: #656664">

        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper">
                <!-- Logo -->
                @php($e_commerce_logo=\App\Model\BusinessSetting::where(['type'=>'company_web_logo'])->first()->value)
                <a class="navbar-brand" href="{{route('admin.dashboard.index')}}" aria-label="">
                    <img class="navbar-brand-logo"
                         onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                         src="{{asset("storage/app/public/company/$e_commerce_logo")}}" alt="Logo">
                    <img class="navbar-brand-logo-mini"
                         onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                         src="{{asset("storage/app/public/company/$e_commerce_logo")}}"
                         alt="Logo">
                </a>
                <!-- End Logo -->
            </div>

            <div class="navbar-nav-wrap-content-left">
                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3 d-xl-none">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                       data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
            </div>


            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right"
                 style="{{Session::get('direction') === "rtl" ? 'margin-left:unset; margin-right: auto' : 'margin-right:unset; margin-left: auto'}}">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row">

                    @if(\App\CPU\Helpers::module_permission_check('support_section'))
                        <li class="nav-item d-none d-md-inline-block">
                            <!-- Notification -->
                            <div class="hs-unfold ">
                                <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle" style="color: #fff"
                                   href="{{route('admin.contact.list')}}">
                                    <i class="tio-email"></i>
                                    @php($message=\App\Model\Contact::where('seen',0)->count())
                                    @if($message!=0)
                                        <span class="btn-status btn-sm-status btn-status-danger">{{ $message }}</span>
                                    @endif
                                </a>
                            </div>
                            <!-- End Notification -->
                        </li>
                    @endif

                    @if(\App\CPU\Helpers::module_permission_check('order_management'))
                        <li class="nav-item d-none d-md-inline-block">
                            <!-- Notification -->
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle" style="color: #fff"
                                   href="{{route('admin.orders.list',['status'=>'pending'])}}">
                                    <i class="tio-shopping-cart-outlined"></i>
                                    <span
                                        class="btn-status btn-sm-status btn-status-danger">{{\App\Model\Order::where('order_status','pending')->count()}}</span>
                                </a>
                            </div>
                            <!-- End Notification -->
                        </li>
                    @endif

                    <li class="nav-item view-web-site-info">
                        <div class="hs-unfold">
                            <a onclick="openInfoWeb()" href="javascript:"
                               class="bg-white js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle">
                                <i class="tio-info"></i>
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker media align-items-center gap-3 navbar-dropdown-account-wrapper dropdown-toggle dropdown-toggle-left-arrow"
                               href="javascript:;" style="color: #fff"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="d-none d-md-block media-body text-right">
                                    <h5 class="profile-name mb-0" style="color: #fff">{{auth('admin')->user()->name}}</h5>
                                    <span class="fz-12">{{ auth('admin')->user()->role->name ?? '' }}</span>
                                </div>
                                <div class="avatar border avatar-circle">
                                    <img class="avatar-img"
                                         onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                         src="{{asset('storage/app/public/admin')}}/{{auth('admin')->user()->image}}"
                                         alt="Image Description">
                                    <span class="d-none avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center text-break">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                 onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                                 src="{{asset('storage/app/public/admin')}}/{{auth('admin')->user()->image}}"
                                                 alt="Image Description">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{auth('admin')->user()->name}}</span>
                                            <span class="card-text">{{auth('admin')->user()->email}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item"
                                   href="{{route('admin.profile.update',auth('admin')->user()->id)}}">
                                    <span class="text-truncate pr-2" title="Settings">{{ translate('settings')}}</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="javascript:" onclick="Swal.fire({
                                    title: '{{translate('do_you_want_to_logout')}}?',
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonColor: '#377dff',
                                    cancelButtonColor: '#363636',
                                    confirmButtonText: `{{ translate('yes') }}`,
                                    denyButtonText: `{{ translate('Do_not_Logout') }}`,
                                    cancelButtonText: `{{ translate('cancel') }}`,
                                    }).then((result) => {
                                    if (result.value) {
                                    location.href='{{route('admin.auth.logout')}}';
                                    } else{
                                    Swal.fire('Canceled', '', 'info')
                                    }
                                    })">
                                    <span class="text-truncate pr-2" title="Sign out">{{ translate('sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
        <div id="website_info" style="display: none;" class="bg-secondary w-100">
            <div class="p-3">
                
                <div class="bg-white p-1 rounded mt-2">
                    <a title="Website home" class="p-2 title-color"
                       href="{{route('home')}}" target="_blank">
                        <i class="tio-globe"></i>
                        {{--<span class="btn-status btn-sm-status btn-status-danger"></span>--}}
                        {{translate('view_website')}}
                    </a>
                </div>
                @if(\App\CPU\Helpers::module_permission_check('support_section'))
                    <div class="bg-white p-1 rounded mt-2">
                        <a class="p-2  title-color"
                           href="{{route('admin.contact.list')}}">
                            <i class="tio-email"></i>
                            {{translate('message')}}
                            @php($message=\App\Model\Contact::where('seen',0)->count())
                            @if($message!=0)
                                <span class="">({{ $message }})</span>
                            @endif
                        </a>
                    </div>
                @endif
                @if(\App\CPU\Helpers::module_permission_check('order_management'))
                    <div class="bg-white p-1 rounded mt-2">
                        <a class="p-2  title-color"
                           href="{{route('admin.orders.list',['status'=>'pending'])}}">
                            <i class="tio-shopping-cart-outlined"></i>
                            {{translate('order_list')}}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>

