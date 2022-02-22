<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use App\Models\LogBalanceChange;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class WalletController extends Controller
{
    public function balance(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'walletId' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ]);
        }

        $wallet = Wallet::find($request['walletId']);

        if (!$wallet) {
            return response()->json([
                "error" => 'Wallet with ID ' . $request["walletId"] . ' not found',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => $wallet->balance,
            "Cache" => Cache::get('currency')
        ]);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transactionType' =>  [
                'required',
                'string',
                Rule::in(['credit', 'debit']),
            ],
            'walletId' => 'integer',
            'currency' => [
                'required',
                'string',
                Rule::in(['RUB', 'USD']),
            ],
            'amount' => 'required|regex:/^\d*(\.\d{2})?$/',
            'reason' => [
                'required',
                'string',
                Rule::in(['stock', 'refund']),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ]);
        }

        $wallet = Wallet::find($request['walletId']);

        if (!$wallet) {
            return response()->json([
                "error" => 'Wallet with ID ' . $request['walletId'] . ' not found',
            ]);
        }

        if ($wallet->currency == 'RUB' && $request['currency'] == "USD") {
            $request['amount'] = $request['amount'] * (float)Cache::get('currency');
        }
        if ($wallet->currency == 'USD' && $request['currency'] == "RUB") {
            $request['amount'] = $request['amount'] / (float)Cache::get('currency');
        }

        $newBalance = 0;
        if ($request['transactionType'] == 'debit') {
            $newBalance = $wallet->balance + $request['amount'];
        }
        if ($request['transactionType'] == 'credit') {
            if ($wallet->balance <= $request['amount']) {
                $newBalance = $wallet->balance - $request['amount'];
            } else {
                return response()->json([
                    "error" => 'Insufficient funds.'
                ]);
            }
        }

        \DB::transaction(function () use ($request, $wallet, $newBalance) {
            $wallet->balance = $newBalance;
            $wallet->save();

            LogBalanceChange::create([
                'walletId' => $request['walletId'],
                'before_currency' => $wallet->currency,
                'after_currency' => $request['currency'],
                'before_balance' => $wallet->balance,
                'after_balance' => $newBalance,
                'reason' => $request['reason']
            ]);
        });

        return response()->json([
            "success" => true,
            "balance" => $newBalance,
            "Cache" => Cache::get('currency')
        ]);
    }
}
