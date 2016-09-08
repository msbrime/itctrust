<?php

/**
 * This file is part of Laratrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Laratrust
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Laratrust Role Model
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Laratrust to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
    'role' => 'App\Role',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Roles Table
    |--------------------------------------------------------------------------
    |
    | This is the roles table used by Laratrust to save roles to the database.
    |
    */
    'roles_table' => 'roles',


    /*
    |--------------------------------------------------------------------------
    | Laratrust Role Model
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Laratrust to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
    'mandate' => 'App\Mandate',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Roles Table
    |--------------------------------------------------------------------------
    |
    | This is the roles table used by Laratrust to save roles to the database.
    |
    */
    'mandates_table' => 'mandates',


    /*
    |--------------------------------------------------------------------------
    | Laratrust PermissionSet Model
    |--------------------------------------------------------------------------
    |
    | This is the PermissionSet model used by Laratrust to create correct relations.  Update
    | the role if it is in a different namespace.
    |
    */
    'permission_set' => 'App\PermissionSet',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Permission Sets Table
    |--------------------------------------------------------------------------
    |
    | This is the roles table used by Laratrust to save roles to the database.
    |
    */
    'permission_sets_table' => 'perm_sets',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Permission Model
    |--------------------------------------------------------------------------
    |
    | This is the Permission model used by Laratrust to create correct relations.
    | Update the permission if it is in a different namespace.
    |
    */
    'permission' => 'App\Permission',

    /*
    |--------------------------------------------------------------------------
    | Laratrust Permissions Table
    |--------------------------------------------------------------------------
    |
    | This is the permissions table used by Laratrust to save permissions to the
    | database.
    |
    */
    'permissions_table' => 'permissions',

    /*
    |--------------------------------------------------------------------------
    | Laratrust permission_role Table
    |--------------------------------------------------------------------------
    |
    | This is the permission_role table used by Laratrust to save relationship
    | between permissions and roles to the database.
    |
    */
    'permission_permission_set_table' => 'perm_perm_set',


    /*
    |--------------------------------------------------------------------------
    | Laratrust permission_set_role Table
    |--------------------------------------------------------------------------
    |
    | This is the permission_set_role table used by Laratrust to save relationship
    | between permissions and roles to the database.
    |
    */
    'permission_set_role_table' => 'perm_set_role',

    /*
    |--------------------------------------------------------------------------
    | Laratrust mandate_permission_set_table Table
    |--------------------------------------------------------------------------
    |
    | This is the mandate_permission_set_table table used by Laratrust to save relationship
    | between permissions and roles to the database.
    |
    */
    'mandate_permission_set_table' => 'mandate_perm_set',

    /*
    |--------------------------------------------------------------------------
    | Laratrust role_user Table
    |--------------------------------------------------------------------------
    |
    | This is the role_user table used by Laratrust to save assigned roles to the
    | database.
    |
    */
    'role_user_table' => 'role_user',

    /*
    |--------------------------------------------------------------------------
    | Laratrust mandate_user Table
    |--------------------------------------------------------------------------
    |
    | This is the role_user table used by Laratrust to save assigned roles to the
    | database.
    |
    */
    'mandate_user_table' => 'mandate_user',

    /*
    |--------------------------------------------------------------------------
    | User Foreign key on Laratrust's role_user Table (Pivot)
    |--------------------------------------------------------------------------
    */
    'user_foreign_key' => 'user_id',

    /*
    |--------------------------------------------------------------------------
    | Role Foreign key on Laratrust's role_user and permission_set_role Tables (Pivot)
    |--------------------------------------------------------------------------
    */
    'role_foreign_key' => 'role_id',

    /*
    |--------------------------------------------------------------------------
    | Mandates Foreign key on Laratrust's role_user and permission_set_role Tables (Pivot)
    |--------------------------------------------------------------------------
    */
    'mandate_foreign_key' => 'mandate_id',

    /*
    |--------------------------------------------------------------------------
    | Permission Foreign key on Laratrust's permission_set_role and permission_permission_set Table (Pivot)
    |--------------------------------------------------------------------------
    */
    'permission_foreign_key' => 'permission_id',

    /*
    |--------------------------------------------------------------------------
    | PermissionSet Foreign key on Laratrust's permission_set_role and permission_permission_set Table (Pivot)
    |--------------------------------------------------------------------------
    */
    'permission_set_foreign_key' => 'permission_set_id',
    
    /*
    |--------------------------------------------------------------------------
    | Method to be called in the middleware return case
    | Available: abort|redirect
    |--------------------------------------------------------------------------
    */
    'middleware_handling' => 'abort',

    /*
    |--------------------------------------------------------------------------
    | Parameter passed to the middleware_handling method
    |--------------------------------------------------------------------------
    */
    'middleware_params' => '403',
];
