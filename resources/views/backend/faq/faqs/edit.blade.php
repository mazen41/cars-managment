@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Edit FAQ')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('faqs.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to FAQs')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill language-bar">
                    @foreach (get_all_active_language() as $key => $language)
                    <li class="nav-item">
                        <a class="nav-link text-reset @if ($language->code == $lang) active @endif py-3" href="{{ route('faqs.edit', ['faq'=>$faq->id, 'lang'=> $language->code] ) }}">
                            <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="11" class="mr-1">
                            <span>{{$language->name}}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <form class="p-4" action="{{ route('faqs.update', $faq->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">

                    @if($lang == config('app.locale'))
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Type')}} <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="type" data-live-search="true" required>
                                <option value="">{{ translate('Select Type') }}</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ $faq->type == $type ? 'selected' : '' }}>
                                        {{ translate($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Question')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" name="question" value="{{ $faq->getTranslation('question', $lang) }}" class="form-control" placeholder="{{translate('Enter the question')}}" required>
                            @error('question')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Answer')}} <i class="las la-language text-danger" title="{{translate('Translatable')}}"></i> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <textarea class="form-control" name="answer" placeholder="{{translate('Enter the answer')}}" required>{{ $faq->getTranslation('answer', $lang) }}</textarea>
                            @error('answer')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    @if($lang == config('app.locale'))
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Slug')}}</label>
                        <div class="col-md-9">
                            <input type="text" name="slug" value="{{ $faq->slug }}" class="form-control" placeholder="{{translate('FAQ Slug')}}">
                            <small class="form-text text-muted">{{translate('Leave empty to auto-generate from question')}}</small>
                            @error('slug')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Sort Order')}}</label>
                        <div class="col-md-9">
                            <input type="number" name="sort_order" value="{{ $faq->sort_order }}" class="form-control" min="0" placeholder="{{translate('Sort Order')}}">
                            <small class="form-text text-muted">{{translate('Higher number has higher priority')}}</small>
                            @error('sort_order')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Publication Status')}}</label>
                        <div class="col-md-9">
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input type="checkbox" name="is_published" value="1" {{ $faq->is_published ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <small class="form-text text-muted">{{translate('Publish this FAQ')}}</small>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('View Count')}}</label>
                        <div class="col-md-9">
                            <input type="number" name="view_count" value="{{ $faq->view_count }}" class="form-control" min="0" readonly>
                            <small class="form-text text-muted">{{translate('Number of times this FAQ has been viewed')}}</small>
                        </div>
                    </div>
                    @endif

                    <div class="form-group mb-0 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{translate('Update FAQ')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
