<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Iyzico Keys
    |--------------------------------------------------------------------------
    |
    | The Iyzico publishable API key and secret key give you access to Iyzico's
    | API.
    |
    */

    'key' => env('IYZICO_KEY'),

    'secret' => env('IYZICO_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cashier Base Url
    |--------------------------------------------------------------------------
    |
    | This is the base URL where Kasiyer sends requests to iyzico.
    |
    */

    'base_url' => env('KASIYER_URL', 'https://sandbox-api.iyzipay.com'),

    /*
    |--------------------------------------------------------------------------
    | Kasiyer Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait
    | provided by Kasiyer. It will serve as the primary model you use while
    | interacting with Kasiyer related methods, subscriptions, and so on.
    |
    */

    'model' => env('KASIYER_MODEL', App\User::class),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when generating charges
    | from your application. Of course, you are welcome to use any of the
    | various world currencies that are currently supported via Iyzico.
    |
    */

    'currency' => env('KASIYER_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale in which your money values are formatted in
    | for display. To utilize other locales besides the default en locale
    | verify you have the "intl" PHP extension installed on the system.
    |
    */

    'currency_locale' => env('KASIYER_CURRENCY_LOCALE', 'en'),

];
