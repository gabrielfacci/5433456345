<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Cookie;
use Auth;

class SiteController extends Controller
{

    public function faq()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }

        return view('faq', ['token' => $token, 'time' => $time]);
    }

    public function support() {
        return view('support', []);
    }

    public function originais() {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('originais', ['token' => $token, 'time' => $time]);
    }

    public function construction() {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('construction', ['token' => $token, 'time' => $time]);
    }

    public function cassino() {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('cassino', ['token' => $token, 'time' => $time]);
    }

    public function newmenu() {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('newmenu', ['token' => $token, 'time' => $time]);
    }

    public function home()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('home', ['token' => $token, 'time' => $time]);
    }

    public function update()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
        }
        return view('authenticator', ['token' => $token, 'time' => $time]);
    }
	
    public function intro()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('intro', ['token' => $token, 'time' => $time]);
    }

    public function tower()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('tower', ['token' => $token, 'time' => $time]);
    }

    public function rewards()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('recompensas', ['token' => $token, 'time' => $time]);
    }
	    public function terms()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('terms', ['token' => $token, 'time' => $time]);
    }
	
    public function games()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('intro', ['token' => $token, 'time' => $time]);
    }

    public function dice()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('dice', ['token' => $token, 'time' => $time]);
    }

    public function admin()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('admin', ['token' => $token, 'time' => $time]);
    }

    public function coinflip()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('coinflip', ['token' => $token, 'time' => $time]);
    }
	
    public function roulette()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('roulette', ['token' => $token, 'time' => $time]);
    }

    public function doubleplus()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $refcode = Cookie::get('refcode');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $referred = DB::table('users')->select('email')->where('code', $refcode)->first();
            $user = DB::table('users')->select('inviter')->where('email', Auth::user()->email)->first();
            if($refcode != null && strlen($refcode) == 7 && $referred != null) {
                if($user->inviter == NULL && $referred->email != Auth::user()->email) {
                    DB::table('users')->where('email', Auth::user()->email)->update(array('inviter' => $referred->email));
                }
            }
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('double-plus', ['token' => $token, 'time' => $time]);
    }

    public function jackpot()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('jackpot', ['token' => $token, 'time' => $time]);
    }

    public function crash()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
        }
        return view('crash1', ['token' => $token, 'time' => $time]);
    }
	
    public function withdraw()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
			return view('withdraw', ['token' => $token, 'time' => $time]);
		}
		else {
            return redirect('auth/login');
        }
    }
	
    public function deposit()
    {
        $token = '';
        $time = time()-34;
    	if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
            return view('deposit', ['token' => $token, 'time' => $time]);
        }
        else {
            return redirect('auth/login');
        }
    }

    public function profile()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            $deposits = DB::table('payment_history')->where('user', Auth::user()->email)->where('credited', 1)->get();
            $deposits2 = DB::table('payment_history')->where('user', Auth::user()->email)->where('credited', 1)->get();
            $total_deposits = 0;
            $profit = 0;
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));

            foreach ($deposits as $deposit) {
                $total_deposits = $deposit->worth;
            }

            $profit = $total_deposits - Auth::user()->wallet;
            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
            return view('profile', ['token' => $token, 'time' => $time, 'profit' => $profit, 'deposits' => $total_deposits]);
        }
        else {
            return redirect('auth/login');
        }
    }
	
    public function referrals()
    {
        $token = '';
        $time = time()-34;
        if (Auth::check()){
            if(Auth::user()->banned) return view('banned');
            $token = hash('sha256','5<Grwtf`CKzk~(Fu'.md5(Auth::user()->email.'-'.time()));
            DB::table('users')->where('email', Auth::user()->email)->update(array('token_time' => $time,'token' => $token,'logged_in' => 0));
            if(strlen(Auth::user()->code) > 0) $rows = DB::table('users')->where(['inviter' => Auth::user()->email])->get()->count(); else $rows = 0;

            if((Auth::user()->cpf == NULL || Auth::user()->last_name == NULL || Auth::user()->phone == NULL || Auth::user()->name == NULL) && Auth::user()->rank != 'siteAdmin') return view('authenticator', ['token' => $token, 'time' => $time]);
            return view('referrals', ['token' => $token, 'time' => $time, 'reffered' => $rows]);
        }
        else {
            return redirect('auth/login');
        }
    }

    public function refcode($code)
    {
        Cookie::queue('refcode', $code, ((60 * 24) * 3));
        Cookie::get('refcode');
        return redirect('/');
    }
}