<?php

namespace App\Http\Controllers\Api;
use App\Models\Wallet;
use App\Models\LogBalanceChange;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;

class WalletController extends Controller
{
    public function balance(Request $request): \Illuminate\Http\JsonResponse
    {
        $walletId = $request->walletId;
        $balance = Wallet::where('id', $walletId)
            ->select('balance')
            ->get();
        return response()->json([
            "data" => $balance,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'transactionType' => 'required',
            'walletId' => 'required',
            'currency' => 'required',
            'amount' => 'required',
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" =>  $validator->errors()
            ]);
        }
        //проверяем валюту
        $balance = Wallet::where('id', $input['walletId'])
            ->get();
        $newBalance = 0;
        if($input['transactionType'] == 'debit'){
            $newBalance = $balance[0]['balance'] + $input['amount'];
        }
        if($input['transactionType'] == 'credit'){
            if($balance[0]['balance'] <= $input['amount']){
                $newBalance = $balance[0]['balance'] - $input['amount'];
            }else{
                return response()->json([
                    "error" =>  'Insufficient funds('
                ]);
            }
        }

        Wallet::where("id", $input['walletId'])->update(["balance" => $newBalance]);

        LogBalanceChange::create([
            'walletId' => $input['walletId'],
            'before_currency' => $balance[0]['currency'],
            'after_currency' => $input['currency'],
            'before_balance' => $balance[0]['balance'],
            'after_balance' => $newBalance,
            'reason' => 'stock'
        ]);

        return response()->json([
            "success" => true,
            "balance" => $newBalance,
        ]);
    }
}
