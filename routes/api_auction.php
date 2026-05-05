<?php
use App\Http\Controllers\Api\V2\Customer\AuctionBrowsingController;
use App\Http\Controllers\Api\V2\Customer\InsuranceDepositController;
use App\Http\Controllers\Api\V2\Customer\AuctionInvoiceController;
use App\Http\Controllers\Api\V2\Customer\AuctionBiddingController;
use App\Http\Controllers\Api\V2\Customer\AuctionOfferController;

Route::group(['prefix' => 'v2', 'middleware' => ['app_language']], function () {

    Route::prefix('auction')->group(function (){
         // Customer Auction API Routes
    Route::prefix('customer')->name('api.v2.customer.')->middleware('auth:sanctum')->group(function () {
        // Insurance deposit management
        Route::prefix('insurance-deposits')->name('insurance-deposit.')->controller(InsuranceDepositController::class)->group(function () {
            Route::post('/pay', 'store')->name('store');
            Route::get('/', 'show')->name('show');
            Route::post('/refund', 'refund')->name('refund');
            Route::get('/eligibility', 'eligibility')->name('eligibility');
        });

        // Bidding management
        Route::controller(AuctionBiddingController::class)->group(function () {
        Route::get('my-bids', 'index')->name('bids.index');
        Route::get('my-bids/{bid}', 'show')->name('bids.show');
        });

        // Offer management
        Route::prefix('my-offers')->name('offers.')->controller(AuctionOfferController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{auctionOffer}', 'show')->name('show');
            Route::delete('/{auctionOffer}', 'destroy')->name('destroy');
        });

        // Invoice management
        Route::prefix('auction-invoices')->name('auction-invoices.')->controller(AuctionInvoiceController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/summary', 'summary')->name('summary');
            Route::get('/{auctionInvoice}', 'show')->name('show');
            Route::post('/{auctionInvoice}/pay', 'pay')->name('pay');
            Route::get('/{auctionInvoice}/download-pdf', 'downloadPdf')->name('download-pdf');
        });
    });

    // Public Auction Browsing API Routes (no authentication required)
    Route::prefix('auction-rooms')->name('api.v2.auction-rooms.')->controller(AuctionBrowsingController::class)
        ->group(function () {
        Route::get('/active', 'activeRooms')->name('index');
        Route::get('/scheduled', 'scheduledRooms')->name('scheduled');
        Route::get('/cars', 'auctionCars')->name('cars');
        Route::get('/{auctionRoom}', 'show')->name('show');
        Route::get('/{auctionRoom}/items', 'items')->name('items');
    });

    Route::prefix('auction-items')->name('api.v2.auction-items.')
        ->group(function () {
        Route::get('/{auctionItem}', 'App\Http\Controllers\Api\V2\Customer\AuctionBrowsingController@itemDetails')->name('show');
        Route::get('/{auctionItem}/bids', 'App\Http\Controllers\Api\V2\Customer\AuctionBrowsingController@bidHistory')->name('bids');

        // Authenticated endpoints for bidding and offers
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{auctionItem}/place-bid', 'App\Http\Controllers\Api\V2\Customer\AuctionBiddingController@store')
                ->middleware('throttle:auction-bids')
                ->name('bids.store');
            Route::post('/{auctionItem}/make-offer', 'App\Http\Controllers\Api\V2\Customer\AuctionOfferController@store')
                ->middleware('throttle:auction-offers')
                ->name('offers.store');
        });
    });

    // Seller Auction API Routes
    Route::prefix('seller')->name('api.v2.seller.')->middleware('auth:sanctum')->group(function () {
        // Auction listing requests
        Route::prefix('listing-requests')->name('auction-listing-requests.')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionListingController@store')->name('store');
            Route::get('/', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionListingController@index')->name('index');
            Route::get('/available-cars','App\Http\Controllers\Api\V2\Seller\SellerAuctionListingController@getAvailableCars')->name('available-cars');
            Route::get('/{id}', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionListingController@show')->name('show')->where('id', '[0-9]+')->middleware('verified-seller');
            Route::delete('/{id}/delete', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionListingController@destroy')->where('id', '[0-9]+')->name('destroy');
        });

        // Auction offers
        Route::prefix('auction-offers')->name('auction-offers.')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionOfferController@index')->name('index');
            Route::post('/accept', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionOfferController@accept')->name('accept');
            Route::post('/reject', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionOfferController@reject')->name('reject');
            Route::get('/{id}', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionOfferController@show')->name('show');
        });

        // Auction items and invoices
        Route::get('auction-items', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionInvoiceController@auctionItems')->name('auction-items.index');
        Route::get('auction-items/{id}', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionInvoiceController@auctionItemDetails')->name('auction-items.show');
        Route::get('auction-invoices', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionInvoiceController@index')->name('auction-invoices.index');
        Route::get('auction-invoices/{id}', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionInvoiceController@show')->name('auction-invoices.show');
        Route::get('auction-invoices/{auctionInvoice}/download-pdf', 'App\Http\Controllers\Api\V2\Seller\SellerAuctionInvoiceController@downloadPdf')->name('auction-invoices.download-pdf');
    });
    });
});
