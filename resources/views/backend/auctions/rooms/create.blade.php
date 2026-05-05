@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h1 class="h3">{{ translate('Create Auction Room') }}</h1>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Room Information') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.auction-rooms.store') }}" method="POST" id="auction-room-form">
                    @csrf

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Room Name') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Description') }}</label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Currency') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="currency_id" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" @if(old('currency_id') == $currency->id) selected @endif>
                                        {{ $currency->name }} ({{ $currency->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div> --}}

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Commission Percentage') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="number" class="form-control" name="commission_percentage" value="{{ old('commission_percentage', 10) }}" min="0" max="100" step="0.01" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            @error('commission_percentage')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Bid Increment Type') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="bid_increment_type" id="bid_increment_type" required>
                                <option value="percentage" @if(old('bid_increment_type') == 'percentage') selected @endif>{{ translate('Percentage') }}</option>
                                <option value="flat" @if(old('bid_increment_type') == 'flat') selected @endif>{{ translate('Flat Amount') }}</option>
                            </select>
                            @error('bid_increment_type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Bid Increment Value') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="bid_increment_value" value="{{ old('bid_increment_value', 5) }}" min="0" step="0.01" required>
                            <small class="text-muted" id="increment-hint">{{ translate('Enter percentage (e.g., 5 for 5%)') }}</small>
                            @error('bid_increment_value')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Base Timer (seconds)') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="base_timer_seconds" value="{{ old('base_timer_seconds', 60) }}" min="10" required>
                            <small class="text-muted">{{ translate('Initial countdown time for each item') }}</small>
                            @error('base_timer_seconds')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Extension Time (seconds)') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="extension_seconds" value="{{ old('extension_seconds', 30) }}" min="5" required>
                            <small class="text-muted">{{ translate('Time added when a bid is placed') }}</small>
                            @error('extension_seconds')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Insurance Deposit Amount') }} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="insurance_deposit_amount" value="{{ old('insurance_deposit_amount', 1000) }}" min="0" step="0.01" required>
                            <small class="text-muted">{{ translate('Required deposit for bidders to participate') }}</small>
                            @error('insurance_deposit_amount')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div> --}}

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Scheduled Start Time') }}</label>
                        <div class="col-md-9">
                            <input type="datetime-local" class="form-control" name="scheduled_start_at" value="{{ old('scheduled_start_at') }}">
                            <small class="text-muted">{{ translate('Leave empty to start manually') }}</small>
                            @error('scheduled_start_at')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-0 text-right">
                        <a href="{{ route('admin.auction-rooms.index') }}" class="btn btn-light">{{ translate('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ translate('Create Room') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Update increment hint based on type
            $('#bid_increment_type').on('change', function() {
                var type = $(this).val();
                if (type === 'percentage') {
                    $('#increment-hint').text('{{ translate('Enter percentage (e.g., 5 for 5%)') }}');
                } else {
                    $('#increment-hint').text('{{ translate('Enter flat amount (e.g., 100)') }}');
                }
            });

            // Trigger on page load
            $('#bid_increment_type').trigger('change');
        });
    </script>
@endsection
