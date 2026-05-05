<?php

use App\Http\Controllers\WalletController;
use App\Http\Controllers\ManualPaymentMethodController;



/*
|--------------------------------------------------------------------------
| Offline Payment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Admin
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    Route::resource('manual_payment_methods', ManualPaymentMethodController::class)->except('destroy');
    Route::get('/manual_payment_methods/destroy/{id}', [ManualPaymentMethodController::class, 'destroy'])->name('manual_payment_methods.destroy');
    Route::get('/offline-wallet-recharge-requests', [WalletController::class, 'offline_recharge_request'])->name('offline_wallet_recharge_request.index');
    Route::post('/offline-wallet-recharge/approved', [WalletController::class, 'updateApproved'])->name('offline_recharge_request.approved');

});
