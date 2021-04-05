<?php
namespace LancerHe\RBAC\Console;

use Illuminate\Console\Command;

class RolePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'rbac:role-permission
                           {role            : id of role.}
                           {permissions?*   : id of permissions.}
                           {--attach        : Add roles for roles.}
                           {--detach        : remove roles from roles.}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $description = 'For specify role, view, add or remove role permissions.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $role = (int)$this->argument('role');
        $permissions = $this->argument('permissions');
        $attach = $this->option('attach');
        $detach = $this->option('detach');

        if (!$attach and !$detach)
        {
            $this->viewRolePermissions($role);
        } else if ($attach and $detach) {
            $this->error('detach and attach conflicts.');
        } else if ($attach) {
            $this->attachRolePermissions($role, $permissions);
        } else if ($detach) {
            $this->detachRolePermissions($role, $permissions);
        }
    }

    /**
     * View role permissions.
     *
     * @param int $role
     */
    public function viewRolePermissions(int $role)
    {
        $class = config('rbac.role_model');
        $role = call_user_func_array([$class, 'find'], [$role]);
        $permissions = $role->permissions()->get()->map(function ($item, $index){
            unset($item['pivot']);
            return $item;
        })->toArray();
        $this->info("\n $role->name has permissions as follows:\n");
        $this->table(['id', 'name', 'slug', 'prefix', 'created_at', 'updated_at'], $permissions);
    }

    /**
     * Delete role permissions.
     *
     * @param int $role user id.
     * @param array $permissions an array of role ids.
     */
    public function detachRolePermissions(int $role, array $permissions)
    {
        $class = config('rbac.role_model');
        $role = call_user_func_array([$class, 'find'], [$role]);
        $role->detachPermissions($permissions);
        $this->viewRolePermissions($role->id);
    }

    /**
     * Add permissions for role.
     *
     * @param int $role
     * @param array $permissions
     */
    public function attachRolePermissions(int $role, array $permissions)
    {
        $class = config('rbac.role_model');
        $role = call_user_func_array([$class, 'find'], [$role]);
        $role->attachPermissions($permissions);
        $this->viewRolePermissions($role->id);
    }
}