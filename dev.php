<?php

//require 'vendor/autoload.php'; // 引入 Composer 自动加载文件
require 'src/PaymentGateway.php';

use VggPay\PaymentGateway;


$config = [
    'projectId' => '2489KDU',
    'SecretKey' => '88d4012da55e249ab48cffbe2f19d6326e524680d5dfa8b5990b02fdc9473682',
    'SecretIV' => '6ad4dabbb9844769fb33e8655a78a7fc'
];


// 创建实例并调用方法
$gateway = new PaymentGateway($config);
$OrderData = [
    "m_orderid" => 'yourShopOrder12345679',
    "currency" => 'EUR',
    "amount" => '815.23',
    "notify_url" => 'https://my-notify-api.com',
    "notify_txt" => '{"Product":"iPhone 13","modelColor":"red","myStrings":"Custom Strings"}',
];
$CreateOrder = $gateway->CreateOrder($OrderData);

var_dump($CreateOrder);



$createTopUp = $gateway->createTopUp([
    "m_userid" => 'userdemo001',
    "firewall" => '2',
    "notify_url" => 'https://my-notify-api.com',
]);
var_dump($createTopUp);
