@php
    $customer_info = \App\CPU\customer_info();
@endphp
<div class="col-lg-3">
    <div class="card profile-sidebar-sticky">
        <div class="card-body position-relative">
            <div
                class="d-lg-none bg-primary rounded px-1 text-white cursor-pointer position-absolute end-1 top-1 profile-menu-toggle">
                <i class="bi bi-list fs-18"></i>
            </div>
            <div class="d-flex flex-row flex-lg-column gap-2 gap-lg-4 align-items-center">
                <div class="avatar overflow-hidden profile-sidebar-avatar border border-primary rounded-circle p-1">
                    <img onerror="this.src='{{ theme_asset('assets/img/image-place-holder.png') }}'"
                        src="{{ asset('storage/app/public/profile') }}/{{ $customer_info->image }}" alt=""
                        class="img-fit dark-support rounded-circle">
                </div>

                <div class="text-lg-center">
                    <h5 class="mb-1">{{ $customer_info->f_name }} {{ $customer_info->l_name }}</h5>
                    <p class="fw-medium">{{ translate('Joined') }}
                        {{ date('d M, Y', strtotime($customer_info->created_at)) }}</p>
                </div>
            </div>

            <div class="profile-menu-aside">
                <div class="profile-menu-aside-close d-lg-none">
                    <i class="bi bi-x-lg text-primary"></i>
                </div>
                <ul class="list-unstyled profile-menu gap-1 mt-3">
                    <li
                        class="{{ Request::is('user-profile') || Request::is('user-account') || Request::is('account-address-*') ? 'active' : '' }}">
                        <a href="{{ route('user-profile') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('My_profile') }}</span>
                        </a>
                    </li>
                    <li
                        class="{{ Request::is('account-oder*') || Request::is('account-order-details*') || Request::is('refund-details*') || Request::is('track-order/order-wise-result-view') ? 'active' : '' }}">
                        <a href="{{ route('account-oder') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon2.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('Orders') }}</span>
                        </a>
                    </li>

                    {{-- <li class="{{ Request::is('fundwallet*') ? 'active' : '' }}">
                        <a href="{{ route('fundwallet-view') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon5.png') }}"
                                class="dark-support" alt="">
                            <span>Add Money To Wallet</span>
                        </a>
                    </li>

                    <li class="{{ Request::is('withdraw*') ? 'active' : '' }}">
                        <a href="{{ route('withdraw-fund') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon5.png') }}"
                                class="dark-support" alt="">
                            <span>Withdraw Money</span>
                        </a>
                    </li> --}}

                    <li class="dropdown">
                        <a href="javascript:void" data-bs-toggle="dropdown">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon5.png') }}"
                                class="dark-support" alt="">
                            <span>Wallet Menu</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- Add dropdown menu items here -->
                            <li><a class="dropdown-item" href="{{ route('fundwallet-view') }}">Add Money</a></li>
                            <li><a class="dropdown-item" href="{{ route('withdraw-fund') }}">Withdraw Money</a></li>
                            <li><a class="dropdown-item" href="#">Withdrawal History</a></li>
                        </ul>
                    </li>

                    <li class="{{ Request::is('wishlists') ? 'active' : '' }}">
                        <a href="{{ route('wishlists') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon3.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('Wish_List') }}</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('compare-list') ? 'active' : '' }}">
                        <a href="{{ route('compare-list') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon4.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('Compare_List') }}</span>
                        </a>
                    </li>

                    @if ($web_config['wallet_status'] == 1)
                        <li class="{{ Request::is('wallet') ? 'active' : '' }}">
                            <a href="{{ route('wallet') }}">
                                <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon5.png') }}"
                                    class="dark-support" alt="">
                                <span>{{ translate('Wallet') }}</span>
                            </a>
                        </li>
                    @endif

                    @if ($web_config['loyalty_point_status'] == 1)
                        <li class="{{ Request::is('loyalty') ? 'active' : '' }}">
                            <a href="{{ route('loyalty') }}">
                                <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon6.png') }}"
                                    class="dark-support" alt="">
                                <span>{{ translate('Loyalty_Point') }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="{{ Request::is('chat/seller') || Request::is('chat/delivery-man') ? 'active' : '' }}">
                        <a href="{{ route('chat', ['type' => 'seller']) }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon7.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('Inbox') }}</span>
                        </a>
                    </li>
                    <li class="{{ Request::is('account-tickets') || Request::is('support-ticket*') ? 'active' : '' }}">
                        <a href="{{ route('account-tickets') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/profile-icon8.png') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('Support_Ticket') }}</span>
                        </a>
                    </li>

                    @if ($web_config['ref_earning_status'])
                        <li class="{{ Request::is('refer-earn') || Request::is('refer-earn*') ? 'active' : '' }}">
                            <a href="{{ route('refer-earn') }}">
                                <img width="20" src="{{ theme_asset('assets/img/icons/refer-and-earn.svg') }}"
                                    class="dark-support" alt="">
                                <span>{{ translate('refer_&_Earn') }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="{{ Request::is('user-coupons') || Request::is('user-coupons*') ? 'active' : '' }}">
                        <a href="{{ route('user-coupons') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/coupon.svg') }}"
                                class="dark-support" alt="">
                            <span>{{ translate('coupons') }}</span>
                        </a>
                    </li>

                    <li class="dropdown">
                        <a href="javascript:void" data-bs-toggle="dropdown">
                            <img width="20" src="{{ theme_asset('assets/img/icons/settings.svg') }}"
                                class="dark-support" alt="">
                            <span>Settings</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- Add dropdown menu items here -->
                            <li><a class="dropdown-item" href="{{ route('user-bank-account') }}">Bank Management</a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('user-transact-pin') }}">Security Pin</a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('customer.auth.logout') }}">
                            <img width="20" src="{{ theme_asset('assets/img/icons/logout.svg') }}"
                                class="dark-support" alt="">
                            <span>Log Out</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div>
