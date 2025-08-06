<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slots extends Model
{

    protected $table = 'slots_transactions';

    protected $fillable = [
        'id',
        'provider',
        'game',
        'game_id',
        'user',
        'action',
        'action_id',
        'charge',
        'status',
        'created_date'
    ];

    static function lastId() {
        $slots = self::select('id')->latest('id')->first();
        return $slots['id'];
    }

    static function checkReplicated($action_id) {
        $action = self::select('id','game','game_id','user','action','action_id','charge','status')->where('action_id', $action_id)->first();
        return $action;
    }

    static function checkDuplicate($provider_tx_id) {
        $action = self::where('game_id', $provider_tx_id)->first();
        if($action) {
            return true;
        } else {
            return false;
        }
    }

    static function getTransactionByGameId($game_id) {
        $action = self::where('game_id', $game_id)->first();
        return $action;
    }
}
