<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\Admin\Report\EarningReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BrandBulkUploadController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomAlertController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Admin\CustomerProductController;
use App\Http\Controllers\Admin\AuctionRoomController;
use App\Http\Controllers\Admin\AuctionListingRequestController;
use App\Http\Controllers\Admin\AuctionOfferController;
use App\Http\Controllers\Admin\AuctionMonitoringController;
use App\Http\Controllers\Admin\AdminAuctionInvoiceController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\DynamicPopupController;
use App\Http\Controllers\ExportsController;
use App\Http\Controllers\FlashDealController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MeasurementPointsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PickupPointController;
use App\Http\Controllers\ProductBulkUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerWithdrawRequestController;
use App\Http\Controllers\SizeChartController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\Payment\FloosakController;
use App\Http\Controllers\ExternalOrderPriceAdjustmentController;
use App\Http\Controllers\MobileAppController;
use App\Http\Controllers\RequestedProductController;
use Laravel\Horizon\Horizon;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
use App\Http\Controllers\Admin\Report\AuctionRoomReportController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FaqCategoryController;

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register admin routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
//Update Routes
Route::controller(UpdateController::class)->group(function () {
    Route::post('/update', 'step0')->name('update');
    Route::get('/update/step1', 'step1')->name('update.step1');
    Route::get('/update/step2', 'step2')->name('update.step2');
    Route::get('/update/step3', 'step3')->name('update.step3');
    Route::post('/purchase_code', 'purchase_code')->name('update.code');
});

Route::get('/admin', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard')->middleware(['auth', 'admin', 'prevent-back-history']);
Route::get('/admin/dashboard/external-orders', [AdminController::class, 'getExternalOrdersData'])->middleware(['auth', 'admin'])->name('admin.dashboard.external_orders');
Route::get('/admin/dashboard/customers-stats', [AdminController::class, 'getCustomersData'])->middleware(['auth', 'admin'])->name('admin.dashboard.customers_data');
Route::get('/admin/customer-growth-data', [AdminController::class, 'getCustomerGrowthData'])->middleware(['auth', 'admin'])->name('admin.customer.growth.data');
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin', 'prevent-back-history']], function () {

    // category
    Route::resource('categories', CategoryController::class)->except(['edit', 'destroy']);
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/get-sub-categories', 'getSubCategories')->name('categories.get-sub-categories');
        Route::get('/categories-brands', 'getCategoryBrands')->name('categories.get-category-brands');
        Route::get('/categories/edit/{id}', 'edit')->name('categories.edit');
        Route::get('/categories/destroy/{id}', 'destroy')->name('categories.destroy');
        Route::post('/categories/featured', 'updateFeatured')->name('categories.featured');
        Route::post('/categories/categoriesByType', 'categoriesByType')->name('categories.categories-by-type');
        // category-wise discount set
        Route::get('/categories-wise-product-discount', 'categoriesWiseProductDiscount')->name('categories_wise_product_discount');
    });

    // Brand
    Route::resource('brands', BrandController::class)->except(['edit', 'destroy']);
    Route::controller(BrandController::class)->group(function () {
        Route::get('/brands/edit/{id}', 'edit')->name('brands.edit');
        Route::get('/brands/destroy/{id}', 'destroy')->name('brands.destroy');
    });

    Route::controller(BrandBulkUploadController::class)->group(function () {
        Route::get('/brand-bulk-upload', 'index')->name('brand_bulk_upload.index');
        Route::post('/brand-bulk-upload/store', 'bulk_upload')->name('brand_bulk_upload');
    });

    Route::controller(AdminController::class)->group(function () {
        Route::post('/dashboard/top-category-products-section', 'top_category_products_section')->name('dashboard.top_category_products_section');
        Route::post('/dashboard/inhouse-top-brands', 'inhouse_top_brands')->name('dashboard.inhouse_top_brands');
        Route::post('/dashboard/inhouse-top-categories', 'inhouse_top_categories')->name('dashboard.inhouse_top_categories');
        Route::post('/dashboard/top-sellers-products-section', 'top_sellers_products_section')->name('dashboard.top_sellers_products_section');
        Route::post('/dashboard/top-brands-products-section', 'top_brands_products_section')->name('dashboard.top_brands_products_section');
    });

    // Products
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products/admin', 'admin_products')->name('products.admin');
        Route::get('/products/seller/{product_type?}', 'seller_products')->name('products.seller');
        Route::get('/products/all', 'all_products')->name('products.all');
        Route::get('/products/create', 'create')->name('products.create');
        Route::post('/products/store/', 'store')->name('products.store');
        Route::get('/products/admin/{id}/edit', 'admin_product_edit')->name('products.admin.edit');
        Route::get('/products/seller/{id}/edit', 'seller_product_edit')->name('products.seller.edit');
        Route::post('/products/update/{product}', 'update')->name('products.update');
        Route::post('/products/todays_deal', 'updateTodaysDeal')->name('products.todays_deal');
        Route::post('/products/featured', 'updateFeatured')->name('products.featured');
        Route::post('/products/published', 'updatePublished')->name('products.published');
        Route::post('/products/approved', 'updateProductApproval')->name('products.approved');
        Route::post('/products/get_products_by_subcategory', 'get_products_by_subcategory')->name('products.get_products_by_subcategory');
        Route::get('/products/duplicate/{id}', 'duplicate')->name('products.duplicate');
        Route::get('/products/destroy/{id}', 'destroy')->name('products.destroy');
        Route::post('/bulk-product-delete', 'bulk_product_delete')->name('bulk-product-delete');

        Route::post('/products/sku_combination', 'sku_combination')->name('products.sku_combination');
        Route::post('/products/sku_combination_edit', 'sku_combination_edit')->name('products.sku_combination_edit');
        Route::post('/products/add-more-choice-option', 'add_more_choice_option')->name('products.add-more-choice-option');
        Route::post('/product-search', 'product_search')->name('product.search');
        Route::post('/get-selected-products', 'get_selected_products')->name('get-selected-products');
        Route::post('/set-product-discount', 'setProductDiscount')->name('set_product_discount');
        Route::get('/products/export', 'export')->name('products.export');
    });

    // Digital Product
    Route::resource('digitalproducts', DigitalProductController::class)->except(['edit', 'destroy']);
    Route::controller(DigitalProductController::class)->group(function () {
        Route::get('/digitalproducts/edit/{id}', 'edit')->name('digitalproducts.edit');
        Route::get('/digitalproducts/destroy/{id}', 'destroy')->name('digitalproducts.destroy');
        Route::get('/digitalproducts/download/{id}', 'download')->name('digitalproducts.download');
    });

    // Requested Products
    Route::resource('requested-products', RequestedProductController::class)->except('destroy');
    Route::controller(RequestedProductController::class)->group(function () {
        Route::get('/requested-products/destroy/{requested_product}', 'destroy')->name('requested-products.destroy');
        Route::post('/requested-products/update-status', 'updateStatus')->name('requested-products.update-status');
        Route::post('/requested-products/bulk-delete', 'bulkDelete')->name('requested-products.bulk-delete');
    });

    Route::controller(ProductBulkUploadController::class)->group(function () {
        //Product Export
        Route::get('/product-bulk-export', 'export')->name('product_bulk_export.index');

        //Product Bulk Upload
        Route::get('/product-bulk-upload/index', 'index')->name('product_bulk_upload.index');
        Route::post('/bulk-product-upload', 'bulk_upload')->name('bulk_product_upload');
        Route::get('/product-csv-download/{type}', 'import_product')->name('product_csv.download');
        Route::get('/vendor-product-csv-download/{id}', 'import_vendor_product')->name('import_vendor_product.download');
        Route::group(['prefix' => 'bulk-upload/download'], function () {
            Route::get('/category', 'pdf_download_category')->name('pdf.download_category');
            Route::get('/brand', 'pdf_download_brand')->name('pdf.download_brand');
            Route::get('/seller', 'pdf_download_seller')->name('pdf.download_seller');
        });
    });

    // Seller
    Route::resource('sellers', SellerController::class)->except('destroy');
    Route::controller(SellerController::class)->group(function () {
        Route::get('sellers_ban/{id}', 'ban')->name('sellers.ban');
        Route::get('/sellers/destroy/{id}', 'destroy')->name('sellers.destroy');
        Route::post('/bulk-seller-delete', 'bulk_seller_delete')->name('bulk-seller-delete');
        Route::get('/sellers/view/{id}/verification', 'show_verification_request')->name('sellers.show_verification_request');
        Route::get('/sellers/approve/{id}', 'approve_seller')->name('sellers.approve');
        Route::get('/sellers/reject/{id}', 'reject_seller')->name('sellers.reject');
        Route::get('/sellers/login/{id}', 'login')->name('sellers.login');
        Route::post('/sellers/payment_modal', 'payment_modal')->name('sellers.payment_modal');
        Route::post('/sellers/profile_modal', 'profile_modal')->name('sellers.profile_modal');
        Route::post('/sellers/approved', 'updateApproved')->name('sellers.approved');
        Route::get('/seller-bulk-export', 'export')->name('shops-export');
    });

    // Seller Payout
    Route::controller(PayoutController::class)->group(function () {
        Route::get('/seller/payments', 'payment_histories')->name('sellers.payment_histories');
        Route::get('/seller/payments/show/{id}', 'show')->name('sellers.payment_history');
        Route::get('/payment-bulk-export', 'paymentBulkExport')->name('payment-bulk-export');
    });

    // Seller Withdraw Request
    Route::resource('/withdraw_requests', SellerWithdrawRequestController::class);
    Route::controller(SellerWithdrawRequestController::class)->group(function () {
        Route::get('/withdraw_requests_all', 'index')->name('withdraw_requests_all');
        Route::post('/withdraw_request/payment_modal', 'payment_modal')->name('withdraw_request.payment_modal');
        Route::post('/withdraw_request/message_modal', 'message_modal')->name('withdraw_request.message_modal');
    });

    // Customer
    Route::resource('customers', CustomerController::class)->except('destroy', 'show');
    Route::controller(CustomerController::class)->group(function () {
        Route::get('customers_ban/{customer}', 'ban')->name('customers.ban');
        Route::get('/customers/login/{id}', 'login')->name('customers.login');
        Route::get('/customers/destroy/{id}', 'destroy')->name('customers.destroy');
        Route::post('/bulk-customer-delete', 'bulk_customer_delete')->name('bulk-customer-delete');
        Route::get('/customer-bulk-export', 'customerBulkExport')->name('customer-bulk-export');
        Route::get('customers-balance', 'customers_balance')->name('customers.customers_balance');
        Route::post('/customers/verify-phone', 'verifyPhone')->name('customers.verify-phone');
        Route::post('/customers/unverify-phone', 'unverifyPhone')->name('customers.unverify-phone');
        Route::post('/customers/verify-email', 'verifyEmail')->name('customers.verify-email');
        Route::post('/customers/unverify-email', 'unverifyEmail')->name('customers.unverify-email');
        Route::get('/customers/ajax/details', 'getDetails')->name('customers.ajax.details');
        Route::get('/customers/{id}/details', 'show')->name('customers.details');
        Route::get('/customers/cancel-deletion-request/{id}', 'cancel_deletion_request')->name('customers.cancel_deletion');
    });

    // Newsletter
    Route::controller(NewsletterController::class)->group(function () {
        Route::get('/newsletter', 'index')->name('newsletters.index');
        Route::post('/newsletter/send', 'send')->name('newsletters.send');
        Route::post('/newsletter/test/smtp', 'testEmail')->name('test.smtp');
    });

    // Dynamic Popup
    Route::resource('dynamic-popups', DynamicPopupController::class)->except('destroy');
    Route::controller(DynamicPopupController::class)->group(function () {
        Route::get('/dynamic-popups/destroy/{id}', 'destroy')->name('dynamic-popups.destroy');
        Route::post('/bulk-dynamic-popup-delete', 'bulk_dynamic_popup_delete')->name('bulk-dynamic-popup-delete');
        Route::post('/dynamic-popups-update-status', 'update_status')->name('dynamic-popups.update-status');
    });

    // Custom Alert
    Route::resource('custom-alerts', CustomAlertController::class)->except('destroy');
    Route::controller(CustomAlertController::class)->group(function () {
        Route::get('/custom-alerts/destroy/{id}', 'destroy')->name('custom-alerts.destroy');
        Route::post('/bulk-custom-alerts-delete', 'bulk_custom_alerts_delete')->name('bulk-custom-alerts-delete');
        Route::post('/custom-alerts-update-status', 'update_status')->name('custom-alerts.update-status');
    });

    Route::resource('profile', ProfileController::class);

    // PDF Settings
    Route::get('/pdf-settings', [BusinessSettingsController::class, 'pdf_settings'])->name('pdf_settings.index');

    // Business Settings
    Route::controller(BusinessSettingsController::class)->group(function () {
        Route::post('/business-settings/update', 'update')->name('business_settings.update');
        Route::get('/external-websites', 'external_websites')->name('business_settings.external_websites');
        Route::post('/external-websites/update', 'update_external_websites')->name('business_settings.external_websites.update');
        Route::post('/business-settings/update/activation', 'updateActivationSettings')->name('business_settings.update.activation');
        Route::post('/payment-activation', 'updatePaymentActivationSettings')->name('payment.activation');
        Route::get('/general-setting', 'general_setting')->name('general_setting.index');
        Route::get('/activation', 'activation')->name('activation.index');
        Route::get('/payment-method', 'payment_method')->name('payment_method.index');
        Route::get('/file_system', 'file_system')->name('file_system.index');
        Route::get('/social-login', 'social_login')->name('social_login.index');
        Route::get('/smtp-settings', 'smtp_settings')->name('smtp_settings.index');
        Route::get('/google-analytics', 'google_analytics')->name('google_analytics.index');
        Route::get('/google-recaptcha', 'google_recaptcha')->name('google_recaptcha.index');
        Route::get('/google-map', 'google_map')->name('google-map.index');
        Route::get('/google-firebase', 'google_firebase')->name('google-firebase.index');

        //change jaib password
        Route::post('/jaib-change-password', 'change_jaib_password')->name('jaib.change-password');
        // floosak key managment
        Route::get('/floosak-check-key', 'check_floosak_key')->name('floosak.check-key');
        Route::get('/floosak-get-key', 'get_new_floosak_key')->name('floosak.get-new-key');
        Route::post('/floosak-verify-key', 'verify_floosak_key')->name('floosak.verify-key');

        //Facebook Settings
        Route::get('/facebook-chat', 'facebook_chat')->name('facebook_chat.index');
        Route::post('/facebook_chat', 'facebook_chat_update')->name('facebook_chat.update');
        Route::get('/facebook-comment', 'facebook_comment')->name('facebook-comment');
        Route::post('/facebook-comment', 'facebook_comment_update')->name('facebook-comment.update');
        Route::post('/facebook_pixel', 'facebook_pixel_update')->name('facebook_pixel.update');

        Route::post('/env_key_update', 'env_key_update')->name('env_key_update.update');
        Route::post('/payment_method_update', 'payment_method_update')->name('payment_method.update');
        Route::post('/google_analytics', 'google_analytics_update')->name('google_analytics.update');
        Route::post('/google_recaptcha', 'google_recaptcha_update')->name('google_recaptcha.update');
        Route::post('/google-map', 'google_map_update')->name('google-map.update');
        Route::post('/google-firebase', 'google_firebase_update')->name('google-firebase.update');

        Route::get('/verification/form', 'seller_verification_form')->name('seller_verification_form.index');
        Route::post('/verification/form', 'seller_verification_form_update')->name('seller_verification_form.update');
        Route::get('/vendor_commission', 'vendor_commission')->name('business_settings.vendor_commission');
        Route::post('/vendor_commission_update', 'vendor_commission_update')->name('business_settings.vendor_commission.update');

        //Shipping Configuration
        Route::get('/shipping_configuration', 'shipping_configuration')->name('shipping_configuration.index');
        Route::post('/shipping_configuration/update', 'shipping_configuration_update')->name('shipping_configuration.update');

        // Order Configuration
        Route::get('/order-configuration', 'order_configuration')->name('order_configuration.index');

        //wallet configuration
        Route::get('/wallet-configuration', 'wallet_configuration')->name('wallet_configuration.index');
    });


    //Currency
    Route::controller(CurrencyController::class)->group(function () {
        Route::get('/currency', 'currency')->name('currency.index');
        Route::post('/currency/update', 'updateCurrency')->name('currency.update');
        Route::post('/your-currency/update', 'updateYourCurrency')->name('your_currency.update');
        Route::get('/currency/create', 'create')->name('currency.create');
        Route::post('/currency/store', 'store')->name('currency.store');
        Route::post('/currency/currency_edit', 'edit')->name('currency.edit');
        Route::post('/currency/update_status', 'update_status')->name('currency.update_status');
    });

    //Tax
    Route::resource('tax', TaxController::class)->except(['destroy', 'edit']);
    Route::controller(TaxController::class)->group(function () {
        Route::get('/tax/edit/{id}', 'edit')->name('tax.edit');
        Route::get('/tax/destroy/{id}', 'destroy')->name('tax.destroy');
        Route::post('tax-status', 'change_tax_status')->name('taxes.tax-status');
    });

    // Language
    Route::resource('/languages', LanguageController::class)->except(['update', 'destroy']);
    Route::controller(LanguageController::class)->group(function () {
        Route::post('/languages/{id}/update', 'update')->name('languages.update');
        Route::get('/languages/destroy/{id}', 'destroy')->name('languages.destroy');
        Route::post('/languages/update_rtl_status', 'update_rtl_status')->name('languages.update_rtl_status');
        Route::post('/languages/update-status', 'update_status')->name('languages.update-status');
        Route::post('/languages/key_value_store', 'key_value_store')->name('languages.key_value_store');

        //App Trasnlation
        Route::post('/languages/app-translations/import', 'importEnglishFile')->name('app-translations.import');
        Route::get('/languages/app-translations/show/{id}', 'showAppTranlsationView')->name('app-translations.show');
        Route::post('/languages/app-translations/key_value_store', 'storeAppTranlsation')->name('app-translations.store');
        Route::get('/languages/app-translations/export/{id}', 'exportARBFile')->name('app-translations.export');
    });


    // website setting
    Route::group(['prefix' => 'website'], function () {
        Route::controller(WebsiteController::class)->group(function () {
            Route::get('/footer', 'footer')->name('website.footer');
            Route::get('/header', 'header')->name('website.header');
            Route::get('/appearance', 'appearance')->name('website.appearance');
            Route::get('/authentication-layout-settings', 'authentication_layout_settings')->name('website.authentication-layout-settings');
            Route::get('/pages', 'pages')->name('website.pages');
        });

        // Custom Page
        Route::resource('custom-pages', PageController::class)->except(['edit', 'destroy']);
        Route::controller(PageController::class)->group(function () {
            Route::get('/custom-pages/edit/{id}', 'edit')->name('custom-pages.edit');
            Route::get('/custom-pages/destroy/{id}', 'destroy')->name('custom-pages.destroy');
        });
    });

    // Staff Roles
    Route::resource('roles', RoleController::class)->except(['edit', 'destroy']);
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles/edit/{id}', 'edit')->name('roles.edit');
        Route::get('/roles/destroy/{id}', 'destroy')->name('roles.destroy');

        // Add Permissiom
        Route::post('/roles/add_permission', 'add_permission')->name('roles.permission');
    });

    // Staff
    Route::resource('staffs', StaffController::class)->except('destroy');
    Route::get('/staffs/destroy/{id}', [StaffController::class, 'destroy'])->name('staffs.destroy');

    // Flash Deal
    Route::resource('flash_deals', FlashDealController::class)->except(['edit', 'destroy']);
    Route::controller(FlashDealController::class)->group(function () {
        Route::get('/flash_deals/edit/{id}', 'edit')->name('flash_deals.edit');
        Route::get('/flash_deals/destroy/{id}', 'destroy')->name('flash_deals.destroy');
        Route::post('/flash_deals/update_status', 'update_status')->name('flash_deals.update_status');
        Route::post('/flash_deals/update_featured', 'update_featured')->name('flash_deals.update_featured');
        Route::post('/flash_deals/product_discount', 'product_discount')->name('flash_deals.product_discount');
        Route::post('/flash_deals/product_discount_edit', 'product_discount_edit')->name('flash_deals.product_discount_edit');
    });

    //Subscribers
    Route::controller(SubscriberController::class)->group(function () {
        Route::get('/subscribers', 'index')->name('subscribers.index');
        Route::get('/subscribers/destroy/{id}', 'destroy')->name('subscriber.destroy');
    });

    // Order
    Route::resource('orders', OrderController::class)->except('destroy');
    Route::controller(OrderController::class)->group(function () {
        // All Orders
        Route::get('/all_orders', 'all_orders')->name('all_orders.index');
        Route::get('/inhouse-orders', 'all_orders')->name('inhouse_orders.index');
        Route::get('/seller_orders', 'all_orders')->name('seller_orders.index');
        Route::get('orders_by_pickup_point', 'all_orders')->name('pick_up_point.index');

        Route::get('/orders/{id}/show', 'show')->name('all_orders.show');
        Route::get('/inhouse-orders/{id}/show', 'show')->name('inhouse_orders.show');
        Route::get('/seller_orders/{id}/show', 'show')->name('seller_orders.show');
        Route::get('/orders_by_pickup_point/{id}/show', 'show')->name('pick_up_point.order_show');

        Route::post('/bulk-order-status', 'bulk_order_status')->name('bulk-order-status');

        Route::post('/bulk-order-delete', 'bulk_order_delete')->name('bulk-order-delete');

        Route::get('/orders/destroy/{id}', 'destroy')->name('orders.destroy');
        Route::post('/orders/details', 'order_details')->name('orders.details');
        Route::post('/orders/update_delivery_status', 'update_delivery_status')->name('orders.update_delivery_status');
        Route::post('/orders/update_payment_status', 'update_payment_status')->name('orders.update_payment_status');
        Route::post('/orders/update_tracking_code', 'update_tracking_code')->name('orders.update_tracking_code');

        //Delivery Boy Assign
        Route::post('/orders/delivery-boy-assign', 'assign_delivery_boy')->name('orders.delivery-boy-assign');

        // Order bulk export
        Route::get('/order-bulk-export', 'orderBulkExport')->name('order-bulk-export');
    });

    Route::get('external-order-invoice/{order_id}', [InvoiceController::class, 'external_order_invoice'])->name('external_order_invoice.download');
    Route::get('users/ajax/search', [CustomerController::class, 'ajaxSearch'])->name('users.ajax.search');

    Route::post('/pay_to_seller', [CommissionController::class, 'pay_to_seller'])->name('commissions.pay_to_seller');

    //Reports
    Route::controller(ReportController::class)->group(function () {
        Route::get('/in_house_sale_report', 'in_house_sale_report')->name('in_house_sale_report.index');
        Route::get('/seller_sale_report', 'seller_sale_report')->name('seller_sale_report.index');
        Route::get('/stock_report', 'stock_report')->name('stock_report.index');
        Route::get('/wish_report', 'wish_report')->name('wish_report.index');
        Route::get('/user_search_report', 'user_search_report')->name('user_search_report.index');
        Route::get('/commission-log', 'commission_history')->name('commission-log.index');
        Route::get('/external-commission-log', 'external_commission_history')->name('external-commission-log.index');
        Route::get('/wallet-history', 'wallet_transaction_history')->name('wallet-history.index');
        Route::get('/export-wallet-transactions', 'walletTransactionBulkExport')->name('wallet-transation-export');
        Route::get('/refund-history', 'refund_history_report')->name('refund-history.index');
        Route::get('/export-sales', 'export_sales_report')->name('export.sales.report');
    });

    // Earning Report
    Route::group(['prefix' => 'reports'], function () {
        Route::get('/earning-payout-report', [EarningReportController::class, 'index'])->name('earning_payout_report.index');
        Route::post('/earning-payout-report/net-sales', [EarningReportController::class, 'net_sales']);
        Route::post('/earning-payout-report/payouts', [EarningReportController::class, 'payouts']);
        Route::post('/earning-payout-report/sale-analytic', [EarningReportController::class, 'sale_analytic']);
        Route::post('/earning-payout-report/payout-analytic', [EarningReportController::class, 'payout_analytic']);
    });

    //Blog Section
    //Blog cateory
    Route::resource('blog-category', BlogCategoryController::class)->except('destroy');
    Route::get('/blog-category/destroy/{id}', [BlogCategoryController::class, 'destroy'])->name('blog-category.destroy');

    // Blog
    Route::resource('blog', BlogController::class)->except('destroy');
    Route::controller(BlogController::class)->group(function () {
        Route::get('/blog/destroy/{id}', 'destroy')->name('blog.destroy');
        Route::post('/blog/change-status', 'change_status')->name('blog.change-status');
    });

    // FAQs
    Route::resource('faqs', FaqController::class)->except(['edit', 'destroy']);
    Route::controller(FaqController::class)->group(function () {
        Route::get('/faqs/edit/{faq}', 'edit')->name('faqs.edit');
        Route::get('/faqs/duplicate/{faq}', 'duplicate')->name('faqs.duplicate');
        Route::get('/faqs/destroy/{faq}', 'destroy')->name('faqs.destroy');
        Route::post('/faqs/toggle-status', 'toggleStatus')->name('faqs.toggle-status');
    });

    //Coupons
    Route::resource('coupon', CouponController::class)->except('destroy');
    Route::controller(CouponController::class)->group(function () {
        Route::post('/coupon/update-status', 'updateStatus')->name('coupon.update_status');
        Route::get('/coupon/destroy/{id}', 'destroy')->name('coupon.destroy');

        //Coupon Form
        Route::post('/coupon/get_form', 'get_coupon_form')->name('coupon.get_coupon_form');
        Route::post('/coupon/get_form_edit', 'get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
    });

    //Reviews
    Route::controller(ReviewController::class)->group(function () {
        Route::get('/reviews', 'index')->name('reviews.index');
        Route::post('/reviews/published', 'updatePublished')->name('reviews.published');
    });

    //Support_Ticket
    Route::controller(SupportTicketController::class)->group(function () {
        Route::get('support_ticket/', 'admin_index')->name('support_ticket.admin_index');
        Route::get('support_ticket/{id}/show', 'admin_show')->name('support_ticket.admin_show');
        Route::post('support_ticket/reply', 'admin_store')->name('support_ticket.admin_store');
    });

    //Pickup_Points
    Route::resource('pick_up_points', PickupPointController::class)->except(['edit', 'destroy']);
    Route::controller(PickupPointController::class)->group(function () {
        Route::get('/pick_up_points/edit/{id}', 'edit')->name('pick_up_points.edit');
        Route::get('/pick_up_points/destroy/{id}', 'destroy')->name('pick_up_points.destroy');
    });

    //conversation of seller customer
    Route::controller(ConversationController::class)->group(function () {
        Route::get('conversations', 'admin_index')->name('conversations.admin_index');
        Route::get('conversations/{id}/show', 'admin_show')->name('conversations.admin_show');
        Route::get('get-new-messages/{conversation_id}/{last_message_id}', 'get_new_messages')->name('admin.get_new_messages');
        Route::get('/conversations/destroy/{id}', 'destroy')->name('conversations.destroy');
    });

    // product Queries show on Admin panel
    Route::controller(ProductQueryController::class)->group(function () {
        Route::get('/product-queries', 'index')->name('product_query.index');
        Route::get('/product-queries/{id}', 'show')->name('product_query.show');
        Route::put('/product-queries/{id}', 'reply')->name('product_query.reply');
    });

    // Product Attribute
    Route::resource('attributes', AttributeController::class)->except(['edit', 'destroy']);
    Route::controller(AttributeController::class)->group(function () {
        Route::get('/attributes/edit/{id}', 'edit')->name('attributes.edit');
        Route::get('/attributes/destroy/{id}', 'destroy')->name('attributes.destroy');

        //Attribute Value
        Route::post('/store-attribute-value', 'store_attribute_value')->name('store-attribute-value');
        Route::get('/edit-attribute-value/{id}', 'edit_attribute_value')->name('edit-attribute-value');
        Route::post('/update-attribute-value/{id}', 'update_attribute_value')->name('update-attribute-value');
        Route::get('/destroy-attribute-value/{id}', 'destroy_attribute_value')->name('destroy-attribute-value');

        //Colors
        Route::get('/colors', 'colors')->name('colors');
        Route::post('/colors/store', 'store_color')->name('colors.store');
        Route::get('/colors/edit/{id}', 'edit_color')->name('colors.edit');
        Route::post('/colors/update/{id}', 'update_color')->name('colors.update');
        Route::get('/colors/destroy/{id}', 'destroy_color')->name('colors.destroy');
    });

    // Size Chart
    Route::resource('size-charts', SizeChartController::class)->except('destroy');
    Route::get('/size-charts/destroy/{id}',  [SizeChartController::class, 'destroy'])->name('size-charts.destroy');
    Route::post('size-charts/get-combination',   [SizeChartController::class, 'get_combination'])->name('size-charts.get-combination');

    // Measurement Points
    Route::resource('measurement-points', MeasurementPointsController::class)->except('destroy');
    Route::get('/measurement-points/destroy/{id}',  [MeasurementPointsController::class, 'destroy'])->name('measurement-points.destroy');



    // Countries
    Route::resource('countries', CountryController::class);
    Route::post('/countries/status', [CountryController::class, 'updateStatus'])->name('countries.status');

    // States
    Route::resource('states', StateController::class);
    Route::post('/states/status', [StateController::class, 'updateStatus'])->name('states.status');

    // Carriers
    Route::resource('carriers', CarrierController::class)->except('destroy');
    Route::controller(CarrierController::class)->group(function () {
        Route::get('/carriers/destroy/{id}', 'destroy')->name('carriers.destroy');
        Route::post('/carriers/update_status', 'updateStatus')->name('carriers.update_status');
    });


    // Zones
    Route::resource('zones', ZoneController::class)->except('destroy');
    Route::get('/zones/destroy/{id}', [ZoneController::class, 'destroy'])->name('zones.destroy');

    Route::resource('cities', CityController::class)->except(['edit', 'destroy']);
    Route::controller(CityController::class)->group(function () {
        Route::get('/cities/edit/{id}', 'edit')->name('cities.edit');
        Route::get('/cities/destroy/{id}', 'destroy')->name('cities.destroy');
        Route::post('/cities/status', 'updateStatus')->name('cities.status');
    });

    Route::view('/system/update', 'backend.system.update')->name('system_update');
    Route::view('/system/server-status', 'backend.system.server_status')->name('system_server');




    // uploaded files
    Route::resource('/uploaded-files', AizUploadController::class)->except('destroy');;
    Route::controller(AizUploadController::class)->group(function () {
        Route::any('/uploaded-files/file-info', 'file_info')->name('uploaded-files.info');
        Route::get('/uploaded-files/destroy/{id}', 'destroy')->name('uploaded-files.destroy');
        Route::post('/bulk-uploaded-files-delete', 'bulk_uploaded_files_delete')->name('bulk-uploaded-files-delete');
        Route::get('/all-file', 'all_file');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('/all-notifications', 'adminIndex')->name('admin.all-notifications');
        Route::get('/notification-settings', 'notificationSettings')->name('notification.settings');

        Route::post('/notifications/bulk-delete', 'bulkDeleteAdmin')->name('admin.notifications.bulk_delete');
        Route::get('/notification/read-and-redirect/{id}', 'readAndRedirect')->name('admin.notification.read-and-redirect');

        // AJAX notification fetching
        Route::post('/notifications/fetch', 'fetchNotifications')->name('admin.notifications.fetch');

        Route::get('/custom-notification', 'customNotification')->name('custom_notification');
        Route::post('/custom-notification/send', 'sendCustomNotification')->name('custom_notification.send');

        //shop custom notification
        Route::get('/shop-custom-notification', 'customShopNotification')->name('shop_custom_notification');
        Route::post('/shop-custom-notification/send', 'sendShopCustomNotification')->name('shop_custom_notification.send');

        Route::get('/custom-notification/history', 'customNotificationHistory')->name('custom_notification.history');
        Route::get('/custom-notifications.delete/{identifier}', 'customNotificationSingleDelete')->name('custom-notifications.delete');
        Route::post('/custom-notifications.bulk_delete', 'customNotificationBulkDelete')->name('custom-notifications.bulk_delete');
        Route::post('/custom-notified-customers-list', 'customNotifiedCustomersList')->name('custom_notified_customers_list');
    });

    Route::resource('notification-type', NotificationTypeController::class)->except(['edit', 'destroy']);
    Route::controller(NotificationTypeController::class)->group(function () {
        Route::get('/notification-type/edit/{id}', 'edit')->name('notification-type.edit');
        Route::post('/notification-type/update-status', 'updateStatus')->name('notification-type.update-status');
        Route::get('/notification-type/destroy/{id}', 'destroy')->name('notification-type.destroy');
        Route::post('/notification-type/bulk_delete', 'bulkDelete')->name('notifications-type.bulk_delete');
        Route::post('/notification-type.get-default-text', 'getDefaulText')->name('notification_type.get_default_text');
    });

    Route::get('/clear-cache', [AdminController::class, 'clearCache'])->name('cache.clear');
    Route::get('/optimize', [AdminController::class, 'optimize'])->name('optimize');

    Route::get('/admin-permissions', [RoleController::class, 'create_admin_permissions']);

    // mobile app settings
    Route::controller(MobileAppController::class)->group(function () {
        Route::get('/mobile-app-version', 'app_version')->name('mobile-app.version');
        Route::get('/mobile-app-sliders', 'app_sliders')->name('mobile-app.sliders');
        Route::post('/mobile-app-version/update', 'update_app_version')->name('mobile-app.version.update');
    });

    //export download
    Route::get('/exports/download/{file}', [ExportsController::class, 'download'])
        ->name('exports.download');
    //horizon
    Horizon::auth(function ($request) {
        return Auth::check() && Auth::user()->hasRole('Tech Support');
    });
    // Customer Products Management
    Route::controller(CustomerProductController::class)->group(function () {
        Route::get('/customer-products', 'index')->name('admin.customer-products.index');
        Route::get('/customer-products/{id}', 'show')->name('admin.customer-products.show');
        Route::post('/customer-products/{id}/moderate', 'moderate')->name('admin.customer-products.moderate');
        Route::post('/customer-products/bulk-moderate', 'bulkModerate')->name('admin.customer-products.bulk-moderate');
        Route::get('/customer-products-analytics', 'analytics')->name('admin.customer-products.analytics');
        Route::get('/customer-products-settings', 'settings')->name('admin.customer-products.settings');
        Route::post('/customer-products-settings', 'updateSettings')->name('admin.customer-products.update-settings');
        Route::get('/export-customer-products', 'export')->name('admin.customer-products.bulk-export');
    });

    // Auction Management Routes
    Route::controller(AuctionRoomController::class)->group(function () {
        Route::get('/auction-rooms', 'index')->name('admin.auction-rooms.index')->middleware('permission:view_auction_rooms');
        Route::get('/auction-rooms/create', 'create')->name('admin.auction-rooms.create')->middleware('permission:create_auction_room');
        Route::post('/auction-rooms', 'store')->name('admin.auction-rooms.store')->middleware('permission:create_auction_room');
        Route::get('/auction-rooms/{auctionRoom}', 'show')->name('admin.auction-rooms.show')->middleware('permission:view_auction_rooms');
        Route::get('/auction-rooms/{auctionRoom}/edit', 'edit')->name('admin.auction-rooms.edit')->middleware('permission:edit_auction_room');
        Route::get('/auction-rooms/{auctionRoom}/monitor', 'monitor')->name('admin.auction-rooms.monitor')->middleware('permission:monitor_auction_room');
        Route::put('/auction-rooms/{auctionRoom}', 'update')->name('admin.auction-rooms.update')->middleware('permission:edit_auction_room');
        Route::post('/auction-rooms/{auctionRoom}/start', 'start')->name('admin.auction-rooms.start')->middleware('permission:start_auction_room');
        Route::post('/auction-rooms/{auctionRoom}/set-scheduled', 'setScheduled')->name('admin.auction-rooms.set-scheduled')->middleware('permission:start_auction_room');
        Route::post('/auction-rooms/{auctionRoom}/cancel', 'cancel')->name('admin.auction-rooms.cancel')->middleware('permission:cancel_auction_room');
        Route::post('/auction-rooms/{auctionRoom}/items', 'addItem')->name('admin.auction-rooms.add-item')->middleware('permission:manage_auction_items');
        Route::delete('/auction-rooms/{auctionRoom}/items/{itemId}', 'removeItem')->name('admin.auction-rooms.remove-item')->middleware('permission:manage_auction_items');
        Route::put('/auction-rooms/{auctionRoom}/items/reorder', 'reorderItems')->name('admin.auction-rooms.reorder-items')->middleware('permission:manage_auction_items');
        Route::get('/auction-rooms/{auctionRoom}/live-stats', 'liveStats')->name('admin.auction-rooms.live-stats')->middleware('permission:monitor_auction_room');
        Route::get('/auction-item-starting-price', 'getAuctionItemStartingPrice')->name('admin.auction-rooms.get-starting-price')->middleware('permission:create_auction_room');
    });

    // Auction Room Report Routes
    Route::controller(AuctionRoomReportController::class)->group(function () {
        Route::get('/auction-rooms/{auctionRoom}/report', 'show')->name('admin.auction-rooms.report')->middleware('permission:view_auction_reports');

        // AJAX filtering endpoints
        Route::get('/auction-rooms/{auctionRoom}/report/bids', 'getFilteredBids')->name('admin.auction-rooms.report.bids')->middleware('permission:view_auction_reports');
        Route::get('/auction-rooms/{auctionRoom}/report/offers', 'getFilteredOffers')->name('admin.auction-rooms.report.offers')->middleware('permission:view_auction_reports');
        Route::get('/auction-rooms/{auctionRoom}/report/audit-logs', 'getFilteredAuditLogs')->name('admin.auction-rooms.report.audit-logs')->middleware('permission:view_auction_reports');
        Route::get('/auction-rooms/{auctionRoom}/report/items', 'getFilteredItems')->name('admin.auction-rooms.report.items')->middleware('permission:view_auction_reports');
    });

    Route::controller(AuctionListingRequestController::class)->group(function () {
        Route::get('/auction-listing-requests', 'index')->name('admin.auction-listing-requests.index')->middleware('permission:view_auction_listing_requests');
        Route::get('/auction-listing-requests/{auctionListingRequest}', 'show')->name('admin.auction-listing-requests.show')->middleware('permission:view_auction_listing_requests');
        Route::post('/auction-listing-requests/{auctionListingRequest}/approve', 'approve')->name('admin.auction-listing-requests.approve')->middleware('permission:approve_auction_listing_request');
        Route::post('/auction-listing-requests/{auctionListingRequest}/reject', 'reject')->name('admin.auction-listing-requests.reject')->middleware('permission:reject_auction_listing_request');
        Route::get('/auction-listing-requests-stats', 'stats')->name('admin.auction-listing-requests.stats')->middleware('permission:view_auction_listing_requests');
        Route::post('/auction-listing-requests/bulk-approve', 'bulkApprove')->name('admin.auction-listing-requests.bulk-approve')->middleware('permission:bulk_manage_listing_requests');
        Route::post('/auction-listing-requests/bulk-reject', 'bulkReject')->name('admin.auction-listing-requests.bulk-reject')->middleware('permission:bulk_manage_listing_requests');
    });

    Route::controller(AuctionOfferController::class)->group(function () {
        Route::get('/auction-offers', 'index')->name('admin.auction-offers.index')->middleware('permission:view_auction_offers');
        Route::get('/auction-offers/{auctionOffer}', 'show')->name('admin.auction-offers.show')->middleware('permission:view_auction_offers');
        Route::post('/auction-offers/{auctionOffer}/force-accept', 'forceAccept')->name('admin.auction-offers.force-accept')->middleware('permission:force_accept_auction_offer');
        Route::post('/auction-offers/{auctionOffer}/force-reject', 'forceReject')->name('admin.auction-offers.force-reject')->middleware('permission:force_reject_auction_offer');
        Route::get('/auction-offers-stats', 'stats')->name('admin.auction-offers.stats')->middleware('permission:view_auction_offers');
    });

    Route::controller(AuctionMonitoringController::class)->group(function () {
        Route::get('/auction-dashboard', 'dashboard')->name('admin.auction-dashboard')->middleware('permission:view_auction_dashboard');
        Route::get('/auction-audit-logs', 'auditLogs')->name('admin.auction-audit-logs')->middleware('permission:view_auction_audit_logs');
        Route::get('/auction-audit-logs/{auctionAuditLog}', 'showAuditLog')->name('admin.auction-audit-logs.show')->middleware('permission:view_auction_audit_logs');
        Route::get('/auction-audit-logs-export', 'exportAuditLogs')->name('admin.auction-audit-logs.export')->middleware('permission:export_auction_audit_logs');
        Route::get('/auction-system-status', 'systemStatus')->name('admin.auction-system-status')->middleware('permission:view_auction_system_status');
        Route::get('/auction-analytics', 'analytics')->name('admin.auction-analytics')->middleware('permission:view_auction_analytics');
    });

    // Insurance Deposits Management Routes
    Route::prefix('insurance-deposits')->name('insurance-deposits.')->controller(\App\Http\Controllers\Admin\InsuranceDepositController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/statistics', 'statistics')->name('statistics');
        Route::get('/{deposit}', 'show')->name('show');
        Route::post('/{deposit}/refund', 'refund')->name('refund');
        Route::post('/update-status','updatePaymentStatus')->name('update-status');
    });

    // Admin Auction Invoice Management Routes
    Route::controller(AdminAuctionInvoiceController::class)->group(function () {
        Route::get('/auction-invoices', 'index')->name('admin.auction-invoices.index')->middleware('permission:view_auction_invoices');
        Route::get('/auction-invoices/{auctionInvoice}', 'show')->name('admin.auction-invoices.show')->middleware('permission:view_auction_invoices');
        Route::get('/auction-invoices/{auctionInvoice}/download-pdf', 'downloadPdf')->name('admin.auction-invoices.download-pdf')->middleware('permission:download_auction_invoices');
        Route::post('/auction-invoices/{auctionInvoice}/update-status', 'updateStatus')->name('admin.auction-invoices.update-status')->middleware('permission:manage_auction_invoices');
        Route::get('/auction-invoices-export', 'export')->name('admin.auction-invoices.export')->middleware('permission:export_auction_invoices');

        // Overdue Invoice Management Routes
        Route::post('/auction-invoices/bulk-reminders', 'sendBulkReminders')->name('admin.auction-invoices.bulk-reminders')->middleware('permission:manage_auction_invoices');
    });
    // auction settings
    Route::get('/auction/settings', '\App\Http\Controllers\BusinessSettingsController@auction_settings')->name('admin.auction.settings');

    //laravel health
    Route::get('/health', HealthCheckResultsController::class)->name('health.index');
});
