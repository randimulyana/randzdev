<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Development Admin Account
    |--------------------------------------------------------------------------
    |
    | Credentials used by the AdminUserSeeder to create a local admin account.
    | The seeder must be run explicitly, refuses to run in production, and
    | skips account creation when no password has been configured.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrator'),

    'email' => env('ADMIN_EMAIL', 'admin@randz.dev'),

    'password' => env('ADMIN_PASSWORD'),

];
