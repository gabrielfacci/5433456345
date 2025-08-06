<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlotsHash extends Model
{

    protected $table = 'slots_hash';

    protected $fillable = [
        'id',
        'user_id',
        'session_token',
        'hash'
    ];

    static function lastHash($hash) {
        $hash_slots = self::where('hash', $hash)->latest('id')->first();
        return $hash_slots;
    }

    static function getSessionToken($session_token) {
        $hash_slots = self::where('session_token', $session_token)->latest('id')->first();
        return $hash_slots;
    }
    
}
