@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('FAQ Details')}}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('faqs.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to FAQs')}}</span>
            </a>
            @can('edit_faq')
                <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-circle btn-primary">
                    <span>{{translate('Edit FAQ')}}</span>
                </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('FAQ Content')}}</h5>
                <div class="pull-right">
                    <span class="badge badge-inline badge-{{ $faq->is_published ? 'success' : 'secondary' }}">
                        {{ $faq->is_published ? translate('Published') : translate('Draft') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4 class="text-primary">{{ $faq->getTranslation('question') }}</h4>
                    <small class="text-muted">
                        {{translate('Type')}}:
                        <span class="badge badge-inline badge-info">{{ translate($faq->type) }}</span>
                    </small>
                </div>

                <div class="mb-4">
                    <h6 class="text-secondary">{{translate('Answer')}}</h6>
                    <div class="aiz-text-editor-content">
                        {!! $faq->getTranslation('answer') !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>{{translate('Slug')}}:</strong> {{ $faq->slug }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>{{translate('Sort Order')}}:</strong> {{ $faq->sort_order }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Available Translations')}}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(get_all_active_language() as $language)
                    @php
                        $translation = $faq->translations->where('locale', $language->code)->first();
                    @endphp
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" height="16" class="mr-2">
                                <strong>{{ $language->name }}</strong>
                                @if($translation && $translation->question && $translation->answer)
                                    <span class="badge badge-inline badge-success ml-auto">{{translate('Complete')}}</span>
                                @elseif($translation)
                                    <span class="badge badge-inline badge-warning ml-auto">{{translate('Partial')}}</span>
                                @else
                                    <span class="badge badge-inline badge-danger ml-auto">{{translate('Missing')}}</span>
                                @endif
                            </div>
                            @if($translation)
                                <div class="mb-2">
                                    <small class="text-muted">{{translate('Question')}}:</small>
                                    <p class="mb-1">{{ $translation->question ?: translate('Not translated') }}</p>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">{{translate('Answer')}}:</small>
                                    <p class="mb-1">{{ $translation->answer ? Str::limit(strip_tags($translation->answer), 100) : translate('Not translated') }}</p>
                                </div>
                            @else
                                <p class="text-muted mb-2">{{translate('No translation available')}}</p>
                            @endif
                            <a href="{{ route('faqs.edit', ['faq'=>$faq->id, 'lang'=> $language->code]) }}" class="btn btn-soft-primary btn-sm">
                                {{translate('Edit Translation')}}
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Quick Actions')}}</h5>
            </div>
            <div class="card-body">
                @can('edit_faq')
                    <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-primary btn-block mb-2">
                        <i class="las la-edit"></i> {{translate('Edit FAQ')}}
                    </a>
                    <a href="{{ route('faqs.duplicate', $faq->id) }}" class="btn btn-info btn-block mb-2">
                        <i class="las la-copy"></i> {{translate('Duplicate FAQ')}}
                    </a>
                @endcan

                @can('publish_faq')
                    <form action="{{ route('faqs.toggle-status') }}" method="POST" class="d-inline-block w-100">
                        @csrf
                        <input type="hidden" name="id" value="{{ $faq->id }}">
                        <input type="hidden" name="status" value="{{ $faq->is_published ? 0 : 1 }}">
                        <button type="submit" class="btn btn-{{ $faq->is_published ? 'warning' : 'success' }} btn-block mb-2">
                            <i class="las la-{{ $faq->is_published ? 'eye-slash' : 'eye' }}"></i>
                            {{ $faq->is_published ? translate('Unpublish') : translate('Publish') }}
                        </button>
                    </form>
                @endcan

                @can('delete_faq')
                    <a href="#" class="btn btn-danger btn-block confirm-delete" data-href="{{route('faqs.destroy', $faq->id)}}">
                        <i class="las la-trash"></i> {{translate('Delete FAQ')}}
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@include('modals.delete_modal')
@endsection
