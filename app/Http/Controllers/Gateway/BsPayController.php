<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\AffiliateWithdraw;
use App\Traits\Affiliates\AffiliateHistoryTrait;
use App\Traits\Gateways\BsPayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use App\Helpers\Core as Helper;

class BsPayController extends Controller
{
    use BsPayTrait, AffiliateHistoryTrait;


    /**
     * @dev victormsalatiel
     * @param Request $request
     * @return null
     */
    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }

    /**
     * Store a newly created resource in storage.
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function callbackMethod(Request $request)
    {
        $data = $request->requestBody;

        \DB::table('debug')->insert(['text' => json_encode($request->all())]);

        if(isset($data['transactionId']) && $data['transactionType'] == 'RECEIVEPIX') {
            if($data['status'] == "PAID") {
                if(self::finalizePayment($data['transactionId'])) {
                    return response()->json([], 200);
                }
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     * @dev victormsalatiel
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    /**
     * Display the specified resource.
     * @dev victormsalatiel
     */
    public function withdrawalFromModal($id, $action = null)
    {
        // Verificar se o usuário está autenticado e é admin
        if(!auth()->check()) {
            \Log::error('Tentativa de acesso sem autenticação ao withdrawalFromModal');
            return back()->with('error', 'Acesso negado');
        }

        if(!auth()->user()->hasRole('admin')) {
            \Log::error('Tentativa de acesso sem permissão de admin ao withdrawalFromModal');
            return back()->with('error', 'Permissão negada');
        }

        try {
            if($action == 'user') {
                return $this->confirmWithdrawalUser($id);
            }

            if($action == 'affiliate') {
                return $this->confirmWithdrawalAffiliate($id);
            }

            // Fallback para compatibilidade com código existente
            return $this->confirmWithdrawalUser($id);
        } catch (\Exception $e) {
            \Log::error('Erro ao processar saque: ' . $e->getMessage(), [
                'id' => $id,
                'action' => $action,
                'user_id' => auth()->id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'Erro ao processar saque: ' . $e->getMessage());
        }
    }

    /**
     * Cancel Withdrawal
     * @param $id
     * @param $action
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelWithdrawalFromModal($id, $action = null)
    {
        if(auth()->user()->hasRole('admin')) {
            if($action == 'user') {
                return $this->cancelWithdrawalUser($id);
            }

            if($action == 'affiliate') {
                return $this->cancelWithdrawalAffiliate($id);
            }
        }

        // Fallback para compatibilidade com código existente
        return $this->cancelWithdrawalUser($id);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    private function confirmWithdrawalUser($id)
    {
        try {
        $withdrawal = Withdrawal::find($id);
            
            if(empty($withdrawal)) {
                \Log::error('Withdrawal não encontrado para ID: ' . $id);
                Notification::make()
                    ->title('Erro no saque')
                    ->body('Saque não encontrado')
                    ->danger()
                    ->send();
                return back();
            }

            \Log::info('Processando saque de usuário:', [
                'id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'pix_key' => $withdrawal->pix_key,
                'pix_type' => $withdrawal->pix_type,
                'currency' => $withdrawal->currency
            ]);

            $pixType = $withdrawal->pix_type;
            $pixKey = $withdrawal->pix_key;
                
            if (in_array($pixType, ['document', 'phone'])) {
                $pixKey = preg_replace('/\D/', '', $pixKey);
            }
            
            $document = $withdrawal->pix_type === 'document'
            ? $withdrawal->pix_key
            : '20597998809';
            
            $parm = [
                'pix_key'    => $pixKey,
                'pix_type'   => $pixType,
                'amount'            => $withdrawal->amount,
                'document'          => $document,
                'payment_id'        => $withdrawal->id,
                'is_affiliate'      => false
            ];

            \Log::info('Parâmetros para MakePayment:', $parm);

            $resp = self::MakePayment($parm);

            \Log::info('Resposta do MakePayment:', ['success' => $resp]);

            if($resp) {
                $withdrawal->update(['status' => 1]);
                Notification::make()
                    ->title('Saque solicitado')
                    ->body('Saque solicitado com sucesso')
                    ->success()
                    ->send();

                return back();
            }else{
                Notification::make()
                    ->title('Erro no saque')
                    ->body('Erro ao solicitar o saque')
                    ->danger()
                    ->send();

                return back();
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao processar saque de usuário: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->title('Erro no saque')
                ->body('Erro interno ao processar saque')
                ->danger()
                ->send();

            return back();
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    private function confirmWithdrawalAffiliate($id)
    {
        try {
        $withdrawal = AffiliateWithdraw::find($id);
            
            if(empty($withdrawal)) {
                \Log::error('AffiliateWithdraw não encontrado para ID: ' . $id);
                Notification::make()
                    ->title('Erro no saque')
                    ->body('Saque de afiliado não encontrado')
                    ->danger()
                    ->send();
                return back();
            }

            \Log::info('Processando saque de afiliado:', [
                'id' => $withdrawal->id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'pix_key' => $withdrawal->pix_key,
                'pix_type' => $withdrawal->pix_type,
                'currency' => $withdrawal->currency
            ]);

            $pixType = $withdrawal->pix_type;
            $pixKey = $withdrawal->pix_key;
                
            if (in_array($pixType, ['document', 'phone'])) {
                $pixKey = preg_replace('/\D/', '', $pixKey);
            }
            
            $document = $withdrawal->pix_type === 'document'
            ? $withdrawal->pix_key
            : '20597998809';
            
            $parm = [
                'pix_key'    => $pixKey,
                'pix_type'   => $pixType,
                'amount'            => $withdrawal->amount,
                'document'          => $document,
                'payment_id'        => $withdrawal->id,
                'is_affiliate'      => true
            ];

            \Log::info('Parâmetros para MakePayment:', $parm);

            $resp = self::MakePayment($parm);

            \Log::info('Resposta do MakePayment:', ['success' => $resp]);

            if($resp) {
                $withdrawal->update(['status' => 1]);
                Notification::make()
                    ->title('Saque solicitado')
                    ->body('Saque solicitado com sucesso')
                    ->success()
                    ->send();

                return back();
            }else{
                Notification::make()
                    ->title('Erro no saque')
                    ->body('Erro ao solicitar o saque')
                    ->danger()
                    ->send();

                return back();
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao processar saque de afiliado: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->title('Erro no saque')
                ->body('Erro interno ao processar saque')
                ->danger()
                ->send();

            return back();
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function cancelWithdrawalUser($id)
    {
        $withdrawal = Withdrawal::find($id);
        if(!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if(!empty($wallet)) {
                $wallet->increment('balance_withdrawal', $withdrawal->amount);

                $withdrawal->update(['status' => 2]);
                Notification::make()
                    ->title('Saque cancelado')
                    ->body('Saque cancelado com sucesso')
                    ->success()
                    ->send();

                return back();
            }
            return back();
        }
        return back();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function cancelWithdrawalAffiliate($id)
    {
        $withdrawal = AffiliateWithdraw::find($id);
        if(!empty($withdrawal)) {
            $wallet = Wallet::where('user_id', $withdrawal->user_id)
                ->where('currency', $withdrawal->currency)
                ->first();

            if(!empty($wallet)) {
                $wallet->increment('refer_rewards', $withdrawal->amount);

                $withdrawal->update(['status' => 2]);
                Notification::make()
                    ->title('Saque cancelado')
                    ->body('Saque cancelado com sucesso')
                    ->success()
                    ->send();

                return back();
            }
            return back();
        }
        return back();
    }
}






















/*namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Affiliates\AffiliateHistoryTrait;
use App\Traits\Gateways\BsPayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class BsPayController extends Controller
{
    use BsPayTrait, AffiliateHistoryTrait;



    public function getQRCodePix(Request $request)
    {
        return self::requestQrcode($request);
    }


    public function callbackMethod(Request $request)
    {
        ///\DB::table('debug')->insert(['text' => json_encode($request->all())]);

        if(isset($request->transactionId) && $request->transactionType == 'RECEIVEPIX') {
            $transaction = Transaction::where('payment_id', $request->transactionId)->where('status', 0)->first();
            if(!empty($transaction)) {
                $wallet = Wallet::where('user_id', $transaction->user_id)->first();
                if(!empty($wallet)) {
                    if($transaction->update(['status' => 1])) {
                        $setting = Setting::first();

                        $checkTransactions = Transaction::where('user_id', $transaction->user_id)->count();
                        if($checkTransactions <= 1) {
                            /// pagar o bonus
                            $bonus = \Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                            $wallet->increment('balance_bonus', $bonus);
                            $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                        }else{
                            $wallet->increment('balance', $transaction->price); /// add saldo
                        }

                        $user = User::find($transaction->user_id);

                        self::saveAffiliateHistory($user); /// paga o afiliado
                        self::FinishTransaction($transaction->price, $user->id); /// finaliza a transação
                    }
                }
            }
        }
    }

    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransaction($request);
    }

    public function withdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if(!empty($withdrawal)) {
            $parm = [
                'pix_key'           => $withdrawal->chave_pix,
                'pix_type'          => $withdrawal->tipo_chave,
                'amount'            => $withdrawal->amount,
                'document'          => $withdrawal->document,
                'payment_id'        => $withdrawal->id
            ];

            $resp = self::MakePayment($parm);

            if($resp) {
                $withdrawal->update(['status' => 1]);
                Notification::make()
                    ->title('Saque solicitado')
                    ->body('Saque solicitado com sucesso')
                    ->success()
                    ->send();

                return back();
            }else{
                Notification::make()
                    ->title('Erro no saque')
                    ->body('Erro ao solicitar o saque')
                    ->danger()
                    ->send();

                return back();
            }
        }
    }
}*/
