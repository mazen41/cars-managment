<?php

// Car Inspector API Routes
Route::group(['prefix' => 'v2/inspector', 'middleware' => ['app_language']], function () {
    // Authentication routes (no middleware required)
    Route::post('login', 'App\Http\Controllers\Api\V2\Inspector\InspectorAuthController@login');
     // languages
        Route::get(
        "languages",
        "App\Http\Controllers\Api\V2\LanguageController@getList",
    );
    //countries
    Route::get(
        "countries",
        "App\Http\Controllers\Api\V2\AddressController@getCountries",
    );
    Route::get(
        "states-by-country/{country_id}",
        "App\Http\Controllers\Api\V2\AddressController@getStatesByCountry",
    );
     Route::get(
        "cities-by-state/{state_id}",
        "App\Http\Controllers\Api\V2\AddressController@getCitiesByState",
    );

    // Protected routes (require inspector authentication)
    Route::middleware(['inspector.auth'])->group(function () {
        // Authentication management
        Route::post('logout', 'App\Http\Controllers\Api\V2\Inspector\InspectorAuthController@logout');
        Route::post('refresh', 'App\Http\Controllers\Api\V2\Inspector\InspectorAuthController@refresh');
        Route::get('me', 'App\Http\Controllers\Api\V2\Inspector\InspectorAuthController@me');

        // Dashboard analytics
        Route::get('dashboard', 'App\Http\Controllers\Api\V2\Inspector\InspectorDashboardController@index');
        Route::get('dashboard/analytics', 'App\Http\Controllers\Api\V2\Inspector\InspectorDashboardController@analytics');

        // Inspection management routes
        Route::get('inspections', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@index');
        Route::get('inspections/{inspection}', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@show');

        // Manual examination routes
        Route::get('manual-examinations', 'App\Http\Controllers\Api\V2\Inspector\ManualExaminationController@index');
        Route::post('manual-examinations', 'App\Http\Controllers\Api\V2\Inspector\ManualExaminationController@store');
        Route::get('manual-examinations/{manualExamination}', 'App\Http\Controllers\Api\V2\Inspector\ManualExaminationController@show');

        // Inspection status management routes
        Route::put('inspections/{inspection}/start', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@start');
        Route::put('inspections/{inspection}/complete', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@complete');
        Route::put('inspections/{inspection}/cancel', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@cancel');

        // Inspection field value management routes
        Route::post('inspections/{inspection}/field-values', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@updateFieldValues');
        Route::post('inspections/{inspection}/upload-photos', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@uploadPhotos');
        Route::post('inspections/{inspection}/remove-photo', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@removePhoto');
        Route::get('inspections/{inspection}/report', 'App\Http\Controllers\Api\V2\Inspector\InspectorInspectionController@generateReport');

        // Payment information routes
        Route::get('payments', 'App\Http\Controllers\Api\V2\Inspector\InspectorPaymentController@index');
        Route::get('payments/summary', 'App\Http\Controllers\Api\V2\Inspector\InspectorPaymentController@summary');
        Route::get('payments/{payment}', 'App\Http\Controllers\Api\V2\Inspector\InspectorPaymentController@show');

        // Profile management routes
        Route::get('profile', 'App\Http\Controllers\Api\V2\Inspector\InspectorProfileController@show');
        Route::put('profile', 'App\Http\Controllers\Api\V2\Inspector\InspectorProfileController@update');
        Route::put('profile/password', 'App\Http\Controllers\Api\V2\Inspector\InspectorProfileController@changePassword');
        Route::put('profile/business-settings', 'App\Http\Controllers\Api\V2\Inspector\InspectorProfileController@updateBusinessSettings');
        Route::post('profile/avatar', 'App\Http\Controllers\Api\V2\Inspector\InspectorProfileController@uploadAvatar');


    });
});
