@extends('theme-views.layouts.app')

@section('title', translate('Track_Order').' | '.$web_config['name']->value.' '.translate('ecommerce'))

@section('content')
    <!-- Main Content -->
    <main class="main-content d-flex flex-column gap-3 py-3 mb-sm-5">
        <div class="container">
            <div class="card h-100">
                <div class="card-body py-4 px-sm-4">
                    <h2 class="mb-30 text-center">{{ translate('Track_order') }}</h2>
                    
                    @if (session()->has('error'))
                        {{ session()->get('error') }}
                    @elseif (session()->has('success'))
                        {{ session()->get('success') }}
                    @endif

                    <form action="{{route('track-order.result')}}" type="submit" method="post" class="p-sm-3">
                        @csrf
                        <div class="d-flex flex-column flex-sm-row flex-wrap gap-3 align-items-sm-end">
                            <div class="flex-grow-1 d-flex gap-3">
                                <div class="form-group flex-grow-1">
                                    <label for="order_id">{{ translate('Order_ID') }}</label>
                                    <input type="text" id="order_id" name="order_id" class="form-control" value="{{ old('order_id', request('id')) }}" 
                                        placeholder="{{ translate('Order_ID') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary h-45 flex-grow-1">{{ translate('Track_order') }}</button>
                            <a href="{{ route('track-order.index') }}" class="btn btn-secondary h-45 flex-grow-1">Reset</a>
                        </div>
                    </form>

                    @if (session()->has('error'))
                        <div class="alert alert-danger"><strong>Error: </strong> {{ session()->get('error') }}</div>
                    @elseif (session()->has('success'))
                        {{ session()->get('success') }}
                    @endif

                    <div class="text-center mt-5">
                        <img width="92" src="{{ theme_asset('assets/img/media/track-order.png') }}" class="dark-support mb-2" alt="">
                        <p class="text-muted">{{ translate('Enter_your_order_ID_to_get_delivery_updates_about_your_order') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- End Main Content -->
@endsection
