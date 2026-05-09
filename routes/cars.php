<?php

use App\Http\Controllers\CarController;
use App\Http\Controllers\CarBrandController;
use App\Http\Controllers\CarFeatureSectionController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\CarCategoryController;
use App\Http\Controllers\CarFeatureController;
use App\Http\Controllers\CarColorController;
use App\Http\Controllers\CarCustomFieldController;
use App\Http\Controllers\CarInspectionTypeController;
use App\Http\Controllers\CarInspectionSectionController;
use App\Http\Controllers\CarInspectionFieldController;
use App\Http\Controllers\CarInspectionController;
use App\Http\Controllers\CarInspectionPaymentController;
use App\Http\Controllers\CarInspectionFieldValueController;
use App\Http\Controllers\CarInspectorController;
use App\Http\Controllers\CarReservationController;
use App\Http\Controllers\Admin\ManualExaminationController;
use App\Http\Controllers\Admin\ManualExaminationPermissionController;

// Cars System Routes
Route::prefix("admin")
    ->name("admin.")
    ->middleware(["auth", "admin"])
    ->group(function () {
        // Cars Management
        Route::prefix("cars")
            ->name("cars.")
            ->group(function () {
                Route::get("/", [CarController::class, "index"])->name("index");
                Route::get("/bulk-export", [CarController::class, "export"])->name("bulk-export");
                Route::get("/create", [CarController::class, "create"])->name(
                    "create",
                );
                Route::post("/", [CarController::class, "store"])->name(
                    "store",
                );
                Route::get("/{car}", [CarController::class, "show"])->name(
                    "show",
                );
                Route::get("/{car}/edit", [CarController::class, "edit"])->name(
                    "edit",
                );
                Route::put("/{car}", [CarController::class, "update"])->name(
                    "update",
                );
                Route::post("/{car}/update-featured-todays-deal", [
                    CarController::class,
                    "updateFeaturedAndTodaysDeal",
                ])->name("update-featured-todays-deal");
                Route::get("/{car}/destroy", [
                    CarController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/toggle-status/{car}", [
                    CarController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::get("/brand/models/{brand}", [
                    CarController::class,
                    "getModelsByBrand",
                ])->name("models-by-brand");
                Route::post("/bulk-update-status", [
                    CarController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::post("/bulk-delete", [
                    CarController::class,
                    "bulkDelete",
                ])->name("bulk-delete");
            });

        // Car Reservations Management
        Route::prefix("car-reservations")
            ->name("car-reservations.")
            ->group(function () {
                Route::get("/", [
                    CarReservationController::class,
                    "index",
                ])->name("index");
                Route::get("/{carReservation}", [
                    CarReservationController::class,
                    "show",
                ])->name("show");
                Route::post("/{carReservation}/confirm", [
                    CarReservationController::class,
                    "confirm",
                ])->name("confirm");
                Route::post("/{carReservation}/cancel", [
                    CarReservationController::class,
                    "cancel",
                ])->name("cancel");
                Route::post("/{carReservation}/mark-as-sold", [
                    CarReservationController::class,
                    "markAsSold",
                ])->name("mark-as-sold");
                Route::post("/{carReservation}/update-payment-status", [
                    CarReservationController::class,
                    "updatePaymentStatus",
                ])->name("update-payment-status");
                Route::delete("/{carReservation}", [
                    CarReservationController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/bulk-update-status", [
                    CarReservationController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::get("/statistics", [
                    CarReservationController::class,
                    "statistics",
                ])->name("statistics");
            });
        Route::prefix("car-reservations")
            ->get("/setup", [CarReservationController::class, "setup"])
            ->name("car-reservations.setup");

        // Car Brands Management
        Route::prefix("car-brands")
            ->name("car-brands.")
            ->group(function () {
                Route::get("/", [CarBrandController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarBrandController::class,
                    "create",
                ])->name("create");
                Route::post("/", [CarBrandController::class, "store"])->name(
                    "store",
                );
                Route::get("/{carBrand}", [
                    CarBrandController::class,
                    "show",
                ])->name("show");
                Route::get("/{carBrand}/edit", [
                    CarBrandController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carBrand}", [
                    CarBrandController::class,
                    "update",
                ])->name("update");
                Route::get("destroy/{carBrand}", [
                    CarBrandController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("toggle-status/{carBrand}", [
                    CarBrandController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::post("/bulk-update-status", [
                    CarBrandController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::get("/{carBrand}/statistics", [
                    CarBrandController::class,
                    "statistics",
                ])->name("statistics");
            });

        // Car Models Management
        Route::prefix("car-models")
            ->name("car-models.")
            ->group(function () {
                Route::get("/", [CarModelController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarModelController::class,
                    "create",
                ])->name("create");
                Route::post("/", [CarModelController::class, "store"])->name(
                    "store",
                );
                Route::get("/{carModel}", [
                    CarModelController::class,
                    "show",
                ])->name("show");
                Route::get("/{carModel}/edit", [
                    CarModelController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carModel}", [
                    CarModelController::class,
                    "update",
                ])->name("update");
                Route::get("destroy/{carModel}", [
                    CarModelController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/toggle-status/{carModel}", [
                    CarModelController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::post("/bulk-update-status", [
                    CarModelController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::get("/{carModel}/statistics", [
                    CarModelController::class,
                    "statistics",
                ])->name("statistics");
            });

        // Car Categories Management
        Route::prefix("car-categories")
            ->name("car-categories.")
            ->group(function () {
                Route::get("/", [CarCategoryController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarCategoryController::class,
                    "create",
                ])->name("create");
                Route::post("/", [CarCategoryController::class, "store"])->name(
                    "store",
                );
                Route::get("/{carCategory}", [
                    CarCategoryController::class,
                    "show",
                ])->name("show");
                Route::get("/{carCategory}/edit", [
                    CarCategoryController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carCategory}", [
                    CarCategoryController::class,
                    "update",
                ])->name("update");
                Route::get("destroy/{carCategory}", [
                    CarCategoryController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("toggle-status/{carCategory}", [
                    CarCategoryController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::post("/bulk-update-status", [
                    CarCategoryController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::post("/update-order", [
                    CarCategoryController::class,
                    "updateOrder",
                ])->name("update-order");
                Route::get("/{carCategory}/statistics", [
                    CarCategoryController::class,
                    "statistics",
                ])->name("statistics");
                Route::get("/parent/{parentId}/subcategories", [
                    CarCategoryController::class,
                    "getSubcategories",
                ])->name("subcategories");
            });

        // Car Features Management
        Route::prefix("car-features")
            ->name("car-features.")
            ->group(function () {
                //Section Managment
                Route::prefix('section')
                ->name('section.')
                ->group(function(){
                    Route::get("/", [CarFeatureSectionController::class, "index"])->name('index');
                    Route::get("/create", [CarFeatureSectionController::class, "create"])->name('create');
                    Route::post("/", [CarFeatureSectionController::class, "store"])->name('store');
                    Route::get("/{carFeatureSection}/edit", [CarFeatureSectionController::class, "edit"])->name('edit');
                    Route::put("/{carFeatureSection}", [CarFeatureSectionController::class, "update"])->name('update');
                    Route::get("/{carFeatureSection}/destroy", [CarFeatureSectionController::class,'delete'])->name('destroy');
                });
                Route::get("/", [CarFeatureController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarFeatureController::class,
                    "create",
                ])->name("create");
                Route::post("/", [CarFeatureController::class, "store"])->name(
                    "store",
                );
                Route::get("/{carFeature}", [
                    CarFeatureController::class,
                    "show",
                ])->name("show");
                Route::get("/{carFeature}/edit", [
                    CarFeatureController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carFeature}", [
                    CarFeatureController::class,
                    "update",
                ])->name("update");
                Route::get("destroy/{carFeature}", [
                    CarFeatureController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/bulk-delete", [
                    CarFeatureController::class,
                    "bulkDelete",
                ])->name("bulk-delete");
                Route::get("/{carFeature}/statistics", [
                    CarFeatureController::class,
                    "statistics",
                ])->name("statistics");
                Route::post("/toggle-for-car", [
                    CarFeatureController::class,
                    "toggleForCar",
                ])->name("toggle-for-car");
            });

        // Car Colors Management
        Route::prefix("car-colors")
            ->name("car-colors.")
            ->group(function () {
                Route::get("/", [CarColorController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarColorController::class,
                    "create",
                ])->name("create");
                Route::post("/", [CarColorController::class, "store"])->name(
                    "store",
                );
                Route::get("/{carColor}", [
                    CarColorController::class,
                    "show",
                ])->name("show");
                Route::get("/{carColor}/edit", [
                    CarColorController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carColor}", [
                    CarColorController::class,
                    "update",
                ])->name("update");
                Route::get("destroy/{carColor}", [
                    CarColorController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("toggle-status/{carColor}", [
                    CarColorController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::post("/bulk-update-status", [
                    CarColorController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::get("/{carColor}/statistics", [
                    CarColorController::class,
                    "statistics",
                ])->name("statistics");
                Route::get("/api", [
                    CarColorController::class,
                    "apiIndex",
                ])->name("api");
            });

        // Car Custom Fields Management
        Route::prefix("car-custom-fields")
            ->name("car-custom-fields.")
            ->group(function () {
                Route::get("/", [
                    CarCustomFieldController::class,
                    "index",
                ])->name("index");
                Route::get("/create", [
                    CarCustomFieldController::class,
                    "create",
                ])->name("create");
                Route::post("/", [
                    CarCustomFieldController::class,
                    "store",
                ])->name("store");
                Route::get("/{carCustomField}", [
                    CarCustomFieldController::class,
                    "show",
                ])->name("show");
                Route::get("/{carCustomField}/edit", [
                    CarCustomFieldController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carCustomField}", [
                    CarCustomFieldController::class,
                    "update",
                ])->name("update");
                Route::get("/{carCustomField}/destroy", [
                    CarCustomFieldController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/update-order", [
                    CarCustomFieldController::class,
                    "updateOrder",
                ])->name("update-order");
                Route::post("/bulk-delete", [
                    CarCustomFieldController::class,
                    "bulkDelete",
                ])->name("bulk-delete");
                Route::get("/{carCustomField}/statistics", [
                    CarCustomFieldController::class,
                    "statistics",
                ])->name("statistics");
                Route::get("/{carCustomField}/field-values", [
                    CarCustomFieldController::class,
                    "getFieldValues",
                ])->name("field-values");
                Route::get("/export", [
                    CarCustomFieldController::class,
                    "export",
                ])->name("export");
            });

        // Car Inspection Types Management
        Route::prefix("car-inspection-types")
            ->name("car-inspection-types.")
            ->group(function () {
                Route::get("/", [
                    CarInspectionTypeController::class,
                    "index",
                ])->name("index");
                Route::get("/create", [
                    CarInspectionTypeController::class,
                    "create",
                ])->name("create");
                Route::post("/", [
                    CarInspectionTypeController::class,
                    "store",
                ])->name("store");
                Route::get("/{carInspectionType}", [
                    CarInspectionTypeController::class,
                    "show",
                ])->name("show");
                Route::get("/{carInspectionType}/edit", [
                    CarInspectionTypeController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carInspectionType}", [
                    CarInspectionTypeController::class,
                    "update",
                ])->name("update");
                Route::get("/{carInspectionType}/destroy", [
                    CarInspectionTypeController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("toggle-status/{carInspectionType}", [
                    CarInspectionTypeController::class,
                    "toggleStatus",
                ])->name("toggle-status");
                Route::post("/{carInspectionType}/duplicate", [
                    CarInspectionTypeController::class,
                    "duplicate",
                ])->name("duplicate");
                Route::post("/bulk-update-status", [
                    CarInspectionTypeController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");
                Route::post("/bulk-delete", [
                    CarInspectionTypeController::class,
                    "bulkDelete",
                ])->name("bulk-delete");
                Route::post("/update-order", [
                    CarInspectionTypeController::class,
                    "updateOrder",
                ])->name("update-order");
                Route::get("/{carInspectionType}/statistics", [
                    CarInspectionTypeController::class,
                    "statistics",
                ])->name("statistics");

                // Nested Sections Management within Inspection Type
                Route::prefix("/{carInspectionType}/sections")
                    ->name("sections.")
                    ->group(function () {
                        Route::post("/", [
                            CarInspectionSectionController::class,
                            "store",
                        ])->name("store");
                        Route::put("/{carInspectionSection}", [
                            CarInspectionSectionController::class,
                            "update",
                        ])->name("update");
                        Route::delete("/{carInspectionSection}", [
                            CarInspectionSectionController::class,
                            "destroy",
                        ])->name("destroy");
                        Route::post("/{carInspectionSection}/duplicate", [
                            CarInspectionSectionController::class,
                            "duplicate",
                        ])->name("duplicate");
                        Route::post("{carInspectionSection}/toggle-status", [
                            CarInspectionSectionController::class,
                            "toggleSectionStatus",
                        ])->name("toggle-status");
                        Route::post("/sort-sections", [
                            CarInspectionSectionController::class,
                            "sortSections",
                        ])->name("sort-sections");

                        // Nested Fields Management within Section
                        Route::prefix("/{carInspectionSection}/fields")
                            ->name("fields.")
                            ->group(function () {
                                Route::post("/", [
                                    CarInspectionFieldController::class,
                                    "store",
                                ])->name("store");
                                Route::get("/{carInspectionField}/edit", [
                                    CarInspectionFieldController::class,
                                    "edit",
                                ])->name("edit");
                                Route::put("/{carInspectionField}", [
                                    CarInspectionFieldController::class,
                                    "update",
                                ])->name("update");
                                Route::delete("/{carInspectionField}", [
                                    CarInspectionFieldController::class,
                                    "destroy",
                                ])->name("destroy");
                                Route::post("/{carInspectionField}/duplicate", [
                                    CarInspectionFieldController::class,
                                    "duplicate",
                                ])->name("duplicate");
                                Route::post(
                                    "/{carInspectionField}/toggle-status",
                                    [
                                        CarInspectionFieldController::class,
                                        "toggleFieldStatus",
                                    ],
                                )->name("toggle-status");

                                Route::post("/sort-fields", [
                                    CarInspectionFieldController::class,
                                    "sortFields",
                                ])->name("sort-fields");
                            });
                    });
            });

        // Car Inspections Management
        Route::prefix("car-inspections")
            ->name("car-inspections.")
            ->group(function () {
                Route::get("/dashboard", [
                    CarInspectionController::class,
                    "dashboard",
                ])->name("dashboard");
                Route::get("/chart-data", [
                    CarInspectionController::class,
                    "chartData",
                ])->name("chart-data");
                Route::get("/", [
                    CarInspectionController::class,
                    "index",
                ])->name("index");
                Route::get("/create", [
                    CarInspectionController::class,
                    "create",
                ])->name("create");
                Route::post("/", [
                    CarInspectionController::class,
                    "store",
                ])->name("store");
                Route::get("/{carInspection}", [
                    CarInspectionController::class,
                    "show",
                ])->name("show");
                Route::get("/{carInspection}/edit", [
                    CarInspectionController::class,
                    "edit",
                ])->name("edit");
                Route::post("/{carInspection}/update", [
                    CarInspectionController::class,
                    "update",
                ])->name("update");
                Route::get("/{carInspection}/destroy", [
                    CarInspectionController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/{carInspection}/start", [
                    CarInspectionController::class,
                    "start",
                ])->name("start");
                Route::get("/{carInspection}/conduct", [
                    CarInspectionController::class,
                    "conduct",
                ])->name("conduct");
                Route::post("/{carInspection}/complete", [
                    CarInspectionController::class,
                    "complete",
                ])->name("complete");
                Route::get("/{carInspection}/progress", [
                    CarInspectionController::class,
                    "progress",
                ])->name("progress");
                Route::get("/{carInspection}/cancel", [
                    CarInspectionController::class,
                    "cancel",
                ])->name("cancel");
                Route::get("/{carInspection}/report", [
                    CarInspectionController::class,
                    "report",
                ])->name("report");
                Route::get("/{carInspection}/deliver-to-inspector", [
                    CarInspectionController::class,
                    "deliverToInspector"
                ])
                ->name('deliver-to-inspector');
                Route::get("/{carInspection}/pdf", [
                    CarInspectionController::class,
                    "downloadPdf",
                ])->name("pdf");
                Route::post("/bulk-update-status", [
                    CarInspectionController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");

                // Car Inspection Payments
                Route::prefix("payments")
                    ->name("payments.")
                    ->group(function () {
                        Route::post("/{carInspection}/set-paid", [
                            CarInspectionPaymentController::class,
                            "setPaid",
                        ])->name("set-paid");
                        Route::post("/{carInspection}/set-unpaid", [
                            CarInspectionPaymentController::class,
                            "setUnpaid",
                        ])->name("set-unpaid");
                        Route::post("/{carInspection}/set-refund", [
                            CarInspectionPaymentController::class,
                            "setRefunded",
                        ])->name("set-refunded");
                    });
            });

        // Manual Examinations
        Route::prefix("manual-examinations")
            ->name("manual-examinations.")
            ->group(function () {
                Route::get("/", [
                    ManualExaminationController::class,
                    "index",
                ])->name("index");
                Route::get("/permissions", [
                    ManualExaminationPermissionController::class,
                    "index",
                ])->name("permissions.index");
                Route::post("/permissions/{center}", [
                    ManualExaminationPermissionController::class,
                    "update",
                ])->name("permissions.update");
                Route::get("/{manualExamination}/photos/{encodedPath}", [
                    ManualExaminationController::class,
                    "photo",
                ])->name("photo");
                Route::get("/{manualExamination}/download", [
                    ManualExaminationController::class,
                    "download",
                ])->name("download");
                Route::get("/{manualExamination}", [
                    ManualExaminationController::class,
                    "show",
                ])->name("show");
                Route::get("/{manualExamination}/schedule", [
                    ManualExaminationController::class,
                    "schedule",
                ])->name("schedule");
                Route::post("/{manualExamination}/schedule", [
                    ManualExaminationController::class,
                    "updateSchedule",
                ])->name("update-schedule");
            });

        // Car Inspection Field Values Management
        Route::prefix("car-inspection-field-values")
            ->name("car-inspection-field-values.")
            ->group(function () {
                Route::post("/", [
                    CarInspectionFieldValueController::class,
                    "store",
                ])->name("store");
                Route::put("/{carInspectionFieldValue}", [
                    CarInspectionFieldValueController::class,
                    "update",
                ])->name("update");
                Route::delete("/{carInspectionFieldValue}", [
                    CarInspectionFieldValueController::class,
                    "destroy",
                ])->name("destroy");
                Route::post("/flag/{carInspectionFieldValue}", [
                    CarInspectionFieldValueController::class,
                    "flag",
                ])->name("flag");
                Route::post("/unflag/{carInspectionFieldValue}", [
                    CarInspectionFieldValueController::class,
                    "unflag",
                ])->name("unflag");
                Route::post("/{carInspectionFieldValue}/upload", [
                    CarInspectionFieldValueController::class,
                    "uploadFile",
                ])->name("upload");
                Route::post("/{carInspectionFieldValue}/attachment/{index}", [
                    CarInspectionFieldValueController::class,
                    "removeAttachment",
                ])->name("remove-attachment");
                Route::post("/bulk-update", [
                    CarInspectionFieldValueController::class,
                    "bulkUpdate",
                ])->name("bulk-update");
                Route::get("/statistics", [
                    CarInspectionFieldValueController::class,
                    "getStatistics",
                ])->name("statistics");
            });

        // Car Inspectors Management
        Route::get("car-inspectors/all-payments", [
            CarInspectorController::class,
            "all_payments",
        ])->name("car-inspectors.all-payments");
        Route::get("car-inspectors/settings", [
            CarInspectorController::class,
            "settings",
        ])->name("car-inspectors.settings");
        Route::prefix("car-inspectors")
            ->name("car-inspectors.")
            ->group(function () {
                Route::get("/", [CarInspectorController::class, "index"])->name(
                    "index",
                );
                Route::get("/create", [
                    CarInspectorController::class,
                    "create",
                ])->name("create");
                Route::post("/", [
                    CarInspectorController::class,
                    "store",
                ])->name("store");
                Route::get("/{carInspector}", [
                    CarInspectorController::class,
                    "show",
                ])->name("show");
                Route::get("/{carInspector}/edit", [
                    CarInspectorController::class,
                    "edit",
                ])->name("edit");
                Route::put("/{carInspector}", [
                    CarInspectorController::class,
                    "update",
                ])->name("update");
                Route::delete("/{carInspector}", [
                    CarInspectorController::class,
                    "destroy",
                ])->name("destroy");

                // Payment routes

                Route::get("/{carInspector}/payments", [
                    CarInspectorController::class,
                    "payments",
                ])->name("payments");
                Route::get("/{carInspector}/make-payment", [
                    CarInspectorController::class,
                    "showPaymentForm",
                ])->name("show-payment-form");
                Route::post("/{carInspector}/make-payment", [
                    CarInspectorController::class,
                    "makePayment",
                ])->name("make-payment");

                // Bulk actions
                Route::post("/bulk-update-status", [
                    CarInspectorController::class,
                    "bulkUpdateStatus",
                ])->name("bulk-update-status");

                // Export
                Route::get("/export", [
                    CarInspectorController::class,
                    "export",
                ])->name("export");

                // Payment history details (AJAX)
                Route::get("/payment-details/{payment}", [
                    CarInspectorController::class,
                    "paymentDetails",
                ])->name("payment-details");

                // Update payment status (AJAX)
                Route::post("/payment-status/{payment}", [
                    CarInspectorController::class,
                    "updatePaymentStatus",
                ])->name("update-payment-status");
            });

        // Geographic helper routes for AJAX
        Route::get("get-states/{countryId}", [
            CarInspectorController::class,
            "getStates",
        ])->name("get-states");

        Route::get("get-cities/{stateId}", [
            CarInspectorController::class,
            "getCities",
        ])->name("get-cities");
    });
Route::prefix('v2')->group(function () {
    Route::middleware(['auth:sanctum', 'manual_examinations.enabled'])->prefix('inspector')->group(function () {
        Route::post('/manual-examinations', [\App\Http\Controllers\Api\V2\Inspector\ManualExaminationController::class, 'store']);
        Route::get('/manual-examinations', [\App\Http\Controllers\Api\V2\Inspector\ManualExaminationController::class, 'index']);
        Route::get('/manual-examinations/{id}/download', [\App\Http\Controllers\Api\V2\Inspector\ManualExaminationController::class, 'downloadPdf']);
        Route::get('/manual-examinations/{id}', [\App\Http\Controllers\Api\V2\Inspector\ManualExaminationController::class, 'show']);
    });

    // Inspection types (no prefix mismatch)
    Route::middleware('auth:sanctum')->prefix('cars')->group(function () {
        Route::get('/inspection-types', [CarInspectionTypeController::class, 'apiIndex']);
    });
});
