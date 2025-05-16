@extends('layouts.back-end.app-seller')

@section('title', translate('Competitions'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        
        <div class="card mb-3 remove-card-shadow">
            <div class="p-3">
                <div class="card-header row justify-content-between align-items-center">
                    <h4 class="ml-3">
                        <img src="{{ asset('/public/assets/back-end/img/business_analytics.png') }}" alt="">
                            Our Competitions
                    </h4>
                    <div class="d-flex gap-2 justify-content-end mr-3">
                        <a href="{{ route('admin.competition.add') }}"
                            class="btn btn--primary text-nowrap">
                            <i class="tio-add"></i>
                            Create Competition
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                <div class="row g-2" id="order_stats">
                    @if ($competitions->count() > 0)
                        @foreach ($competitions as $competition)
                            <div class="col-lg-3">
                                <div class="card h-100 d-flex justify-content-center align-items-center">
                                    <div class="card-body d-flex flex-column gap-10 align-items-center justify-content-center">
                                        <img width="100" class="mb-2" src="{{ asset('/public/assets/back-end/img/competition.jpg') }}" alt="">
                                        <h3 class="for-card-count mb-0 fz-24 text-warning">₦ {{ number_format($competition->minimum_sales_amount, 2) }}</h3>
                                        <h4 class="font-weight-bold text-capitalize mb-2" style="font-size: 28px">
                                            {{ $competition->competition_name }}
                                        </h4>
                                        <span class="d-flex mb-2 text-danger">{{ number_format($competition->seller_joined_count) }} Participant(s)</span>
                                        <a href="{{ route('seller.competition.view-competition', $competition->id) }}" class="btn btn-info px-4">
                                            <strong>View Competition</strong>
                                        </a>
                                    </div>
                                </div>
                            </div>                        
                        @endforeach                        
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection

@push('script')
    <script src="{{ asset('public/assets/back-end') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('public/assets/back-end') }}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script
        src="{{ asset('public/assets/back-end') }}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js">
    </script>
@endpush
