@extends('backend.layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0 h6">{{translate('PDF Settings')}}</h1>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ route('business_settings.update') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Header Image')}}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-upload-dir="pdf-images">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="pdf_header_image">
                                    <input type="hidden" name="pdf_header_image" value="{{ get_setting('pdf_header_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                    @php
                                        $headerImageSetting = get_setting('pdf_header_image');
                                        $headerImageUrl = null;
                                        if (!empty($headerImageSetting)) {
                                            $headerImageUrl = uploaded_asset($headerImageSetting);
                                        }
                                    @endphp
                                    @if($headerImageUrl)
                                        <img src="{{ $headerImageUrl }}" alt="Header Image" style="max-width: 200px; max-height: 100px;" onerror="this.style.display='none'; console.error('Header image failed to load: {{ $headerImageUrl }}');">
                                    @elseif(!empty($headerImageSetting))
                                        <div class="alert alert-warning">
                                            <small>Image path found but file not accessible: {{ $headerImageSetting }}</small>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <small>No header image uploaded</small>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">{{ translate('Image that will appear in the header of all PDF reports') }}</small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Footer Image')}}</label>
                            <div class="col-sm-9">
                                <div class="input-group" data-toggle="aizuploader" data-type="image" data-upload-dir="pdf-images">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div>
                                    </div>
                                    <div class="form-control file-amount">{{ translate('Choose Files') }}</div>
                                    <input type="hidden" name="types[]" value="pdf_footer_image">
                                    <input type="hidden" name="pdf_footer_image" value="{{ get_setting('pdf_footer_image') }}" class="selected-files">
                                </div>
                                <div class="file-preview box sm">
                                    @php
                                        $footerImageSetting = get_setting('pdf_footer_image');
                                        $footerImageUrl = null;
                                        if (!empty($footerImageSetting)) {
                                            $footerImageUrl = uploaded_asset($footerImageSetting);
                                        }
                                    @endphp
                                    @if($footerImageUrl)
                                        <img src="{{ $footerImageUrl }}" alt="Footer Image" style="max-width: 200px; max-height: 100px;" onerror="this.style.display='none'; console.error('Footer image failed to load: {{ $footerImageUrl }}');">
                                    @elseif(!empty($footerImageSetting))
                                        <div class="alert alert-warning">
                                            <small>Image path found but file not accessible: {{ $footerImageSetting }}</small>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <small>No footer image uploaded</small>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">{{ translate('Image that will appear in the footer of all PDF reports') }}</small>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-3 col-from-label">{{translate('Disclaimer')}}</label>
                            <div class="col-sm-9">
                                <input type="hidden" name="types[]" value="pdf_disclaimer">
                                <textarea name="pdf_disclaimer" class="form-control" rows="4" placeholder="{{ translate('Enter disclaimer text...') }}">{{ get_setting('pdf_disclaimer') }}</textarea>
                                <small class="text-muted">{{ translate('Disclaimer text that will appear at the end of all PDF reports') }}</small>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
