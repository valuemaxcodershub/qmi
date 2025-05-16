<!-- Feature -->
<section class="feature-secton">
    <div class="container py-3">
        <style>
            .mkto{
                width:100% !important;
                height:50% !important;
            }
            .f-ads{
                overflow:hidden !important;
                /* padding: 0px 0 !important; */
                white-space: nowrap;
            }
                        
            .f-mq{
                animation: 18s slide_ads infinite linear !important;
                
                display: flex !important;                            

            }
            .f-mq .f-maqr{
                width:110px !important;
                height:70px !important;
                margin:0 25px !important;
            }
            .f-mq .f-maqr img{
                margin: 0 10px;
            }

            @keyframes slide_ads {
                from{
                    transform: translateX(0) ;
                }
                to{
                    transform: translateX(-100%) ;
                }
            }
        </style>
        <div class="feature-section-inner"> 
            <div class="row my-5">
                <div class="col-md-12">
                    <strong class="text-black" style="font-size: 24px">Our Partners</strong>
                </div>
                <div class="f-ads d-flex mt-3  rounded ">
                    <div class="f-mq">
                       
                        @foreach(App\Model\Brand::active()->inRandomOrder()->take(15)->get() as $brand)
                            <div class="f-maqr">
                                <img 
                                    src="{{asset("storage/app/public/brand/$brand->image")}}" alt="{{$brand->name}}"
                                    loading="lazy" onerror="this.src='{{ theme_asset('assets/img/image-place-holder-2_1.png') }}'"
                                    class="rounded w-100 h-100">
                            </div>  
                        @endforeach
                                                              
                    </div>                
                </div>
            </div>
            <div class="row g-3 g-lg-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="media gap-3 mb-3 align-items-center">
                        <div class="feature-icon-wrap">
                            <img src="{{theme_asset('assets/img/icons/f1.png')}}" alt="">
                        </div>
                        <div class="media-body">
                            <h5 class="mb-2">{{translate('Drop_Shipping')}}</h5>
                            <div class="fs-12">{{translate('Dropshipping_all_across_the_country')}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="media gap-3 mb-3 align-items-center">
                        <div class="feature-icon-wrap">
                            <img src="{{theme_asset('assets/img/icons/f2.png')}}" alt="">
                        </div>
                        <div class="media-body">
                            <h5 class="mb-2">{{translate('Authentic_Products')}}</h5>
                            <div class="fs-12">100% {{translate('Authentic_Products')}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="media gap-3 mb-3 align-items-center">
                        <div class="feature-icon-wrap">
                            <img src="{{theme_asset('assets/img/icons/f3.png')}}" alt="">
                        </div>
                        <div class="media-body">
                            <h5 class="mb-2">100% {{translate('Secure_Payment')}}</h5>
                            <div class="fs-12">{{translate('We_Ensure_Secure_Transactions')}}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="media gap-3 mb-3 align-items-center">
                        <div class="feature-icon-wrap">
                            <img src="{{theme_asset('assets/img/icons/f4.png')}}" alt="">
                        </div>
                        <div class="media-body">
                            <h5 class="mb-2">{{translate('24/7_Support_Center')}}</h5>
                            <div class="fs-12">{{translate('We_Ensure_Quality_Support')}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const Logocopy = document.querySelector(".f-mq").cloneNode(true)
    document.querySelector(".f-ads").appendChild(Logocopy)
</script>
