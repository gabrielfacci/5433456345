<?php

use App\Http\Controllers\Api\Profile\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameOpenController;
use App\Http\Controllers\Api\DailyBonusController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\VipController;
use App\Http\Controllers\Gift\GiftController;
use App\Http\Controllers\Api\SystemNotificationController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\Profile\CashbackController;
use App\Http\Controllers\DistributionController;
use App\Models\DistributionSystem;
use App\Models\Order;
use App\Models\GamesKey;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
 * Auth Route with JWT
 */
Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
    include_once(__DIR__ . '/groups/api/auth/auth.php');
});

Route::group(['middleware' => ['auth.jwt']], function () {
    Route::prefix('profile')
        ->group(function ()
        {
            include_once(__DIR__ . '/groups/api/profile/profile.php');
            include_once(__DIR__ . '/groups/api/profile/affiliates.php');
            include_once(__DIR__ . '/groups/api/profile/wallet.php');
            include_once(__DIR__ . '/groups/api/profile/likes.php');
            include_once(__DIR__ . '/groups/api/profile/favorites.php');
            include_once(__DIR__ . '/groups/api/profile/recents.php');
            
            // Rotas das raspadinhas
            Route::post('/raspadinha', [ProfileController::class, 'playScratchCard']);
            Route::post('/raspadinha-cinco', [ProfileController::class, 'playScratchCardCincoMil']);
            Route::post('/raspadinha-dez', [ProfileController::class, 'playScratchCardDezMil']);
            Route::post('/raspadinha-milhao', [ProfileController::class, 'playScratchCardMilhao']);
            Route::post('/raspadinha-make', [ProfileController::class, 'playScratchCardMake']);
            Route::post('/raspadinha-6', [ProfileController::class, 'playScratchCard6']);
            Route::post('/raspadinha-7', [ProfileController::class, 'playScratchCard7']);
            Route::post('/raspadinha-8', [ProfileController::class, 'playScratchCard8']);
            Route::post('/raspadinha-9', [ProfileController::class, 'playScratchCard9']);
        });

    Route::prefix('wallet')
        ->group(function ()
        {
            include_once(__DIR__ . '/groups/api/wallet/deposit.php');
            include_once(__DIR__ . '/groups/api/wallet/withdraw.php');
        });
});

Route::middleware('auth:api')->post('/gifts/redeem', [GiftController::class, 'redeem']);
Route::middleware('auth:api')->get('/gifts/redeems', [GiftController::class, 'listRedeems']);

// Rotas das Raspadinhas
Route::get('/raspadinhas', [App\Http\Controllers\Api\RaspadinhaController::class, 'index']);
Route::get('/raspadinhas/{category}', [App\Http\Controllers\Api\RaspadinhaController::class, 'byCategory']);


Route::prefix('categories')
    ->group(function ()
    {
        include_once(__DIR__ . '/groups/api/categories/index.php');;
    });

include_once(__DIR__ . '/groups/api/games/index.php');
include_once(__DIR__ . '/groups/api/gateways/bspay.php');
include_once(__DIR__ . '/groups/api/gateways/ondapay.php');

Route::prefix('search')
    ->group(function ()
    {
        include_once(__DIR__ . '/groups/api/search/search.php');
    });

Route::prefix('profile')
    ->group(function ()
    {
        Route::post('/getLanguage', [ProfileController::class, 'getLanguage']);
        Route::put('/updateLanguage', [ProfileController::class, 'updateLanguage']);
    });

Route::prefix('providers')
    ->group(function ()
    {

    });


Route::prefix('settings')
    ->group(function ()
    {
        include_once(__DIR__ . '/groups/api/settings/settings.php');
        include_once(__DIR__ . '/groups/api/settings/banners.php');
        include_once(__DIR__ . '/groups/api/settings/currency.php');
    });

Route::get('/settings/sportsbook', [SettingsController::class, 'getSportsbook']);

// Custom Layout
Route::get('custom-layout', function() {
    return response()->json(\Helper::getCustom());
});

// LANDING SPIN
Route::prefix('spin')
    ->group(function ()
    {
        include_once(__DIR__ . '/groups/api/spin/index.php');
    })
    ->name('landing.spin.');

// Sistema de Distribuição - Webhook para automação
Route::post('/distribution/process', function() {
    $distribution = DistributionSystem::first();
    
    if (!$distribution) {
        return response()->json(['status' => 'not_found', 'message' => 'Sistema não encontrado']);
    }
    
    if (!$distribution->ativo) {
        return response()->json(['status' => 'inactive', 'message' => 'Sistema está inativo']);
    }

    if (!$distribution->start_cycle_at) {
        $distribution->update(['start_cycle_at' => now()]);
    }

    $statusChanged = false;

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
            $statusChanged = true;
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
            $statusChanged = true;
        }
    }

    return response()->json([
        'status' => 'processed',
        'modo' => $distribution->modo,
        'changed' => $statusChanged,
        'total_arrecadado' => $distribution->total_arrecadado,
        'total_distribuido' => $distribution->total_distribuido,
        'message' => 'Sistema processado com sucesso'
    ]);
});

// Endpoint de teste sem autenticação
Route::get('/distribution/test', function() {
    $distribution = DistributionSystem::first();
    
    if (!$distribution) {
        return response()->json(['status' => 'not_found', 'message' => 'Sistema não encontrado']);
    }
    
    return response()->json([
        'status' => 'found',
        'ativo' => $distribution->ativo,
        'modo' => $distribution->modo,
        'meta_arrecadacao' => $distribution->meta_arrecadacao,
        'total_arrecadado' => $distribution->total_arrecadado,
        'total_distribuido' => $distribution->total_distribuido,
        'message' => 'Dados do sistema'
    ]);
});

// Endpoint que força processamento (para debug)
Route::get('/distribution/force', function() {
    $distribution = DistributionSystem::first();
    
    if (!$distribution) {
        return response()->json(['status' => 'not_found', 'message' => 'Sistema não encontrado']);
    }
    
    // Força processamento mesmo se inativo
    if (!$distribution->start_cycle_at) {
        $distribution->update(['start_cycle_at' => now()]);
    }

    $statusChanged = false;

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
            $statusChanged = true;
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
            $statusChanged = true;
        }
    }

    return response()->json([
        'status' => 'forced',
        'modo' => $distribution->modo,
        'changed' => $statusChanged,
        'total_arrecadado' => $distribution->total_arrecadado,
        'total_distribuido' => $distribution->total_distribuido,
        'ativo' => $distribution->ativo,
        'message' => 'Processamento forçado'
    ]);
});

// Endpoint para processamento a cada 30 segundos
Route::post('/distribution/process-30s', function () {
    try {
        \Artisan::call('distribution:process-30s');
        $output = \Artisan::output();
        
        return response()->json([
            'status' => 'processed-30s',
            'message' => 'Processamento a cada 30 segundos executado',
            'output' => $output,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro no processamento: ' . $e->getMessage(),
            'timestamp' => now()
        ], 500);
    }
});

// Endpoint para verificação de status (usado pelo frontend)
Route::post('/distribution/check', [DistributionController::class, 'check']);

Route::get('/distribution/status', function () {
    $distribution = \App\Models\DistributionSystem::first();
    
    if (!$distribution) {
        return response()->json([
            'error' => 'Sistema de distribuição não encontrado'
        ], 404);
    }

    // Verificar e atualizar automaticamente
    $totalBets = \App\Models\Order::where('type', 'bet')
        ->where('created_at', '>=', $distribution->start_cycle_at)
        ->sum('amount');
    
    $totalWins = \App\Models\Order::where('type', 'win')
        ->where('created_at', '>=', $distribution->start_cycle_at)
        ->sum('amount');

    $mudou = false;
    $mensagem = '';

    if ($distribution->modo === 'arrecadacao' && $totalBets >= $distribution->meta_arrecadacao) {
        // Mudar para distribuição
        $distribution->update([
            'modo' => 'distribuicao',
            'start_cycle_at' => now(),
            'total_arrecadado' => $totalBets,
        ]);
        
        // Atualizar RTP
        $setting = \App\Models\GamesKey::first();
        if ($setting) {
            try {
                \Illuminate\Support\Facades\Http::withOptions(['force_ip_resolve' => 'v4'])
                    ->put('https://api.playfivers.com/api/v2/agent', [
                        'agentToken' => $setting->playfiver_token,
                        'secretKey' => $setting->playfiver_secret,
                        'rtp' => $distribution->rtp_distribuicao,
                        'bonus_enable' => true,
                    ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao atualizar RTP', ['error' => $e->getMessage()]);
            }
        }
        
        $mudou = true;
        $mensagem = '🎉 Arrecadação completa! Mudou para DISTRIBUIÇÃO';
        
        \Log::info('ARRECADAÇÃO COMPLETA - Mudou para DISTRIBUIÇÃO', [
            'total_arrecadado' => $totalBets,
            'meta' => $distribution->meta_arrecadacao
        ]);
    } elseif ($distribution->modo === 'distribuicao' && $totalWins >= $distribution->meta_distribuicao) {
        // Mudar para arrecadação
        $distribution->update([
            'modo' => 'arrecadacao',
            'start_cycle_at' => now(),
            'total_arrecadado' => 0,
            'total_distribuido' => 0,
        ]);
        
        // Atualizar RTP
        $setting = \App\Models\GamesKey::first();
        if ($setting) {
            try {
                \Illuminate\Support\Facades\Http::withOptions(['force_ip_resolve' => 'v4'])
                    ->put('https://api.playfivers.com/api/v2/agent', [
                        'agentToken' => $setting->playfiver_token,
                        'secretKey' => $setting->playfiver_secret,
                        'rtp' => $distribution->rtp_arrecadacao,
                        'bonus_enable' => true,
                    ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao atualizar RTP', ['error' => $e->getMessage()]);
            }
        }
        
        $mudou = true;
        $mensagem = '🎉 Distribuição completa! Mudou para ARRECADAÇÃO';
        
        \Log::info('DISTRIBUIÇÃO COMPLETA - Mudou para ARRECADAÇÃO', [
            'total_distribuido' => $totalWins,
            'meta' => $distribution->meta_distribuicao
        ]);
    }

    return response()->json([
        'modo' => $distribution->fresh()->modo,
        'total_arrecadado' => $totalBets,
        'total_distribuido' => $totalWins,
        'meta_arrecadacao' => $distribution->meta_arrecadacao,
        'meta_distribuicao' => $distribution->meta_distribuicao,
        'progresso_arrecadacao' => $distribution->modo === 'arrecadacao' ? 
            ($totalBets / $distribution->meta_arrecadacao) * 100 : 0,
        'progresso_distribuicao' => $distribution->modo === 'distribuicao' ? 
            ($totalWins / $distribution->meta_distribuicao) * 100 : 0,
        'mudou' => $mudou,
        'mensagem' => $mensagem,
        'ultima_atualizacao' => $distribution->fresh()->start_cycle_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'),
    ]);
});
