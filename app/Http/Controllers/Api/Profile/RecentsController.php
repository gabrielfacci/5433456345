<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RecentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth('api')->id();
        $cacheKey = "user_recent_games_{$userId}";
        
        // Tenta buscar do cache primeiro
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            try {
                // Busca os últimos 20 jogos únicos jogados pelo usuário
                $recentGames = Order::select('game', 'created_at')
                    ->where('user_id', $userId)
                    ->where('type', 'bet') // Apenas apostas, não vitórias
                    ->orderBy('created_at', 'desc')
                    ->groupBy('game')
                    ->limit(20)
                    ->get();

                if ($recentGames->isEmpty()) {
                    return response()->json([], 400);
                }

                // Busca os detalhes dos jogos com eager loading
                $games = Game::whereIn('game_code', $recentGames->pluck('game'))
                    ->with(['provider', 'category'])
                    ->get()
                    ->map(function ($game) use ($recentGames) {
                        // Adiciona a data do último jogo
                        $lastPlayed = $recentGames->where('game', $game->game_code)->first();
                        $game->last_played_at = $lastPlayed ? $lastPlayed->created_at : null;
                        return $game;
                    })
                    ->sortByDesc('last_played_at')
                    ->values();

                if ($games->isEmpty()) {
                    Log::warning('Nenhum jogo encontrado para os códigos: ' . implode(', ', $recentGames->pluck('game')->toArray()));
                    return response()->json([], 400);
                }

                return response()->json([
                    'games' => $games,
                    'total' => $games->count()
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao buscar jogos recentes: ' . $e->getMessage());
                return response()->json(['error' => 'Erro ao buscar jogos recentes'], 500);
            }
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
