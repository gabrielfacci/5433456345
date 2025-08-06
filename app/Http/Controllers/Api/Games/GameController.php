<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameLike;
use App\Models\GameOpenConfig;
use App\Models\GamesKey;
use App\Models\Gateway;
use App\Models\Provider;
use App\Models\Wallet;
use App\Models\Deposit;
use Carbon\Carbon;

use App\Traits\Providers\PlayFiverTrait;
use App\Traits\Providers\PlayConnectTrait;
use Illuminate\Http\Request;

class GameController extends Controller
{
    use PlayConnectTrait,
        PlayFiverTrait;

    /**
     * @dev victormsalatiel
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = Provider::with(['games', 'games.provider', 'games.likes'])
            ->whereHas('games')
            ->orderBy('name', 'desc')
            ->where('status', 1)
            ->get();

        // Adicionar dados de like se o usuário estiver autenticado
        if (auth('api')->check()) {
            $userId = auth('api')->id();
            
            $providers->each(function ($provider) use ($userId) {
                $provider->games->each(function ($game) use ($userId) {
                    $game->hasLike = $game->hasLikeByUser($userId);
                    $game->totalLikes = $game->likes()->count();
                });
            });
        }

        return response()->json(['providers' =>$providers]);
    }

    /**
     * @dev victormsalatiel
     * @return \Illuminate\Http\JsonResponse
     */
    public function featured()
    {
        \Log::info('Buscando jogos em destaque');

        $featured_games_section_1 = Game::with(['provider', 'likes'])
            ->where('featured_section_1', 1)
            ->where('status', 1)
            ->get();

        \Log::info('Jogos da seção 1:', ['count' => $featured_games_section_1->count()]);

        $featured_games_section_2 = Game::with(['provider', 'likes'])
            ->where('featured_section_2', 1)
            ->where('status', 1)
            ->get();

        \Log::info('Jogos da seção 2 (NOME AQUI3):', [
            'count' => $featured_games_section_2->count(),
            'games' => $featured_games_section_2->pluck('game_name')->toArray()
        ]);

        $featured_games_section_3 = Game::with(['provider', 'likes'])
            ->where('featured_section_3', 1)
            ->where('status', 1)
            ->get();

        $featured_games_section_4 = Game::with(['provider', 'likes'])
            ->where('featured_section_4', 1)
            ->where('status', 1)
            ->get();

        $featured_games_section_5 = Game::with(['provider', 'likes'])
            ->where('featured_section_5', 1)
            ->where('status', 1)
            ->get();

        // Adicionar dados de like se o usuário estiver autenticado
        if (auth('api')->check()) {
            $userId = auth('api')->id();
            
            $featured_games_section_1->each(function ($game) use ($userId) {
                $game->hasLike = $game->hasLikeByUser($userId);
                $game->totalLikes = $game->likes()->count();
            });
            
            $featured_games_section_2->each(function ($game) use ($userId) {
                $game->hasLike = $game->hasLikeByUser($userId);
                $game->totalLikes = $game->likes()->count();
            });
            
            $featured_games_section_3->each(function ($game) use ($userId) {
                $game->hasLike = $game->hasLikeByUser($userId);
                $game->totalLikes = $game->likes()->count();
            });
            
            $featured_games_section_4->each(function ($game) use ($userId) {
                $game->hasLike = $game->hasLikeByUser($userId);
                $game->totalLikes = $game->likes()->count();
            });
            
            $featured_games_section_5->each(function ($game) use ($userId) {
                $game->hasLike = $game->hasLikeByUser($userId);
                $game->totalLikes = $game->likes()->count();
            });
        }

        return response()->json([
            'featured_games_section_1' => $featured_games_section_1,
            'featured_games_section_2' => $featured_games_section_2,
            'featured_games_section_3' => $featured_games_section_3,
            'featured_games_section_4' => $featured_games_section_4,
            'featured_games_section_5' => $featured_games_section_5,
        ]);
    }

    /**
     * Source Provider
     *
     * @dev victormsalatiel
     * @param Request $request
     * @param $token
     * @param $action
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function sourceProvider(Request $request, $token, $action)
    {
        $tokenOpen = \Helper::DecToken($token);
        $validEndpoints = ['session', 'icons', 'spin', 'freenum'];

        if (in_array($action, $validEndpoints)) {
            if(isset($tokenOpen['status']) && $tokenOpen['status'])
            {
                $game = Game::whereStatus(1)->where('game_code', $tokenOpen['game'])->first();
                if(!empty($game)) {
                    $controller = \Helper::createController($game->game_code);

                    switch ($action) {
                        case 'session':
                            return $controller->session($token);
                        case 'spin':
                            return $controller->spin($request, $token);
                        case 'freenum':
                            return $controller->freenum($request, $token);
                        case 'icons':
                            return $controller->icons();
                    }
                }
            }
        } else {
            return response()->json([], 500);
        }
    }

    /**
     * @dev victormsalatiel
     * Store a newly created resource in storage.
     */
    public function toggleFavorite($id)
    {
        if(auth('api')->check()) {
            $checkExist = GameFavorite::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if(!empty($checkExist)) {
                if($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            }else{
                $gameFavoriteCreate = GameFavorite::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if($gameFavoriteCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * @dev victormsalatiel
     * Store a newly created resource in storage.
     */
    public function toggleLike($id)
    {
        if(auth('api')->check()) {
            $checkExist = GameLike::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if(!empty($checkExist)) {
                if($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            }else{
                $gameLikeCreate = GameLike::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if($gameLikeCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * @dev victormsalatiel
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $game = Game::with(['categories', 'provider', 'likes'])->whereStatus(1)->find($id);
        if(!empty($game)) {
            if(auth('api')->check()) {
                $wallet = Wallet::where('user_id', auth('api')->user()->id)->first();
                $hasRealBalance = $wallet->total_balance_without_bonus > 0;
                $hasAnyBalance = $wallet->total_balance > 0;
                $hasOnlyBonus = $hasAnyBalance && !$hasRealBalance;
                
                // Verificar configuração de abertura de jogos
                $config = GameOpenConfig::first();
                $requiresDepositToday = $config && $config->requires_deposit_today;
                
                if($requiresDepositToday) {
                    // Se não tem saldo nenhum OU tem apenas saldo bônus, precisa depositar hoje
                    if(!$hasAnyBalance || $hasOnlyBonus) {
                        $user = auth('api')->user();
                        $hasDepositToday = Deposit::where('user_id', $user->id)
                            ->where('status', 1)
                            ->whereDate('created_at', Carbon::today())
                            ->exists();
                        
                        if(!$hasDepositToday) {
                            $message = !$hasAnyBalance 
                                ? 'Você não tem saldo. Para jogar, precisa fazer um depósito.'
                                : 'Você tem apenas saldo bônus. Para jogar, precisa fazer um depósito.';
                                
                            return response()->json([
                                'error' => $message,
                                'status' => false,
                                'action' => 'deposit',
                                'requires_deposit' => true,
                                'reason' => !$hasAnyBalance ? 'no_balance' : 'bonus_only'
                            ], 200);
                        }
                    }
                }
                
                // Se chegou até aqui, pode jogar
                // Se a configuração está desativada, pode jogar sem saldo
                // Se a configuração está ativada, já foi verificado acima que pode jogar
                $game->increment('views');

                $token = \Helper::MakeToken([
                            'id' => auth('api')->user()->id,
                            'game' => $game->game_code
                        ]);
                     
                // Adicionar dados de like se o usuário estiver autenticado
                if (auth('api')->check()) {
                    $game->hasLike = $game->hasLikeByUser(auth('api')->id());
                    $game->totalLikes = $game->likes()->count();
                }

                switch ($game->distribution) {
                    case 'source':
                        return response()->json([
                            'game' => $game,
                            'gameUrl' => url('/originals/'.$game->game_code.'/index.html?token='.$token),
                            'token' => $token
                        ]);
                        case 'play_fiver':
                            $playfiver = self::playFiverLaunch($game->game_id, $game->only_demo);
                            return response()->json([
                                'game' => $game,
                                'gameUrl' => $playfiver['launch_url'],
                                'token' => $token
                            ]);
  
                    case 'vibra_gaming':
                        return response()->json([
                            'game' => $game,
                            'gameUrl' => self::GenerateGameLaunch($game),
                            'token' => $token
                        ]);


                        return response()->json(['error' => $fiversLaunch, 'status' => false ], 400);
                    case 'pgclone':
                        $games2ApiLaunch = self::GameLaunchGames2($game->provider->code, $game->game_id, 'pt', auth('api')->id());

                        if(isset($games2ApiLaunch['launch_url'])) {
                            return response()->json([
                                'game' => $game,
                                'gameUrl' => $games2ApiLaunch['launch_url'],
                                'token' => $token
                            ]);
                        }
                    case 'worldslot':
                        $worldslotLaunch = self::GameLaunchWorldSlot($game->provider->code, $game->game_id, 'pt', auth('api')->id());

                        if(isset($worldslotLaunch['launch_url'])) {
                            return response()->json([
                                'game' => $game,
                                'gameUrl' => $worldslotLaunch['launch_url'],
                                'token' => $token
                            ]);
                        }

                        return response()->json(['error' => $worldslotLaunch, 'status' => false ], 400);

                }
                
                // Se chegou até aqui é porque não tem saldo
                return response()->json([
                    'error' => 'Você precisa ter saldo para jogar.',
                    'status' => false,
                    'action' => 'deposit',
                    'reason' => 'no_balance'
                ], 200);
            }
            return response()->json(['error' => 'Você precisa tá autenticado para jogar', 'status' => false ], 400);
        }
        return response()->json(['error' => '', 'status' => false ], 400);
    }

    /**
     * @dev victormsalatiel
     * Show the form for editing the specified resource.
     */
    public function allGames(Request $request)
    {
        $query = Game::query();
        $query->with(['provider', 'categories', 'likes']);

        if (!empty($request->provider) && $request->provider != 'all') {
            $query->where('provider_id', $request->provider);
        }

        if (!empty($request->category) && $request->category != 'all') {
            $query->whereHas('categories', function ($categoryQuery) use ($request) {
                $categoryQuery->where('slug', $request->category);
            });
        }

        if (isset($request->searchTerm) && !empty($request->searchTerm) && strlen($request->searchTerm) > 2) {
            $query->whereLike(['game_code', 'game_name', 'description', 'distribution', 'provider.name'], $request->searchTerm);
        }else{
            $query->orderBy('views', 'desc');
        }

        $games = $query
            ->where('status', 1)
            ->paginate(12)->appends(request()->query());

        // Adicionar dados de like se o usuário estiver autenticado
        if (auth('api')->check()) {
            $games->getCollection()->transform(function ($game) {
                $game->hasLike = $game->hasLikeByUser(auth('api')->id());
                $game->totalLikes = $game->likes()->count();
                return $game;
            });
        }

        return response()->json(['games' => $games]);
    }

  
        public function webhookPlayFiver(Request $request)
          {
              return self::webhookPlayFiverAPI($request);
          }
         
}
