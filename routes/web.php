<?php

use App\Models\Game;
use App\Models\CustomLayout;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gift\GiftController;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\DistributionController;
use App\Models\DistributionSystem;
use App\Models\Order;
use App\Models\GamesKey;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|Sme
*/
Route::get('clear', function() {
    Artisan::command('clear', function () {
        Artisan::call('optimize:clear');
       return back();
    });

    return back();
});

// GAMES PROVIDER
include_once(__DIR__ . '/groups/provider/playFiver.php');
include_once(__DIR__ . '/groups/provider/games.php');



// GATEWAYS

include_once(__DIR__ . '/groups/gateways/bspay.php');
include_once(__DIR__ . '/groups/gateways/ondapay.php');

/// SOCIAL
include_once(__DIR__ . '/groups/auth/social.php');

// APP
include_once(__DIR__ . '/groups/layouts/app.php');

Route::get('/feedbacks', function () {
    $custom = CustomLayout::first();
    return Inertia::render('Feedback/FeedbackPage', [
        'custom' => $custom
    ]);
})->name('feedbacks');

Route::get('/esportes', function () {
    return view('layouts.app');
})->name('esportes');

Route::get('/', function () {
    return view('spa');
});

Route::get('/raspadinhas', function () {
    return view('spa');
})->name('raspadinhas');

Route::get('api/distribution/manual-update', [DistributionController::class, 'checkDistributionSystem'])
    ->name('distribution.manual.update');

Route::prefix('auth')->group(function() {
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login');
    Route::post('/register', [RegisterController::class, 'store'])->name('register');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot-password');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('reset-password');
});

// Sistema de Distribuição - Processar automaticamente
Route::get('/process-distribution', function() {
    $distribution = DistributionSystem::first();
    
    if (!$distribution || !$distribution->ativo) {
        return response()->json(['status' => 'inactive']);
    }

    if (!$distribution->start_cycle_at) {
        $distribution->update(['start_cycle_at' => now()]);
    }

    if ($distribution->modo === 'arrecadacao') {
        $totalBets = Order::where('type', 'bet')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        $distribution->total_arrecadado = $totalBets;
        $distribution->save();

        if ($totalBets >= $distribution->meta_arrecadacao) {
            $distribution->update([
                'total_arrecadado' => 0,
                'modo' => 'distribuicao',
                'start_cycle_at' => now(),
            ]);

            // Atualizar RTP
            $setting = GamesKey::first();
            if ($setting) {
                try {
                    Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->put('https://api.playfivers.com/api/v2/agent', [
                            'agentToken' => $setting->playfiver_token,
                            'secretKey' => $setting->playfiver_secret,
                            'rtp' => $distribution->rtp_distribuicao,
                            'bonus_enable' => true,
                        ]);
                } catch (\Exception $e) {
                    //
                }
            }
        }
    } elseif ($distribution->modo === 'distribuicao') {
        $totalWins = Order::where('type', 'win')
            ->where('created_at', '>=', $distribution->start_cycle_at)
            ->sum('amount');

        $distribution->total_distribuido = $totalWins;
        $distribution->save();

        $valorDistribuir = $distribution->meta_arrecadacao * ($distribution->percentual_distribuicao / 100);

        if ($totalWins >= $valorDistribuir) {
            $distribution->update([
                'total_distribuido' => 0,
                'modo' => 'arrecadacao',
                'start_cycle_at' => now(),
            ]);

            // Atualizar RTP
            $setting = GamesKey::first();
            if ($setting) {
                try {
                    Http::withOptions(['force_ip_resolve' => 'v4'])
                        ->put('https://api.playfivers.com/api/v2/agent', [
                            'agentToken' => $setting->playfiver_token,
                            'secretKey' => $setting->playfiver_secret,
                            'rtp' => $distribution->rtp_arrecadacao,
                            'bonus_enable' => true,
                        ]);
                } catch (\Exception $e) {
                    //
                }
            }
        }
    }

    return response()->json(['status' => 'processed', 'modo' => $distribution->modo]);
});

Route::post('/distribution/check', [DistributionController::class, 'check'])->middleware(['throttle:3,1']);

Route::get('/distribution-status', function () {
    return view('distribution-status');
});

// Rota para a página de raspadinhas
Route::get('/raspadinhas', function () {
    return Inertia::render('Raspadinhas/RaspadinhasPage');
})->name('raspadinhas');

Route::get('/{any}', function () {
    return view('spa');
})->where('any', '.*');

