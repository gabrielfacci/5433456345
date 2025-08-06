<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gateway\OndaPayController;
Route::prefix('ondapay')
    ->group(function () {
        Route::post('qrcode-pix', [OndaPayController::class, 'getQRCodePix']);
        Route::any('callback', [OndaPayController::class, 'callbackMethod']);
        Route::post('consult-status-transaction', [OndaPayController::class, 'consultStatusTransactionPix']);

        Route::get('withdrawal/{id}', [OndaPayController::class, 'withdrawalFromModal'])->name('ondapay.withdrawal');
        Route::get('cancelwithdrawal/{id}', [OndaPayController::class, 'cancelWithdrawalFromModal'])->name('ondapay.cancelwithdrawal');
    });