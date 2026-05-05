@extends('backend.layouts.app')

@section('content')

<div class="col-lg-12">
    <div class="card">
        <div class="card-header">
            <h3 class="mb-0 h6">{{translate('Manual Payment Information')}}</h3>
        </div>

        <form action="{{ route('manual_payment_methods.update', $manual_payment_method->id) }}" method="POST">
          <input name="_method" type="hidden" value="PATCH">
          @csrf
            <div class="card-body">

                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="name" value="{{ $manual_payment_method->name }}" placeholder="" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label" for="signinSrEmail">{{translate('Checkout Thumbnail')}} (438x235)px</label>
                    <div class="col-md-8">
                        <div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="false">
                            <div class="input-group-prepend">
                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                            </div>
                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                            <input type="hidden" name="photo" value="{{ $manual_payment_method->photo }}" class="selected-files">
                        </div>
                        <div class="file-preview box sm">
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-from-label" for="name">{{translate('Details')}}</label>
                    <span class="text-info">{{translate('Use | symbole to enter new line')}}</span>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="details" value="{{ $manual_payment_method->details }}" placeholder="" required>
                    </div>
                </div>

            </div>
            <div class="form-group mb-3 text-right">
                <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
            </div>
        </form>
    </div>
</div>

@endsection
