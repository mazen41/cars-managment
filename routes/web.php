<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SizeChartController;
use Illuminate\Http\Request;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PublicManualExaminationPhotoController;
/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */


Route::get('/refresh-csrf', function () {
    return csrf_token();
});

Route::group(['middleware' => ['prevent-back-history', 'handle-demo-login']], function () {
    Auth::routes(['verify' => true]);
});
Route::get('/', [HomeController::class, 'landing_page'])->name('home');
//shops
Route::resource('shops', ShopController::class)->middleware('handle-demo-login');
Route::controller(HomeController::class)->group(function () {
    Route::get('/seller/login', 'login')->name('seller.login')->middleware('handle-demo-login');
});
//general routes
Route::get('/logout', [LoginController::class, 'logout']);
// Language Switch
Route::post('/language', [LanguageController::class, 'changeLanguage'])->name('language.change');
// Currency Switch
Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');
// Size Chart Show
Route::get('/size-charts-show/{id}', [SizeChartController::class, 'show'])->name('size-charts-show');

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/resend', 'resend')->name('email-verification.resend');
    Route::get('/email/resend-ajax', 'resend_ajax')->name('verification.resend-ajax');
    Route::get('/verification-confirmation/{code}', 'verification_confirmation')->name('email.verification.confirmation');
});

//subscribing to webpush notifications
Route::middleware(['auth'])->group(function () {

    Route::post('/web-push-subscribe', function (Request $request) {
        $request->user()->updatePushSubscription(
            $request->input('endpoint'),
            $request->input('keys.p256dh'),
            $request->input('keys.auth')
        );
        return response()->json(['success' => true], 200);
    })->name('web-push.subscribe');

    //Messages routes
    Route::resource('messages', MessageController::class);

    Route::get('invoice/{order_id}', [InvoiceController::class, 'invoice_download'])->name('invoice.download');
});

Route::controller(AddressController::class)->group(function () {
    Route::post('/get-states', 'getStates')->name('get-state');
    Route::post('/get-cities', 'getCities')->name('get-city');
});

/**
 * Public photo streaming for manual examinations.
 *
 * This route is intentionally NOT under the `api` middleware group because browser <img> tags
 * cannot include custom headers (like System-Key). It also works even when `/storage` symlink
 * isn't exposed correctly by the web server.
 */
Route::get('/manual-examinations/{manualExamination}/photos/{encodedPath}', [PublicManualExaminationPhotoController::class, 'show'])
    ->name('public.manual-examinations.photo');

// AIZ Uploader
Route::controller(AizUploadController::class)->middleware(['auth'])->group(function () {
    Route::post('/aiz-uploader', 'show_uploader');
    Route::post('/aiz-uploader/upload', 'upload');
    Route::get('/aiz-uploader/get-uploaded-files', 'get_uploaded_files');
    Route::post('/aiz-uploader/get_file_by_ids', 'get_preview_files');
    Route::get('/aiz-uploader/download/{id}', 'attachment_download')->name('download_attachment');
});

Route::controller(HomeController::class)->group(function () {
    // Policies
    Route::get('/seller-policy', 'sellerpolicy')->name('sellerpolicy');
    Route::get('/return-policy', 'returnpolicy')->name('returnpolicy');
    Route::get('/support-policy', 'supportpolicy')->name('supportpolicy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/privacy-policy', 'privacypolicy')->name('privacypolicy');
});

Route::get('/sitemap.xml', function () {
    return base_path('sitemap.xml');
});

// Subscribe
//Route::resource('subscribers', SubscriberController::class)->except('index');

//Blog Section
Route::controller(BlogController::class)->group(function () {
    Route::get('/blog', 'all_blog')->name('blog');
    Route::get('/blog/{slug}', 'blog_details')->name('blog.details');
});

Route::controller(PageController::class)->group(function () {

    //Custom page
    Route::get('/{slug}', 'show_custom_page')->name('custom-pages.show_custom_page');
});

// PLaceholder route for product page to show deprecation message
Route::get('/product/{slug}', function(Request $request, $slug){
    if($request->wantsJson()){
        return response()->json([
            'success' => true,
            'message' => 'This route was depricated'
        ]);
    }
    flash()->warning(translate('This product page route is deprecated. Please use the API endpoints to fetch product details.'));
    return redirect()->route('home');

})->name('product');
