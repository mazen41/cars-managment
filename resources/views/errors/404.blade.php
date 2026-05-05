@extends('frontend.layouts.error')

@section('content')
<section class="text-center py-6">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mx-auto">
				<div class="container">
                    <dotlottie-player src="{{static_asset('assets/lottie/404-2.lottie')}}" background="transparent" speed="1" style="width: 500px; height: 300px" direction="1" playMode="normal" loop autoplay></dotlottie-player>
                </div>
			    <h1 class="fw-700">{{ translate('Page Not Found!') }}</h1>
			    <p class="fs-16 opacity-60">{{ translate('The page you are looking for has not been found on our server.') }}</p>
			</div>
		</div>
    </div>
</section>
@endsection
