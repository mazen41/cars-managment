@extends('backend.layouts.app')

@section('content')
    <div class="page-content">
        <div class="aiz-titlebar text-left mt-2 pb-2 px-3 px-md-2rem border-bottom border-gray">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="h3">{{ translate('Mobile App Slideres Settings') }}</h1>
                </div>
            </div>
        </div>

        <div class="d-sm-flex">
            <!-- page side nav -->
            <div class="page-side-nav c-scrollbar-light px-3 py-2">
                <ul class="nav nav-tabs flex-sm-column border-0" role="tablist" aria-orientation="vertical">
                    <!-- Home Slider -->
                    <li class="nav-item">
                        <a class="nav-link" id="home-slider-tab" href="#home_slider" data-toggle="tab"
                            data-target="#home_slider" type="button" role="tab" aria-controls="home_slider"
                            aria-selected="true">
                            {{ translate('Home Slider') }}
                        </a>
                    </li>
                    <!-- Banner Level 1 -->
                    <li class="nav-item">
                        <a class="nav-link" id="banner-1-tab" href="#banner_1" data-toggle="tab" data-target="#banner_1"
                            type="button" role="tab" aria-controls="banner_1" aria-selected="false">
                            {{ translate('Banner Level 1') }}
                        </a>
                    </li>
                    <!-- Banner Level 2 -->
                    <li class="nav-item">
                        <a class="nav-link" id="banner-2-tab" href="#banner_2" data-toggle="tab" data-target="#banner_2"
                            type="button" role="tab" aria-controls="banner_2" aria-selected="false">
                            {{ translate('Banner Level 2') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- tab content -->
            <div class="flex-grow-1 p-sm-3 p-lg-2rem mb-2rem mb-md-0">
                <div class="tab-content">

                    <!-- Language Bar -->
                    <ul class="nav nav-tabs nav-fill border-light language-bar">
                        @foreach (get_all_active_language() as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link text-reset @if ($language->code == $lang) active @else bg-soft-dark border-light border-left-0 @endif py-3"
                                    href="{{ route('mobile-app.sliders', ['lang' => $language->code]) }}">
                                    <img src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}"
                                        height="11" class="mr-1">
                                    <span>{{ $language->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Home Slider -->
                    <div class="tab-pane fade" id="home_slider" role="tabpanel" aria-labelledby="home-slider-tab">
                        <form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab" value="home_slider">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_slider_images">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_slider_links">

                            <div class="bg-surface p-3 p-sm-2rem">
                                <div class="w-100">
                                    <!-- Information -->
                                    <div class="fs-11 d-flex mb-2rem">
                                        <div>
                                            <svg id="_79508b4b8c932dcad9066e2be4ca34f2"
                                                data-name="79508b4b8c932dcad9066e2be4ca34f2"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 16 16">
                                                <path id="Path_40683" data-name="Path 40683"
                                                    d="M8,16a8,8,0,1,1,8-8A8.024,8.024,0,0,1,8,16ZM8,1.333A6.667,6.667,0,1,0,14.667,8,6.686,6.686,0,0,0,8,1.333Z"
                                                    fill="#9da3ae" />
                                                <path id="Path_40684" data-name="Path 40684"
                                                    d="M10.6,15a.926.926,0,0,1-.667-.333c-.333-.467-.067-1.133.667-2.933.133-.267.267-.6.4-.867a.714.714,0,0,1-.933-.067.644.644,0,0,1,0-.933A3.408,3.408,0,0,1,11.929,9a.926.926,0,0,1,.667.333c.333.467.067,1.133-.667,2.933-.133.267-.267.6-.4.867a.714.714,0,0,1,.933.067.644.644,0,0,1,0,.933A3.408,3.408,0,0,1,10.6,15Z"
                                                    transform="translate(-3.262 -3)" fill="#9da3ae" />
                                                <circle id="Ellipse_813" data-name="Ellipse 813" cx="1"
                                                    cy="1" r="1" transform="translate(8 3.333)" fill="#9da3ae" />
                                                <path id="Path_40685" data-name="Path 40685"
                                                    d="M12.833,7.167a1.333,1.333,0,1,1,1.333-1.333A1.337,1.337,0,0,1,12.833,7.167Zm0-2a.63.63,0,0,0-.667.667.667.667,0,1,0,1.333,0A.63.63,0,0,0,12.833,5.167Z"
                                                    transform="translate(-3.833 -1.5)" fill="#9da3ae" />
                                            </svg>
                                        </div>
                                        <div class="ml-2 text-gray">
                                            <div>{{ translate('Aspect ratio should be 169\70') }}</div>
                                        </div>
                                    </div>
                                    <!-- Images & links -->
                                    <div class="home-slider-target">
                                        @php
                                            $app_home_slider_images = get_setting(
                                                'app_home_slider_images',
                                                null,
                                                $lang,
                                            );
                                            $app_home_slider_links = get_setting('app_home_slider_links', null, $lang);
                                        @endphp
                                        @if ($app_home_slider_images != null)
                                            @foreach (json_decode($app_home_slider_images, true) as $key => $value)
                                                <div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent"
                                                    style="border: 1px dashed #e4e5eb;">
                                                    <div class="row gutters-5">
                                                        <!-- Image -->
                                                        <div class="col-md-5">
                                                            <div class="form-group mb-md-0">
                                                                <div class="input-group" data-toggle="aizuploader"
                                                                    data-type="image">
                                                                    <div class="input-group-prepend">
                                                                        <div
                                                                            class="input-group-text bg-soft-secondary font-weight-medium">
                                                                            {{ translate('Browse') }}</div>
                                                                    </div>
                                                                    <div class="form-control file-amount">
                                                                        {{ translate('Choose
                                                                                                                                    File') }}
                                                                    </div>
                                                                    <input type="hidden" name="app_home_slider_images[]"
                                                                        class="selected-files"
                                                                        value="{{ json_decode($app_home_slider_images, true)[$key] }}">
                                                                </div>
                                                                <div class="file-preview box sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- link -->
                                                        <div class="col-md">
                                                            <div class="form-group mb-md-0">
                                                                <input type="text" class="form-control"
                                                                    placeholder="http://" name="app_home_slider_links[]"
                                                                    value="{{ isset(json_decode($app_home_slider_links, true)[$key]) ? json_decode($app_home_slider_links, true)[$key] : '' }}">
                                                            </div>
                                                        </div>
                                                        <!-- remove parent button -->
                                                        <div class="col-md-auto">
                                                            <div class="form-group mb-md-0">
                                                                <button type="button"
                                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                                    data-toggle="remove-parent"
                                                                    data-parent=".remove-parent">
                                                                    <i class="las la-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Add button -->
                                    <div class="">
                                        <button type="button"
                                            class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                            style="background: #fcfcfc;" data-toggle="add-more"
                                            data-content='
													<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
														<div class="row gutters-5">
															<!-- Image -->
															<div class="col-md-5">
																<div class="form-group mb-md-0">
																	<div class="input-group" data-toggle="aizuploader" data-type="image">
																		<div class="input-group-prepend">
																			<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate(' Browse') }}</div>
                                                                            </div>
                                                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                                                            <input type="hidden" name="app_home_slider_images[]" class="selected-files" value="">
                                                                        </div>
                                                                        <div class="file-preview box sm">
                                                                        </div>
                                                                    </div>
                                                            </div>
                                                            <!-- link -->
                                                            <div class="col-md">
                                                                <div class="form-group mb-md-0">
                                                                    <input type="text" class="form-control" placeholder="http://" name="app_home_slider_links[]"
                                                                        value="">
                                                                </div>
                                                            </div>
                                                            <!-- remove parent button -->
                                                            <div class="col-md-auto">
                                                                <div class="form-group mb-md-0">
                                                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                                        data-toggle="remove-parent" data-parent=".remove-parent">
                                                                        <i class="las la-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>'
                                            data-target=".home-slider-target">
                                            <i class="las la-2x text-success la-plus-circle"></i>
                                            <span class="ml-2">{{ translate('Add New') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Save Button -->
                                <div class="mt-4 text-right">
                                    <button type="submit"
                                        class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Banner Level 1 -->
                    <div class="tab-pane fade" id="banner_1" role="tabpanel" aria-labelledby="banner-1-tab">
                        <form action="{{ route('business_settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab" value="banner_1">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_banner1_images">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_banner1_links">

                            <div class="bg-surface p-3 p-sm-2rem">
                                <div class="w-100">
                                    <!-- Information -->
                                    <div class="fs-11 d-flex mb-2rem">
                                        <div>
                                            <svg id="_79508b4b8c932dcad9066e2be4ca34f2"
                                                data-name="79508b4b8c932dcad9066e2be4ca34f2"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 16 16">
                                                <path id="Path_40683" data-name="Path 40683"
                                                    d="M8,16a8,8,0,1,1,8-8A8.024,8.024,0,0,1,8,16ZM8,1.333A6.667,6.667,0,1,0,14.667,8,6.686,6.686,0,0,0,8,1.333Z"
                                                    fill="#9da3ae" />
                                                <path id="Path_40684" data-name="Path 40684"
                                                    d="M10.6,15a.926.926,0,0,1-.667-.333c-.333-.467-.067-1.133.667-2.933.133-.267.267-.6.4-.867a.714.714,0,0,1-.933-.067.644.644,0,0,1,0-.933A3.408,3.408,0,0,1,11.929,9a.926.926,0,0,1,.667.333c.333.467.067,1.133-.667,2.933-.133.267-.267.6-.4.867a.714.714,0,0,1,.933.067.644.644,0,0,1,0,.933A3.408,3.408,0,0,1,10.6,15Z"
                                                    transform="translate(-3.262 -3)" fill="#9da3ae" />
                                                <circle id="Ellipse_813" data-name="Ellipse 813" cx="1"
                                                    cy="1" r="1" transform="translate(8 3.333)" fill="#9da3ae" />
                                                <path id="Path_40685" data-name="Path 40685"
                                                    d="M12.833,7.167a1.333,1.333,0,1,1,1.333-1.333A1.337,1.337,0,0,1,12.833,7.167Zm0-2a.63.63,0,0,0-.667.667.667.667,0,1,0,1.333,0A.63.63,0,0,0,12.833,5.167Z"
                                                    transform="translate(-3.833 -1.5)" fill="#9da3ae" />
                                            </svg>
                                        </div>
                                        <div class="ml-2 text-gray">
                                            <div>{{ translate('Aspect ratio should be 270\120') }}</div>
                                        </div>
                                    </div>
                                    <!-- Images & links -->
                                    <div class="home-banner1-target">
                                        @php
                                            $app_home_banner1_images = get_setting(
                                                'app_home_banner1_images',
                                                null,
                                                $lang,
                                            );
                                            $app_home_banner1_links = get_setting(
                                                'app_home_banner1_links',
                                                null,
                                                $lang,
                                            );
                                        @endphp
                                        @if ($app_home_banner1_images != null)
                                            @foreach (json_decode($app_home_banner1_images, true) as $key => $value)
                                                <div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent"
                                                    style="border: 1px dashed #e4e5eb;">
                                                    <div class="row gutters-5">
                                                        <!-- Image -->
                                                        <div class="col-md-5">
                                                            <div class="form-group mb-md-0">
                                                                <div class="input-group" data-toggle="aizuploader"
                                                                    data-type="image">
                                                                    <div class="input-group-prepend">
                                                                        <div
                                                                            class="input-group-text bg-soft-secondary font-weight-medium">
                                                                            {{ translate('Browse') }}</div>
                                                                    </div>
                                                                    <div class="form-control file-amount">
                                                                        {{ translate('Choose File') }}</div>
                                                                    <input type="hidden" name="app_home_banner1_images[]"
                                                                        class="selected-files"
                                                                        value="{{ json_decode($app_home_banner1_images, true)[$key] }}">
                                                                </div>
                                                                <div class="file-preview box sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- link -->
                                                        <div class="col-md">
                                                            <div class="form-group mb-md-0">
                                                                <input type="text" class="form-control"
                                                                    placeholder="http://" name="app_home_banner1_links[]"
                                                                    value="{{ isset(json_decode($app_home_banner1_links, true)[$key]) ? json_decode($app_home_banner1_links, true)[$key] : '' }}">
                                                            </div>
                                                        </div>
                                                        <!-- remove parent button -->
                                                        <div class="col-md-auto">
                                                            <div class="form-group mb-md-0">
                                                                <button type="button"
                                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                                    data-toggle="remove-parent"
                                                                    data-parent=".remove-parent">
                                                                    <i class="las la-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Add button -->
                                    <div class="">
                                        <button type="button"
                                            class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                            style="background: #fcfcfc;" data-toggle="add-more"
                                            data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate(' Browse') }}</div>
                                                            </div>
                                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                                            <input type="hidden" name="app_home_banner1_images[]" class="selected-files" value="">
                                                        </div>
                                                        <div class="file-preview box sm">
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- link -->
                                            <div class="col-md">
                                                <div class="form-group mb-md-0 mb-0">
                                                    <input type="text" class="form-control" placeholder="http://" name="app_home_banner1_links[]" value="">
                                                </div>
                                            </div>
                                            <!-- remove parent button -->
                                            <div class="col-md-auto">
                                                <div class="form-group mb-md-0">
                                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent"
                                                        data-parent=".remove-parent">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            </div>
                                            </div>'
                                            data-target=".home-banner1-target">
                                            <i class="las la-2x text-success la-plus-circle"></i>
                                            <span class="ml-2">{{ translate('Add New') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Save Button -->
                                <div class="mt-4 text-right">
                                    <button type="submit"
                                        class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Banner Level 2 -->
                    <div class="tab-pane fade" id="banner_2" role="tabpanel" aria-labelledby="banner-2-tab">
                        <form action="{{ route('business_settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="tab" value="banner_2">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_banner2_images">
                            <input type="hidden" name="types[][{{ $lang }}]" value="app_home_banner2_links">

                            <div class="bg-surface p-3 p-sm-2rem">
                                <div class="w-100">
                                    <!-- Information -->
                                    <div class="fs-11 d-flex mb-2rem">
                                        <div>
                                            <svg id="_79508b4b8c932dcad9066e2be4ca34f2"
                                                data-name="79508b4b8c932dcad9066e2be4ca34f2"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 16 16">
                                                <path id="Path_40683" data-name="Path 40683"
                                                    d="M8,16a8,8,0,1,1,8-8A8.024,8.024,0,0,1,8,16ZM8,1.333A6.667,6.667,0,1,0,14.667,8,6.686,6.686,0,0,0,8,1.333Z"
                                                    fill="#9da3ae" />
                                                <path id="Path_40684" data-name="Path 40684"
                                                    d="M10.6,15a.926.926,0,0,1-.667-.333c-.333-.467-.067-1.133.667-2.933.133-.267.267-.6.4-.867a.714.714,0,0,1-.933-.067.644.644,0,0,1,0-.933A3.408,3.408,0,0,1,11.929,9a.926.926,0,0,1,.667.333c.333.467.067,1.133-.667,2.933-.133.267-.267.6-.4.867a.714.714,0,0,1,.933.067.644.644,0,0,1,0,.933A3.408,3.408,0,0,1,10.6,15Z"
                                                    transform="translate(-3.262 -3)" fill="#9da3ae" />
                                                <circle id="Ellipse_813" data-name="Ellipse 813" cx="1"
                                                    cy="1" r="1" transform="translate(8 3.333)" fill="#9da3ae" />
                                                <path id="Path_40685" data-name="Path 40685"
                                                    d="M12.833,7.167a1.333,1.333,0,1,1,1.333-1.333A1.337,1.337,0,0,1,12.833,7.167Zm0-2a.63.63,0,0,0-.667.667.667.667,0,1,0,1.333,0A.63.63,0,0,0,12.833,5.167Z"
                                                    transform="translate(-3.833 -1.5)" fill="#9da3ae" />
                                            </svg>
                                        </div>
                                        <div class="ml-2 text-gray">
                                            <div>{{ translate('Aspect ratio should be 270\120') }}</div>
                                        </div>
                                    </div>
                                    <!-- Images & links -->
                                    <div class="home-banner2-target">
                                        @php
                                            $app_home_banner2_images = get_setting(
                                                'app_home_banner2_images',
                                                null,
                                                $lang,
                                            );
                                            $app_home_banner2_links = get_setting(
                                                'app_home_banner2_links',
                                                null,
                                                $lang,
                                            );
                                        @endphp
                                        @if ($app_home_banner2_images != null)
                                            @foreach (json_decode($app_home_banner2_images, true) as $key => $value)
                                                <div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent"
                                                    style="border: 1px dashed #e4e5eb;">
                                                    <div class="row gutters-5">
                                                        <!-- Image -->
                                                        <div class="col-md-5">
                                                            <div class="form-group mb-md-0">
                                                                <div class="input-group" data-toggle="aizuploader"
                                                                    data-type="image">
                                                                    <div class="input-group-prepend">
                                                                        <div
                                                                            class="input-group-text bg-soft-secondary font-weight-medium">
                                                                            {{ translate('Browse') }}</div>
                                                                    </div>
                                                                    <div class="form-control file-amount">
                                                                        {{ translate('Choose File') }}</div>
                                                                    <input type="hidden" name="app_home_banner2_images[]"
                                                                        class="selected-files"
                                                                        value="{{ json_decode($app_home_banner2_images, true)[$key] }}">
                                                                </div>
                                                                <div class="file-preview box sm">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- link -->
                                                        <div class="col-md">
                                                            <div class="form-group mb-md-0">
                                                                <input type="text" class="form-control"
                                                                    placeholder="http://" name="app_home_banner2_links[]"
                                                                    value="{{ isset(json_decode($app_home_banner2_links, true)[$key]) ? json_decode($app_home_banner2_links, true)[$key] : '' }}">
                                                            </div>
                                                        </div>
                                                        <!-- remove parent button -->
                                                        <div class="col-md-auto">
                                                            <div class="form-group mb-md-0">
                                                                <button type="button"
                                                                    class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger"
                                                                    data-toggle="remove-parent"
                                                                    data-parent=".remove-parent">
                                                                    <i class="las la-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <!-- Add button -->
                                    <div class="">
                                        <button type="button"
                                            class="btn btn-block border hov-bg-soft-secondary fs-14 rounded-0 d-flex align-items-center justify-content-center"
                                            style="background: #fcfcfc;" data-toggle="add-more"
                                            data-content='
											<div class="p-3 p-md-4 mb-3 mb-md-2rem remove-parent" style="border: 1px dashed #e4e5eb;">
												<div class="row gutters-5">
													<!-- Image -->
													<div class="col-md-5">
														<div class="form-group mb-md-0">
															<div class="input-group" data-toggle="aizuploader" data-type="image">
																<div class="input-group-prepend">
																	<div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate(' Browse') }}</div>
                                                            </div>
                                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                                            <input type="hidden" name="app_home_banner2_images[]" class="selected-files" value="">
                                                        </div>
                                                        <div class="file-preview box sm">
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- link -->
                                            <div class="col-md">
                                                <div class="form-group mb-md-0 mb-0">
                                                    <input type="text" class="form-control" placeholder="http://" name="app_home_banner2_links[]" value="">
                                                </div>
                                            </div>
                                            <!-- remove parent button -->
                                            <div class="col-md-auto">
                                                <div class="form-group mb-md-0">
                                                    <button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent"
                                                        data-parent=".remove-parent">
                                                        <i class="las la-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            </div>
                                            </div>'
                                            data-target=".home-banner2-target">
                                            <i class="las la-2x text-success la-plus-circle"></i>
                                            <span class="ml-2">{{ translate('Add New') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Save Button -->
                                <div class="mt-4 text-right">
                                    <button type="submit"
                                        class="btn btn-success w-230px btn-md rounded-2 fs-14 fw-700 shadow-success">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            AIZ.plugins.bootstrapSelect('refresh');
        });
    </script>
    <script>
        $(document).ready(function() {
            var hash = document.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            } else {
                $('.nav-tabs a[href="#home_slider"]').tab('show');
            }

            // Change hash for page-reload
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                window.location.hash = e.target.hash;
            });
        });
    </script>
@endsection
