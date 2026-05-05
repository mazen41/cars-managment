<?php

use App\Http\Controllers\Api\V2\DeliveryBoyController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "v2", "middleware" => ["app_language"]], function () {

    Route::prefix("delivery-boy")->group(function () {
        Route::controller(DeliveryBoyController::class)->group(function () {
            Route::get(
                "dashboard-summary/{id}",
                "dashboard_summary",
            )->middleware("auth:sanctum");
            Route::get(
                "deliveries/completed/{id}",
                "completed_delivery",
            )->middleware("auth:sanctum");
            Route::get(
                "deliveries/cancelled/{id}",
                "cancelled_delivery",
            )->middleware("auth:sanctum");
            Route::get(
                "deliveries/on_the_way/{id}",
                "on_the_way_delivery",
            )->middleware("auth:sanctum");
            Route::get(
                "deliveries/picked_up/{id}",
                "picked_up_delivery",
            )->middleware("auth:sanctum");
            Route::get(
                "deliveries/assigned/{id}",
                "assigned_delivery",
            )->middleware("auth:sanctum");
            Route::get(
                "collection-summary/{id}",
                "collection_summary",
            )->middleware("auth:sanctum");
            Route::get("earning-summary/{id}", "earning_summary")->middleware(
                "auth:sanctum",
            );
            Route::get("collection/{id}", "collection")->middleware(
                "auth:sanctum",
            );
            Route::get("earning/{id}", "earning")->middleware("auth:sanctum");
            Route::get("cancel-request/{id}", "cancel_request")->middleware(
                "auth:sanctum",
            );
            Route::post(
                "change-delivery-status",
                "change_delivery_status",
            )->middleware("auth:sanctum");
            //Delivery Boy Order
            Route::get(
                "purchase-history-details/{id}",
                "App\Http\Controllers\Api\V2\DeliveryBoyController@details",
            )->middleware("auth:sanctum");
            Route::get(
                "purchase-history-items/{id}",
                "App\Http\Controllers\Api\V2\DeliveryBoyController@items",
            )->middleware("auth:sanctum");
        });
    });
});
