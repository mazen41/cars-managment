@extends('backend.layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-12 text-center">
                <x-health-logo/>
                <h2 class="fw-bold mb-3">System Health Status</h2>
                @if ($lastRanAt)
                    <div class="small mt-2 {{ $lastRanAt->diffInMinutes() > 5 ? 'text-danger' : 'text-muted' }}">
                        {{ __('health::notifications.check_results_from') }} {{ $lastRanAt->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="my-4">
            @if (count($checkResults?->storedCheckResults ?? []))
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @foreach ($checkResults->storedCheckResults as $result)
                        <div class="col mb-3">
                            <div class="card h-100 shadow-sm "
                                 @if ($result->status === 'ok')
                                     style="border-color: #28a745;"
                                 @elseif ($result->status === 'warning')
                                     style="border-color: #ffc107;"
                                 @elseif ($result->status === 'skipped')
                                     style="border-color: #007bff;"
                                 @elseif ($result->status === 'failed' || $result->status === 'crashed')
                                     style="border-color: #dc3545;"
                                 @else
                                     style="border-color: #6c757d;"
                                 @endif>
                                <div class="card-body d-flex align-items-start">
                                    <div class="ml-3">
                                        <x-health-status-indicator :result="$result" />
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-bold">
                                            {{ $result->label }}
                                        </h5>
                                        <p class="card-text text-muted text-secondary">
                                            @if (!empty($result->notificationMessage))
                                                {{ $result->notificationMessage }}
                                            @else
                                                {{ $result->shortSummary }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
