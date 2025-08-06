<?php


use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProfileController::class, 'index']);
Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
Route::post('/updateName', [ProfileController::class, 'updateName']);
Route::post('/update-name', [ProfileController::class, 'updateName']);
Route::post('/update-phone', [ProfileController::class, 'updatePhone']);
Route::post('/update-delivery', [ProfileController::class, 'updateDelivery']);
Route::get('/recent-bets', [ProfileController::class, 'getRecentBets']);
Route::get('/bet-history', [ProfileController::class, 'getBetHistory']);
