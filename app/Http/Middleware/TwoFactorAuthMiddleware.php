<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class TwoFactorAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (!auth()->check()) {
            return $next($request);
        }

        // Verifica se o usuário tem role de admin
        if (!auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        // Verifica se já passou pela verificação 2FA
        if (Session::get('2fa_verified')) {
            return $next($request);
        }

        // Log para debug
        Log::info('2FA Middleware', [
            'user' => auth()->user()->email,
            'verified' => Session::get('2fa_verified'),
            'path' => $request->path()
        ]);

        // Se não passou pela 2FA, redireciona para a página de verificação
        // Exceto se já estiver na página de verificação para evitar loop
        if ($request->path() !== Filament::getCurrentPanel()->getPath() . '/2fa-verify') {
            return redirect()->to('/' . Filament::getCurrentPanel()->getPath() . '/2fa-verify');
        }

        return $next($request);
    }
} 