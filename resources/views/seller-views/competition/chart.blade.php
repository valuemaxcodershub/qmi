@extends('layouts.back-end.app-seller')

@section('title', translate('Competitions'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        
        <div class="card mb-3 remove-card-shadow">
            <div class="card-body">
                <div class="row justify-content-between align-items-center g-2 mb-3">
                    <div class="col-sm-12">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img src="{{ asset('/public/assets/back-end/img/business_analytics.png') }}" alt="">
                            Competition Detail : {{ $competition_name }}
                        </h4>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th> S/No </th>
                                    <th> Seller Details </th>
                                    <th> Total Sales </th>
                                    <th> Min. Expected Sales </th>
                                    <th> Competition Date </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($competition_charts->count() > 0)
                                    @foreach ($competition_charts as $chartIndex => $chartData)
                                        <tr>
                                            <td> {{ $chartIndex + 1 }} </td>
                                            <td> 
                                                <strong>{{ $chartData->fullname }}</strong> 
                                                <span class="d-flex">Seller Type: {{ $chartData->seller_type_name }}</span>
                                            </td>
                                            <td>  &#8358; {{ number_format($chartData->total_sold, 2) }} </td>
                                            <td>  &#8358; {{ number_format($chartData->minimum_sales_amount	, 2) }} </td>
                                            <td> 
                                                <strong class="d-flex">Start Date: {{ $chartData->start_date }}</strong> 
                                                <strong class="d-flex">End Date: {{ $chartData->end_date }}</strong> 
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
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
