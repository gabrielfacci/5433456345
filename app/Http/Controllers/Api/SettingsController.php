<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getSportsbook()
    {
        return response()->json([
            'enabled' => (bool)Setting::get('sportsbook_enabled', false),
            'url' => Setting::get('sportsbook_url', ''),
        ]);
    }
} 