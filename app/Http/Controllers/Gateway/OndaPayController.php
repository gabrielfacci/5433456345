<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;

use App\Traits\Gateways\OndaPayTrait;
use App\Traits\Affiliates\AffiliateHistoryTrait;

use Illuminate\Support\Facades\DB;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Traits\Gateways\BsPayTrait;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
class OndaPayController extends Controller
{
    use OndaPayTrait, AffiliateHistoryTrait;

    /*** @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    
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
     * @dev victormsalatiel
     */
    public function callbackMethod(Request $request)
    {
        ///\DB::table('debug')->insert(['text' => json_encode($request->all())]);
        $request = $request->all();
        
        //echo var_dump($request);
        if(isset($request['transaction_id']) && $request['type_transaction'] == 'CASH_IN') {
            $transaction = Transaction::where('payment_id', $request['transaction_id'])->where('status', 0)->first();
            $deposit = Deposit::where('payment_id', $request['transaction_id'])->where('status', 0)->first();
            
            if(!empty($transaction)) {
                //echo "to aqui";
                $wallet = Wallet::where('user_id', $transaction->user_id)->first();
                if(!empty($wallet)) {
                    if($transaction->update(['status' => 1])) {
                        $deposit->update(['status' => 1]);
                        $setting = Setting::first();

                        $checkTransactions = Transaction::where('user_id', $transaction->user_id)->count();
                        if($checkTransactions <= 1) {
                            /// pagar o bonus
                            $bonus = \Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                            $wallet->increment('balance_bonus', $bonus);
                            $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                        }
                        
                        /// SEMPRE adicionar o valor depositado à carteira principal
                        if($setting->disable_rollover) {
                            $wallet->increment('balance_withdrawal', $transaction->price); /// carteira de saque
                        } else {
                            $wallet->increment('balance', $transaction->price); /// carteira de jogos, não permite sacar
                        }

                        $user = User::find($transaction->user_id);

                        self::saveAffiliateHistory($user); /// paga o afiliado
                        self::FinishTransactionOndaPay($transaction->price, $user->id); /// finaliza a transação
                    }
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
        return self::consultStatusTransactionOndaPay($request);
    }

    /**
     * Display the specified resource.
     * @dev victormsalatiel
     */
    public function withdrawalFromModal($id)
    {
        $withdrawal = Withdrawal::find($id);
        if(!empty($withdrawal)) {
            $parm = [
                'pix_key'           => $withdrawal->chave_pix,
                'pix_type'          => $withdrawal->tipo_chave,
                'amount'            => $withdrawal->amount,
                'document'          => $withdrawal->document,
                'payment_id'        => $withdrawal->id,
              	'name' => $withdrawal->user->name
            ];

            $resp = self::MakePaymentOndaPay($parm);

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
    
    /**
     * Cancel Withdrawal
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelWithdrawalFromModal($id, $action='user')
    {
        if($action == 'user') {
            return $this->cancelWithdrawalUser($id);
        }

        if($action == 'affiliate') {
            return $this->cancelWithdrawalAffiliate($id);
        }
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
}
