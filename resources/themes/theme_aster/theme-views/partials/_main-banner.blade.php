<section class="banner">
    <div class="container">
        <div class="card moble-border-0">
            <div class="p-0 p-sm-3 m-sm-1">
                <div class="row g-3 mko">
                    <div class="col-xl-3 col-lg-4 d-none d-xl-block">
                        <div class="">
                            <h6 class="mb-2 text-uppercase">{{ translate('Find_What_You_Need') }}</h6>
                            <ul class="dropdown-menu dropdown-menu--static" style="--bs-dropdown-min-width: auto">
                                @foreach ($categories as $key => $category)
                                    <li class="{{ $category->childes->count() > 0 ? 'menu-item-has-children' : '' }}">
                                        <a href="javascript:"
                                            onclick="location.href='{{ route('products', ['id' => $category['id'], 'data_from' => 'category', 'page' => 1]) }}'">
                                            {{ $category['name'] }}
                                        </a>
                                        @if ($category->childes->count() > 0)
                                            <ul class="sub-menu">
                                                @foreach ($category['childes'] as $subCategory)
                                                    <li
                                                        class="{{ $subCategory->childes->count() > 0 ? 'menu-item-has-children' : '' }}">
                                                        <a href="javascript:"
                                                            onclick="location.href='{{ route('products', ['id' => $subCategory['id'], 'data_from' => 'category', 'page' => 1]) }}'">
                                                            {{ $subCategory['name'] }}
                                                        </a>
                                                        @if ($subCategory->childes->count() > 0)
                                                            <ul class="sub-menu">
                                                                @foreach ($subCategory['childes'] as $subSubCategory)
                                                                    <li>
                                                                        <a
                                                                            href="{{ route('products', ['id' => $subSubCategory['id'], 'data_from' => 'category', 'page' => 1]) }}">
                                                                            {{ $subSubCategory['name'] }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                                <li class="d-flex justify-content-center mt-3 py-0">
                                    <a
                                        href="{{ route('products', ['data_from' => 'latest']) }}"class="btn-link text-primary">
                                        {{ translate('view_all') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- middle banner -->
                    <div class="col-xl-6 m-0">
                        <div class="row g-2 g-sm-3">
                            <div class="col-12">
                                <div class="swiper-container shadow-sm rounded w-100 h-100">
                                    <!-- Swiper -->
                                    <div class="swiper" data-swiper-loop="true" data-swiper-navigation-next="null"
                                        data-swiper-navigation-prev="null">
                                        <div class="swiper-wrapper">
                                            @foreach ($main_banner as $key => $banner)
                                                <div class="swiper-slide">
                                                    <a href="{{ $banner['url'] }}" class="h-100">
                                                        <img src="{{ asset('storage/app/public/banner') }}/{{ $banner['photo'] }}"
                                                            loading="lazy"
                                                            onerror="this.src='{{ theme_asset('assets/img/image-place-holder-2_1.png') }}'"
                                                            alt="" class="dark-support rounded">
                                                    </a>
                                                </div>
                                            @endforeach
                                            @if (count($main_banner) == 0)
                                                <img src="{{ theme_asset('assets/img/image-place-holder-2_1.png') }}"
                                                    loading="lazy" alt="" class="dark-support rounded">
                                            @endif
                                        </div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                            </div>
                            @foreach ($footer_banner as $key => $banner)
                                <div class="col-6 d-none d-xl-block">
                                    <a href="{{ $banner['url'] }}" class="ad-hover h-100">
                                        <img src="{{ asset('storage/app/public/banner') }}/{{ $banner['photo'] }}"
                                            loading="lazy" alt=""
                                            onerror="this.src='{{ theme_asset('assets/img/image-place-holder-2_1.png') }}'"
                                            class="dark-support rounded w-100 img-fit">
                                    </a>
                                </div>
                            @endforeach
                            @if (count($footer_banner) == 0)
                                <div class="col-6 d-none d-xl-block">
                                    <span class="ad-hover h-100">
                                        <img src="{{ theme_asset('assets/img/image-place-holder-2_1.png') }}"
                                            loading="lazy" alt="" class="dark-support rounded w-100 img-fit">
                                    </span>
                                </div>
                                <div class="col-6 d-none d-xl-block">
                                    <span class="ad-hover h-100">
                                        <img src="{{ theme_asset('assets/img/image-place-holder-2_1.png') }}"
                                            loading="lazy" alt="" class="dark-support rounded w-100 img-fit">
                                    </span>
                                </div>
                            @endif
                            @if (count($footer_banner) == 1)
                                <div class="col-6 d-none d-xl-block">
                                    <span class="ad-hover h-100">
                                        <img src="{{ theme_asset('assets/img/image-place-holder-2_1.png') }}"
                                            loading="lazy" alt="" class="dark-support rounded w-100">
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- middle banner -->
                    @if (count($random_coupon) > 0)
                        <div class="col-xl-3 d-none d-xl-block w-100">
                            <div class="bg-primary-light rounded p-3 mt-lg-3">
                                <h3 class="text-primary my-3">{{ translate('Happy_Club') }}</h3>
                                <p>{{ translate('collect_coupons_from_stores_and_apply_to_get_special_discount_from_stores') }}
                                </p>

                                <div class="d-flex flex-wrap gap-3">
                                    @foreach ($random_coupon as $coupon)
                                        <div class="club-card card custom-border-color hover-shadow flex-grow-1"
                                            onclick="coupon_copy('{{ $coupon->code }}')">
                                            <div class="d-flex flex-column gap-2 p-3">
                                                <h5 class="d-flex gap-2 align-items-center">
                                                    @if ($coupon->coupon_type == 'free_delivery')
                                                        {{ translate($coupon->coupon_type) }}
                                                        <img src="{{ theme_asset('assets/img/svg/delivery-car.svg') }}"
                                                            alt="" class="svg">
                                                    @else
                                                        {{ $coupon->discount_type == 'amount' ? \App\CPU\Helpers::currency_converter($coupon->discount) : $coupon->discount . '%' }}
                                                        OFF
                                                        <img src="{{ theme_asset('assets/img/svg/dollar.svg') }}"
                                                            alt="" class="svg">
                                                    @endif
                                                </h5>
                                                <h6 class="fs-12">
                                                    <span class="text-muted">{{ translate('for') }}</span>
                                                    <span class="text-uppercase ">
                                                        @if ($coupon->seller_id == '0')
                                                            {{ translate('All_Shops') }}
                                                        @elseif($coupon->seller_id == null)
                                                            <a class="shop-name"
                                                                onclick="location.href='{{ route('shopView', ['id' => 0]) }}'">
                                                                {{ $web_config['name']->value }}
                                                            </a>
                                                        @else
                                                            <a class="shop-name"
                                                                onclick="location.href='{{ isset($coupon->seller->shop) ? route('shopView', ['id' => $coupon->seller->shop['id']]) : 'javascript:' }}'">
                                                                {{ isset($coupon->seller->shop) ? $coupon->seller->shop->name : translate('shop_not_found') }}
                                                            </a>
                                                        @endif
                                                    </span>
                                                </h6>
                                                <h6 class="text-primary fs-12">{{ translate('code') }}:
                                                    {{ $coupon->code }}</h6>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                    <!-- top side banner -->
                    <style>
                        .mkto{
                            width:100% !important;
                            height:50% !important;
                        }
                        .jko{
                            height:100% !important;
                            
                        }

                        .ads{
                            overflow:hidden !important;
                            /* padding: 0px 0 !important; */
                            white-space: nowrap;
                        }
                       

                        
                        .mq{
                            animation: 18s slide_ads infinite linear !important;
                           
                            display: flex !important;                            

                        }
                        .mq .maqr{
                            width:190px !important;
                            height:150px !important;
                            margin:0 10px !important;
                        }

                        @keyframes slide_ads {
                            from{
                               transform: translateX(0) ;
                            }
                            to{
                                transform: translateX(-100%) ;
                            }
                        }
                        .default-1{
                            width: 100% !important;
                            height: 75px !important;
                        }

                    </style>
                        <div class="col-xl-3 d-none d-xl-block gap-4 m-0 jko">
                            @if (count($top_side_banner) > 0)
                                @foreach ($top_side_banner as $bannerIndex => $bannerData)
                                    @if ($bannerIndex <= 1)
                                        <a href="{{ $bannerData['url'] }}" class="mkto">
                                            <img src="{{ asset('storage/app/public/banner') }}/{{ $bannerData ? $bannerData['photo'] : '' }}"
                                                onerror="this.src='{{ theme_asset('assets/img/top-side-banner-placeholder.png') }}'"
                                                alt="" class="dark-support rounded w-100 h-100  mb-3">
                                        </a>
                                    @endif
                                @endforeach
                                <img src="https://cdn.pixabay.com/animation/2023/10/10/13/26/13-26-26-701_512.gif" class="dark-support rounded p-0 m-0 default-1">

                                
                            @else
                                <img src="{{ theme_asset('assets/img/top-side-banner-placeholder.png') }}"
                                    class="dark-support rounded w-100">
                            @endif
                            <!-- <a href="{{ $bannerData['url'] }}" class="mkto"> -->
                                
                            <!-- </a> -->
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (count($mobileAdsBanner) > 0) 
            <div class="ads d-flex mt-3 d-xl-none rounded ">
                <div class="mq">
                    @foreach ($mobileAdsBanner as $adIndex => $adData)
                    
                            <div class="maqr">
                            <img src="{{ asset('storage/app/public/banner') }}/{{ $adData['photo'] }}"
                                    loading="lazy" alt=""
                                    onerror="this.src='{{ theme_asset('assets/img/image-place-holder-2_1.png') }}'"
                                    class="rounded  w-100 h-100">
                            </div>
                                    
                    @endforeach

                </div>
            
            </div>
        @endif
    </div>
</section>

<script>
    const copy = document.querySelector(".mq").cloneNode(true)
    document.querySelector(".ads").appendChild(copy)
</script>
