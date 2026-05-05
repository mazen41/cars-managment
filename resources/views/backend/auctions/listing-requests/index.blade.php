@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Auction Listing Requests')}}</h1>
        </div>
    </div>
</div>
<br>

<!-- Statistics Cards -->
<div class="row gutters-10 mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-clock la-3x text-warning mb-2"></i>
                <h4 class="mb-0">{{ $stats['pending_count'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Pending') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-check-circle la-3x text-success mb-2"></i>
                <h4 class="mb-0">{{ $stats['approved_count'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Approved') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-times-circle la-3x text-danger mb-2"></i>
                <h4 class="mb-0">{{ $stats['rejected_count'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Rejected') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center py-3">
                <i class="las la-list la-3x text-info mb-2"></i>
                <h4 class="mb-0">{{ $stats['total_count'] ?? 0 }}</h4>
                <small class="text-muted">{{ translate('Total') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_requests" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Listing Requests') }}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulkApprove()">{{translate('Approve Selected')}}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="showBulkRejectModal()">{{translate('Reject Selected')}}</a>
                </div>
            </div>

            <div class="col-md-2 ml-auto">
                <select class="form-control form-control-sm aiz-selectpicker mb-2 mb-md-0" id="status" name="status" onchange="sort_requests()">
                    <option value="">{{ translate('All Status') }}</option>
                    <option value="pending" @if (request('status') == 'pending') selected @endif>{{ translate('Pending') }}</option>
                    <option value="approved" @if (request('status') == 'approved') selected @endif>{{ translate('Approved') }}</option>
                    <option value="rejected" @if (request('status') == 'rejected') selected @endif>{{ translate('Rejected') }}</option>
                </select>
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="{{ translate('From Date') }}" onchange="sort_requests()">
            </div>

            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="{{ translate('To Date') }}" onchange="sort_requests()">
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @if(request('search')) value="{{ request('search') }}" @endif placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ translate('Car') }}</th>
                        <th data-breakpoints="md">{{ translate('Seller') }}</th>
                        <th data-breakpoints="md">{{ translate('Starting Price') }}</th>
                        {{-- <th data-breakpoints="md">{{ translate('Reserve Price') }}</th> --}}
                        <th data-breakpoints="md">{{ translate('Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Submitted') }}</th>
                        <th width="15%" class="text-right">{{ translate('Options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>
                                <div class="form-group">
                                    <div class="aiz-checkbox-inline">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" class="check-one" name="id[]" value="{{ $request->id }}">
                                            <span class="aiz-square-check"></span>
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row gutters-5 w-200px w-md-300px mw-100">
                                    <div class="col-auto">
                                        <img src="{{ $request->car->main_photo_url ?? static_asset('assets/img/placeholder.jpg') }}" alt="{{translate('Car')}}" class="size-50px img-fit">
                                    </div>
                                    <div class="col text-truncate">
                                        <span class="text-muted text-truncate-2">{{ $request->car->car_name ?? 'N/A' }}</span>
                                        <br>
                                        <small class="text-muted">{{ $request->car->vin ?? 'No VIN' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($request->seller)
                                    <span class="text-muted">{{ $request->seller->name }}</span>
                                    <br>
                                    <small class="text-muted">{{ $request->seller->email }}</small>
                                @else
                                    <span class="text-muted">{{ translate('N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ format_price($request->requested_starting_price) }}</span>
                            </td>
                            {{-- <td>
                                @if($request->requested_reserve_price)
                                    <span class="text-muted">{{ format_price($request->requested_reserve_price) }}</span>
                                @else
                                    <span class="text-muted">{{ translate('None') }}</span>
                                @endif
                            </td> --}}
                            <td>
                                @if($request->status == 'pending')
                                    <span class="badge badge-inline badge-warning">{{ translate('Pending') }}</span>
                                @elseif($request->status == 'approved')
                                    <span class="badge badge-inline badge-success">{{ translate('Approved') }}</span>
                                @else
                                    <span class="badge badge-inline badge-danger">{{ translate('Rejected') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $request->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-right">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.auction-listing-requests.show', $request->id) }}" title="{{ translate('View Details') }}">
                                    <i class="las la-eye"></i>
                                </a>
                                @if($request->status == 'pending')
                                    <a href="javascript:void(0)" class="btn btn-soft-success btn-icon btn-circle btn-sm" onclick="approveRequest({{ $request->id }})" title="{{ translate('Approve') }}">
                                        <i class="las la-check"></i>
                                    </a>
                                    <a href="javascript:void(0)" class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="showRejectModal({{ $request->id }})" title="{{ translate('Reject') }}">
                                        <i class="las la-times"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $requests->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

<!-- Reject Request Modal -->
<div class="modal fade" id="reject-request-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Reject Listing Request') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reject-request-form">
                    <input type="hidden" id="reject_request_id" name="request_id">
                    <div class="form-group">
                        <label for="reject_reason">{{ translate('Rejection Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="rejectRequest()">{{ translate('Reject Request') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulk-reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{ translate('Bulk Reject Requests') }}</h4>
                <button type="button" class="close" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulk-reject-form">
                    <div class="form-group">
                        <label for="bulk_reject_reason">{{ translate('Rejection Reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="bulk_reject_reason" name="reason" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-danger" onclick="bulkReject()">{{ translate('Reject Selected') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function sort_requests() {
            $('#sort_requests').submit();
        }

        function approveRequest(id) {
            var approveUrl = '{{ route('admin.auction-listing-requests.approve', ':id') }}'.replace(':id', id);
            if (confirm('{{ translate('Are you sure you want to approve this listing request?') }}')) {
                $.post(approveUrl, {
                    _token: '{{ csrf_token() }}'
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                }).fail(function(xhr) {
                    AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to approve request') }}');
                });
            }
        }

        function showRejectModal(id) {
            $('#reject_request_id').val(id);
            $('#reject_reason').val('');
            $('#reject-request-modal').modal('show');
        }

        function rejectRequest() {
            var requestId = $('#reject_request_id').val();
            var reason = $('#reject_reason').val();
            var rejectUrl = '{{ route('admin.auction-listing-requests.reject', ':id') }}'.replace(':id', requestId);
            if (!reason) {
                AIZ.plugins.notify('warning', '{{ translate('Please provide a rejection reason') }}');
                return;
            }

            $.post(rejectUrl, {
                _token: '{{ csrf_token() }}',
                reason: reason
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#reject-request-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to reject request') }}');
            });
        }

        function bulkApprove() {
            var ids = [];
            $("input[name='id[]']:checked").each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one request') }}');
                return;
            }

            if (confirm('{{ translate('Are you sure you want to approve selected requests?') }}')) {
                $.post('{{ route('admin.auction-listing-requests.bulk-approve') }}', {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                }, function(data) {
                    if (data.success) {
                        AIZ.plugins.notify('success', data.message);
                        location.reload();
                    } else {
                        AIZ.plugins.notify('danger', data.message);
                    }
                }).fail(function(xhr) {
                    AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to approve requests') }}');
                });
            }
        }

        function showBulkRejectModal() {
            var ids = [];
            $("input[name='id[]']:checked").each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                AIZ.plugins.notify('warning', '{{ translate('Please select at least one request') }}');
                return;
            }

            $('#bulk_reject_reason').val('');
            $('#bulk-reject-modal').modal('show');
        }

        function bulkReject() {
            var ids = [];
            $("input[name='id[]']:checked").each(function() {
                ids.push($(this).val());
            });

            var reason = $('#bulk_reject_reason').val();

            if (!reason) {
                AIZ.plugins.notify('warning', '{{ translate('Please provide a rejection reason') }}');
                return;
            }

            $.post('{{ route('admin.auction-listing-requests.bulk-reject') }}', {
                _token: '{{ csrf_token() }}',
                ids: ids,
                reason: reason
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    $('#bulk-reject-modal').modal('hide');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            }).fail(function(xhr) {
                AIZ.plugins.notify('danger', xhr.responseJSON?.message || '{{ translate('Failed to reject requests') }}');
            });
        }

        $(document).ready(function() {
            // Check all functionality
            $('.check-all').on('click', function() {
                if (this.checked) {
                    $('.check-one').each(function() {
                        this.checked = true;
                    });
                } else {
                    $('.check-one').each(function() {
                        this.checked = false;
                    });
                }
            });

            // Individual checkbox functionality
            $('.check-one').on('click', function() {
                if ($('.check-one:checked').length == $('.check-one').length) {
                    $('.check-all').prop('checked', true);
                } else {
                    $('.check-all').prop('checked', false);
                }
            });
        });
    </script>
@endsection
