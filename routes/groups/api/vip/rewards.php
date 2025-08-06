<?php

use App\Http\Controllers\Api\VipRewardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/vip-rewards', [VipRewardController::class, 'index']);
    Route::get('/vip-rewards/{id}', [VipRewardController::class, 'show']);
    Route::post('/vip-rewards/{id}/claim', [VipRewardController::class, 'claim']);
    Route::get('/vip-rewards-history', [VipRewardController::class, 'history']);
}); 