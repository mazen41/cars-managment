@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ translate('Listing Request Details') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.auction-listing-requests.index') }}" class="btn btn-circle btn-light">
                <span>{{translate('Back to List')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Car Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Car Information') }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h4>{{ $request->car->car_name ?? 'N/A' }}</h4>
                    </div>
                </div>

                <!-- Car Main photo -->

                <div class="row mb-3">
                    <div class="col-md-12">
                            <img src="{{ $request->car->main_photo_url }}" class="d-block w-100" alt="Car Image" style="max-height: 400px; object-fit: contain;">
                    </div>
                </div>


                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" width="30%">{{ translate('VIN') }}</td>
                        <td>{{ $request->car->vin ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Brand') }}</td>
                        <td>{{ $request->car->carBrand->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Model') }}</td>
                        <td>{{ $request->car->carModel->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Year') }}</td>
                        <td>{{ $request->car->manufacture_year ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Color') }}</td>
                        <td>{{ $request->car->carColor->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Mileage') }}</td>
                        <td>{{ number_format($request->car->milage ?? 0) }} km</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Fuel Type') }}</td>
                        <td>{{ $request->car->fuel_type ? translate(ucfirst($request->car->fuel_type)) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Transmission') }}</td>
                        <td>{{ $request->car->transmission_type ? translate(ucfirst($request->car->transmission_type)) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Condition') }}</td>
                        <td>
                            @if($request->car->condition == 'new')
                                <span class="badge badge-inline badge-success">{{ translate('New') }}</span>
                            @else
                                <span class="badge badge-inline badge-info">{{ translate('Used') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Description') }}</td>
                        <td>{{ $request->car->description ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Inspection Status -->
        @if($request->car->latestInspection())
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Inspection Status') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="30%">{{ translate('Inspection Type') }}</td>
                            <td>{{ $request->car->latestInspection()->inspectionType->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Inspector') }}</td>
                            <td>{{ $request->car->latestInspection()->inspector->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Inspection Date') }}</td>
                            <td>{{ $request->car->latestInspection()->inspection_date ? $request->car->latestInspection()->inspection_date->format('d M Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Status') }}</td>
                            <td>
                                @if($request->car->latestInspection()->status == 'completed')
                                    <span class="badge badge-inline badge-success">{{ translate('Completed') }}</span>
                                @else
                                    <span class="badge badge-inline badge-warning">{{ translate(ucfirst($request->car->latestInspection()->status)) }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <a href="{{ route('admin.car-inspections.show', $request->car->latestInspection()->id) }}" class="btn btn-sm btn-primary">
                        {{ translate('View Full Inspection Report') }}
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Request Details -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Request Details') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">{{ translate('Status') }}</td>
                        <td>
                            @if($request->status == 'pending')
                                <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                            @elseif($request->status == 'approved')
                                <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Seller') }}</td>
                        <td>
                            @if($request->seller)
                                {{ $request->seller->name }}<br>
                                <small class="text-muted">{{ $request->seller->email }}</small>
                            @else
                                {{ translate('N/A') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">{{ translate('Starting Price') }}</td>
                        <td><strong>{{ format_price($request->requested_starting_price) }}</strong></td>
                    </tr>
                    {{-- <tr>
                        <td class="text-muted">{{ translate('Reserve Price') }}</td>
                        <td>
                            @if($request->requested_reserve_price)
                                <strong>{{ format_price($request->requested_reserve_price) }}</strong>
                            @else
                                <span class="text-muted">{{ translate('None') }}</span>
                            @endif
                        </td>
                    </tr> --}}
                    <tr>
                        <td class="text-muted">{{ translate('Submitted') }}</td>
                        <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if($request->reviewed_at)
                        <tr>
                            <td class="text-muted">{{ translate('Reviewed By') }}</td>
                            <td>{{ $request->reviewer->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ translate('Reviewed At') }}</td>
                            <td>{{ $request->reviewed_at->format('d M Y H:i') }}</td>
                        </tr>
                    @endif
                </table>

                @if($request->admin_notes)
                    <div class="alert alert-info">
                        <strong>{{ translate('Admin Notes') }}:</strong><br>
                        {{ $request->admin_notes }}
                    </div>
                @endif

                @if($request->status == 'pending')
                    <div class="mt-3">
                        <button class="btn btn-success btn-block" onclick="showApproveModal()">
                            <i class="las la-check"></i> {{ translate('Approve Request') }}
                        </button>
                        <button class="btn btn-danger btn-block" onclick="showRejectModal()">
                            <i class="las la-times"></i> {{ translate('Reject Request') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approve-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Approve Listing Request') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approve-form">
                    <div class="form-group">
                        <label for="admin_notes">{{ translate('Admin Notes (Optional)') }}</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-success" onclick="approveRequest()">{{ translate('Approve') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Reject Listing Request') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reject-form">
                    <div class="form-group">
                        <label for="reject_reason">{{ translate('Rejection Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="rejectRequest()">{{ translate('Reject') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function showApproveModal() {
            $('#admin_notes').val('');
            $('#approve-modal').modal('show');
        }

        function approveRequest() {
            var adminNotes = $('#admin_notes').val();

            $.post('{{ route('admin.auction-listing-requests.approve', $request->id) }}', {
                _token: '{{ csrf_token() }}',
                admin_notes: adminNotes
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#approve-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to approve request') }}');
            });
        }

        function showRejectModal() {
            $('#reject_reason').val('');
            $('#reject-modal').modal('show');
        }

        function rejectRequest() {
            var reason = $('#reject_reason').val();

            if (!reason) {
                AIZ.plugins.notify('warning', '{{ translate('Please provide a rejection reason') }}');
                return;
            }

            $.post('{{ route('admin.auction-listing-requests.reject', $request->id) }}', {
                _token: '{{ csrf_token() }}',
                reason: reason
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#reject-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to reject request') }}');
            });
        }
    </script>
@endsection
