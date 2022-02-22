<?php

$curl = curl_init();
$params = array(
    'transactionType' => 'debit',
    'walletId' => 1,
    'amount' => 12.22,
    'currency' => 'USD',
    'reason' => 'refund'
);

curl_setopt($curl, CURLOPT_URL, 'http://734183-cs93484.tmweb.ru/api/wallet/update');
curl_setopt($curl, CURLOPT_HTTPHEADER, array());
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));

$response = curl_exec($curl);
curl_close($curl);

echo $response; 
