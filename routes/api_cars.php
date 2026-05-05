<?php

use App\Http\Controllers\Api\V2\CarBrandController;
use App\Http\Controllers\Api\V2\CarModelController;
use App\Http\Controllers\Api\V2\CarCategoryController;
use App\Http\Controllers\Api\V2\CarFeatureController;
use App\Http\Controllers\Api\V2\CarCustomFieldController;
use App\Http\Controllers\Api\V2\CarInspectionTypeController;
use App\Http\Controllers\Api\V2\CarInspectionController;
use App\Http\Controllers\Api\V2\CarInspectorController;
use App\Http\Controllers\Api\V2\CarReservationController;
use App\Http\Controllers\Api\V2\CarController;

// Public Car API routes
Route::prefix("v2/cars")
    ->name("api.v2.cars.")
    ->middleware(["app_language"])
    ->group(function () {
        // Main car endpoints
        Route::get("/list", [CarController::class, "index"])->name("index");
        Route::get("/search", [CarController::class, "search"])->name("search");
        Route::get("/filters", [CarController::class, "filters"])->name(
            "filters",
        );
        Route::get("/featured", [CarController::class, "featured"])->name(
            "featured",
        );
         Route::get("/todays-deal", [CarController::class, "todaysDeal"])->name(
            "todays-deal",
        );
        Route::get("/{car}/show", [CarController::class, "show"])->name("show");
        Route::get("/{car}/similar", [CarController::class, "similar"])->name(
            "similar",
        );

        // Additional endpoints
        Route::get("/statistics", [CarController::class, "statistics"])->name(
            "statistics",
        );

        Route::post("/compare", [CarController::class, "compare"])->name(
            "compare",
        );

        Route::get("/brands/{brandId}/cars", [
            CarController::class,
            "carsByBrand",
        ])->name("cars-by-brand");

        Route::get("/categories/{categoryId}/cars", [
            CarController::class,
            "carsByCategory",
        ])->name("cars-by-category");

        // Utility endpoints
        Route::post("/{car}/view", [CarController::class, "recordView"])->name(
            "record-view",
        );

        Route::get("/popular", [CarController::class, "popular"])->name(
            "popular",
        );

        Route::post("/nearby", [CarController::class, "nearbyCars"])->name(
            "nearby",
        );

        Route::get("/price-suggestions", [
            CarController::class,
            "priceSuggestions",
        ])->name("price-suggestions");

        Route::get("/{car}/availability", [
            CarController::class,
            "availability",
        ])->name("availability");

        //Supporting data endpoints
        //Brands
        Route::get("/brands", [CarBrandController::class, "index"])->name(
            "brands.api",
        );

        Route::get("/brands/popular", [
            CarBrandController::class,
            "popular",
        ])->name("brands.popular");
        // Models
        Route::get("/models", [CarModelController::class, "index"])->name(
            "models.api",
        );
        Route::get("/models/popular", [
            CarModelController::class,
            "popular",
        ])->name("models.popular");
        Route::get("/models/by-brands", [
            CarModelController::class,
            "getByBrands",
        ])->name("models.by-brands");
        Route::get("/brands/{brand}/models", [
            CarModelController::class,
            "getByBrand",
        ])->name("models.by-brand");

        //Categories
        Route::get("/categories", [
            CarCategoryController::class,
            "index",
        ])->name("categories.api");
        Route::get("/categories/{category}/subcategories", [
            CarCategoryController::class,
            "getSubcategories",
        ])->name("subcategories");
        Route::get("/categories/tree", [
            CarCategoryController::class,
            "tree",
        ])->name("categories.tree");

        //Features
        Route::get("/features", [CarFeatureController::class, "index"])->name(
            "features.api",
        );
        Route::get("/features/popular", [
            CarFeatureController::class,
            "popular",
        ])->name("features.popular");
        Route::get("/features/search", [
            CarFeatureController::class,
            "search",
        ])->name("features.search");
        Route::get("/features/with-usage-count", [
            CarFeatureController::class,
            "withUsageCount",
        ])->name("features.with-usage-count");
        Route::get("/features/car/{carId}", [
            CarFeatureController::class,
            "getByCarId",
        ])->name("features.by-car");

        //Custom fields
        Route::get("/custom-fields", [
            CarCustomFieldController::class,
            "index",
        ])->name("custom-fields.api");

        //Car inspection types
        Route::get("/inspection-types", [
            CarInspectionTypeController::class,
            "index",
        ])->name("api.car-inspections.index");

        //Car inspectors
        Route::get("/inspectors", [
            CarInspectorController::class,
            "index",
        ])->name("api.car-inspectors.index");

        Route::get("/inspectors/search", [
            CarInspectorController::class,
            "search",
        ])->name("api.car-inspectors.search");

        Route::get("/inspectors/statistics", [
            CarInspectorController::class,
            "statistics",
        ])->name("api.car-inspectors.statistics");

        Route::get("/inspectors/by-country/{countryCode?}", [
            CarInspectorController::class,
            "byCountry",
        ])->name("api.car-inspectors.by-country");

        Route::get("/inspectors/by-state/{stateId?}", [
            CarInspectorController::class,
            "byState",
        ])->name("api.car-inspectors.by-state");

        Route::get("/inspectors/{carInspector}/show", [
            CarInspectorController::class,
            "show",
        ])->name("api.car-inspectors.show");
    });

// Auth API routes
Route::prefix("v2")
    ->middleware(["auth:sanctum", "app_language"])
    ->group(function () {
        // Car inspections
        Route::prefix("car-inspections")->group(function () {
            Route::get("/", [
                CarInspectionController::class,
                "index",
            ])->name("api.car-inspections.index");
            Route::get("{carInspection}/show", [
                CarInspectionController::class,
                "show",
            ])->name("api.car-inspections.show");
            Route::post("order", [
                CarInspectionController::class,
                "order",
            ])->name("api.car-inspections.order");
            Route::get("/{carInspection}/download-pdf", [
                CarInspectionController::class,
                "downloadPdf",
            ])->name("api.car-inspections.download-pdf");
        });

        // Car reservation API routes
        Route::prefix("car-reservations")->group(function () {
            Route::get("/", [CarReservationController::class, "index"])->name(
                "api.car-reservations.index",
            );

            Route::post("/store", [
                CarReservationController::class,
                "store",
            ])->name("api.car-reservations.store");

            Route::get("/{carReservation}", [
                CarReservationController::class,
                "show",
            ])->name("api.car-reservations.show");

            Route::post("/{carReservation}/cancel", [
                CarReservationController::class,
                "cancel",
            ])->name("api.car-reservations.cancel");
        });

        // Car availability check
        Route::get("/cars/{car}/availability", [
            CarReservationController::class,
            "checkAvailability",
        ])->name("api.cars.availability");
    });
