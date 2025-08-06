<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Evolution extends Model
{

    protected $table = 'evo_transactions';

    protected $fillable = [
        'transationId',
        'transationRefId',
        'uuid',
        'amount',
        'status',
        'type',
        'gameId',
        'gameType',
        'tableId',
        'currency',
        'userId',
        'sid'
    ];

    // static function lastId() {
    //     $slots = self::select('id')->latest('id')->first();
    //     return $slots['id'];
    // }

    // static function checkReplicated($action_id) {
    //     $action = self::select('id','game','game_id','user','action','action_id','charge','status')->where('action_id', $action_id)->first();
    //     return $action;
    // }
}

