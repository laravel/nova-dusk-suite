<?php

use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Http\Middleware\Authorize;
use Laravel\Nova\Http\Middleware\BootTools;
use Laravel\Nova\Http\Middleware\DispatchServingNovaEvent;
use Laravel\Nova\Http\Middleware\HandleInertiaRequests;

return [

    /*
    |--------------------------------------------------------------------------
    | Nova App Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to display the name of the application within the UI
    | or in other locations. Of course, you're free to change the value.
    |
    */

    'name' => env('NOVA_APP_NAME', env('APP_NAME')),

    /*
    |--------------------------------------------------------------------------
    | Nova Domain Name
    |--------------------------------------------------------------------------
    |
    | This value is the "domain name" associated with your application. This
    | can be used to prevent Nova's internal routes from being registered
    | on subdomains which do not need access to your admin application.
    |
    */

    'domain' => env('NOVA_DOMAIN_NAME', null),

    /*
    |--------------------------------------------------------------------------
    | Nova Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Nova will be accessible from. Feel free to
    | change this path to anything you like. Note that this URI will not
    | affect Nova's internal API routes which aren't exposed to users.
    |
    */

    'path' => '/nova',

    /*
    |--------------------------------------------------------------------------
    | Nova Authentication Guard
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the authentication guard that will
    | be used to protect your Nova routes. This option should match one
    | of the authentication guards defined in the "auth" config file.
    |
    */

    'guard' => env('NOVA_GUARD', null),

    /*
    |--------------------------------------------------------------------------
    | Nova Password Reset Broker
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the password broker that will be
    | used when passwords are reset. This option should mirror one of
    | the password reset options defined in the "auth" config file.
    |
    */

    'passwords' => env('NOVA_PASSWORDS', null),

    /*
    |--------------------------------------------------------------------------
    | Nova Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Nova route, giving you the
    | chance to add your own middleware to this stack or override any of
    | the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        'web',
        HandleInertiaRequests::class,
        DispatchServingNovaEvent::class,
        BootTools::class,
    ],

    'api_middleware' => [
        'nova',
        Authenticate::class,
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova Pagination Type
    |--------------------------------------------------------------------------
    |
    | This option defines the visual style used in Nova's resource pagination
    | views. You may select between "simple", "load-more", and "links" for
    | your applications. Feel free to adjust this option to your choice.
    |
    */

    'pagination' => 'simple',

    /*
    |--------------------------------------------------------------------------
    | Nova Action Resource Class
    |--------------------------------------------------------------------------
    |
    | This configuration option allows you to specify a custom resource class
    | to use instead of the type that ships with Nova. You may use this to
    | define any extra form fields or other custom behavior as required.
    |
    */

    'actions' => [
        'resource' => ActionResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova Storage Disk
    |--------------------------------------------------------------------------
    |
    | This configuration option allows you to define the default driver
    | used to storing file related fields, based on filesystems base
    | configuration in your Laravel application.
    |
     */

    'storage_disk' => env('NOVA_STORAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Nova Currency
    |--------------------------------------------------------------------------
    |
    | This configuration option allows you to define the default currency
    | used by the Currency field within Nova. You may change this to a
    | valid ISO 4217 currency code to suit your application's needs.
    |
    */

    'currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Nova Authentication Routes
    |--------------------------------------------------------------------------
    |
    */

    'routes' => [
        'login' => false,
        'logout' => false,
        'forgot_password' => false,
        'reset_password' => false,
    ],
];
