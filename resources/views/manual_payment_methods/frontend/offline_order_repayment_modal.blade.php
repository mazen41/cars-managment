<form class="" action="{{ route('purchase_history.make_payment') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal-body gry-bg px-3 pt-3 mx-auto c-scrollbar-light">
        <input type="hidden" name="order_id" value="{{$order_id}}">
        <div class="align-items-center gutters-5 row">
            <!-- Manual Payment Methods -->
            @foreach(get_all_manual_payment_methods() as $method)
              <div class="col-6 col-md-4">
                <label class="aiz-megabox d-block mb-3">
                    <input value="manual_payment" type="radio" name="payment_option" onchange="toggleManualPaymentData({{ $method->id }})" data-id="{{ $method->id }}" checked>
                    <span class="d-block p-3 aiz-megabox-elem">
                        <img src="{{ uploaded_asset($method->photo) }}" class="img-fluid mb-2">
                        <span class="d-block text-center">
                            <span class="d-block fw-600 fs-15">{{ $method->heading }}</span>
                        </span>
                    </span>
                </label>
              </div>
            @endforeach
        </div>

        <div id="manual_payment_data">
            <!-- Payment description -->
            <div class="card rounded-0 shadow-none border mb-3 p-3 d-none">
                <input id="manual_payment_id" type="hidden" name="manual_payment_id">
                <div id="manual_payment_description">

                </div>
            </div>

            <div class="card rounded-0 shadow-none border mb-3 p-3">
                 <!-- Sender name -->
                 <div class="row mt-3">
                    <div class="col-md-3">
                        <label>{{ translate('Name')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="form-control mb-3 rounded-0" name="name" placeholder="{{ translate('Name') }}" required>
                    </div>
                </div>
                <!-- Amount -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label>{{ translate('Amount')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="number" lang="en" class="form-control mb-3 rounded-0" min="0" step="0.01" name="amount" placeholder="{{ translate('Amount') }}" required>
                    </div>
                </div>
                <!-- Transaction ID -->
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ translate('Transaction ID')}} <span class="text-danger">*</span></label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="form-control mb-3 rounded-0" name="trx_id" placeholder="{{ translate('Transaction ID') }}" required>
                    </div>
                </div>
                <!-- Payment screenshot -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">{{ translate('Photo') }}</label>
                    <div class="col-md-9">
                        <div class="input-group" data-toggle="aizuploader" data-type="image">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium rounded-0">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose image') }}</div>
                            <input type="hidden" name="photo" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Confirm Button -->
            <div class="form-group text-right">
                <button type="submit" class="btn btn-sm btn-primary rounded-0 w-150px transition-3d-hover">{{translate('Confirm')}}</button>
            </div>
        </div>
    </div>
</form>

@foreach(get_all_manual_payment_methods() as $method)
  <div id="manual_payment_info_{{ $method->id }}" class="d-none">
    <div>{!!str_replace("|", "<br>", $method->details)!!}</div>
  </div>
@endforeach

<script type="text/javascript">
    $(document).ready(function(){
        toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));
    });

    function toggleManualPaymentData(id){
        $('#manual_payment_description').parent().removeClass('d-none');
        $('#manual_payment_description').html($('#manual_payment_info_'+id).html());
        $('#manual_payment_id').val(id)
    }
</script>
