<?php
namespace LancerHe\RBAC\Traits;

use LancerHe\RBAC\Model\Permission;
use LancerHe\RBAC\Model\Role;

trait RBACUser
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            config('rbac.role_model'),
            config('rbac.table.role_users'),
            config('rbac.constraint.role_users.user_id'),
            config('rbac.constraint.role_users.role_id'));
    }


    /**
     * Many-to-Many relations with Permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            'LancerHe\RBAC\Model\Permission',
            config('rbac.table.user_permissions'),
            config('rbac.constraint.user_permissions.user_id'),
            config('rbac.constraint.user_permissions.permission_id'));
    }

    /**
     * user permissions consist of user-role-permissions and user-permissions;
     *
     * @return array
     */
    public function arrayPermissions()
    {
        $base = $this->permissions()->get()->toArray();

        foreach ($this->roles as $role)
        {
            $base = array_merge($base, $role->permissions()->get()->toArray());
        }

        return collect($base)->unique('name')->toArray();
    }

    /**
     * @param $roles string|array role name or array of role names.
     * @param bool $requireAll
     *
     * @return bool
     */
    public function hasRoles($roles, $requireAll=false)
    {
        $roles = is_array($roles) ? $roles : explode(',', $roles);

        $user_role_names = collect($this->roles()->select(['name'])->get())->pluck('name')->toArray();
        $intersect = array_intersect($roles, $user_role_names);

        return $requireAll ? count($roles) == count($intersect)
                           : count($intersect) >= (empty($roles) ? 0 : 1);
    }

    /**
     * check user whether has access to permissions;
     *
     * @param $permissions string|array permission name or array of permission names.
     * @param bool $requireAll
     *
     * @return bool
     */
    public function hasPermissions($permissions, $requireAll=false)
    {
        $permissions = is_array($permissions) ? $permissions : explode(',', $permissions);

        $user_permissions = collect($this->arrayPermissions())->pluck('name')->toArray();

        $intersect = array_intersect($permissions, $user_permissions);

        return $requireAll ? count($permissions) == count($intersect)
                           : count($intersect) >= (empty($permissions) ? 0 : 1);
    }

    /**
     * attach more than one role to user.
     *
     * @param $roles object|array|int|string
     */

    public function attachRoles($roles)
    {
        $roles = collect((array) $roles)->map(function ($role, $index){
            return (is_int($role) or is_numeric($role))
                    ? (int) $role
                    : (is_object($role)
                        ? $role->getKey()
                        : (is_array($role)
                            ? $role['id']
                            : (is_string($role)
                                ? Role::getByName($role)->getKey()
                                : -1
                            )));
        })->filter(function($role, $index) {
            return $role != -1 and ! $this->hasRoles(Role::find($role)->name);
        })->toArray();

        $this->roles()->attach($roles);
    }

    /**
     * detach more than one role from user;
     *
     * @param $roles object|array|int|string
     */

    public function detachRoles($roles)
    {
        $roles = collect((array) $roles)->map(function ($role, $index){

            return (is_int($role) or is_numeric($role))
                    ? (int) $role
                    : (is_object($role)
                        ? $role->getKey()
                        : (is_array($role)
                            ? $role['id']
                            : (is_string($role)
                                ? Role::getByName($role)->getKey()
                                : ''
                            )));

        })->filter(function ($role, $index){ return $role != -1; })->toArray();

        $this->roles()->detach($roles);
    }

    /**
     * attach more than one permission to user.
     *
     * $permissions object|array|int|string
     */

    public function attachPermissions($permissions)
    {
        $permissions = collect((array) $permissions)->map(function ($permission, $index){
            return (is_int($permission) or is_numeric($permission))
                    ? (int) $permission
                    : (is_object($permission)
                        ? $permission->getKey()
                        : (is_array($permission)
                            ? $permission['id']
                            : (is_string($permission)
                                ? Permission::getByName($permission)->getKey()
                                : -1
                            )));
        })->filter(function ($permission, $index) {
            return $permission != -1 and ! $this->hasPermissions(Permission::find($permission)->name);
        })->toArray();

        $this->permissions()->attach($permissions);
    }

    /**
     * detach more than one permission to user.
     *
     * @param $permissions object|array|int|string
     */
    public function detachPermissions($permissions)
    {
        $permissions = collect((array) $permissions)->map(function ($permission, $index){
            return (is_int($permission) or is_numeric($permission))
                    ? (int) $permission
                    : (is_object($permission)
                        ? $permission->getKey()
                        : (is_array($permission)
                            ? $permission['id']
                            : (is_string($permission)
                                ? Permission::getByName($permission)->getKey()
                                : -1
                            )));
        })->filter(function ($role, $index){ return $role != -1; })->toArray();

        $this->permissions()->detach($permissions);
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles       Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function ability($roles = [], $permissions = [], array $options = [])
    {
        $roles = is_array($roles) ? $roles : explode(',', $roles);
        $permissions = is_array($permissions) ? $permissions : explode(',', $permissions);
        $options = collect(['validate_all' => false, 'return_type' => 'boolean'])->merge($options)->all();

        $checkedRoles = $this->hasRoles($roles, $options['validate_all']);
        $checkedPermissions = $this->hasPermissions($permissions, $options['validate_all']);
        $result = $options['validate_all'] ? $checkedRoles and $checkedPermissions
                                           : $checkedRoles or $checkedPermissions;

        switch ($options['return_type'])
        {
            case 'boolean': return $result;
            case 'array'  : return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
            case 'both'   : return [$result, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
            default       : throw  new \InvalidArgumentException();
        }
    }
}