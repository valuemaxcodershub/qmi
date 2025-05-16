@extends('layouts.back-end.app')

@section('title', 'Seller Types MGT')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                Seller's Upgrade Request
            </h2>
        </div>
        <!-- End Page Title -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="p-3">
                        <div class="card-header row justify-content-between align-items-center">
                            <h5 class="ml-3">
                                Upgrade Request
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="alert alert-danger p-3" style="font-size: 16px">
                            Viewing upgrade request from <strong>{{ $viewUpgrade->currentsellertype->name }}</strong> to
                            <strong>{{ $viewUpgrade->sellertype->name }}</strong>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mt-3 mb-3">
                                <h3><u>Section A : Seller Information</u></h3>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Seller Name:</strong>
                                    {{ $viewUpgrade->seller->f_name . ' ' . $viewUpgrade->seller->l_name }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Seller Mobile Number:</strong> {{ $viewUpgrade->seller->phone }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Seller Email Address:</strong> {{ strtolower($viewUpgrade->seller->email) }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Seller Business ID:</strong>
                                    {{ strtolower($viewUpgrade->seller->business_shortcode) }}
                                </div>
                            </div>

                            <div class="col-md-12 mt-3 mb-3">
                                <h3><u>Section B (1): Business Information</u></h3>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Company Name:</strong>
                                    {{ $viewUpgrade->company_name }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Company Email:</strong>
                                    <a href="mailto:{{ $viewUpgrade->company_email }}">{{ $viewUpgrade->company_email }}</a>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Company Phone:</strong>
                                    {{ $viewUpgrade->company_phone }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Years in Operation:</strong>
                                    {{ $viewUpgrade->business_year }} Year(s)
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="border p-2">
                                    <strong>Company Address:</strong>
                                    {{ $viewUpgrade->company_address }}
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="border p-2">
                                    <strong>Organization worked with:</strong>
                                    {{ $viewUpgrade->partner_companies }}
                                </div>
                            </div>

                            <div class="col-md-12 mt-3 mb-3">
                                <h3><u>Section B (1): Business Representative Information</u></h3>
                            </div>

                            @php
                                $managerDetail = json_decode($viewUpgrade->manager_details);
                            @endphp

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Manager's Name:</strong>
                                    {{ $managerDetail->name }}
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <div class="border p-2">
                                    <strong>Manager's Phone:</strong>
                                    {{ $managerDetail->phone }}
                                </div>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="border p-2">
                                    <strong>Manager's Address:</strong>
                                    {{ $managerDetail->address }}
                                </div>
                            </div>

                            <div class="col-md-12 mt-3 mb-3">
                                <h3><u>Section B (2): Business Information (Attachments)</u></h3>
                            </div>

                            @php
                                $attachments = json_decode($viewUpgrade->attachments, true);
                            @endphp

                            @foreach ($attachments as $attachmentIndex => $attachmentData)
                                @if (strtolower($attachmentIndex) != 'payment_receipt')
                                    <div class="col-md-3 mb-2">
                                        <div class="card">
                                            <div class="text-center">
                                                <img src="{{ asset('storage/app/' . $attachmentData) }}"
                                                    class="img-thumbnail text-center" style="width: 50%; height: 100%">
                                            </div>
                                            <div class="card-footer text-center">
                                                <div
                                                    style="background: #c4c4c4; padding: 5px; border: 1px solid #f5f5f5; border-radius: 5px; color: #fff; font-size: 16px">
                                                    {{ ucwords(str_replace(['_', '-'], ' ', $attachmentIndex)) }}
                                                    <a href="{{ asset('storage/app/' . $attachmentData) }}" target="_blank"
                                                        class="btn btn-sm btn-info">View</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if (isset($attachments['payment_receipt']))
                                <div class="col-md-12 mt-3 mb-3">
                                    <h3><u>Section B (2): Payment Receipt </u></h3>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <div class="card">
                                        <div class="text-center">
                                            <img src="{{ asset('storage/app/' . $attachments['payment_receipt']) }}"
                                                class="img-thumbnail text-center" style="width: 50%; height: 100%">
                                        </div>
                                        <div class="card-footer text-center">
                                            <div
                                                style="background: #c4c4c4; padding: 5px; border: 1px solid #f5f5f5; border-radius: 5px; color: #fff; font-size: 16px">
                                                Payment Receipt
                                                <a href="{{ asset('storage/app/' . $attachments['payment_receipt']) }}"
                                                    target="_blank" class="btn btn-sm btn-info">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-12 mt-3 mb-3">
                                    <div class="alert alert-danger">
                                        Payment receipt not uploaded yet
                                    </div>
                                </div>
                            @endif

                            @if (isset($attachments['payment_receipt']))
                                <div class="card-footer">
                                    <div class="text-center mt-2 mb-2">
                                        @if ($viewUpgrade->status == 'pending')
                                            <a href="{{ route('admin.sellers.update-request', ['reference' => $viewUpgrade->reference, 'status' => 'review']) }}"
                                                class="btn btn-info"><strong>In Review</strong></a>
                                        @elseif ($viewUpgrade->status == 'pending' or $viewUpgrade->status == 'review')
                                            <a href="{{ route('admin.sellers.update-request', ['reference' => $viewUpgrade->reference, 'status' => 'approved']) }}"
                                                class="btn btn-success"><strong>Approve Request</strong></a>
                                            <button class="btn btn-danger"type="button" data-bs-toggle="modal"
                                                data-bs-target="#myModal"><strong>Decline Request</strong></button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="col-md-12 mb-2">
                            <div class="border p-2">
                                <strong>Status:</strong>
                                {!! $viewUpgrade->statusHtml !!}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- The Modal -->
        <div class="modal" id="declineModal">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Reason for Declining</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form
                        action="{{ route('admin.sellers.decline-update-request', ['reference' => request()->reference]) }}"
                        method="POST">
                        @csrf
                        <!-- Modal body -->
                        <div class="modal-body">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea type="text" class="form-control" name="message" placeholder="Provide reason for declining..."
                                        rows="5" required></textarea>
                                </div>
                            </div>

                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>


    </div>
@endsection
