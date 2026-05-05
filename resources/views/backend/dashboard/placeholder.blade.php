@extends('backend.layouts.app')

@section('title', translate('Dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">{{ translate('Welcome back') }} {{ auth()->user()->name }}</h2>
                            <p class="mb-0 opacity-75">
                                {{ translate('Today is') }} {{  now()->format('l, F j, Y')}}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <div class="me-3">
                                    <i class="fas fa-user-circle fa-3x opacity-75"></i>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-tag fa-2x text-primary"></i>
                    </div>
                    <h6 class="card-title">{{ translate('User ID') }}</h6>
                    <p class="card-text text-muted">#{{ auth()->user()->id }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-envelope fa-2x text-info"></i>
                    </div>
                    <h6 class="card-title">{{ translate('Email') }}</h6>
                    <p class="card-text text-muted small">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-calendar-alt fa-2x text-success"></i>
                    </div>
                    <h6 class="card-title">{{ translate('Member Since') }}</h6>
                    <p class="card-text text-muted">{{ auth()->user()->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <h6 class="card-title">{{ translate('Role') }}</h6>
                    <p class="card-text text-muted">
                       {{ auth()->user()->getRoleNames()->first() ?? translate('Staff') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
