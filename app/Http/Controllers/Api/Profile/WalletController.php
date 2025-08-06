<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewWithdrawalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wallet = Wallet::whereUserId(auth('api')->id())->where('active', 1)->first();
        return response()->json(['wallet' => $wallet], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function myWallet()
    {
        $wallets = Wallet::whereUserId(auth('api')->id())->get();
        return response()->json(['wallets' => $wallets], 200);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function setWalletActive($id)
    {
        $checkWallet = Wallet::whereUserId(auth('api')->id())->where('active', 1)->first();
        if(!empty($checkWallet)) {
            $checkWallet->update(['active' => 0]);
        }

        $wallet = Wallet::find($id);
        if(!empty($wallet)) {
            $wallet->update(['active' => 1]);
            return response()->json(['wallet' => $wallet], 200);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestWithdrawal(Request $request)
{
    $setting = Setting::first();

    if (!auth('api')->check()) {
        return response()->json(['error' => 'Não autenticado.'], 401);
    }

    $user = auth('api')->user();

    // Verificar se utilizou bônus de cadastro
    if ($user->utilizou_bonus_cadastro == 1 && $setting->disable_deposit_min == 1) {
        $depositSum = \DB::table('deposits')
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->sum('amount');

        if ($depositSum < $setting->deposit_min_saque) {
            $remaining = $setting->deposit_min_saque - $depositSum;
            return response()->json([
                'error' => 'Você precisa depositar mais ' . number_format($remaining, 2, ',', '.') . ' para poder sacar.'
            ], 400);
        }
    }

    // Regras de validação dinâmicas
    $rules = [
        'amount' => ['required', 'numeric', 'min:'.$setting->min_withdrawal, 'max:'.$setting->max_withdrawal],
        'accept_terms' => 'required|accepted',
    ];

    if ($request->type === 'pix') {
        $rules['pix_type'] = 'required';

        switch ($request->pix_type) {
            case 'document':
                $rules['pix_key'] = 'required|cpf_ou_cnpj';
                break;
            case 'email':
                $rules['pix_key'] = 'required|email';
                break;
            default:
                $rules['pix_key'] = 'required';
                break;
        }
    }

    if ($request->type === 'bank') {
        $rules['bank_info'] = 'required';
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Verificar limite de saques
    if ($setting->withdrawal_limit && $setting->withdrawal_period) {
        $query = Withdrawal::where('user_id', $user->id);
        switch ($setting->withdrawal_period) {
            case 'daily':
                $query->whereDate('created_at', now()->toDateString());
                break;
            case 'weekly':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                break;
            case 'yearly':
                $query->whereYear('created_at', now()->year);
                break;
        }

        if ($query->count() >= $setting->withdrawal_limit) {
            return response()->json(['error' => 'Você atingiu o limite de saques do período.'], 400);
        }
    }

    // Verificar saldo
    if (floatval($request->amount) > floatval($user->wallet->balance_withdrawal)) {
        return response()->json(['error' => 'Você não tem saldo suficiente'], 400);
    }

    // Criar registro de saque
    $data = [
        'user_id' => $user->id,
        'amount' => \Helper::amountPrepare($request->amount),
        'type' => $request->type,
        'currency' => $request->currency,
        'symbol' => $request->symbol,
        'status' => 0,
    ];

    if ($request->type === 'pix') {
        $data['pix_key'] = $request->pix_key;
        $data['pix_type'] = $request->pix_type;
    }

    if ($request->type === 'bank') {
        $data['bank_info'] = $request->bank_info;
    }

    $withdrawal = Withdrawal::create($data);

    if ($withdrawal) {
        $user->wallet->decrement('balance_withdrawal', floatval($request->amount));

        // Notificar administradores
        $admins = User::where('role_id', 0)->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewWithdrawalNotification($user->name, $request->amount));
        }

        return response()->json(['status' => true, 'message' => 'Processando saque...'], 200);
    }

    return response()->json(['error' => 'Erro ao realizar o saque'], 400);
}
  }