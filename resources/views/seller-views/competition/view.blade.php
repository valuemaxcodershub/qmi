@extends('layouts.back-end.app-seller')

@section('title', 'View Competition')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        
        <div class="card mb-3 remove-card-shadow">
            <div class="card-header justify-content-between align-items-center">
                <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                    <img src="{{ asset('/public/assets/back-end/img/business_analytics.png') }}" alt="">
                    <strong>
                        {{ $competition->competition_name }}
                    </strong>
                </h4>
                <div class="d-flex gap-2 justify-content-end mr-3">
                    <span class="btn btn--primary text-nowrap">
                        Minimum Sales Amount: <strong>&#8358; {{ number_format($competition->minimum_sales_amount, 2) }}</strong>
                    </span>
                </div>
            </div>
            <div class="card-body" style="white-space: pre-line;">
                {!! !empty($competition->competition_description) ? nl2br($competition->competition_description) : 'No Description' !!}
            </div>
            
            <div class="card-footer text-center">
                @if ($competition->has_joined)
                    <button class="btn btn-info px-4" disabled>
                        <strong>Already Joined</strong>
                    </button>                                      
                    <a href="{{ route('seller.competition.chart-competition', $competition_joined->id) }}" class="btn btn-primary px-4">
                        <strong>View Stats</strong>
                    </a>                                      
                @elseif ((int) $competition->status == 1)
                    <a href="{{ route('seller.competition.join-competition', $competition->id) }}" class="btn btn-success px-4" onclick="return confirm('Are you sure you want to participate in this competition ?')">
                        <strong>Join Competition</strong>
                    </a>
                @else
                    <button class="btn btn-danger px-4" disabled>
                        <strong>Join Competition</strong>
                    </button>
                @endif
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
