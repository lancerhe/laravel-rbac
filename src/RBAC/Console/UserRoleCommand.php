<?php
namespace LancerHe\RBAC\Console;

use Illuminate\Console\Command;

class UserRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:user-role
                           {user     : id of user.}
                           {roles?*   : id of roles.}
                           {--attach : Add roles for users.}
                           {--detach : remove roles from users.}';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $description = 'For specify user, view, add or remove user roles.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = (int)$this->argument('user');
        $roles = $this->argument('roles');
        $attach = $this->option('attach');
        $detach = $this->option('detach');
        if (!$attach and !$detach)
        {
            $this->viewUserRoles($user);
        } else if ($attach and $detach) {
            $this->error('detach and attach conflicts.');
        } else if ($attach) {
            $this->attachUserRoles($user, $roles);
        } else if ($detach) {
            $this->detachUserRoles($user, $roles);
        }
    }

    /**
     * View user roles.
     *
     * @param  $user int  user id.
     */
    public function viewUserRoles(int $user)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $roles = $user->roles()->get()->map(function ($item, $index){
            unset($item['pivot']);
            return $item;
        })->toArray();
        $this->info("\n $user->name has roles as follows:\n");
        $this->table(['id', 'name', 'slug', 'created_at', 'updated_at'], $roles);
    }

    /**
     * Delete user role.
     *
     * @param int $user user id.
     * @param array $roles an array of role ids.
     */
    public function detachUserRoles(int $user, array $roles)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $user->detachRoles($roles);
        $this->viewUserRoles($user->id);
    }

    /**
     * Add role for user.
     *
     * @param int $user
     * @param array $roles
     */
    public function attachUserRoles(int $user, array $roles)
    {
        $class = config('rbac.user_model');
        $user = call_user_func_array([$class, 'find'], [$user]);
        $user->attachRoles($roles);
        $this->viewUserRoles($user->id);
    }
}