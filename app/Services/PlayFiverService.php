<?php

namespace App\Services;

use App\Models\GamesKey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlayFiverService
{
    protected static $secretPlayFiver;
    protected static $codePlayFiver;
    protected static $tokenPlayFiver;

    private static function credencialFiverPlay()
    {
        $setting = GamesKey::first();
        self::$secretPlayFiver = $setting->getAttributes()['playfiver_secret'];
        self::$codePlayFiver = $setting->getAttributes()['playfiver_code'];
        self::$tokenPlayFiver = $setting->getAttributes()['playfiver_token'];
    }
    public static function RoundsFree($dados){
        self::credencialFiverPlay();
        $postArray = [
            "agent_token" => self::$tokenPlayFiver,
            "secret_key" => self::$secretPlayFiver,
            "user_code" => $dados['username'],
            "game_code" => $dados['game_code'],
            "rounds" => $dados['rounds']
        ];  
        Log::info($postArray);
        $response = Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])->post("https://api.playfivers.com/api/v2/free_bonus", $postArray);
        $data = $response->json();

        if ($response->successful() && $data['status']) {
            return ["status" => true, "message" => $data['msg']];
        } else {
            return ["status" => false, "message" => $data['msg']];
        }
        return ["status" => false, "message" => $data['msg']];

    }
}
