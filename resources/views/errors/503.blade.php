@extends('frontend.layouts.error')

@section('content')
<section class="align-items-center d-flex h-100 bg-surface">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mx-auto text-center py-4">
				<dotlottie-player src="{{static_asset('assets/lottie/maintenance.lottie')}}" background="transparent" speed="1" style="width: 400px; height: 400px" direction="1" playMode="normal" loop autoplay></dotlottie-player>
			    <h3 class="fw-600 mt-5">{{translate('We are Under Maintenance.')}}</h3>
			    <div class="lead">{{translate('We will be back soon!')}}</div>
			</div>
		</div>
	</div>
</section>
@endsection
@section('script')
<script src="{{static_asset('assets/js/dotlottie-player.js')}}"></script>
@endsection
