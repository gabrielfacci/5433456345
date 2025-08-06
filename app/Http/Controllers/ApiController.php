<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Auth;
use Input;

class ApiController extends Controller
{
    public function transaction_history()
    {
    	if (Auth::check()){
            $results = DB::table('payment_history')->where('user', Auth::user()->email)->get();
            $withdraws = DB::table('withdraw')->where('email', Auth::user()->email)->get();

            $final = [];

            foreach($results as $result) {
                if($result->offer_state == 'pending') {
                    $result->status = 'Pendente';
                } else if($result->offer_state == 'paid') {
                    $result->status = 'Aprovado';
                } else {
                    $result->status = 'Cancelado';
                }

                $result = array(
                    'type' => 'Deposito',
                    'id' => count($final)+1,
                    'status' => $result->status,
                    'worth' => $result->worth,
                    'date' => $result->created_at,
                    'user' => $result->user
                );
                array_push($final, $result);
            }

            foreach($withdraws as $withdraw) {
                if($withdraw->status == '0') {
                    $withdraw->status = 'Pendente';
                } else if($withdraw->status == '1') {
                    $withdraw->status = 'Aprovado';
                } else {
                    $withdraw->status = 'Cancelado';
                }
                $withdraw = array(
                    'type' => 'Retirada',
                    'id' => count($final)+1,
                    'status' => $withdraw->status,
                    'worth' => $withdraw->amount,
                    'date' => $withdraw->date,
                    'user' => $withdraw->email
                );
                array_push($final, $withdraw);
            }

            return $final;
            // sort array with given user-defined function

        }
        else {
            return redirect('auth/login');
        }
    }

    public function betting_history()
    {
    	if (Auth::check()){
            $results = DB::table('wallet_change')->where('user', Auth::user()->email)->get();

            return $results;
        }
        else {
            return redirect('auth/login');
        }
    }

    public function affiliates_collect()
    {
        if(empty(Input::get('targetSID'))) return array('success'=>false,'reason'=>'affiliatesNoIDSupplied');
        if(gettype(Input::get('targetSID')) !== 'string') return array('success'=>false,'reason'=>'affiliatesNoIDSupplied');
        $user = DB::table('users')->where('email', strip_tags(Input::get('targetSID')))->get();
        if(count($user) < 1) return array('success'=>false,'reason'=>'affiliatesNoUserFound');
        $user = $user->first();
        if(empty($user->code)) return array('success'=>false,'reason'=>'affiliatesNoReferral');

        if(strlen($user->code) > 0) $rows = DB::table('users')->where('inviter', $user->email)->get()->count(); else $rows = 0;
        $profit = Auth::user()->referRewards - Auth::user()->collected;
        if($profit < 1) return array('success'=>false,'reason'=>'affiliatesNoCoinsToCollect');

        DB::table('users')->where('email', strip_tags(Input::get('targetSID')))->update(array('collected'=>DB::raw('collected + '.$profit),'wallet'=>DB::raw('wallet + '.$profit)));
        DB::table('wallet_change')->insert(array('user'=>strip_tags(Input::get('targetSID')),'change'=>$profit,'reason'=>'Affiliates - '.Input::get('targetSID')));
        return array('success'=>true,'reffered' => $rows + Auth::user()->collected, 'profit' => $profit);
    }
}