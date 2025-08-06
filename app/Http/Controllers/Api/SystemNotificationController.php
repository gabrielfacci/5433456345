<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SystemNotificationController extends Controller
{
    /**
     * Retorna lista de notificações e quantidade de não lidas.
     */
    public function index(): JsonResponse
    {
        try {
        $user = Auth::user();
        $lastCheck = $user?->last_notifications_check;

            // Otimizar query para buscar apenas campos necessários
        $notifications = SystemNotification::query()
                ->select(['id', 'title', 'description', 'image', 'link', 'created_at'])
            ->where('active', true)
            ->latest()
            ->get();

            // Map image URL de forma mais eficiente
            $notifications = $notifications->map(function ($item) {
                $data = $item->only(['id', 'title', 'description', 'image', 'link', 'created_at']);
                if ($data['image']) {
                    $data['image'] = Storage::disk('public')->url($data['image']);
            }
                return $data;
        });

        $unread = $notifications->where('created_at', '>', $lastCheck)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread'        => $unread,
        ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar notificações: ' . $e->getMessage());
            return response()->json([
                'notifications' => [],
                'unread'        => 0,
            ], 500);
        }
    }

    /**
     * Marca todas como lidas (atualiza timestamp).
     */
    public function markRead(): JsonResponse
    {
        if ($user = Auth::user()) {
            $user->forceFill(['last_notifications_check' => now()])->save();
        }

        return response()->json(['status' => 'ok']);
    }
}
