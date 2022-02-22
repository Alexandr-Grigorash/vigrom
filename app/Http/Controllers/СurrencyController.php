<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class СurrencyController extends Controller
{
    public function index()
    {
        $response = Http::get('https://www.cbr.ru/scripts/XML_daily.asp');
        $array = json_decode(json_encode(simplexml_load_string($response)),true);

        if ( !empty($array['Valute'])) {
            $i=0;
            foreach ($array['Valute'] as $elem) {
                if($elem['CharCode'] == "USD"){
                    $currency = $elem['Value'];
                }
                ++$i;
            }
        }
        Cache::put('currency', $currency, $seconds = 300);
    }


}
