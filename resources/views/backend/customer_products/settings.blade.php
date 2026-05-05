@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Customer Products Settings')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.customer-products.index') }}" class="btn btn-light">
                <i class="las la-arrow-left"></i> {{translate('Back to Products')}}
            </a>
        </div>
    </div>
</div>

<form action="{{ route('admin.customer-products.update-settings') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Feature Configuration')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Enable Customer Products')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="customer_product_enabled" value="1" 
                                       @if(get_setting('customer_product_enabled')) checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">
                                {{translate('Allow customers to create and manage their own product listings')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Require Moderation')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="require_moderation" value="1" 
                                       @if(get_setting('require_moderation')) checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">
                                {{translate('All customer products must be approved by admin before being visible to public')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Auto-approve Trusted Customers')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="auto_approve_trusted_customers" value="1" 
                                       @if(get_setting('auto_approve_trusted_customers')) checked @endif>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">
                                {{translate('Automatically approve products from customers with good history')}}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Image Upload Settings')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Maximum Images per Product')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="max_images_per_product" 
                                   value="{{ get_setting('max_images_per_product') }}" 
                                   min="1" max="20" required>
                            <small class="form-text text-muted">
                                {{translate('Maximum number of additional images (excluding main photo)')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Maximum Image Size (MB)')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="max_image_size_mb" 
                                   value="{{ get_setting('max_image_size_mb') }}" 
                                   min="1" max="10" required>
                            <small class="form-text text-muted">
                                {{translate('Maximum file size for each image upload')}}
                            </small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Content Restrictions')}}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Minimum Product Name Length')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="min_name_length" 
                                   value="3" min="1" max="50" readonly>
                            <small class="form-text text-muted">
                                {{translate('Minimum characters required for product name')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Maximum Product Name Length')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="max_name_length" 
                                   value="255" min="50" max="500" readonly>
                            <small class="form-text text-muted">
                                {{translate('Maximum characters allowed for product name')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Minimum Description Length')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="min_description_length" 
                                   value="10" min="5" max="100" readonly>
                            <small class="form-text text-muted">
                                {{translate('Minimum characters required for product description')}}
                            </small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Maximum Description Length')}}</label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="max_description_length" 
                                   value="5000" min="1000" max="10000" readonly>
                            <small class="form-text text-muted">
                                {{translate('Maximum characters allowed for product description')}}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="las la-save"></i> {{translate('Save Settings')}}
                        </button>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <a href="{{ route('admin.customer-products.analytics') }}" class="btn btn-info btn-block">
                            <i class="las la-chart-bar"></i> {{translate('View Analytics')}}
                        </a>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('admin.customer-products.index', ['moderation_status' => 'pending']) }}" 
                           class="btn btn-warning btn-block">
                            <i class="las la-clock"></i> {{translate('Pending Products')}}
                        </a>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('admin.customer-products.index', ['moderation_status' => 'approved']) }}" 
                           class="btn btn-success btn-block">
                            <i class="las la-check-circle"></i> {{translate('Approved Products')}}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{translate('Feature Statistics')}}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-primary">0</h4>
                                <small class="text-muted">{{translate('Total Products')}}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning">0</h4>
                            <small class="text-muted">{{translate('Pending Review')}}</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-success">0</h4>
                                <small class="text-muted">{{translate('Approved')}}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger">0</h4>
                            <small class="text-muted">{{translate('Rejected')}}</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

@endsection

@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        // Load current statistics
        loadStatistics();
    });

    function loadStatistics() {
        // This would typically make an AJAX call to get current statistics
        // For now, we'll leave the placeholder values
    }
</script>
@endsection