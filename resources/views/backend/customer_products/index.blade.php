@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{translate('Customer Products')}}</h1>
    </div>
</div>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Customer Products Management')}}</h5>
            </div>

            <div class="dropdown mb-2 mb-md-0">
                <button class="btn border btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('PDF')">{{ translate('Export PDF') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('XLS')">{{ translate('Export XLS') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_export('CSV')">{{ translate('Export CSV') }}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_moderate('approve')">{{translate('Approve Selected')}}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_moderate('reject')">{{translate('Reject Selected')}}</a>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="moderation_status" onchange="sort_products()">
                        <option value="">{{translate('All Status')}}</option>
                        <option value="pending" @if(request('moderation_status') == 'pending') selected @endif>{{translate('Pending')}}</option>
                        <option value="approved" @if(request('moderation_status') == 'approved') selected @endif>{{translate('Approved')}}</option>
                        <option value="rejected" @if(request('moderation_status') == 'rejected') selected @endif>{{translate('Rejected')}}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="category_id" onchange="sort_products()">
                        <option value="">{{translate('All Categories')}}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @if(request('category_id') == $category->id) selected @endif>
                                {{ $category->getTranslation('name') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <select class="form-control form-control-sm aiz-selectpicker" name="customer_id" onchange="sort_products()">
                        <option value="">{{translate('All Customers')}}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->user_id }}" @if(request('customer_id') == $customer->user_id) selected @endif>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="{{translate('Search products...')}}"
                           value="{{ request('search') }}" onchange="sort_products()">
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
                                        <input type="hidden" name="export_type" value="PDF">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{translate('Product')}}</th>
                        <th>{{translate('Customer')}}</th>
                        <th>{{translate('Category')}}</th>
                        <th>{{translate('Price')}}</th>
                        <th>{{translate('Location')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Created')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{ $product->id }}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($product->main_photo)
                                    <img src="{{ uploaded_asset($product->main_photo) }}"
                                         alt="{{ $product->name }}"
                                         class="size-50px img-fit rounded mr-3">
                                @else
                                    <div class="size-50px bg-light rounded mr-3 d-flex align-items-center justify-content-center">
                                        <i class="las la-image text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $product->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $product->user->email }}</small>
                            </div>
                        </td>
                        <td>{{ $product->category->getTranslation('name') }}</td>
                        <td>{{ single_price($product->price) }}</td>
                        <td>
                            <small>
                                {{ $product->city->name ?? '' }}{{ $product->city && $product->state ? ', ' : '' }}{{ $product->state->name ?? '' }}
                            </small>
                        </td>
                        <td>
                            @if($product->moderation_status == 'pending')
                                <span class="badge badge-inline badge-warning">{{translate('Pending')}}</span>
                            @elseif($product->moderation_status == 'approved')
                                <span class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                            @else
                                <span class="badge badge-inline badge-danger">{{translate('Rejected')}}</span>
                            @endif
                        </td>
                        <td>{{ $product->created_at->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                               href="{{ route('admin.customer-products.show', $product->id) }}"
                               title="{{translate('View')}}">
                                <i class="las la-eye"></i>
                            </a>

                            @if($product->moderation_status == 'pending')
                                <a class="btn btn-soft-success btn-icon btn-circle btn-sm"
                                   href="javascript:void(0)"
                                   onclick="moderate_product({{ $product->id }}, 'approve')"
                                   title="{{translate('Approve')}}">
                                    <i class="las la-check"></i>
                                </a>
                                <a class="btn btn-soft-danger btn-icon btn-circle btn-sm"
                                   href="javascript:void(0)"
                                   onclick="show_reject_modal({{ $product->id }})"
                                   title="{{translate('Reject')}}">
                                    <i class="las la-times"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="aiz-pagination">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulk-reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Bulk Reject Products')}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="bulk-reject-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Rejection Reason')}}</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required
                                  placeholder="{{translate('Please provide a reason for rejection...')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{translate('Reject Selected')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single Reject Modal -->
<div class="modal fade" id="reject-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title h6">{{translate('Reject Product')}}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reject-form">
                <input type="hidden" name="product_id" id="reject-product-id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{translate('Rejection Reason')}}</label>
                        <textarea class="form-control" name="rejection_reason" rows="3" required
                                  placeholder="{{translate('Please provide a reason for rejection...')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">{{translate('Reject Product')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    function sort_products() {
        $('#sort_products').submit();
    }

    function moderate_product(productId, action) {
        $.post('{{ route("admin.customer-products.moderate", ":id") }}'.replace(':id', productId), {
            _token: '{{ csrf_token() }}',
            action: action
        }, function(data) {
            if (data.success) {
                AIZ.plugins.notify('success', data.message);
                location.reload();
            } else {
                AIZ.plugins.notify('danger', data.message);
            }
        });
    }

    function show_reject_modal(productId) {
        $('#reject-product-id').val(productId);
        $('#reject-modal').modal('show');
    }

    function bulk_moderate(action) {
        var selectedIds = [];
        $('.check-one:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            AIZ.plugins.notify('warning', '{{ translate("Please select at least one product") }}');
            return;
        }

        if (action === 'approve') {
            $.post('{{ route("admin.customer-products.bulk-moderate") }}', {
                _token: '{{ csrf_token() }}',
                id: selectedIds,
                action: 'approve'
            }, function(data) {
                if (data.success) {
                    AIZ.plugins.notify('success', data.message);
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', data.message);
                }
            });
        } else {
            $('#bulk-reject-modal').modal('show');
        }
    }

    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        var productId = $('#reject-product-id').val();
        var reason = $(this).find('[name="rejection_reason"]').val();

        $.post('{{ route("admin.customer-products.moderate", ":id") }}'.replace(':id', productId), {
            _token: '{{ csrf_token() }}',
            action: 'reject',
            rejection_reason: reason
        }, function(data) {
            if (data.success) {
                AIZ.plugins.notify('success', data.message);
                $('#reject-modal').modal('hide');
                location.reload();
            } else {
                AIZ.plugins.notify('danger', data.message);
            }
        });
    });

    $('#bulk-reject-form').on('submit', function(e) {
        e.preventDefault();
        var selectedIds = [];
        $('.check-one:checked').each(function() {
            selectedIds.push($(this).val());
        });

        var reason = $(this).find('[name="rejection_reason"]').val();

        $.post('{{ route("admin.customer-products.bulk-moderate") }}', {
            _token: '{{ csrf_token() }}',
            id: selectedIds,
            action: 'reject',
            rejection_reason: reason
        }, function(data) {
            if (data.success) {
                AIZ.plugins.notify('success', data.message);
                $('#bulk-reject-modal').modal('hide');
                location.reload();
            } else {
                AIZ.plugins.notify('danger', data.message);
            }
        });
    });

     $(document).on("change", ".check-all", function() {
        if (this.checked) {
            // Iterate each checkbox
            $('.check-one:checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('.check-one:checkbox').each(function() {
                this.checked = false;
            });
        }

    });

    function bulk_export (type){
        var url = '{{route('admin.customer-products.bulk-export')}}';
        $("#sort_products").attr("action", url);
        $('input[name="export_type"]').val(type);
        $('#sort_products').submit();
        $("#sort_products").attr("action", '');
    }
</script>
@endsection
