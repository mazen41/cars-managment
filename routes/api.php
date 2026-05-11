<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Middleware\EnsureSystemKey;
use Illuminate\Support\Facades\Route;

Route::group(
    ["prefix" => "v2/auth", "middleware" => ["app_language"]],
    function () {
        Route::post("login", [AuthController::class, "login"]);
        Route::post("signup", [AuthController::class, "signup"]);
        Route::post("shop-signup", [AuthController::class, "shopSignup"]);
        Route::post('signup-password', [AuthController::class, 'signUpPassword']);
        Route::post("social-login", [AuthController::class, "socialLogin"]);
        Route::post("password/forget_request", [
            PasswordResetController::class,
            "forgetRequest",
        ]);
        Route::post("password/confirm_reset", [
            PasswordResetController::class,
            "confirmReset",
        ]);
        Route::post("password/resend_code", [
            PasswordResetController::class,
            "resendCode",
        ]);
        Route::post("password/verify-code", [
            PasswordResetController::class,
            "verifyCode"
        ]);
        Route::middleware("auth:sanctum")->group(function () {
            Route::get("logout", [AuthController::class, "logout"]);
            Route::get("account-deletion", [
                AuthController::class,
                "account_deletion_request",
            ]);

            Route::get("cancel-deletion-request", [
                AuthController::class,
                "cancel_deletion_request",
            ]);

            Route::get("user", [AuthController::class, "user"]);
            Route::get("resend_code", [AuthController::class, "resendCode"]);
            Route::post("confirm_code", [AuthController::class, "confirmCode"]);
        });

        Route::post("info", [
            AuthController::class,
            "getUserInfoByAccessToken",
        ]);
    },
);

Route::group(["prefix" => "v2", "middleware" => ["app_language"]], function () {

    // Public manual examination report endpoint (used by web/QR pages)
    Route::get('public/manual-examinations/{manualExamination}', [\App\Http\Controllers\Api\V2\Inspector\ManualExaminationController::class, 'show']);

    Route::apiResource("carts", CartController::class)
        ->only("destroy")
        ->names([
            "index" => "api.carts.destroy",
        ]);
    Route::controller(CartController::class)->group(function () {
        Route::get("cart-summary", "summary");
        Route::post("cart-count", "count");
        Route::post("carts/process", "process");
        Route::post("carts/add", "add");
        Route::post("carts/change-quantity", "changeQuantity");
        Route::post("carts", "getList");
        Route::post("guest-customer-info-check", "guestCustomerInfoCheck");
        Route::post("updateCartStatus", "updateCartStatus");
        Route::post("carts/bulk-delete", "bulkDelete");
    });

    Route::post("guest-user-account-create", [
        AuthController::class,
        "guestUserAccountCreate",
    ]);

    Route::post("coupon-apply", [
        CheckoutController::class,
        "apply_coupon_code",
    ]);

    Route::post("coupon-remove", [
        CheckoutController::class,
        "remove_coupon_code",
    ]);

    Route::post("delivery-info", [
        ShippingController::class,
        "getDeliveryInfo",
    ]);

    Route::post("shipping_cost", [ShippingController::class, "shipping_cost"])->middleware('auth:sanctum');
    Route::post("carriers", [CarrierController::class, "index"]);

    Route::post("update-address-in-cart", [
        AddressController::class,
        "updateAddressInCart",
    ]);

    Route::post("update-shipping-type-in-cart", [
        AddressController::class,
        "updateShippingTypeInCart",
    ]);
    Route::get("payment-types", [PaymentTypesController::class, "getList"]);

    Route::group(["middleware" => ["app_user_unbanned"]], function () {
        // customer downloadable product list
        Route::get(
            "/digital/purchased-list",
            "App\Http\Controllers\Api\V2\PurchaseHistoryController@digital_purchased_list",
        )->middleware("auth:sanctum");
        Route::get(
            "/purchased-products/download/{id}",
            "App\Http\Controllers\Api\V2\DigitalProductController@download",
        )->middleware("auth:sanctum");

        Route::get("wallet/history", [
            WalletController::class,
            "walletRechargeHistory",
        ])->middleware("auth:sanctum");

        Route::controller(ChatController::class)->group(function () {
            Route::get("chat/conversations", "conversations")->middleware(
                "auth:sanctum",
            );
            Route::get("chat/sent-conversations", "sentConversations")->middleware(
                "auth:sanctum",
            );
            Route::get("chat/received-conversations", "receivedConversations")->middleware(
                "auth:sanctum",
            );
            Route::get("chat/messages/{id}", "messages")->middleware(
                "auth:sanctum",
            );
            Route::post("chat/insert-message", "insertMessage")->middleware(
                "auth:sanctum",
            );
            Route::get(
                "chat/get-new-messages/{conversation_id}/{last_message_id}",
                "getNewMessages",
            )->middleware("auth:sanctum");
            Route::post(
                "chat/create-conversation",
                "createConversation",
            )->middleware("auth:sanctum");
            Route::get("chat/new-message-count", "count")->middleware(
                "auth:sanctum",
            );
        });

        Route::controller(PurchaseHistoryController::class)->group(function () {
            Route::get("purchase-history", "index")->middleware("auth:sanctum");
            Route::get("purchase-history-details/{id}", "details")->middleware(
                "auth:sanctum",
            );
            Route::get("purchase-history-items/{id}", "items")->middleware(
                "auth:sanctum",
            );
            Route::get("re-order/{id}", "re_order")->middleware("auth:sanctum");
        });

        Route::get("invoice/download/{id}", [
            InvoiceController::class,
            "invoice_download",
        ])->middleware("auth:sanctum");

        Route::get(
            "customer/info",
            "App\Http\Controllers\Api\V2\CustomerController@show",
        )->middleware("auth:sanctum");

        Route::get("get-home-delivery-address", [
            AddressController::class,
            "getShippingInCart",
        ])->middleware("auth:sanctum");

        //Follow
        Route::controller(FollowSellerController::class)->group(function () {
            Route::get("/followed-shops", "index")->middleware("auth:sanctum");
            Route::get("/followed-shops/store/{id}", "store")->middleware(
                "auth:sanctum",
            );
            Route::get(
                "/followed-shops/remove/{shopId}",
                "remove",
            )->middleware("auth:sanctum");
            Route::get(
                "/followed-shops/check/{shopId}",
                "checkFollow",
            )->middleware("auth:sanctum");
        });

        Route::post(
            "reviews/submit",
            "App\Http\Controllers\Api\V2\ReviewController@submit",
        )
            ->name("api.reviews.submit")
            ->middleware("auth:sanctum");

        Route::get(
            "shop/user/{id}",
            "App\Http\Controllers\Api\V2\ShopController@shopOfUser",
        )->middleware("auth:sanctum");

        Route::get(
            "wishlists-check-product",
            "App\Http\Controllers\Api\V2\WishlistController@isProductInWishlist",
        )->middleware("auth:sanctum");
        Route::get(
            "wishlists-add-product",
            "App\Http\Controllers\Api\V2\WishlistController@add",
        )->middleware("auth:sanctum");
        Route::get(
            "wishlists-remove-product",
            "App\Http\Controllers\Api\V2\WishlistController@remove",
        )->middleware("auth:sanctum");
        Route::get(
            "wishlists",
            "App\Http\Controllers\Api\V2\WishlistController@index",
        )->middleware("auth:sanctum");
        Route::name("api.")
            ->apiResource(
                "wishlists",
                "App\Http\Controllers\Api\V2\WishlistController",
            )
            ->middleware("auth:sanctum");

        Route::get(
            "user/shipping/address",
            "App\Http\Controllers\Api\V2\AddressController@addresses",
        )->middleware("auth:sanctum");
        Route::post(
            "user/shipping/create",
            "App\Http\Controllers\Api\V2\AddressController@createShippingAddress",
        )->middleware("auth:sanctum");
        Route::post(
            "user/shipping/update",
            "App\Http\Controllers\Api\V2\AddressController@updateShippingAddress",
        )->middleware("auth:sanctum");
        Route::post(
            "user/shipping/update-location",
            "App\Http\Controllers\Api\V2\AddressController@updateShippingAddressLocation",
        )->middleware("auth:sanctum");
        Route::post(
            "user/shipping/make_default",
            "App\Http\Controllers\Api\V2\AddressController@makeShippingAddressDefault",
        )->middleware("auth:sanctum");
        Route::get(
            "user/shipping/delete/{address_id}",
            "App\Http\Controllers\Api\V2\AddressController@deleteShippingAddress",
        )->middleware("auth:sanctum");

        Route::get(
            "clubpoint/get-list",
            "App\Http\Controllers\Api\V2\ClubpointController@get_list",
        )->middleware("auth:sanctum");
        Route::post(
            "clubpoint/convert-into-wallet",
            "App\Http\Controllers\Api\V2\ClubpointController@convert_into_wallet",
        )->middleware("auth:sanctum");

        Route::get(
            "refund-request/get-list",
            "App\Http\Controllers\Api\V2\RefundRequestController@get_list",
        )->middleware("auth:sanctum");
        Route::post(
            "refund-request/send",
            "App\Http\Controllers\Api\V2\RefundRequestController@send",
        )->middleware("auth:sanctum");
            // Deprecated wallet payment, use online payment instead
        Route::post(
            "payments/pay/wallet",
            "App\Http\Controllers\Api\V2\WalletController@processPayment",
        )->middleware("auth:sanctum");
        Route::post("order/store", [
            OrderController::class,
            "store",
        ])->middleware("auth:sanctum");

        Route::get(
            "order/cancel/{id}",
            "App\Http\Controllers\Api\V2\OrderController@order_cancel",
        )->middleware("auth:sanctum");

        Route::get(
            "profile/counters",
            "App\Http\Controllers\Api\V2\ProfileController@counters",
        )->middleware("auth:sanctum");

        Route::post(
            "profile/update",
            "App\Http\Controllers\Api\V2\ProfileController@update",
        )->middleware("auth:sanctum");

        Route::post(
            "profile/update-device-token",
            "App\Http\Controllers\Api\V2\ProfileController@update_device_token",
        )->middleware("auth:sanctum");
        Route::post(
            "profile/update-image",
            "App\Http\Controllers\Api\V2\ProfileController@updateImage",
        )->middleware("auth:sanctum");
        Route::post(
            "profile/image-upload",
            "App\Http\Controllers\Api\V2\ProfileController@imageUpload",
        )->middleware("auth:sanctum");
        Route::post(
            "profile/check-phone-and-email",
            "App\Http\Controllers\Api\V2\ProfileController@checkIfPhoneAndEmailAvailable",
        )->middleware("auth:sanctum");

        Route::post(
            "file/image-upload",
            "App\Http\Controllers\Api\V2\FileController@imageUpload",
        )->middleware("auth:sanctum");
        Route::get(
            "file-all",
            "App\Http\Controllers\Api\V2\FileController@index",
        )->middleware("auth:sanctum");
        Route::post(
            "file/upload",
            "App\Http\Controllers\Api\V2\AizUploadController@upload",
        )->middleware("auth:sanctum");

        Route::get("wallet/balance", [
            WalletController::class,
            "balance",
        ])->middleware("auth:sanctum");
        Route::post("wallet/offline-recharge", [
            WalletController::class,
            "offline_recharge",
        ])->middleware("auth:sanctum");

        Route::controller(CustomerPackageController::class)->group(function () {
            Route::post(
                "offline/packages-payment",
                "purchase_package_offline",
            )->middleware("auth:sanctum");
            Route::post(
                "free/packages-payment",
                "purchase_package_free",
            )->middleware("auth:sanctum");
        });

        // Notification
        Route::controller(NotificationController::class)->group(function () {
            Route::get("all-notification", "allNotification")->middleware(
                "auth:sanctum",
            );
            Route::get(
                "unread-notifications",
                "unreadNotifications",
            )->middleware("auth:sanctum");
            Route::post("notifications/bulk-delete", "bulkDelete")->middleware(
                "auth:sanctum",
            );
            Route::get(
                "notifications/mark-as-read/{notificationId}",
                "notificationMarkAsRead",
            )->middleware("auth:sanctum");
        });

        Route::get("products/last-viewed", [
            ProductController::class,
            "lastViewedProducts",
        ])->middleware("auth:sanctum");
    });

    //end user bann

    Route::get("coupon-list", [CouponController::class, "couponList"]);
    Route::get("coupon-products/{id}", [
        CouponController::class,
        "getCouponProducts",
    ]);

    Route::get(
        "get-search-suggestions",
        "App\Http\Controllers\Api\V2\SearchSuggestionController@getList",
    );
    Route::get(
        "languages",
        "App\Http\Controllers\Api\V2\LanguageController@getList",
    );

    // Requested Products
    Route::get("requested-products", [RequestedProductsController::class, "index"]);
    Route::get("requested-products/popular", [RequestedProductsController::class, "popular"]);
    Route::get("requested-products/categories-with-counts", [RequestedProductsController::class, "categoriesWithCounts"]);
    Route::get("requested-products/{requestedProduct}", [RequestedProductsController::class, "show"]);

    Route::middleware("auth:sanctum")->group(function () {
        Route::get("requested-products/user/requests", [RequestedProductsController::class, "userRequests"]);
        Route::post("requested-products/store", [RequestedProductsController::class, "store"]);
        Route::put("requested-products/{requestedProduct}", [RequestedProductsController::class, "update"]);
        Route::delete("requested-products/{requestedProduct}", [RequestedProductsController::class, "destroy"]);
    });

    Route::get(
        "seller/top",
        "App\Http\Controllers\Api\V2\SellerController@topSellers",
    );

    Route::apiResource(
        "banners",
        "App\Http\Controllers\Api\V2\BannerController",
    )
        ->only("index")
        ->names([
            "index" => "api.banners.index",
        ]);

    Route::get("brands/top", "App\Http\Controllers\Api\V2\BrandController@top");

    Route::apiResource("brands", "App\Http\Controllers\Api\V2\BrandController")
        ->only("index")
        ->names([
            "index" => "api.brands.index",
        ]);
    Route::get(
        "category-brands",
        "App\Http\Controllers\Api\V2\BrandController@getCategoryBrands",
    )->name("categoryBrands");

    Route::get(
        "get-business-settings",
        "App\Http\Controllers\Api\V2\BusinessSettingController@get_Settings",
    ); // new

    Route::get(
        "auction-configuration",
        "App\Http\Controllers\Api\V2\BusinessSettingController@auction_configuration",
    );

    Route::get(
        "category/info/{slug}",
        "App\Http\Controllers\Api\V2\CategoryController@info",
    );
    Route::get(
        "categories/featured",
        "App\Http\Controllers\Api\V2\CategoryController@featured",
    );
    Route::get(
        "categories/home",
        "App\Http\Controllers\Api\V2\CategoryController@home",
    );
    Route::get(
        "categories/top",
        "App\Http\Controllers\Api\V2\CategoryController@top",
    );
    Route::apiResource(
        "categories",
        "App\Http\Controllers\Api\V2\CategoryController",
    )
        ->only("index")
        ->names([
            "index" => "api.categories.index",
        ]);
    Route::get(
        "sub-categories/{id}",
        "App\Http\Controllers\Api\V2\SubCategoryController@index",
    )->name("subCategories.index");

    Route::apiResource(
        "colors",
        "App\Http\Controllers\Api\V2\ColorController",
    )->only("index");

    Route::apiResource(
        "currencies",
        "App\Http\Controllers\Api\V2\CurrencyController",
    )->only("index");

    Route::apiResource(
        "customers",
        "App\Http\Controllers\Api\V2\CustomerController",
    )->only("show");

    Route::apiResource(
        "general-settings",
        "App\Http\Controllers\Api\V2\GeneralSettingController",
    )->only("index");

    Route::apiResource(
        "home-categories",
        "App\Http\Controllers\Api\V2\HomeCategoryController",
    )->only("index");

    Route::get(
        "filter/categories",
        "App\Http\Controllers\Api\V2\FilterController@categories",
    );
    Route::get(
        "filter/brands",
        "App\Http\Controllers\Api\V2\FilterController@brands",
    );
    Route::get(
        "filter/general-market",
        "App\Http\Controllers\Api\V2\FilterController@generalMarketFilters",
    );
     Route::get(
        "filter/customer-products",
        "App\Http\Controllers\Api\V2\FilterController@customerProductFilters",
    );
    Route::get(
        "filter/cars",
        "App\Http\Controllers\Api\V2\FilterController@carFilters",
    );
    Route::get(
        "products/inhouse",
        "App\Http\Controllers\Api\V2\ProductController@inhouse",
    );
    Route::get(
        "products/sellers/all",
        "App\Http\Controllers\Api\V2\ProductController@index_seller"
    );
    Route::get(
        "products/seller/{id}",
        "App\Http\Controllers\Api\V2\ProductController@seller",
    );
    Route::get(
        "products/category/{slug}",
        "App\Http\Controllers\Api\V2\ProductController@categoryProducts",
    )->name("api.products.category");
    Route::get(
        "products/sub-category/{id}",
        "App\Http\Controllers\Api\V2\ProductController@subCategory",
    )->name("products.subCategory");
    Route::get(
        "products/sub-sub-category/{id}",
        "App\Http\Controllers\Api\V2\ProductController@subSubCategory",
    )->name("products.subSubCategory");
    Route::get(
        "products/brand/{slug}",
        "App\Http\Controllers\Api\V2\ProductController@brand",
    )->name("api.products.brand");
    Route::get(
        "products/todays-deal",
        "App\Http\Controllers\Api\V2\ProductController@todaysDeal",
    );
    Route::get(
        "products/featured",
        "App\Http\Controllers\Api\V2\ProductController@featured",
    );
    Route::get(
        "products/best-seller",
        "App\Http\Controllers\Api\V2\ProductController@bestSeller",
    );
    Route::get(
        "products/top-from-seller/{slug}",
        "App\Http\Controllers\Api\V2\ProductController@topFromSeller",
    );
    Route::get(
        "products/frequently-bought/{slug}",
        "App\Http\Controllers\Api\V2\ProductController@frequentlyBought",
    )->name("products.frequently_bought");

    Route::get(
        "products/featured-from-seller/{id}",
        "App\Http\Controllers\Api\V2\ProductController@newFromSeller",
    )->name("products.featuredromSeller");
    Route::get(
        "products/search",
        "App\Http\Controllers\Api\V2\ProductController@search",
    );
    Route::get(
        "products/variant/price",
        "App\Http\Controllers\Api\V2\ProductController@getPrice",
    );
    Route::get(
        "products/digital",
        "App\Http\Controllers\Api\V2\ProductController@digital",
    )->name("products.digital");
    Route::apiResource(
        "products",
        "App\Http\Controllers\Api\V2\ProductController",
    )->except(["store", "update", "destroy", "show"]);

    Route::get(
        "products/{product}/similar",
        "App\Http\Controllers\Api\V2\ProductController@similar",
    )->name('products.similar');

    Route::get(
        "products/{slug}",
        "App\Http\Controllers\Api\V2\ProductController@product_details",
    )->name('products.show');

    //Use this route outside of auth because initialy we created outside of auth we do not need auth initialy
    //We can't change it now because we didn't send token in header from mobile app.
    //We need the upload update Flutter app then we will write it in auth middleware.
    Route::controller(CustomerPackageController::class)->group(function () {
        Route::get("customer-packages", "customer_packages_list");
    });

    Route::get(
        "/reviews",
        "App\Http\Controllers\Api\V2\ReviewController@index",
    )->name("api.reviews.index");

    Route::get(
        "shops/details/{id}",
        "App\Http\Controllers\Api\V2\ShopController@info",
    )->name("shops.info");
    Route::get(
        "shops/products/all/{id}",
        "App\Http\Controllers\Api\V2\ShopController@allProducts",
    )->name("shops.allProducts");
    Route::get(
        "shops/products/top/{id}",
        "App\Http\Controllers\Api\V2\ShopController@topSellingProducts",
    )->name("shops.topSellingProducts");
    Route::get(
        "shops/products/featured/{id}",
        "App\Http\Controllers\Api\V2\ShopController@featuredProducts",
    )->name("shops.featuredProducts");
    Route::get(
        "shops/products/new/{id}",
        "App\Http\Controllers\Api\V2\ShopController@newProducts",
    )->name("shops.newProducts");
    Route::get(
        "shops/brands/{id}",
        "App\Http\Controllers\Api\V2\ShopController@brands",
    )->name("shops.brands");
    Route::apiResource("shops", "App\Http\Controllers\Api\V2\ShopController")
        ->only("index")
        ->names([
            "index" => "api.shops.index",
        ]);

    Route::get(
        "sliders",
        "App\Http\Controllers\Api\V2\SliderController@sliders",
    );
    Route::get(
        "banners-one",
        "App\Http\Controllers\Api\V2\SliderController@bannerOne",
    );
    Route::get(
        "banners-two",
        "App\Http\Controllers\Api\V2\SliderController@bannerTwo",
    );
    Route::get(
        "banners-three",
        "App\Http\Controllers\Api\V2\SliderController@bannerThree",
    );
    Route::get(
        "policies/privacy",
        "App\Http\Controllers\Api\V2\PolicyController@privacyPolicy",
    )->name("policies.privacy");
    Route::get(
        "policies/seller",
        "App\Http\Controllers\Api\V2\PolicyController@sellerPolicy",
    )->name("policies.seller");
    Route::get(
        "policies/support",
        "App\Http\Controllers\Api\V2\PolicyController@supportPolicy",
    )->name("policies.support");
    Route::get(
        "policies/return",
        "App\Http\Controllers\Api\V2\PolicyController@returnPolicy",
    )->name("policies.return");

    Route::post(
        "get-user-by-access_token",
        "App\Http\Controllers\Api\V2\UserController@getUserInfoByAccessToken",
    );

    Route::get(
        "cities",
        "App\Http\Controllers\Api\V2\AddressController@getCities",
    );
    Route::get(
        "states",
        "App\Http\Controllers\Api\V2\AddressController@getStates",
    );
    Route::get(
        "countries",
        "App\Http\Controllers\Api\V2\AddressController@getCountries",
    );

    Route::get(
        "cities-by-state/{state_id}",
        "App\Http\Controllers\Api\V2\AddressController@getCitiesByState",
    );
    Route::get(
        "states-by-country/{country_id}",
        "App\Http\Controllers\Api\V2\AddressController@getStatesByCountry",
    );

    // Route::post('coupon/apply', 'App\Http\Controllers\Api\V2\CouponController@apply')->middleware('auth:sanctum');

    // Unified Payment Routes (New Architecture)
    Route::prefix("payment")
        ->name("api.payment.")
        ->group(function () {
            Route::post(
                "pay",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@pay",
            )->name("pay");
            Route::post(
                "status",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@checkStatus",
            )->name("status");
            Route::post(
                "validate",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@validatePaymentData",
            )->name("validate");

            // One step payment routes
            Route::post(
                "cart",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@cartPayment",
            )->name("cart");
            Route::post(
                "car-reservation",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@carReservationPayment",
            )->name("car-reservation");
            Route::post(
                "car-inspection",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@carInspectionPayment",
            )->name("car-inspection");
             Route::post(
                "insurance-deposit",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@insuranceDepositPayment",
            )->name("insurance-deposit");
             Route::post(
                "auction-invoice",
                "App\Http\Controllers\Api\V2\Payment\PaymentController@auctionInvoicePayment",
            )->name("auction-invoice");
        });

    Route::post(
        "email/resend",
        "App\Http\Controllers\Api\V2\UserController@send_new_verification_link",
    )->name("api.email.resend");
    Route::post(
        "email/check",
        "App\Http\Controllers\Api\V2\UserController@check_email_verification",
    );
    Route::prefix('offline/payment')
    ->name('api.offline.payment.')
    ->group(function(){

        Route::post(
        "offline/payment/submit",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@submit",
    )->name("submit");
    // One step payment routes
    Route::post(
        "cart",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@cartPayment",
    )->name("cart");
    Route::post(
        "car-reservation",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@carReservationPayment",
    )->name("car-reservation");
    Route::post(
        "car-inspection",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@carInspectionPayment",
    )->name("car-inspection");
        Route::post(
        "insurance-deposit",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@insuranceDepositPayment",
    )->name("insurance-deposit");
        Route::post(
        "auction-invoice",
        "App\Http\Controllers\Api\V2\Payment\OfflinePaymentController@auctionInvoicePayment",
    )->name("auction-invoice");

    });


    Route::get(
        "flash-deals",
        "App\Http\Controllers\Api\V2\FlashDealController@index",
    );
    Route::get(
        "flash-deals/info/{slug}",
        "App\Http\Controllers\Api\V2\FlashDealController@info",
    );
    Route::get(
        "flash-deal-products/{id}",
        "App\Http\Controllers\Api\V2\FlashDealController@products",
    );

    //Addon list
    Route::get(
        "addon-list",
        "App\Http\Controllers\Api\V2\ConfigController@addon_list",
    );
    //Activated social login list
    Route::get(
        "activated-social-login",
        "App\Http\Controllers\Api\V2\ConfigController@activated_social_login",
    );

    //Business Sttings list
    Route::post(
        "business-settings",
        "App\Http\Controllers\Api\V2\ConfigController@business_settings",
    );
    //Pickup Point list
    Route::get(
        "pickup-list",
        "App\Http\Controllers\Api\V2\ShippingController@pickup_list",
    );


    // customer file upload
    Route::controller(CustomerFileUploadController::class)
        ->middleware("auth:sanctum")
        ->group(function () {
            Route::post("file/upload", "upload");
            Route::get("file/all", "index");
            Route::get("file/delete/{id}", "destroy");
        });

    Route::controller(MobileAppController::class)->group(function () {
        Route::get("app-version/{os}", "get_app_version");
    });

    Route::get(
        "popups",
        "App\Http\Controllers\Api\V2\DynamicPopupController@index",
    );

    // Customer Product API Routes
    Route::prefix('customer/customer-products')->name('api.v2.customer-products.')->group(function () {
        // Authenticated customer endpoints
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\V2\CustomerProductController@index')->name('index');
            Route::post('/store', 'App\Http\Controllers\Api\V2\CustomerProductController@store')->name('store');
            Route::get('/show/{customerProduct}', 'App\Http\Controllers\Api\V2\CustomerProductController@show')->name('show');
            Route::put('/update/{customerProduct}', 'App\Http\Controllers\Api\V2\CustomerProductController@update')->name('update');
            Route::get('/destroy/{customerProduct}', 'App\Http\Controllers\Api\V2\CustomerProductController@destroy')->name('destroy');

        });
    });

    // Public Customer Product API Routes (no authentication required)
    Route::prefix('customer-products')->name('api.v2.public.customer-products.')->group(function () {
        Route::get('/', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@index')->name('index');
        Route::get('/featured', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@featured')->name('featured');
        Route::get('/stats', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@stats')->name('stats');
        Route::get('/search', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@search')->name('search');
        Route::get('/category/{categoryId}', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@byCategory')->name('by-category');
        Route::get('/location/{stateId}/{cityId?}', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@byLocation')->name('by-location');
        Route::get('/show/{customerProduct}', 'App\Http\Controllers\Api\V2\PublicCustomerProductController@show')->name('show');
    });

    //faqs
    Route::get('faqs', 'App\Http\Controllers\Api\V2\FaqController@index');
    Route::get('faqs/types', 'App\Http\Controllers\Api\V2\FaqController@types');
});

Route::fallback(function () {
    return response()->json([
        "data" => [],
        "success" => false,
        "status" => 404,
        "message" => "Invalid Route",
    ]);
});
