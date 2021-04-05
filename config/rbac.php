<?php

return [
    /**
     * ---------------------------------
     *           RBAC tables
     * ---------------------------------
     *
     * the blow are name of rbac tables.
     *
     */
    'table' => [

        'users' => 'users',

        'roles' => 'roles',

        'permissions' => 'permissions',

        'role_users'  => 'role_users',

        'role_permissions' => 'role_permissions',

        'user_permissions' => 'user_permissions',
    ],

    /**
     * ----------------------------------
     *         specify user model
     * ----------------------------------
     *
     */
    'user_model' => 'App\User',

    /**
     * ----------------------------------
     *         specify permission model
     * ----------------------------------
     *
     */
    'permission_model' => 'LancerHe\RBAC\Model\Permission',

    /**
     * ----------------------------------
     *         specify role model
     * ----------------------------------
     *
     */
    'role_model' => 'LancerHe\RBAC\Model\Role',

    /**
     * ----------------------------------
     *          specify constraint
     * ----------------------------------
     */
    'constraint' => [

        'role_users' => [

            'user_id' => 'user_id',

            'role_id' => 'role_id'
        ],

        'role_permissions' => [

            'role_id' => 'role_id',

            'permission_id' => 'permission_id',
        ],

        'user_permissions' => [

            'user_id' => 'user_id',

            'permission_id' => 'permission_id',
        ],
    ]
];