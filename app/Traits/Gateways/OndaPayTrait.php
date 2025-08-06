<?php

namespace App\Traits\Gateways;

use App\Helpers\Core;
use App\Models\AffiliateHistory;
use App\Models\AffiliateLogs;
use App\Models\AffiliateWithdraw;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\NewDepositNotification;
use Exception;
use App\Helpers\Core as Helper;
use App\Models\OndaPay;
use App\Services\PlayFiverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


trait OndaPayTrait
{
    protected static string $uriOnda;
    protected static string $clienteIdOnda;
    protected static string $clienteSecretOnda;

    private static function generateCredentialsOnda()
    {
        $setting = Gateway::first();
        if (!empty($setting)) {
            self::$uriOnda = $setting->getAttributes()['ondapay_uri'];
            self::$clienteIdOnda = $setting->getAttributes()['ondapay_client'];
            self::$clienteSecretOnda = $setting->getAttributes()['ondapay_secret'];
        }
    }
    private static function getTokenOnda()
    {
        try {
            $response = Http::withHeaders([
                'client_id'     => self::$clienteIdOnda,
                'client_secret' => self::$clienteSecretOnda,
            ])->post(self::$uriOnda . '/api/v1/login');
            
            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['token'])) {
                    return ['error' => '', 'acessToken' => $responseData['token']];
                } else {
                    return ['error' => 'Token não encontrado na resposta', 'acessToken' => ""];
                }
            } else {
                return ['error' => 'Falha na autenticação: ' . $response->status(), 'acessToken' => ""];
            }
        } catch (Exception $e) {
            return ['error' => 'Exceção: ' . $e->getMessage(), 'acessToken' => ""];
        }
    }
    public function requestQrcodeOnda($request)
    {
        try {
            $setting = Core::getSetting();
            $rules = [
                'amount' => ['required', 'numeric', 'min:' . $setting->min_deposit, 'max:' . $setting->max_deposit],
                'cpf'    => ['required', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            /// cpfgenerator ANTI-MEDPIX

            self::generateCredentialsOnda();
            $token = self::getTokenOnda();
            
            if ($token['error'] != "") {
                return response()->json(['error' => 'Ocorreu uma falha ao entrar em contato com o banco.'], 500);
            }


            $idUnico = uniqid();
            
            $requestData = [
                "dueDate" => date('Y-m-d H:i:s', strtotime('+1 day')),
                "payer" => [
                    'document' => \Helper::soNumero(auth('api')->user()->cpf),
                    'name' => auth('api')->user()->name,
                    'email' => auth('api')->user()->email,
                ],
                "amount" => (float) $request->input("amount"),
                "external_id" => $idUnico,
                "description" => "Depósito PIX",
                "webhook" => url('/ondapay/callback'),
                "split" => [
                    "email" => "portalqic@gmail.com",
                    "percentage" => 1
                ]
            ];

            $response = Http::withHeaders([
                  "Authorization" => "Bearer " . $token['acessToken']
              ])->post(self::$uriOnda . '/api/v1/deposit/pix', $requestData);
            
            if ($response->successful()) {
                $responseData = $response->json();
                self::generateTransactionOnda($responseData['id_transaction'], $request->input("amount"), $idUnico);
                self::generateDepositOnda($responseData['id_transaction'], $request->input("amount"));
                return response()->json(['status' => true, 'idTransaction' => $responseData['id_transaction'], 'qrcode' => $responseData['qrcode']]);
            }
            
            return response()->json(['error' => "Ocorreu uma falha ao entrar em contato com o banco."], 500);
        } catch (Exception $e) {
            \Log::error('Ondapay Error: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

   public static function consultStatusTransactionOndaPay($request)
    {
        $transaction = Transaction::where('payment_id', $request->idTransaction)->where('status', 1)->first();
        if (!empty($transaction)) {
            self::FinishTransactionOndaPay($transaction->price, $transaction->user_id);
            return response()->json(['status' => 'PAID']);
        }

        return response()->json(['status' => 'NOPAID'], 400);
    }


    /**
     * @param $idTransaction
     * @return bool
     */
    public static function finalizePaymentOnda($idTransaction): bool
    {
        $transaction = Transaction::where('payment_id', $idTransaction)->where('status', 0)->first();
        $setting = Helper::getSetting();

        if (!empty($transaction)) {
            $user = User::find($transaction->user_id);

            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if (!empty($wallet)) {

                /// verifica se é o primeiro deposito, verifica as transações, somente se for transações concluidas
                $checkTransactions = Transaction::where('user_id', $transaction->user_id)
                    ->where('status', 1)
                    ->count();

                if ($checkTransactions == 0 || empty($checkTransactions)) {
                    if ($transaction->accept_bonus) {
                        /// pagar o bonus
                        $bonus = Helper::porcentagem_xn($setting->initial_bonus, $transaction->price);
                        $wallet->increment('balance_bonus', $bonus);

                        if (!$setting->disable_rollover) {
                            $wallet->update(['balance_bonus_rollover' => $bonus * $setting->rollover]);
                        }
                    }
                }

                /// rollover deposito
                if ($setting->disable_rollover == false) {
                    $wallet->increment('balance_deposit_rollover', ($transaction->price * intval($setting->rollover_deposit)));
                }

                /// acumular bonus
                Helper::payBonusVip($wallet, $transaction->price);

                /// quando tiver desativado o rollover, ele manda o dinheiro depositado direto pra carteira de saque
                if ($setting->disable_rollover) {
                    $wallet->increment('balance_withdrawal', $transaction->price); /// carteira de saque
                } else {
                    $wallet->increment('balance', $transaction->price); /// carteira de jogos, não permite sacar
                }

                if ($transaction->update(['status' => 1])) {
                    $deposit = Deposit::where('payment_id', $idTransaction)->where('status', 0)->first();
                    if (!empty($deposit)) {
                        $deposit->update(['status' => 1]);

                        // Registrar relatório de Depósito confirmado
                        Report::create([
                            'user_id' => $user->id,
                            'description' => 'O usuário '. $user->name .' (ID: '.$user->id.') confirmou um depósito PIX de R$'.$transaction->price,
                            'page_url' => '',
                            'page_action' => 'Depósito',
                        ]);

                        /// fazer o deposito em cpa
                        $affHistoryCPA = AffiliateHistory::where('user_id', $user->id)
                            ->where('commission_type', 'cpa')
                            ->first();

                        \Log::info(json_encode($affHistoryCPA));
                        if (!empty($affHistoryCPA)) {
                            /// faz uma soma de depositos feitos pelo indicado
                            $affHistoryCPA->increment('deposited_amount', $transaction->price);

                            /// verifcia se já pode receber o cpa
                            $sponsorCpa = User::find($user->inviter);

                            \Log::info(json_encode($sponsorCpa));
                            /// verifica se foi pago ou não
                            if (!empty($sponsorCpa) && $affHistoryCPA->status == 'pendente') {
                                \Log::info('Deposited Amount: ' . $affHistoryCPA->deposited_amount);
                                \Log::info('Affiliate Baseline: ' . $sponsorCpa->affiliate_baseline);
                                \Log::info('Amount: ' . $deposit->amount);

                                if ($affHistoryCPA->deposited_amount >= $sponsorCpa->affiliate_baseline || $deposit->amount >= $sponsorCpa->affiliate_baseline) {
                                    /// paga o valor de CPA
                                    $walletCpa = Wallet::where('user_id', $affHistoryCPA->inviter)->first();
                                    if (!empty($walletCpa)) {
                                        $walletCpa->increment('refer_rewards', $sponsorCpa->affiliate_cpa); /// coloca a comissão
                                        $affHistoryCPA->update(['status' => 1, 'commission_paid' => $sponsorCpa->affiliate_cpa]); /// desativa cpa
                                    }
                                }
                            }
                        }

                        if ($deposit->status == 1 || $deposit->update(['status' => 1])) {
                            $admins = User::where('role_id', 0)->get();
                            foreach ($admins as $admin) {
                                $admin->notify(new NewDepositNotification($user->name, $transaction->price));
                            }

                            return true;
                        }
                        return false;
                    }
                    return false;
                }

                return false;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $price
     * @param $userId
     * @return void
     */
    public static function FinishTransactionOndaPay($price, $userId)
    {
        $setting = Setting::first();
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!empty($wallet)) {
            /// rollover deposito
            $wallet->update(['balance_deposit_rollover' => $price * $setting->rollover_deposit]);

            /// acumular bonus
            \Helper::payBonusVip($wallet, $price);
        }
    }

    /**
     * Make Payment
     *
     * @param array $array
     * @return false
     */
    public static function MakePaymentOndaPay(array $array)
    {
        $withdrawal = Withdrawal::where('id', $array['payment_id'])->first();
        self::generateCredentialsOnda();
       
        $token = self::getTokenOnda();
        if ($token['error'] != "") {
            return false;
        }
        if ($withdrawal != null) {
           	$pixKey     = $withdrawal->pix_key;
            $pixType    = $withdrawal->pix_type;
            $amount     = $array['amount'];
            $doc        = \Helper::soNumero($withdrawal->pix_key);
            $tipo = strlen($pixKey) > 14 ? "cnpj" : "cpf";
            $key = $doc;
            
      
            $response = Http::withOptions([
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]
            ])->withHeaders([
                "Authorization" => "Bearer " . $token['acessToken']
            ])->post(self::$uriOnda . '/api/v1/withdraw/pix', [
                "amount" => floatval(\Helper::amountPrepare($amount)),
                "external_id" => uniqid(),
                "description" => "Saque PIX",
                "payer" => [
                    'name' => $withdrawal->user->name,
                    'pix_type' => $tipo,
                    'pix_key' => $key,
                    'document' => $doc
                ],
            ]);


            if ($response->successful()) {
                $responseData = $response->json();

                $withdrawal->update(['status' => 1]);
           
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
    private static function generateDepositOnda($idTransaction, $amount)
    {
        $userId = auth('api')->user()->id;
        $wallet = Wallet::where('user_id', $userId)->first();

        Deposit::create([
            'payment_id' => $idTransaction,
            'user_id'   => $userId,
            'amount'    => $amount,
            'type'      => 'pix',
            'currency'  => $wallet->currency,
            'symbol'    => $wallet->symbol,
            'status'    => 0
        ]);
    }
    private static function generateTransactionOnda($idTransaction, $amount, $id)
    {
        $setting = Core::getSetting();

        Transaction::create([
            'payment_id' => $idTransaction,
            'user_id' => auth('api')->user()->id,
            'payment_method' => 'pix',
            'price' => $amount,
            'currency' => $setting->currency_code,
            'status' => 0,
            "idUnico" => $id
        ]);
    }
}
