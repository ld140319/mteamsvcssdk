<?php

/**
 * key is class name
 */
return [
    'zipkinlogpath' => '@@ZIPKIN_LOG_PATH@@',
    'notificationservice' => '@@NOTIFICATION_SERVICE_URL@@',
    'callbackservice' => '',
    'hotelService' => '',
    'userservice' => [
        'host' => '@@USER_CENTER_URL@@',
        'appId' => '1000',
        'storeId' => '4'
    ],
   'useropspservice' => [
        'host' => '@@USEROPSP_SERVICES_URL@@'
   ],
    'reviewservice' => [
        'host' => '@@REVIEWSVCS_SERVICES_URL@@'
    ],
    'productservice' => [
        'host' => '@@ACTIVITY_PRODUCT_URL@@',
    ],
    'priceservice' => [
        'host' => '@@PRICE_POLICY_URL@@',
    ],
    'tagservice' => [
        'host' => '@@OPSP_SERVICES_URL@@'
    ],
    'fileservice' => [
        'host' => '@@FILESVCS_API_URL@@',
    ],
    'usernotificationservice' => [
        'host' => '@@USER_CENTER_URL@@'
    ],
    'paymentservice' => [
        'service_host' => '@@PAYMENT_SERVICES_URL@@',
        'pay_host' => '@@PAYMENT_SITE_URL@@'
    ],
    'mobileservice' => [
        'host' => '@@MOBILESVCS_SERVICES_URL@@'
    ],
    'financeservice' => [
        'host' => '@@FINANCE_SERVICES_URL@@',
    ],
    'orderservice' => [
        'host' => '@@API_ORDER_SERVICES_URL@@',
    ],
    'paservice' => [
        'host' => '@@PROVIDER_SERVICES_URL@@',
    ],
    'productcenterservice' => [
        'host' => '@@ALL_PRODUCT_URL@@'
    ],
    'riskservice' => [
        'host' => '@@RISK_SERVICES_URL@@'
    ],
    'airportservice' => [
        'host' => '@@AIRPORT_SERVICES_URL@@'
    ],
    'stockservice' => [
        'host' => '@@STOCK_SERVICES_URL@@'
    ],
    'workservice' => [
        'host' => '@@WORKORDER_SERVICES_URL@@',
    ],
    'cpsservice' => [
        'host' => '@@DISTRIBUTION_SERVICES_URL@@'
    ],
    'searchservice' => [
        'host' => '@@SEARCH_SERVICES_URL@@'
    ],
    'couponservice' => [
        'host' => '@@PRICE_POLICY_URL@@'
    ],
    'alarmservice' => [
        'host' => '@@ALARM_SERVICES_URL@@'
    ],
    'cartservice' => [
        'host' => '@@OPSP_SERVICES_URL@@'
    ],
    'recommendationservice' => [
        'host' => '@@RECOM_SERVICES_URL@@'
    ],
    'opspservice' => [
        'host' => '@@OPSP_SERVICES_URL@@'
    ],
    'optionservice' => [
        'host' => '@@OPTION_SERVICES_URL@@'
    ],
    'insuranceservice' => [
        'host' => '@@INSURANCE_SERVICES_URL@@'
    ],
    'productcenterv1service' => [
        'host' => '@@ALL_PRODUCT_URL@@'
    ],
    'pricecenterservice' => [
        'host' => '@@PRICE_POLICY_URL@@'
    ],
    'redis' => [
        'scheme' => 'tcp',
        'host' => '@@REDIS_HOST@@',
        'port' => '@@REDIS_PORT@@'
    ],
    'walletservice' => [
        'host' => '@@WALLET_SERVICES_URL@@'
    ],
    'invoiceservice' => [
        'host' => '@@OPSP_SERVICES_URL@@'
    ],
    'factoryservice' => [
        'host' => '@@URL_PRODUCT_FACTORY@@'
    ]
];
