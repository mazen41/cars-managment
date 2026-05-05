@extends('frontend.layouts.error')

@section('content')
<section class="text-center py-6">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mx-auto">
				<img src="{{ static_asset('assets/img/expired.svg') }}" class="img-fluid w-50">
				<h1 class="h2 fw-700 mt-5">{{ translate("Link Expired!") }}</h1>
		    	<p class="fs-16 opacity-60">{{ translate("The confirmation link is expired. Request a new one") }}</p>
			</div>
		</div>
	</div>
</section>
@endsection
