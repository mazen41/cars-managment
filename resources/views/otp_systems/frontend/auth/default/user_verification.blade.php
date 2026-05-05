@extends('auth.layouts.authentication')

@section('content')
    <!-- aiz-main-wrapper -->
    <div class="aiz-main-wrapper d-flex flex-column justify-content-md-center bg-surface">
        <section class="bg-surface overflow-hidden">
            <div class="row">
                <div class="col-xxl-6 col-xl-9 col-lg-10 col-md-7 mx-auto py-lg-4">
                    <div class="card shadow-none rounded-0 border-0">
                        <div class="row no-gutters">
                            <!-- Left Side Image-->
                            <div class="col-lg-6">
                                <img src="{{ uploaded_asset(get_setting('phone_number_verify_page_image')) }}" alt="{{ translate('Phone Number Verify Page Image') }}" class="img-fit h-100">
                            </div>

                            <!-- Right Side -->
                            <div class="col-lg-6 p-4 p-lg-5 border right-content">
                                <!-- Site Icon -->
                                <div class="size-48px mb-3 mx-auto mx-lg-0">
                                    <img src="{{ uploaded_asset(get_setting('site_icon')) }}" alt="{{ translate('Site Icon')}}" class="img-fit h-100">
                                </div>

                                <!-- Titles -->
                                <div class="text-center text-lg-left">
                                    <h1 class="fs-20 fs-md-24 fw-700 text-primary" style="text-transform: uppercase;">{{ translate('Phone Verification')}}</h1>
                                    <h5 class="fs-14 fw-400 ">{{ translate('Verification code has been sent. Please wait a few minutes.')}}</h5>
                                </div>

                                <!-- Login form -->
                                <div class="pt-3">
                                    <div class="text-center">

                                        <form class="form-default" role="form" action="{{ route('verification.submit') }}" method="POST">
                                            @csrf

                                            <!-- Verification Code -->
                                            <div class="form-group">
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" name="verification_code">
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="mb-4 mt-4">
                                                <button type="submit" class="btn btn-primary btn-block fw-700 fs-14 rounded-0">{{  translate('Verify') }}</button>
                                            </div>
                                        </form>
                                        <button id="resend" class="btn btn-primary rounded-2 mt-2">{{translate('Resend Code')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Go Back -->
                        <div class="mt-3 mr-4 mr-md-0">
                            <a href="{{ url()->previous() }}" class="ml-auto fs-14 fw-700 d-flex align-items-center text-primary" style="max-width: fit-content;">
                                <i class="las la-arrow-left fs-20 mr-1"></i>
                                {{ translate('Back to Previous Page')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('script')
<script>
   $(document).ready(function() {
    var timeoutSeconds = 120;

    $('#resend').click(function() {
        var $button = $(this);
        var originalText = $button.text();
        $button.prop('disabled', true);
        $button.text("{{translate('Please wait')}}");

        $.ajax({
            url: "{{ route('verification.phone.resend-ajax') }}",
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    AIZ.plugins.notify('success', response.message);

                    var counter = timeoutSeconds;
                    $button.text("{{translate('Wait')}}" + " " + counter + " " + "{{translate('seconds')}}");

                    var interval = setInterval(function() {
                        counter--;
                        $button.text("{{translate('Wait')}}" + " " + counter + " " + "{{translate('seconds')}}");

                        if (counter <= 0) {
                            clearInterval(interval);
                            $button.text(originalText);
                            $button.prop('disabled', false);
                        }
                    }, 1000);
                } else {
                    AIZ.plugins.notify('danger', response.message);
                    $button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                var errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                AIZ.plugins.notify('danger', errorMessage);
                $button.text(originalText);
                $button.prop('disabled', false);
            }
        });
    });
});

</script>
@endsection
