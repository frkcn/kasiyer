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
    | Kasiyer Base Url
    |--------------------------------------------------------------------------
    |
    | This is the base URL where Kasiyer sends requests to iyzico.
    |
    */

    'base_url' => env('KASIYER_URL', 'https://sandbox-api.iyzipay.com'),

    /*
    |--------------------------------------------------------------------------
    | Kasiyer Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Kasiyer's views, such as the webhook
    | route, will be available. You're free to tweak this path based on
    | the needs of your particular application or design preferences.
    |
    */

    'path' => env('KASIYER_PATH', 'iyzico'),

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

    'currency' => env('KASIYER_CURRENCY', 'TRY'),

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

    'locale' => env('KASIYER_LOCALE', 'tr'),

];
