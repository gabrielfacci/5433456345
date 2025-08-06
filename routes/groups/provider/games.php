<?php

use App\Http\Controllers\Api\Games\GameController;
use Illuminate\Support\Facades\Route;

// VOXELGATOR
Route::post('venix_api', [GameController::class, 'webhookPlayIGamingMethod']);

// TBS Russian
Route::post('tbs', [GameController::class, 'webhookPlayGamingMethod']);

// PlayConnect
Route::post('cron/playconnect', [GameController::class, 'WebhooksPlayConnect']);

// Fivers
Route::post('gold_api', [GameController::class, 'webhookGoldApiMethod']);

// Ever
Route::post('ever', [GameController::class, 'webhookEvergameMethod']);

// PG Clone
Route::post('pgclone/gold_api/user_balance', [GameController::class, 'webhookPgcloneUserBalanceMethod']);
Route::post('pgclone/gold_api/game_callback', [GameController::class, 'webhookPgcloneGameCallbackMethod']);

// Worldslot
Route::post('gold_api/user_balance', [GameController::class, 'webhookUserBalanceMethod']);
Route::post('gold_api/game_callback', [GameController::class, 'webhookGameCallbackMethod']);
Route::post('gold_api/money_callback', [GameController::class, 'webhookMoneyCallbackMethod']);
