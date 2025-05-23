<div class="dropdown">
    <a href="{{ route('shop-cart') }}" class="position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside"
        aria-expanded="false">
        @php($cart = \App\CPU\CartManager::get_cart())
        <!-- <i class="bi bi-bag "></i> -->
        <i class="bi bi-cart fs-4"></i>
        <span class="count">{{ $cart->count() }}</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end scrollY-60" style="--bs-dropdown-min-width: 22.5rem">
        @if ($cart->count() > 0)
            @php($sub_total = 0)
            @php($total_tax = 0)
            @foreach ($cart as $cartItem)
                @php($product = \App\Model\Product::find($cartItem['product_id']))
                <li>
                    <div class="media gap-3">
                        <div class="avatar avatar-xxl position-relative overflow-hidden rounded">
                            <img src="{{ \App\CPU\ProductManager::product_image_path('thumbnail') }}/{{ $cartItem['thumbnail'] }}"
                                onerror="this.src='{{ theme_asset('assets/img/image-place-holder.png') }}'"
                                loading="lazy" alt="Product"
                                class="img-fit dark-support rounded img-fluid overflow-hidden {{ $product->status == 0 ? 'blur-section' : '' }}">
                            @if ($product->status == 0)
                                <span class="temporary-closed position-absolute text-center p-2">
                                    <span>{{ translate('not_Available') }}</span>
                                </span>
                            @endif
                        </div>
                        <div class="media-body">
                            <h6 class="mb-2 {{ $product->status == 0 ? 'blur-section' : '' }}">
                                <a
                                    href="{{ $product->status == 1 ? route('product', $cartItem['slug']) : 'javascript:' }}">{{ Str::limit($cartItem['name'], 30) }}</a>
                            </h6>
                            <div class="d-flex gap-3 justify-content-between align-items-end">
                                <div class="d-flex flex-column gap-1 {{ $product->status == 0 ? 'blur-section' : '' }}">
                                    <div class="fs-12"><span
                                            class="cart_quantity_{{ $cartItem['id'] }}">{{ $cartItem['quantity'] }}</span>
                                        ×
                                        {{ \App\CPU\Helpers::currency_converter($cartItem['price'] - $cartItem['discount']) }}
                                    </div>
                                    <div class="product__price d-flex flex-wrap gap-2">
                                        <del
                                            class="product__old-price quantity_price_of_{{ $cartItem['id'] }}">{{ \App\CPU\Helpers::currency_converter($cartItem['price'] * $cartItem['quantity']) }}</del>
                                        <ins
                                            class="product__new-price discount_price_of_{{ $cartItem['id'] }}">{{ \App\CPU\Helpers::currency_converter(($cartItem['price'] - $cartItem['discount']) * (int) $cartItem['quantity']) }}</ins>
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div class="quantity">
                                    @if ($product->status == 1)
                                        <span class="quantity__minus cart_quantity__minus{{ $cartItem['id'] }}"
                                            onclick="updateCartQuantity('{{ $cartItem['id'] }}','{{ $cartItem['product_id'] }}', '-1', 'minus')">
                                            <i
                                                class="{{ $cartItem['quantity'] == ($product ? $product->minimum_order_qty : 1) ? 'bi bi-trash3-fill text-danger fs-10' : 'bi bi-dash' }}"></i>
                                        </span>
                                        <input type="text"
                                            class="quantity__qty cart_quantity_of_{{ $cartItem['id'] }}"
                                            data-min="{{ $product ? $product->minimum_order_qty : 1 }}"
                                            value="{{ $cartItem['quantity'] }}"
                                            onchange="updateCartQuantity('{{ $cartItem['id'] }}','{{ $cartItem['product_id'] }}', '0')">
                                        <span class="quantity__plus"
                                            onclick="updateCartQuantity('{{ $cartItem['id'] }}','{{ $cartItem['product_id'] }}', '1')">
                                            <i class="bi bi-plus"></i>
                                        </span>
                                    @else
                                        <span class="quantity__minus cart_quantity__minus{{ $cartItem['id'] }}"
                                            onclick="updateCartQuantity('{{ $cartItem['id'] }}','{{ $cartItem['product_id'] }}', '-{{ $cartItem['quantity'] }}', 'minus')">
                                            <i class="bi bi-trash3-fill text-danger fs-10"></i>
                                        </span>
                                        <input type="text"
                                            class="quantity__qty cart_quantity_of_{{ $cartItem['id'] }}"
                                            data-min="{{ $product ? $product->minimum_order_qty : 1 }}" value="0"
                                            onchange="updateCartQuantity('{{ $cartItem['id'] }}','{{ $cartItem['product_id'] }}', '0')">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @php($sub_total += ($cartItem['price'] - $cartItem['discount']) * (int) $cartItem['quantity'])
                @php($total_tax += $cartItem['tax'] * (int) $cartItem['quantity'])
            @endforeach

            <li>
                <div class="flex-between-gap-3 pt-2 pb-4">
                    <h6>{{ translate('total') }}</h6>
                    <h3 class="text-primary cart_total_amount">{{ \App\CPU\Helpers::currency_converter($sub_total) }}
                    </h3>
                </div>
                <div class="d-flex gap-3">
                    @if ($web_config['guest_checkout_status'] || auth('customer')->check())
                        <a href="{{ route('shop-cart') }}"
                            class="btn btn-outline-primary btn-block">{{ translate('view_cart') }}</a>
                        <a href="{{ route('checkout-details') }}"
                            class="btn btn-primary btn-block">{{ translate('go_to_checkout') }}</a>
                    @else
                        <a href="{{ route('customer.auth.login') }}"
                            class="btn btn-outline-primary btn-block">{{ translate('view_cart') }}</button>
                            <a href="{{ route('customer.auth.login') }}"
                                class="btn btn-primary btn-block">{{ translate('go_to_checkout') }}</a>
                    @endif
                </div>
            </li>
        @else
            <div class="widget-cart-item">
                <h6 class="text-danger text-center m-0 p-2"><i class="fa fa-cart-arrow-down"></i>
                    {{ translate('empty_Cart') }}
                </h6>
            </div>
        @endif
    </ul>
</div>
