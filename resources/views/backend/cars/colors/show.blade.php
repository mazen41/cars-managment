@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('Car Color Details')}}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('admin.car-colors.index') }}" class="btn btn-circle btn-info">
                <span>{{translate('Back to Colors')}}</span>
            </a>
            <a href="{{ route('admin.car-colors.edit', $carColor->id) }}" class="btn btn-circle btn-warning">
                <span>{{translate('Edit Color')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Color Information')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted font-weight-bold">{{translate('Color Name')}}:</td>
                        <td>{{ $carColor->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">{{translate('Hex Code')}}:</td>
                        <td>
                            @if($carColor->hex_code)
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="width: 25px; height: 25px; background-color: {{ $carColor->hex_code }}; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px;"></div>
                                    <span class="badge badge-inline badge-light">{{ $carColor->hex_code }}</span>
                                </div>
                            @else
                                <span class="text-muted">{{translate('No hex code specified')}}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">{{translate('Status')}}:</td>
                        <td>
                            @if($carColor->status == 'active')
                                <span class="badge badge-inline badge-success">{{translate('Active')}}</span>
                            @else
                                <span class="badge badge-inline badge-secondary">{{translate('Inactive')}}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">{{translate('Created At')}}:</td>
                        <td>{{ $carColor->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">{{translate('Updated At')}}:</td>
                        <td>{{ $carColor->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Cars using this color -->
        @if($carColor->cars->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Cars Using This Color')}} ({{ $carColor->cars->count() }})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{translate('Car Title')}}</th>
                                <th>{{translate('Brand')}}</th>
                                <th>{{translate('Model')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th>{{translate('Created')}}</th>
                                <th class="text-right">{{translate('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carColor->cars->take(10) as $car) {{-- Show only first 10 cars --}}
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($car->thumbnail)
                                            <img src="{{ uploaded_asset($car->thumbnail) }}" alt="Car" class="size-40px img-fit me-2" style="margin-right: 10px;">
                                        @endif
                                        <span>{{ Str::limit($car->name ?? 'N/A', 30) }}</span>
                                    </div>
                                </td>
                                <td>{{ $car->brand->name ?? 'N/A' }}</td>
                                <td>{{ $car->model->name ?? 'N/A' }}</td>
                                <td>
                                    @if($car->status == 'published')
                                        <span class="badge badge-inline badge-success">{{translate('Published')}}</span>
                                    @elseif($car->status == 'draft')
                                        <span class="badge badge-inline badge-warning">{{translate('Draft')}}</span>
                                    @else
                                        <span class="badge badge-inline badge-secondary">{{ ucfirst($car->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $car->created_at->format('d M Y') }}</td>
                                <td class="text-right">
                                    @if(Route::has('admin.cars.show'))
                                        <a href="{{ route('admin.cars.show', $car->id) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="{{translate('View Car')}}">
                                            <i class="las la-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($carColor->cars->count() > 10)
                    <div class="text-center mt-3">
                        <small class="text-muted">{{translate('Showing first 10 cars. Total cars using this color: ')}} {{ $carColor->cars->count() }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Statistics')}}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h3 class="text-primary mb-0">{{ $carColor->cars->count() }}</h3>
                            <small class="text-muted">{{translate('Total Cars')}}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h3 class="text-success mb-0">{{ $carColor->cars->where('status', 'published')->count() }}</h3>
                        <small class="text-muted">{{translate('Published Cars')}}</small>
                    </div>
                </div>

                @if($carColor->hex_code)
                <div class="mt-4">
                    <h6 class="mb-2">{{translate('Color Preview')}}</h6>
                    <div class="color-preview-large" style="width: 100%; height: 100px; background-color: {{ $carColor->hex_code }}; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                    <div class="text-center mt-2">
                        <small class="text-muted">{{ $carColor->hex_code }}</small>
                    </div>
                </div>
                @endif

                <hr>

                <!-- Quick Actions -->
                <div class="mt-3">
                    <h6 class="mb-2">{{translate('Quick Actions')}}</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.car-colors.edit', $carColor->id) }}" class="btn btn-sm btn-warning">
                            <i class="las la-edit"></i> {{translate('Edit Color')}}
                        </a>

                        @if($carColor->canBeDeleted())
                            <a href="#" class="btn btn-sm btn-danger confirm-delete" data-href="{{ route('admin.car-colors.destroy', $carColor->id) }}">
                                <i class="las la-trash"></i> {{translate('Delete Color')}}
                            </a>
                        @else
                            <button class="btn btn-sm btn-danger" disabled title="{{translate('Cannot delete color that is assigned to cars')}}">
                                <i class="las la-trash"></i> {{translate('Delete Color')}}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection
