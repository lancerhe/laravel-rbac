<?php
namespace LancerHe\RBAC\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function __construct(array $attributes = [])
    {
        $this->table = config('rbac.table.roles');

        parent::__construct($attributes);
    }

    public function users()
    {
        return $this->belongsToMany(
            config('rbac.user_model'),
            config('rbac.table.role_users'),
            config('rbac.constraint.role_users.role_id'),
            config('rbac.constraint.role_users.user_id'));
    }

    public function permissions()
    {
        return $this->belongsToMany(
            config('rbac.permission_model'),
            config('rbac.table.role_permissions'),
            config('rbac.constraint.role_permissions.role_id'),
            config('rbac.constraint.role_permissions.permission_id'));
    }

    /**
     * @param $role string  role name;
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getByName($role)
    {
        return Role::where('name', $role)->first();
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

        $user_permissions = collect($this->permissions()->select(['name'])->get())->pluck('name')->toArray();

        $intersect = array_intersect($permissions, $user_permissions);

        return $requireAll ? count($permissions) == count($intersect)
            : count($intersect) >= (empty($permissions) ? 0 : 1);
    }

    /**
     * attach more than one permission to user.
     *
     * @param $permissions object|array|int|string
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
}