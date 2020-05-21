<?php

return [

    /*
     * Project`s id
     */
    'project_id' => env('MYKASSA_PROJECT_ID', ''),

    /*
     * First project`s secret key
     */
    'secret_key' => env('MYKASSA_SECRET_KEY', ''),

    /*
     * Second project`s secret key
     */
    'secret_key_second' => env('MYKASSA_SECRET_KEY_SECOND', ''),

    /*
     * Locale for payment form
     */
    'locale' => 'ru',  // ru || en

    /*
     * Allowed currenc'ies http://www.mykassa.org/page/api#currency
     *
     * If currency = null, that parameter doesn`t be setted
     */
    'currency' => null,

    /*
     * Allowed ip's http://www.mykassa.org/page/api#goto2
     */
    'allowed_ips' => [
        '144.76.93.115',
        '144.76.93.119',
        '78.47.60.198',
        '136.243.38.108',
        //
        '141.101.76.129',
        '141.101.77.139',
        '172.69.54.242',
        '172.69.54.86',
    ],

    /**
     * Skip checked allowed ips
     */
    'check_ip_skip' => false,

    /*
     *  SearchOrder
     *  Search order in the database and return order details
     *  Must return array with:
     *
     *  _orderStatus
     *  _orderSum
     */
    'searchOrder' => null, //  'App\Http\Controllers\MyKassaController@searchOrder',

    /*
     *  PaidOrder
     *  If current _orderStatus from DB != paid then call PaidOrderFilter
     *  update order into DB & other actions
     */
    'paidOrder' => null, //  'App\Http\Controllers\MyKassaController@paidOrder',

    /*
     * Customize error messages
     */
    'errors' => [
        'validateOrderFromHandle' => 'Validate Order Error',
        'searchOrder' => 'Search Order Error',
        'paidOrder' => 'Paid Order Error',
    ],

    /*
     * Url to init payment on MyKassa
     * http://www.mykassa.org/page/api#goto1
     */
    'pay_url' => 'http://www.mykassa.org/api/merchant.php',
];
