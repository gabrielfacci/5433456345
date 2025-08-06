<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Evolution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EvoController extends Controller
{
    public function check(Request $r)
    {
        $postFields = $r->all();
        session_start();

        $user = User::where('id', intval($postFields['userId']))->first();

        if (!$user) {
            return [
                "code" => 200,
                "status" => "INVALID_PARAMETER",
                "sid" =>  "",
                "uuid" => ""
            ];
        }

        $balance = $user->wallet;
        $bonus = $user->wallet_bonus;

        return [
            "code" => 200,
            "status" => "OK",
            "balance" => sprintf('%.2f', $balance),
            "bonus" => sprintf('%.2f', $bonus),
            "sid" =>  session_id(),
            "uuid" => $postFields['uuid']
        ];
    } // Ok

    public function sid(Request $r)
    {
        $postFields = $r->all();
        session_start();

        return [
            "code" => 200,
            "status" => "OK",
            "sid" =>  session_id(),
            "uuid" => $postFields['uuid'],
        ];
    } // Ok

    public function balance(Request $r)
    {
        $postFields = $r->all();

        $user = User::where('id', intval($postFields['userId']))->first();

        if (!$user) {
            return [
                "code" => 200,
                "status" => "INVALID_PARAMETER"
            ];
        }

        $balance = $user->wallet;
        $bonus = $user->wallet_bonus;

        return [
            "code" => 200,
            "status" => "OK",
            "balance" => sprintf('%.2f', $balance),
            "bonus" => sprintf('%.2f', $bonus),
        ];
    } // Ok

    public function debit(Request $r)
    {
        $postFields = $r->all();
        $check = Evolution::where('transationRefId', $postFields['transaction']['refId'])->first();
        $user = User::where('id', intval($postFields['userId']))->first();

        if (!$user) {
            return [
                "code" => 200,
                "status" => "INVALID_PARAMETER",
                "balance" => null,
                "bonus" => null,
                'uuid' => $postFields['uuid'],
            ];
        }

        if ($check && $check->type === 'cancel') {
            return [
                "code" => 200,
                "status" => "FINAL_ERROR_ACTION_FAILED",
                "balance" => null,
                "bonus" => null,
                'uuid' => $postFields['uuid'],
            ];
        }   

        if (!$check) {
            Evolution::insert(
                [
                    'transationId' => $postFields['transaction']['id'],
                    'transationRefId' => $postFields['transaction']['refId'],
                    'uuid' => $postFields['uuid'],
                    'amount' => $postFields['transaction']['amount'],
                    'status' => NULL,
                    'type' => 'deposit',
                    'gameId' => $postFields['game']['id'],
                    'gameType' => $postFields['game']['type'],
                    'tableId' => $postFields['game']['details']['table']['id'],
                    'currency' => $postFields['currency'],
                    'userId' => $postFields['userId'],
                    'sid' => $postFields['sid'],
                ]
            );
        }

        if ($check) {
            $balance = $user->wallet;
            $bonus = $user->wallet_bonus;

            return [
                "code" => 200,
                "status" => "OK",
                "balance" => sprintf('%.2f', $balance),
                "bonus" => sprintf('%.2f', $bonus),
                'uuid' => $postFields['uuid'],
            ];
        }

        if ($postFields['transaction']['amount'] > $user->wallet) {
            return [
                "code" => 200,
                "status" => "INSUFFICIENT_FUNDS",
                "balance" => null,
                "bonus" => null,
                'uuid' => $postFields['uuid'],
            ];
        }

        $user->wallet -= $postFields['transaction']['amount'];
        $user->save();

        $balance = $user->wallet;
        $bonus = $user->wallet_bonus;

        return [
            "code" => 200,
            "status" => "OK",
            "balance" => sprintf('%.2f', $balance),
            "bonus" => sprintf('%.2f', $bonus),
            'uuid' => $postFields['uuid'],
        ];
    } // Ok

    public function credit(Request $r)
    {
        $postFields = $r->all();
        $status = NULL;

        $user = User::where('id', intval($postFields['userId']))->first();
        $check = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'deposit')->first();
        $checkCredited = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'credit')->first();
        $checkCancel = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'cancel')->first();

        if (!$user) {
            return [
                "code" => 200,
                "status" => "INVALID_PARAMETER",
                "sid" =>  "",
                "uuid" => ""
            ];
        }
        
        if (!$check && !$checkCredited && !$checkCancel) {
            return [
                "code" => 200,
                "status" => "BET_DOES_NOT_EXIST",
                "balance" => null
            ];
        }

        if ($checkCredited || $checkCancel) {
            return [
                "code" => 200,
                "status" => "BET_ALREADY_SETTLED",
                "balance" => null
            ];
        }
        
        if ($postFields['transaction']['amount'] <= 0) {
            $status = 'loss';
        } else {
            $status = 'win';
        }

        Evolution::insert(
            [
                'transationId' => $postFields['transaction']['id'],
                'transationRefId' => $postFields['transaction']['refId'],
                'uuid' => $postFields['uuid'],
                'amount' => $postFields['transaction']['amount'],
                'status' => $status,
                'type' => 'credit',
                'gameId' => $postFields['game']['id'],
                'gameType' => $postFields['game']['type'],
                'tableId' => $postFields['game']['details']['table']['id'],
                'currency' => $postFields['currency'],
                'userId' => $postFields['userId'],
                'sid' => $postFields['sid'],
            ]
        );

        $user->wallet += $postFields['transaction']['amount'];
        $user->save();

        $balance = $user->wallet;
        $bonus = $user->wallet_bonus;

        return [
            "code" => 200,
            "status" => "OK",
            "balance" => sprintf('%.2f', $balance),
            "bonus" => sprintf('%.2f', $bonus),
            'uuid' => $postFields['uuid'],
        ];
    } // Ok

    public function cancel(Request $r)
    {
        $postFields = $r->all();

        $user = User::where('id', intval($postFields['userId']))->first();
        $check = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'deposit')->first();
        $checkCredit = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'credit')->first();
        $checkCancel = Evolution::where('transationRefId', $postFields['transaction']['refId'])->where('type', 'cancel')->first();
        
        if (!$user) {
            return [
                "code" => 200,
                "status" => "INVALID_PARAMETER",
                "sid" =>  "",
                "uuid" => ""
            ];
        }

        if (!$check && !$checkCredit && !$checkCancel) {
            Evolution::insert(
                [
                    'transationId' => $postFields['transaction']['id'],
                    'transationRefId' => $postFields['transaction']['refId'],
                    'uuid' => $postFields['uuid'],
                    'amount' => $postFields['transaction']['amount'],
                    'status' => NULL,
                    'type' => 'cancel',
                    'gameId' => $postFields['game']['id'],
                    'gameType' => $postFields['game']['type'],
                    'tableId' => $postFields['game']['details']['table']['id'],
                    'currency' => $postFields['currency'],
                    'userId' => $postFields['userId'],
                    'sid' => $postFields['sid'],
                ]
            );

            return [
                "code" => 200,
                "status" => "BET_DOES_NOT_EXIST",
                "balance" => null
            ];
        }

        if ($checkCredit || $checkCancel) {
            return [
                "code" => 200,
                "status" => "BET_ALREADY_SETTLED",
                "balance" => null
            ];
        }

        $check->type = 'cancel';
        $check->save();

        $user->wallet += $postFields['transaction']['amount'];
        $user->save();

        $balance = $user->wallet;
        $bonus = $user->wallet_bonus;

        return [
            "code" => 200,
            "status" => "OK",
            "balance" => sprintf('%.2f', $balance),
            "bonus" => sprintf('%.2f', $bonus),
            'uuid' => $postFields['uuid'],
        ];
    } // Ok

    public function game($game)
    {
        session_start();

        $token = '';
        $time = time() - 34;

        $game_url = 'https://br-beyond-com.uat1.evo-test.com/ua/v1/beyondbr00000001/test123';

        if (!Auth::user()) {
            return redirect('/');
        }

        $user = Auth::user();
        $token = hash('sha256', '5<Grwtf`CKzk~(Fu' . md5(Auth::user()->email . '-' . time()));

        $postData = [
            "uuid" => strval($user->id),
            "player" => [
                "id" => strval($user->id),
                "update" => true,
                "firstName" => $user->name,
                "lastName" => $user->last_name,
                "nickname" => $user->username,
                "language" => "pt",
                "country" => "BR",
                "currency" => "BRL",
                "session" => [
                    "id" => session_id(),
                    "ip" => "31.220.54.234"
                ],
            ],
            "config" => [
                "game" => [
                    "table" => [
                        "id" => $game
                    ]
                ],
                "channel" => [
                    "wrapped" => true,
                    "mobile" => true
                ]
            ]
        ];

        $jsonData = json_encode($postData); // Convertendo o array para JSON

        $ch = curl_init(); // Inicializando o cURL
        curl_setopt($ch, CURLOPT_URL, $game_url); // Definindo a URL da requisição
        curl_setopt($ch, CURLOPT_POST, 1); // Definindo o método HTTP como POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Definindo o corpo da requisição
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Definindo o cabeçalho Content-Type
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retornando a resposta em vez de exibir na tela

        $response = curl_exec($ch); // Executando a requisição

        curl_close($ch);
        // Tratando a resposta
        if (!$response) {
            return redirect('/');
        } else {
            $data = json_decode($response, TRUE);

            if (!array_key_exists("entryEmbedded", $data)) {
                return redirect('/');
            } 

            $game_url = "https://br-beyond-com.uat1.evo-test.com" . $data["entryEmbedded"];
            return view('aviator', compact('game_url', 'token', 'time'));
        }
    } // Ok
}
