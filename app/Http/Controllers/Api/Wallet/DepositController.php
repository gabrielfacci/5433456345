<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Traits\Gateways\BsPayTrait;
use Illuminate\Http\Request;
use App\Traits\Gateways\OndaPayTrait;
class DepositController extends Controller
{
    use BsPayTrait, OndaPayTrait;

    /**
     * @param Request $request
     * @return array|false[]
     */
    public function submitPayment(Request $request)
    {
        \Log::info($request->gateway);
        switch ($request->gateway) {
            case 'ondapay':
                return self::requestQrcodeOnda($request);
            case 'bspay':
                return BsPayTrait::requestQrcode($request);
            }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function consultStatusTransactionPix(Request $request)
    {
        return self::consultStatusTransactionOndaPay($request);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::select('amount', 'created_at', 'currency', 'id', 'status', 'symbol', 'type', 'updated_at', 'user_id')
                        ->whereUserId(auth('api')->id())
                        ->paginate();
        return response()->json(['deposits' => $deposits], 200);
    }

}
