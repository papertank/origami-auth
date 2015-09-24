<?php
return array(

    /*
    |--------------------------------------------------------------------------
    | Default Models
    |--------------------------------------------------------------------------
    |
    | This option controls the default models that are set up with permissions
    | for list, create, update and destroy
    |
    */

    'models' => [
        \App\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Permissions
    |--------------------------------------------------------------------------
    |
    | This option controls the default permissions that are setup
    |
    */

    'permissions' => [

        'access-admin' => [
            'name' => 'access-admin',
            'label' => 'Access admin'
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    | Default Roles
    |
    | This option controls the mapping of roles to permissions.
    |
    */

    'roles' => [

        'admin' => [
            'name' => 'admin',
            'label' => 'Admin User',
            'permissions' => [
                \App\User::class => [
                    'list',
                    'create',
                    'update',
                    'destroy'
                ],
                'access-admin'
            ],
        ],

    ],

);