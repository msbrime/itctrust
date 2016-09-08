<?php

return [
    
    'role_structure' => [
        'superadministrator' => ["full_access","user_access","default"],
        'administrator' => ["user_access","default"],
        'user' => ["default"],
    ],

    'mandate_structure' => [

        'administrator' => ["user_access"]

    ],

    'permission_set_structure' => [
        'full_access' => [
            'users' => 'c,r,u,d',
            'acl' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'user_access' => [
            'users' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'default' => [
            'profile' => 'r,u'
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete'
    ]
];
