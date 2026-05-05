<?php

use App\Http\Controllers\Payment\FloosakController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\Payment\JawaliController;
use App\Http\Controllers\Payment\JaibController;
use App\Http\Controllers\Api\V2\Payment\JaibController as JaibApi;
use App\Utility\PaymentUtility\JaibUtility;

Route::controller(JawaliController::class)->group(function(){
    Route::get('/jawali-test', 'test_login');
    Route::get('/jawali-test-enquiry', 'test_enquiry');
});

Route::controller(JaibController::class)->group(function(){
    Route::get('/jaib-login', 'test_login');
    Route::get('/jaib-pay', 'test_payment');
    Route::get('/jaib-refund', 'test_refund');
    Route::get('/jaib-check', 'test_check');

});

Route::controller(JaibAPI::class)->group(function(){
    Route::post('/jaib-api-pay', 'pay');


});
Route::get('email', function (){
    $order = \App\Models\Order::find(11)->first();
    return view('emails.invoice', compact('order'));
});
// Route::controller(FloosakController::class)->group(function(){
//     Route::get('/floosak-verify','test_verify');
// });

