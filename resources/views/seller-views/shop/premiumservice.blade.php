
@extends('layouts.back-end.app-seller')
@section('title', 'Premium Service Boosting')
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('public/assets/back-end')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
     <!-- Custom styles for this page -->
     <link href="{{asset('public/assets/back-end/css/croppie.css')}}" rel="stylesheet">
     <meta name="csrf-token" content="{{ csrf_token() }}">

     {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bs-red: red;
        }

        .msg_cell {
            border: 1px solid #c4c4c4; 
            padding: 10px;
            margin-top: 10px;
        }

        .slider{
            -webkit-appearance: none !important;
            appearance: none !important;
            height: 15px !important;
            border-radius: 6px;
            background: #d3d3d3;
        }

        .slider::-webkit-slider-thumb {
            background: red !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            width:25px !important;
            height: 25px !important;
            cursor: pointer !important; 
            border-radius: 50%;
        }
        .slider::-moz-range-thumb {
            background: red !important;
            -webkit-appearance: none !important;
            appearance: none !important;
            width:25px !important;
            height: 25px !important;
            cursor: pointer !important;
        }

        .bi-caret-up-fill {
            color: var(--bs-red);
            font-size: 20px;
            transition: transform .5s ease;
        }

        .links {
            color: red;
        }

        .days p {
            width: 25px;
            height: 25px;
            text-align: center;
            border-radius: 50%;
            transition: background .5s ease, color .5s ease;
        }

        .days {
            cursor: pointer;
            font-weight: bold;

            &:hover {
                &>.bi-caret-up-fill {
                    /* color: white; */
                    transform: translateY(-9px);
                }

                &>p {
                    background-color: red;
                    color: white;
                }
            }
        }

        .btn {
            background: var(--bs-red);
            color: white;

            &:hover {
                background: var(--bs-red) !important;
                color: white;
            }
        }

        .boost--btn {
            font-size: 20px;
        }

        /* .search-input{
            width: 400px;
        } */

        @media (min-width: 768px){
            .search-input{
                width: 400px;
            }
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('content')
    <!-- Content Row -->
    <div class="content container-fluid">

    <!-- Page Title -->
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{asset('/public/assets/back-end/img/shop-info.png')}}" alt="">
            Premium Service Boosting
        </h2>
    </div>
    <!-- End Page Title -->

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 ">Boost Product</h5>
                    <a href="{{route('seller.shop.view')}}" class="btn btn--primary __inline-70 px-4 text-white">{{ translate('back') }}</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.shop.boost-product') }}" method="post">
                        @csrf
                        <div class="alert alert-secondary">
                            To boost your product for more engagement, kindly select the number of days you would like your product to be active using the slider below. <br>
                            Boosting of product for a day cost <strong class="text-dark">&#8358;{{ number_format($boostingFee, 2) }}</strong>
                        </div>
                        <div class="bg-light p-3 p-lg-5 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3>
                                    Number of days : <span id="day">0</span>
                                </h3>
                                <h3>
                                    Total price : <span class="">₦</span> <span id="output">0</span>
                                </h3>
                            </div>

                            <div class="mt-5 mx-auto row align-items-center justify-content-center w-100">
                                <div class="col-1 d-none d-md-block">
                                    <button class="btn" id="btn-decrement">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                </div>

                                <div class="col-12 col-lg-10">
                                    <input type="range" name="days" class="slider form-range m-auto w-100" min="0" max="60" step="1" id="customRange3" value="0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">0</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">10</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">20</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">30</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">40</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">50</p>
                                        </div>
                                        <div class="d-flex days flex-column justify-content-center align-items-center">
                                            <i class="bi bi-caret-up-fill"></i>
                                            <p class="">60</p>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-1 d-none d-md-block">
                                    <button class="btn" id="btn-increment">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <div class="row d-md-none">
                                    <div class="col-6">
                                        <button class="btn" id="btn-decrement">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button class="btn" id="btn-increment">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="m-auto mt-5">
                        

                            <div class="rounded bg-light py-5 mt-5">
                                <h2 class="text-center mb-4">Product Information</h2>
                                <p class="text-center mb-3">
                                    <b>Please provide the following information</b>
                                </p>
                                <div class="row p-5 col-8 m-auto">
                                    <div class="col-lg d-flex justify-content-center align-items-center mb-5 mb-lg-0">
                                        <div class="my-auto">
                                            <label for="url">Provide Product URL 
                                                <span class="" title="Please provide the product link as shown in your browser address bar">?</span>
                                            </label>
                                            <div class="d-flex justify-content-center align-items-center gap-3">
                                                <input type="text" class="form-control search-input" id="product_url" name="product_url" placeholder="Enter product url e.g https://pavi.ng/product/gold-wrist-watch-XaGob0" >
                                                <button class="btn btn-primary search_product">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div id="search_result"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="m-auto">
                            

                            <button class="w-100 boost--btn btn mt-3 boost_product_btn" type="submit" disabled>Boost your post</button>
                            <p class="text-justify mt-4" style="text-align: justify;">
                                By clicking <b>"Boost Your Post"</b>, it is an indication that you have ageed to the
                                <a href="#" class="links">Terms & Conditions</a> of Pavi.Ng, it also confirms that you will abide to
                                by the Safety Tips & declare that the post does not include any prohibited items as contained in the
                                Law and Judiciary
                            </p>
                        </div>


                        <!-- <div class="my-5">
                            <h2 class="text-center">Pay With Card</h2>
                            <div class="row">
                                <div class="col d-flex justify-content-center align-items-center">
                                    <div style="width: 300px;">
                                        <img class="w-100" src="https://pavi.ng/storage/app/brand/mastercard.jpg" alt="">
                                    </div>
                                </div>
                                <div class="col d-flex justify-content-center align-items-center">
                                    <div style="width: 300px;">
                                        <img class="w-100" src="https://pavi.ng/storage/app/brand/visa.jpg" alt="">
                                    </div>
                                </div>
                                <div class="col d-flex justify-content-center align-items-center">
                                    <div style="width: 300px;">
                                        <img class="w-100" src="https://pavi.ng/storage/app/brand/verve.jpg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('script')
    <script>

        $(".search_product").on("click", function () {
            let product_url = $('#product_url').val(); 
            let search_result = $('#search_result');
            let searchBtn = $(".search_product");
            let boostButton = $(".boost_product_btn");
            search_result.html("");
            boostButton.attr("disabled", true);
            if (product_url == "") {
                Swal.fire({
                    title: 'Error!',
                    text: "Please provide product url",
                    confirmButtonColor: '#377dff',
                })
            }
            else {
                let route = "{{ route('seller.shop.search-product') }}"
                let data = {
                    seller_id: "{{ $sellerId }}",
                    product_url: product_url
                }
                searchBtn.html("Please wait <i class='tio-sync tio-spin'></i>").attr("disabled", true);
                // Generate the image URL with JavaScript string concatenation
                $.ajax({
                    url: route,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log(response);
                        // let decode_response = JSON.parse(response);
                        let decode_response = response;
                        let productData = decode_response.data;
                        let imageUrl = "{{ asset('storage/app/public/product/') }}" + "/" + productData.image;
                        let resultInfo = `
                            <div class='alert alert-info mt-3 mb-3'><strong>Product Found! Kindly ensure this is the product you would like to boost for more audience</strong></div>
                            <div class='msg_cell'><strong> Product Name: </strong> ${productData.name}</div>
                                    <div class='msg_cell'><strong> Product Slug: </strong> ${productData.slug}</div>
                                    <div class='msg_cell'><strong> Product Image: </strong> <br>
                                        <img src="${imageUrl}" alt="${productData.name}" class='img img-thumbnail img-responsive' style="height: 200px; width: 200px">
                                    </div>
                                    <input name='product_id' type="hidden" value="${productData.id}">
                            <div class='form-group mt-5'>
                                <label>Select Payment Method</label>
                                <select class='form-control' name='payment_method'>
                                    <option value=''>-- Please Select --</option>
                                    <option value='loyalty_wallet'>Pay with Loyalty Wallet</option>
                                    <option value='atm_card'>Pay with Card</option>
                                </select>
                            </div>
                            `

                        searchBtn.html("Search").attr("disabled", false);
                        search_result.html(resultInfo);
                        boostButton.attr("disabled", false);
                    },
                    error (jqXHR, textStatus, errorThrown) {
                        let response = JSON.parse(jqXHR.responseText);
                        searchBtn.html("Search").attr("disabled", false);
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            confirmButtonColor: '#377dff',
                        })    
                    }
                });
            }
        });

        const range = document.getElementById("customRange3")
        const output = document.getElementById("output")
        const buttons = document.querySelectorAll(".days p")
        const dayOutput = document.getElementById("day")
        const incrementBtn = document.querySelectorAll("#btn-increment")
        const decrementBtn = document.querySelectorAll("#btn-decrement")

        function updateValue(value) {
            let boostingFee = "{{ $boostingFee }}"
            output.textContent = `${value * boostingFee}`
            dayOutput.textContent = `${value}`
        }
        range.addEventListener("change", (e) => updateValue(e.target.value))

        buttons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                range.value = Number(e.target.textContent)
                updateValue(range.value)
            })
        });

        incrementBtn.forEach(IncreBtn => {
            IncreBtn.addEventListener("click", ()=>{
                if(Number(range.value) < Number(range.max)){
                    range.value++
                    updateValue(range.value)
                }
           })
        });
       

        decrementBtn.forEach(DecreBtn => {
            DecreBtn.addEventListener("click", ()=>{
                if(Number(range.value) > Number(range.min)){
                    range.value--
                    updateValue(range.value)
                }
            })
        });
       

        document.getElementById('productImage').addEventListener('change', (e)=>{
            const input = e.target;
            console.log(e.target)

            const reader = new FileReader()
            
            reader.readAsDataURL(input.files[0])
            reader.onload = function (e){
                const img = document.getElementById('upload')
                const text = document.getElementById('upload_text')
                const error = document.getElementById('image-error')
                

                
                const tempImg = new Image();
                tempImg.src = reader.result;
                tempImg.onload = function () {
                    const width = tempImg.naturalWidth;
                    const height = tempImg.naturalHeight;
                    console.log('Image dimensions:', width, 'x', height);

                    if(width < 1024 || height < 1024 ){
                        text.style.display = "block" 
                        error.style.display = "inline" 
                        img.src = 'https://pavi.ng/storage/app/brand/upload.jpg'
                        img.classList.add('w-50', 'h-50');
                        img.classList.remove('w-100', 'h-100');
                        error.textContent = `This image's dimensions (${width + ' x '  + height}) are not allowed`
                        return;
                    }
                    text.style.display = "none" 
                    error.style.display = "none" 
                    img.src = reader.result;
                    img.classList.remove('w-50', 'h-50');
                    img.classList.add('w-100', 'h-100');
                    console.log(reader.result);
                }
            }

        
        })
    </script>
@endpush
