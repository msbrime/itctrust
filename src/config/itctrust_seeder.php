<?php

return [
    
    'role_structure' => [
        'superadministrator' => ["full_access","data_entry","default"],
        'administrator' => ["data_entry","default"],
        'user' => ["default"],
    ],

    'permission_set_structure' => [
        'full_access' => [
            'users' => 'c,r,u,d',
            'acl' => 'c,r,u,d',
            'profile' => 'r,u'
        ],
        'data_entry' => [
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
