<?php
namespace LancerHe\RBAC\Console;

use Illuminate\Console\Command;


class UserPermissionCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'rbac:user-permission
                           {user            : id of user.}
                           {permissions?*   : id of permissions.}
                           {--attach        : Add roles for users.}
                           {--detach        : remove roles from users.}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $description = 'For specify user, view, add or remove user permissions.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = (int)$this->argument('user');
        $permissions = $this->argument('permissions');
        $attach = $this->option('attach');
        $detach = $this->option('detach');
        if (!$attach and !$detach)
        {
            $this->viewUserPermissions($user);
        } else if ($attach and $detach) {
            $this->error('detach and attach conflicts.');
        } else if ($attach) {
            $this->attachUserPermissions($user, $permissions);
        } else if ($detach) {
            $this->detachUserPermissions($user, $permissions);
        }
    }

    /**
     * View user permissions.
     *
     * @param int $user
     */
    public function viewUserPermissions(int $user)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $permissions = $user->permissions()->get()->map(function ($item, $index){
            unset($item['pivot']);
            return $item;
        })->toArray();
        $this->info("\n $user->name has permissions as follows:\n");
        $this->table(['id', 'name', 'slug', 'prefix', 'created_at', 'updated_at'], $permissions);
    }

    /**
     * Delete user permissions.
     *
     * @param int $user user id.
     * @param array $permissions an array of user ids.
     */
    public function detachUserPermissions(int $user, array $permissions)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $user->detachPermissions($permissions);
        $this->viewUserPermissions($user->id);
    }

    /**
     * Add permissions for user.
     *
     * @param int $user
     * @param array $permissions
     */
    public function attachUserPermissions(int $user, array $permissions)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $user->attachPermissions($permissions);
        $this->viewUserPermissions($user->id);
    }
}