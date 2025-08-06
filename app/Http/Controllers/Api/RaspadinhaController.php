<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Raspadinha;
use Illuminate\Http\Request;

class RaspadinhaController extends Controller
{
    public function index()
    {
        $raspadinhas = Raspadinha::active()->ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => $raspadinhas
        ]);
    }

    public function byCategory($category)
    {
        $raspadinhas = Raspadinha::active()->byCategory($category)->ordered()->get();
        
        return response()->json([
            'success' => true,
            'data' => $raspadinhas
        ]);
    }
}
