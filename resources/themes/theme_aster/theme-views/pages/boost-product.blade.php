@extends('theme-views.layouts.app')

@section('title', "Buyer's Protection".' | '.$web_config['name']->value.' '.translate('ecommerce'))
@push('css_or_js')
   
<meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> 
   <style>
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            font-size: 14px;
        } */

        /* .tp:hover .pricing-table:not(:hover){
            transform: scale(0.97);

        } */


        .verify{
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .pricing-table {
            box-shadow: 0px 0px 6px #ccc;
            text-align: center;
            padding: 30px 0 10px 0;
            border-radius: 20px;
            transition: all 0.3s ease;
            background: white;
        }

        .pricing-table:hover {
            transform: scale(1.1);
        }

        .pricing-table .head {
            border-bottom: 2px solid red;
            padding-bottom: 50px;
            transition: all 0.5s ease;
        }

        /* .pricing-table:hover .head {
            border-bottom: 1px solid red;
        } */

        .pricing-table .head .title {
            margin-bottom: 20px;
            font-size: 30px;
            font-weight: 700;
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
        }

        .pricing-table .content .price {
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            width: fit-content;
            height: fit-content;
            margin: auto;
            /* line-height: 90px; */
            border-radius: 10px;
            border: 5px solid #fff;
            box-shadow: 0px 0px 10px #ccc;
            margin-top: -30px;
            display: flex;
            align-items: center;
            transition: all 0.5s ease;
            padding: 0px 10px;
        }

        .pricing-table:hover .content .price {
            transform: scale(1.2);
        }

        .pricing-table .content .price h1 {
            color: #fff;
            font-size: 25px;
            margin: auto;
            font-weight: 700;
        }

        .header2 {
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            width: fit-content;
            height: fit-content;
            margin: auto;
            margin-bottom: 80px;
            /* line-height: 90px; */
            border-radius: 10px;
            border: 5px solid #fff;
            box-shadow: 0px 0px 10px #ccc;

            display: flex;
            align-items: center;
            transition: all 0.5s ease;
            padding: 7px 10px;

            & > * {
                color: #fff;
                font-size: clamp(2em, 3vw, 3em);
                margin: auto;
                font-weight: 700;
            }
        }
        .header3 {
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            width: fit-content;
            height: fit-content;
            
            /* line-height: 90px; */
            border-radius: 10px;
            border: 5px solid #fff;
            box-shadow: 0px 0px 10px #ccc;
            /* margin:auto; */

            display: flex;
            align-items: center;
            transition: all 0.5s ease;
            padding: 17px 20px;
            transform: rotate(-90deg);
            & > * {
                color: #fff;
                font-size: clamp(1.3em, 2vw, 3em);
                margin: auto;
                font-weight: 700;
            }
        }


        .pricing-table .content table {
            text-align: left;
            width: 80%;
            margin: 35px 20px 5px 20px !important;
            padding-top: 90px;
            margin: auto;
        }

        .pricing-table .content table tr {
            transition: 0.7s 0s ease;
            cursor: pointer;

            & td {
                transition: 0.9s 0s ease;
                padding: 5px;
                font-size: 12px;
                color: #000000;
                font-weight: 600;

                & del {
                    color: #585656;
                    font-weight: 500;
                    font-size: 14px;
                }
            }
        }

        .pricing-table .content table tr:hover {


            & td {


                color: rgb(175, 4, 4) !important;

                & del {
                    color: #585656;
                    font-weight: 500;
                    transform: scale(1);

                }
            }
        }

        /* 
        .pricing-table .content table tr td {
           
        } */

        .upgrade {
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            border-radius: 40px !important;
            font-weight: 500 !important;
            position: relative !important;
            display: flex !important;
            justify-content: center;
            align-items: center;
        }

        .upgrade .btn {
            color: #fff !important;
            padding: 16px 60px !important;
            display: inline-block !important;
            text-align: center !important;
            font-weight: 600 !important;
            -webkit-transition: all 0.3s linear !important;
            -moz-transition: all 0.3 linear !important;
            transition: all 0.3 linear !important;
            border: none !important;
            font-size: 22px !important;
            text-transform: capitalize !important;
            position: relative !important;
            text-decoration: none !important;
            margin: 2px !important;
            z-index: 9999 !important;
            text-decoration: none !important;
            border-radius: 50px !important;
        }

        .upgrade .btn:hover {
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
        }

        .upgrade .btn.bordered {
            z-index: 50 !important;
            color: rgb(175, 4, 4) !important;
        }

        .upgrade:hover .btn.bordered {
            color: #fff !important;
        }

        .upgrade .btn.bordered:after {
            background: #fff !important;
            border-radius: 50px !important;
            content: "";
            height: 100% !important;
            left: 0 !important;
            position: absolute !important;
            top: 0 !important;
            /* -webkit-transition: all 0.3s linear !important ;
            -moz-transition: all 0.3 linear !important ; */
            transition: all 0.3 linear !important;
            width: 100% !important;
            z-index: -1 !important;
            /* -webkit-transform: scale(1) !important ;
            -moz-transform: scale(1) !important ; */
            transform: scale(1) !important;
        }

        .upgrade:hover .btn.bordered:after {
            opacity: 0 !important;
            transform: scale(0) !important;
        }

        .bi {
            font-size: 25px;
        }

        .bi-x {
            color: red;
        }

        .bi-check-lg {
            color: green;
        }

        .bi-award-fill {
            margin: 0 10px 0 0;
            color: black;
        }

        .bi-award-fill.red {
            color: transparent;
            background: linear-gradient(to left, rgb(253, 66, 19), rgb(175, 4, 4));
            -webkit-background-clip: text;
            background-clip: text;
        }

        .bi-award-fill.green {
            color: transparent;
            background: linear-gradient(to left, rgb(106, 255, 69), rgb(29, 127, 10));
            -webkit-background-clip: text;
            background-clip: text;
        }

        .bi-award-fill.purple {
            color: transparent;
            background: linear-gradient(to left, rgb(173, 69, 177), rgb(76, 8, 118));
            -webkit-background-clip: text;
            background-clip: text;
        }

        .bi-award-fill.gold {
            color: transparent;
            background: linear-gradient(to left, rgb(228, 233, 83), rgb(255, 230, 0));
            -webkit-background-clip: text;
            background-clip: text;
        }

        .special-services p{
            font-size: clamp(0.8em, 1vw, 2em);
        }
    </style>
  
    
@endpush
@section('content')

<!-- Main Content -->
<main class="main-content d-flex flex-column gap-3 pb-3">
    <div class="page-title overlay py-5 __opacity-half background-custom-fit" style="--opacity: .5"

    @if ($page_title_banner)
        @if (File::exists(base_path('storage/app/public/banner/'.json_decode($page_title_banner['value'])->image)))
        data-bg-img="{{ asset('storage/app/public/banner/'.json_decode($page_title_banner['value'])->image) }}"
        @else
        data-bg-img="{{theme_asset('assets/img/media/page-title-bg.png')}}"
        @endif
    @else
        data-bg-img="{{theme_asset('assets/img/media/page-title-bg.png')}}"
    @endif
    >
        <div class="container">
            <h1 class="absolute-white text-center">Boost Products </h1>
        </div>
    </div> 
    <div class="mx-2 mx-lg-5 m-auto">
        <div class="card my-2">
            <div class="card-body px-lg-4 text-dark page-paragraph">
            <div class="container-fluid">
        <div class="container my-2">
           

            <div class="header2">
                <h1 class="text-center">
                    BOOST PLAN
                </h1>
            </div>
        </div>
        <div class="tp row row-cols-1  row-cols-md-2 row-cols-lg-5 g-4">
            <div class="col">
                <div class="pricing-table">
                    <div class="head">
                        <h4 class="title">Free</h4>
                    </div>
                    <div class="content">
                        <div class="price">
                            <h1>&#8358;0</h1>
                        </div>
                        <table>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Merchant category | Free
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Service Quality | Regular
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Product Upload - 5
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>SMS Notification</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Email Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Dedicated personal manager</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Video Upload</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Store Front Link
                                </td>
                            </tr>
                            <tr>
                                <!-- <td><i class="bi bi-x"></i></td> -->
                                <td colspan="2">
                                    <i class="bi bi-award-fill"></i> <del>Verified Badge</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Trust Assurance</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Advert placement on two major social media platforms.</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>Promotion Power - limited</td>
                            </tr>
                        </table>
                        <!-- <ul>
                            <li>Merchant category | Free</li>
                            <li>Service Quality | Regular</li>
                            <li>Product Upload - 5</li>
                            <li><del>SMS Notification</del></li>
                            <li><del>Email Notification</del></li>
                            <li><del>Dedicated personal manager</del></li>
                            <li><del>Video Upload</del></li>
                            <li><del>Store Front Link</del></li>
                            <li><del>Verified Badge</del></li>
                            <li><del>Trust Assurance</del></li>
                            <li><del>Advert placement on two major social media platforms.</del></li>
                            <li><del></del></li>
                        </ul> -->

                    </div>
                </div>
            </div>
            <div class="col">
                <div class="pricing-table">
                    <div class="head">
                        <h4 class="title">Individual</h4>
                    </div>
                    <div class="content">
                        <div class="price">
                            <h1>&#8358;25,500</h1>
                        </div>
                        <table>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Merchant category | Verified
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Service Quality | Premium
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Product Upload - 10
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>SMS Notification</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Email Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Dedicated personal manager</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Video Upload</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Store Front Link
                                </td>
                            </tr>
                            <tr>
                                <!-- <td><i class="bi bi-x"></i></td> -->
                                <td colspan="2">
                                    <i class="bi bi-award-fill red"></i> Verified Badge
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Trust Assurance
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Advert placement on two major social media platforms.</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>Promotion Power - 5x More</td>
                            </tr>
                        </table>
                        <!-- <ul>
                            <li>Merchant category | Free</li>
                            <li>Service Quality | Regular</li>
                            <li>Product Upload - 5</li>
                            <li><del>SMS Notification</del></li>
                            <li><del>Email Notification</del></li>
                            <li><del>Dedicated personal manager</del></li>
                            <li><del>Video Upload</del></li>
                            <li><del>Store Front Link</del></li>
                            <li><del>Verified Badge</del></li>
                            <li><del>Trust Assurance</del></li>
                            <li><del>Advert placement on two major social media platforms.</del></li>
                            <li><del></del></li>
                        </ul> -->

                    </div>
                </div>
            </div>
            <div class="col">
                <div class="pricing-table">
                    <div class="head">
                        <h4 class="title">Corporate</h4>
                    </div>
                    <div class="content">
                        <div class="price">
                            <h1>&#8358;36,500</h1>
                        </div>
                        <table>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Merchant category | Verified
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Service Quality | Premium
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Product Upload - 20
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>SMS Notification</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Email Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Dedicated personal manager</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Video Upload</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Store Front Link
                                </td>
                            </tr>
                            <tr>
                                <!-- <td><i class="bi bi-x"></i></td> -->
                                <td colspan="2">
                                    <i class="bi bi-award-fill green"></i> Verified Badge
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Trust Assurance
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-x"></i></td>
                                <td>
                                    <del>Advert placement on two major social media platforms.</del>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>Promotion Power - 10x More</td>
                            </tr>
                        </table>
                        <!-- <ul>
                            <li>5 GB Ram</li>
                            <li>40GB SSD Cloud Storage</li>
                            <li>Month Subscription</li>
                            <li>Responsive Framework</li>
                            <li>Monthly Billing Software</li>
                            <li>1 Free Website</li>
                        </ul> -->

                    </div>
                </div>
            </div>

            <div class="col">
                <div class="pricing-table">
                    <div class="head">
                        <h4 class="title">Super</h4>
                    </div>
                    <div class="content">
                        <div class="price">
                            <h1>&#8358;78,500</h1>
                        </div>
                        <table>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Merchant category | Verified
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Service Quality | Premium
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Product Upload - 40
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    SMS Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Email Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Dedicated personal manager
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Video Upload
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Store Front Link
                                </td>
                            </tr>
                            <tr>
                                <!-- <td><i class="bi bi-x"></i></td> -->
                                <td colspan="2">
                                    <i class="bi bi-award-fill purple"></i> Verified Badge
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Trust Assurance
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Advert placement on two major social media platforms.
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>Promotion Power - 15x More</td>
                            </tr>
                        </table>
                        <!-- <ul>
                            <li>5 GB Ram</li>
                            <li>40GB SSD Cloud Storage</li>
                            <li>Month Subscription</li>
                            <li>Responsive Framework</li>
                            <li>Monthly Billing Software</li>
                            <li>1 Free Website</li>
                        </ul> -->

                    </div>
                </div>
            </div>
            <div class="col">
                <div class="pricing-table">
                    <div class="head">
                        <h4 class="title">gold</h4>
                    </div>
                    <div class="content">
                        <div class="price">
                            <h1>&#8358;136,700</h1>
                        </div>
                        <table>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Merchant category | Verified
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Service Quality | Premium
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Product Upload - Unlimited
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    SMS Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Email Notification
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Dedicated personal manager
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Video Upload
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Store Front Link
                                </td>
                            </tr>
                            <tr>
                                <!-- <td><i class="bi bi-x"></i></td> -->
                                <td colspan="2">
                                    <i class="bi bi-award-fill gold"></i> Verified Badge
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Trust Assurance
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>
                                    Advert placement on two major social media platforms.
                                </td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-check-lg"></i></td>
                                <td>Promotion Power - Unlimited</td>
                            </tr>
                        </table>
                        <!-- <ul>
                            <li>5 GB Ram</li>
                            <li>40GB SSD Cloud Storage</li>
                            <li>Month Subscription</li>
                            <li>Responsive Framework</li>
                            <li>Monthly Billing Software</li>
                            <li>1 Free Website</li>
                        </ul> -->

                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-center align-items-center mt-5">
            <h4  class="my-4 text-center">
                To boost your products and services successfully, you must be a Verified member. Incase you are not yet
                verified , <a href="{{ route('seller.business.upgrade-business') }}" class="verify">Click Here</a>
            </h4>
            <div class="upgrade mt-5">
                <a href="{{ route('seller.shop.premiumservice') }}" class="btn bordered radius">Upgrade Now</a>
            </div>
        </div>

        <div class="d-flex gap-5 flex-column flex-lg-row justify-content-center align-items-center col-lg-9 mt-2 pt-2 m-auto">
            <div class=" d-flex justify-content-center align-items-center">
                <div class="m-auto" style="height: 200px;width:200px">
                    <img src="{{ asset('storage/app/public/33.jpg') }}" class="w-100 h-100 rounded">
                </div>
            </div>
            <div class="col-lg-5">
                <div class="my-5 py-5 special-services">
                   
                    <p class="my-4">
                        <b># GOLD MERCHANT CATEGORY:</b> is reserved for product manufacturers and merchants that deals with; building materials, properties, cars, heavy duty vehicles, trucks, key and foreign brands and foreign service providers.
                    </p>

                    <p class="my-4">
                        <b># SUPER MERCHANTS CATEGORY:</b> Are reserved for dealers, wholesalers in all products except those mentioned above. 
                    </p>
                </div>
            </div>
        </div>

        
    </div>
            </div>
        </div>
    </div>
</main>
<!-- End Main Content -->

@endsection