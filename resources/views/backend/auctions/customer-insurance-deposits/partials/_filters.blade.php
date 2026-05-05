<div class="card-header row gutters-5 border-top">
    <div class="col-12">
        <h6 class="mb-3">{{ translate('Filters') }}</h6>
    </div>

    <!-- Status Filter -->
    <div class="col-lg-2 col-md-3 col-sm-6">
        <div class="form-group mb-0">
            <label class="form-label text-muted small">{{ translate('Status') }}</label>
            <select class="form-control form-control-sm aiz-selectpicker" name="status" onchange="filterDeposits()">
                <option value="">{{ translate('All Status') }}</option>
                <option value="pending" @if(request('status') == 'pending') selected @endif>
                    {{ translate('Pending') }}
                </option>
                <option value="paid" @if(request('status') == 'paid') selected @endif>
                    {{ translate('Paid') }}
                </option>
                <option value="refunded" @if(request('status') == 'refunded') selected @endif>
                    {{ translate('Refunded') }}
                </option>
            </select>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="col-lg-3 col-md-4 col-sm-6">
        <div class="form-group mb-0">
            <label class="form-label text-muted small">{{ translate('Search') }}</label>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="search"
                   placeholder="{{ translate('Customer name or email...') }}"
                   value="{{ request('search') }}">
        </div>
    </div>

    <!-- Date From Filter -->
    <div class="col-lg-2 col-md-3 col-sm-6">
        <div class="form-group mb-0">
            <label class="form-label text-muted small">{{ translate('Date From') }}</label>
            <input type="date" 
                   class="form-control form-control-sm aiz-date-range" 
                   name="date_from"
                   value="{{ request('date_from') }}"
                   onchange="filterDeposits()">
        </div>
    </div>

    <!-- Date To Filter -->
    <div class="col-lg-2 col-md-3 col-sm-6">
        <div class="form-group mb-0">
            <label class="form-label text-muted small">{{ translate('Date To') }}</label>
            <input type="date" 
                   class="form-control form-control-sm aiz-date-range" 
                   name="date_to"
                   value="{{ request('date_to') }}"
                   onchange="filterDeposits()">
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="form-group mb-0">
            <label class="form-label text-muted small d-block">&nbsp;</label>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="las la-filter"></i>
                {{ translate('Apply Filters') }}
            </button>
            <a href="{{ route('insurance-deposits.index') }}" class="btn btn-sm btn-light">
                <i class="las la-times"></i>
                {{ translate('Clear') }}
            </a>
        </div>
    </div>
</div>

<style>
.form-label {
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.gutters-5 > [class*="col-"] {
    padding-right: 0.5rem;
    padding-left: 0.5rem;
}

@media (max-width: 767px) {
    .gutters-5 > [class*="col-"] {
        margin-bottom: 1rem;
    }
}
</style>
